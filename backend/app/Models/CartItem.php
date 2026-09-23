<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'type',
        'quantity',
        'rental_start_date',
        'rental_end_date',
        'rental_pricing_tier',
    ];

    protected $casts = [
        'rental_start_date' => 'date',
        'rental_end_date' => 'date',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
