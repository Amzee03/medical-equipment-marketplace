<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'function',
        'brand',
        'model',
        'specifications',
        'condition',
        'purchase_available',
        'sale_price',
        'stock_purchase',
        'rental_available',
        'rental_price_daily',
        'rental_price_weekly',
        'rental_price_monthly',
        'min_rental_days',
        'max_rental_days',
        'shipping_owner_delivery',
        'shipping_express',
        'shipping_regular',
        'shipping_pickup',
        'status',
    ];

    protected $casts = [
        'specifications' => 'array',
        'purchase_available' => 'boolean',
        'rental_available' => 'boolean',
        'shipping_owner_delivery' => 'boolean',
        'shipping_express' => 'boolean',
        'shipping_regular' => 'boolean',
        'shipping_pickup' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function equipmentUnits()
    {
        return $this->hasMany(EquipmentUnit::class);
    }
}
