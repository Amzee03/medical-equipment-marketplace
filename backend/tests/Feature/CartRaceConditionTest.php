<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\EquipmentUnit;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CartRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_add_to_cart_requests_for_last_unit_rejects_second_request()
    {
        // 1. Setup
        $user = User::factory()->create();
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
            'shipping_owner_delivery' => true,
            'shipping_express'        => false,
            'shipping_regular'        => false,
            'shipping_pickup'         => false,
            'status'                  => 'active',
        ]);

        // Create exactly ONE available unit
        EquipmentUnit::create([
            'product_id' => $product->id,
            'unit_code' => 'UNIT-XRAY-1',
            'status' => 'available',
            'condition' => 'baik',
        ]);

        $payload = [
            'product_id' => $product->id,
            'type' => 'rental',
            'quantity' => 1,
            'rental_start_date' => Carbon::tomorrow()->format('Y-m-d'),
            'rental_end_date' => Carbon::tomorrow()->addDays(2)->format('Y-m-d'),
        ];

        // 2. Request pertama (berhasil karena available unit = 1)
        $response1 = $this->actingAs($user, 'sanctum')->postJson('/api/cart/items', $payload);
        $response1->assertStatus(200);

        // 3. Request kedua (harus ditolak karena quantity di cart menjadi 2, sementara unit cuma 1)
        $response2 = $this->actingAs($user, 'sanctum')->postJson('/api/cart/items', $payload);
        
        // Assert gagal
        $response2->assertStatus(422);
        $this->assertEquals('Unit tidak mencukupi untuk periode yang dipilih.', $response2->json('message'));
    }
}
