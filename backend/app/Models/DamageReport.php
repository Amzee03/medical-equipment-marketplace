<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamageReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_item_id',
        'reported_by',
        'condition',
        'description',
        'repair_cost',
        'replacement_cost',
        'status',
    ];

    public function rentalItem()
    {
        return $this->belongsTo(RentalItem::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
