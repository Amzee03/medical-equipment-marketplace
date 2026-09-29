<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRentalValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_product_with_rental_available_true_without_rental_fields_fails_422()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create([
            'name' => 'Alat Diagnostik',
            'slug' => 'alat-diagnostik',
            'status' => 'active',
        ]);

        $payload = [
            'category_id'             => $category->id,
            'name'                    => 'Mesin USG Portable',
            'sku'                     => 'USG-PORT-001',
            'condition'               => 'baru',
            'purchase_available'      => false,
            'rental_available'        => true,
            // rental_price_daily, min_rental_days, max_rental_days sengaja tidak diisi
            'shipping_owner_delivery' => true,
            'shipping_express'        => false,
            'shipping_regular'        => true,
            'shipping_pickup'         => false,
            'status'                  => 'active',
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/products', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'rental_price_daily',
            'min_rental_days',
            'max_rental_days',
        ]);
    }

    public function test_create_product_with_rental_available_true_with_required_rental_fields_succeeds_201()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create([
            'name' => 'Alat Terapi',
            'slug' => 'alat-terapi',
            'status' => 'active',
        ]);

        $payload = [
            'category_id'             => $category->id,
            'name'                    => 'Ventilator ICU Portable',
            'sku'                     => 'VENT-001',
            'condition'               => 'baru',
            'purchase_available'      => false,
            'rental_available'        => true,
            'rental_price_daily'      => 500000,
            'min_rental_days'         => 3,
            'max_rental_days'         => 30,
            'shipping_owner_delivery' => true,
            'shipping_express'        => false,
            'shipping_regular'        => true,
            'shipping_pickup'         => false,
            'status'                  => 'active',
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/products', $payload);

        $response->assertStatus(201);
    }

    public function test_update_product_with_rental_available_true_without_rental_fields_fails_422()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create([
            'name' => 'Monitoring',
            'slug' => 'monitoring',
            'status' => 'active',
        ]);

        $product = \App\Models\Product::create([
            'category_id'             => $category->id,
            'name'                    => 'Patient Monitor',
            'slug'                    => 'patient-monitor',
            'sku'                     => 'PM-001',
            'condition'               => 'baru',
            'purchase_available'      => true,
            'sale_price'              => 15000000,
            'stock_purchase'          => 5,
            'rental_available'        => false,
            'shipping_owner_delivery' => true,
            'shipping_express'        => false,
            'shipping_regular'        => true,
            'shipping_pickup'         => false,
            'status'                  => 'active',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/admin/products/{$product->id}", [
                'rental_available' => true,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'rental_price_daily',
            'min_rental_days',
            'max_rental_days',
        ]);
    }
}
