<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KtpReviewTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(User $user, string $status): Order
    {
        return Order::create([
            'user_id' => $user->id, 'order_number' => 'ORD-KTP-' . rand(100, 999),
            'order_type' => 'rental', 'status' => $status,
            'subtotal' => 100000, 'shipping_cost' => 0, 'total' => 100000,
            'shipping_method' => 'regular', 'shipping_recipient_name' => 'Test',
            'shipping_phone' => '08123456789', 'shipping_full_address' => 'Jl Test',
            'shipping_city' => 'Jakarta', 'shipping_province' => 'DKI', 'shipping_postal_code' => '12345',
        ]);
    }

    public function test_approve_ktp_also_confirms_awaiting_orders()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['ktp_status' => 'pending_review']);

        $pendingOrder = $this->makeOrder($user, 'awaiting_ktp_verification');

        $response = $this->actingAs($admin, 'sanctum')->postJson("/api/admin/users/{$user->id}/ktp/approve");

        $response->assertStatus(200);
        $this->assertEquals('approved', $user->fresh()->ktp_status);
        $this->assertEquals('confirmed', $pendingOrder->fresh()->status);
    }

    public function test_reject_ktp_sets_rejection_reason()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['ktp_status' => 'pending_review']);

        $response = $this->actingAs($admin, 'sanctum')->postJson("/api/admin/users/{$user->id}/ktp/reject", [
            'rejection_reason' => 'Foto KTP buram'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('rejected', $user->fresh()->ktp_status);
        $this->assertEquals('Foto KTP buram', $user->fresh()->ktp_rejection_reason);
    }
}
