<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\EquipmentUnit;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CheckoutRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_users_checkout_same_rental_unit()
    {
        // 1. Setup
        $category = Category::create([
            'name' => 'Alat Terapi',
            'slug' => 'alat-terapi',
            'status' => 'active',
        ]);

        $product = Product::create([
            'category_id'             => $category->id,
            'name'                    => 'Mesin X-Ray',
            'slug'                    => 'mesin-x-ray',
            'sku'                     => 'XRAY-002',
            'condition'               => 'baru',
            'purchase_available'      => false,
            'rental_available'        => true,
            'rental_price_daily'      => 100000,
            'min_rental_days'         => 1,
            'max_rental_days'         => 10,
            'shipping_owner_delivery' => true,
            'status'                  => 'active',
        ]);

        // Create exactly ONE available unit
        EquipmentUnit::create([
            'product_id' => $product->id,
            'unit_code' => 'UNIT-XRAY-2',
            'status' => 'available',
            'condition' => 'baik',
        ]);

        // User 1
        $user1 = User::factory()->create(['ktp_status' => 'approved']);
        $address1 = Address::create([
            'user_id' => $user1->id,
            'label' => 'Rumah',
            'recipient_name' => 'User 1',
            'phone' => '08123456789',
            'full_address' => 'Jl. A No 1',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '12345',
            'is_default' => true,
        ]);
        
        $cart1 = clone $user1->cart()->firstOrCreate();
        $cart1->items()->create([
            'product_id' => $product->id,
            'type' => 'rental',
            'quantity' => 1,
            'rental_start_date' => Carbon::tomorrow()->format('Y-m-d'),
            'rental_end_date' => Carbon::tomorrow()->addDays(2)->format('Y-m-d'),
        ]);

        // User 2
        $user2 = User::factory()->create(['ktp_status' => 'approved']);
        $address2 = Address::create([
            'user_id' => $user2->id,
            'label' => 'Rumah',
            'recipient_name' => 'User 2',
            'phone' => '08123456789',
            'full_address' => 'Jl. B No 2',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '12345',
            'is_default' => true,
        ]);
        
        $cart2 = clone $user2->cart()->firstOrCreate();
        $cart2->items()->create([
            'product_id' => $product->id,
            'type' => 'rental',
            'quantity' => 1,
            'rental_start_date' => Carbon::tomorrow()->format('Y-m-d'),
            'rental_end_date' => Carbon::tomorrow()->addDays(2)->format('Y-m-d'),
        ]);

        $payload1 = [
            'address_id' => $address1->id,
            'shipping_method' => 'owner_delivery',
            'rental_agreement_accepted' => true,
        ];
        
        $payload2 = [
            'address_id' => $address2->id,
            'shipping_method' => 'owner_delivery',
            'rental_agreement_accepted' => true,
        ];

        // 2. User 1 Checkouts
        $response1 = $this->actingAs($user1, 'sanctum')->postJson('/api/checkout', $payload1);
        $response1->assertStatus(201);
        
        // Assert User 1 has orders and rental items
        $this->assertEquals(1, $user1->orders()->count());
        $order1 = $user1->orders()->first();
        $this->assertEquals('rental', $order1->order_type);
        $this->assertIsInt($response1->json('payment.amount'));

        // 3. User 2 Checkouts right after User 1
        $response2 = $this->actingAs($user2, 'sanctum')->postJson('/api/checkout', $payload2);
        
        // Assert User 2 fails
        $response2->assertStatus(422);
        
        // Assert DB is clean for User 2 (Rollback works)
        $this->assertEquals(0, $user2->orders()->count());
    }
}
