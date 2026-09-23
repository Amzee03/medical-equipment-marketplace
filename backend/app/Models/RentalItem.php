<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'equipment_unit_id',
        'start_date',
        'end_date',
        'rental_due_date',
        'actual_return_date',
        'late_days',
        'late_fee',
        'condition_at_return',
        'rental_status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'rental_due_date' => 'date',
        'actual_return_date' => 'date',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function equipmentUnit()
    {
        return $this->belongsTo(EquipmentUnit::class);
    }

    public function damageReports()
    {
        return $this->hasMany(DamageReport::class);
    }
}
