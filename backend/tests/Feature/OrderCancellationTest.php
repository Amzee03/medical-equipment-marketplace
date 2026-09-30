<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\EquipmentUnit;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RentalItem;
use App\Services\RentalAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancelling_order_frees_up_rental_unit()
    {
        // 1. Setup Data
        $user = User::factory()->create(['ktp_status' => 'approved']);
        $category = Category::create([
            'name' => 'Alat Terapi',
            'slug' => 'alat-terapi',
            'status' => 'active',
        ]);

        $product = Product::create([
            'category_id'             => $category->id,
            'name'                    => 'Mesin X-Ray',
            'slug'                    => 'mesin-x-ray',
            'sku'                     => 'XRAY-001',
            'condition'               => 'baru',
            'purchase_available'      => false,
            'rental_available'        => true,
            'rental_price_daily'      => 100000,
            'min_rental_days'         => 1,
            'max_rental_days'         => 10,
            'status'                  => 'active',
        ]);

        $unit = EquipmentUnit::create([
            'product_id' => $product->id,
            'unit_code' => 'UNIT-1',
            'status' => 'available',
            'condition' => 'baik',
        ]);

        $startDate = Carbon::tomorrow()->format('Y-m-d');
        $endDate = Carbon::tomorrow()->addDays(2)->format('Y-m-d');

        // Create Order manually
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-R-123',
            'order_type' => 'rental',
            'status' => 'paid',
            'subtotal' => 200000,
            'shipping_cost' => 0,
            'total' => 200000,
            'shipping_method' => 'regular',
            'shipping_recipient_name' => 'John Doe',
            'shipping_phone' => '08123456789',
            'shipping_full_address' => 'Jl. Test No 1',
            'shipping_city' => 'Jakarta',
            'shipping_province' => 'DKI Jakarta',
            'shipping_postal_code' => '12345',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 200000,
            'subtotal' => 200000,
        ]);

        RentalItem::create([
            'order_item_id' => $orderItem->id,
            'equipment_unit_id' => $unit->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'rental_due_date' => $endDate,
            'rental_status' => 'confirmed',
        ]);

        // 2. Check Availability BEFORE cancel
        $rentalService = app(RentalAvailabilityService::class);
        $availabilityBefore = $rentalService->checkAvailability($product->id, $startDate, $endDate);
        $this->assertFalse($availabilityBefore['available']);

        // 3. User cancels the order
        $response = $this->actingAs($user, 'sanctum')->postJson("/api/orders/{$order->id}/cancel", [
            'cancellation_reason' => 'Berubah pikiran'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('cancelled', $order->fresh()->status);

        // 4. Check Availability AFTER cancel
        $availabilityAfter = $rentalService->checkAvailability($product->id, $startDate, $endDate);
        $this->assertTrue($availabilityAfter['available']);
        $this->assertEquals(1, $availabilityAfter['available_units_count']);
    }

    public function test_user_cannot_access_or_cancel_other_users_order()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $orderA = Order::create([
            'user_id' => $userA->id,
            'order_number' => 'ORD-A',
            'order_type' => 'purchase',
            'status' => 'pending_payment',
            'subtotal' => 100,
            'shipping_cost' => 0,
            'total' => 100,
            'shipping_method' => 'regular',
            'shipping_recipient_name' => 'John Doe',
            'shipping_phone' => '08123456789',
            'shipping_full_address' => 'Jl. Test No 1',
            'shipping_city' => 'Jakarta',
            'shipping_province' => 'DKI Jakarta',
            'shipping_postal_code' => '12345',
        ]);

        // User B tries to view Order A
        $responseShow = $this->actingAs($userB, 'sanctum')->getJson("/api/orders/{$orderA->id}");
        $responseShow->assertStatus(403);

        // User B tries to cancel Order A
        $responseCancel = $this->actingAs($userB, 'sanctum')->postJson("/api/orders/{$orderA->id}/cancel", [
            'cancellation_reason' => 'Hacker'
        ]);
        $responseCancel->assertStatus(403);
    }

    public function test_cancelling_confirmed_rental_order_success()
    {
        $user = User::factory()->create(['ktp_status' => 'approved']);
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-R-CONFIRMED',
            'order_type' => 'rental',
            'status' => 'confirmed',
            'subtotal' => 200000,
            'shipping_cost' => 0,
            'total' => 200000,
            'shipping_method' => 'regular',
            'shipping_recipient_name' => 'John Doe',
            'shipping_phone' => '08123456789',
            'shipping_full_address' => 'Jl. Test No 1',
            'shipping_city' => 'Jakarta',
            'shipping_province' => 'DKI Jakarta',
            'shipping_postal_code' => '12345',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/orders/{$order->id}/cancel", [
            'cancellation_reason' => 'Batal sewa'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_admin_cannot_update_invalid_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::create([
            'user_id' => User::factory()->create()->id,
            'order_number' => 'ORD-R-ADMIN',
            'order_type' => 'rental',
            'status' => 'paid',
            'subtotal' => 200000,
            'shipping_cost' => 0,
            'total' => 200000,
            'shipping_method' => 'regular',
            'shipping_recipient_name' => 'John Doe',
            'shipping_phone' => '08123456789',
            'shipping_full_address' => 'Jl. Test No 1',
            'shipping_city' => 'Jakarta',
            'shipping_province' => 'DKI Jakarta',
            'shipping_postal_code' => '12345',
        ]);

        $response = $this->actingAs($admin, 'sanctum')->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'status_ngasal_xyz'
        ]);

        $response->assertStatus(422);
        $this->assertNotEquals('status_ngasal_xyz', $order->fresh()->status);
    }
}
