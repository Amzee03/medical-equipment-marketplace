<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'unit_code',
        'condition',
        'status',
        'notes',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function rentalItems()
    {
        return $this->hasMany(RentalItem::class);
    }
}
