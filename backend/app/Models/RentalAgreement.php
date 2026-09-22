<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'agreed_at',
        'agreement_version',
        'snapshot_content',
    ];

    protected $casts = [
        'agreed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
