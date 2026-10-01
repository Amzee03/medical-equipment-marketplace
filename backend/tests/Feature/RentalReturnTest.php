<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DamageReport;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RentalItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalReturnTest extends TestCase
{
    use RefreshDatabase;

    private function makeRentalSetup(User $user, string $startDate, string $endDate, string $dueDate, string $rentalStatus = 'confirmed'): array
    {
        $category = Category::create(['name' => 'Alat', 'slug' => 'alat', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Nebulizer', 'slug' => 'nebulizer',
            'sku' => 'NEB-01', 'condition' => 'baru', 'purchase_available' => false,
            'rental_available' => true, 'rental_price_daily' => 50000,
            'min_rental_days' => 1, 'max_rental_days' => 30, 'status' => 'active',
        ]);
        $unit = EquipmentUnit::create([
            'product_id' => $product->id, 'unit_code' => 'NEB-UNIT-1',
            'status' => 'rented', 'condition' => 'baik',
        ]);
        $order = Order::create([
            'user_id' => $user->id, 'order_number' => 'ORD-R-TEST',
            'order_type' => 'rental', 'status' => 'active',
            'subtotal' => 100000, 'shipping_cost' => 0, 'total' => 100000,
            'shipping_method' => 'regular', 'shipping_recipient_name' => 'Test',
            'shipping_phone' => '08123456789', 'shipping_full_address' => 'Jl Test',
            'shipping_city' => 'Jakarta', 'shipping_province' => 'DKI', 'shipping_postal_code' => '12345',
        ]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id,
            'quantity' => 1, 'unit_price' => 100000, 'subtotal' => 100000,
        ]);
        $rentalItem = RentalItem::create([
            'order_item_id' => $orderItem->id, 'equipment_unit_id' => $unit->id,
            'start_date' => $startDate, 'end_date' => $endDate,
            'rental_due_date' => $dueDate, 'rental_status' => $rentalStatus,
        ]);

        return compact('product', 'unit', 'order', 'orderItem', 'rentalItem');
    }

    public function test_overdue_command_marks_past_due_rentals()
    {
        $user = User::factory()->create();
        $pastDue = Carbon::yesterday()->toDateString();
        $setup = $this->makeRentalSetup($user, Carbon::now()->subDays(5)->toDateString(), $pastDue, $pastDue, 'confirmed');

        $this->artisan('rentals:update-overdue')->assertSuccessful();

        $this->assertEquals('overdue', $setup['rentalItem']->fresh()->rental_status);
    }

    public function test_return_with_late_fee_calculated_correctly()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        // due date 3 days ago
        $dueDate = Carbon::now()->subDays(3)->toDateString();
        $setup = $this->makeRentalSetup($user, Carbon::now()->subDays(5)->toDateString(), $dueDate, $dueDate, 'overdue');

        $response = $this->actingAs($admin, 'sanctum')->postJson("/api/admin/rental-items/{$setup['rentalItem']->id}/return", [
            'condition_at_return' => 'baik',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['late_days' => 3]);
        // late_fee = 3 * 50000
        $response->assertJsonFragment(['late_fee' => 150000.0]);

        $this->assertEquals('available', $setup['unit']->fresh()->status);
        $this->assertEquals('returned', $setup['rentalItem']->fresh()->rental_status);
    }

    public function test_return_with_damage_creates_damage_report_and_sets_unit_maintenance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $dueDate = Carbon::tomorrow()->toDateString();
        $setup = $this->makeRentalSetup($user, Carbon::yesterday()->toDateString(), $dueDate, $dueDate, 'active');

        $response = $this->actingAs($admin, 'sanctum')->postJson("/api/admin/rental-items/{$setup['rentalItem']->id}/return", [
            'condition_at_return' => 'rusak_berat',
            'notes' => 'Layar retak parah',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('maintenance', $setup['unit']->fresh()->status);
        $this->assertEquals('returned', $setup['rentalItem']->fresh()->rental_status);
        $this->assertDatabaseHas('damage_reports', [
            'rental_item_id' => $setup['rentalItem']->id,
            'condition' => 'rusak_berat',
            'status' => 'pending',
        ]);
    }

    public function test_order_becomes_completed_when_all_rental_items_returned()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $dueDate = Carbon::tomorrow()->toDateString();
        $setup = $this->makeRentalSetup($user, Carbon::yesterday()->toDateString(), $dueDate, $dueDate, 'active');

        $this->actingAs($admin, 'sanctum')->postJson("/api/admin/rental-items/{$setup['rentalItem']->id}/return", [
            'condition_at_return' => 'baik',
        ])->assertStatus(200);

        $this->assertEquals('completed', $setup['order']->fresh()->status);
    }
}
