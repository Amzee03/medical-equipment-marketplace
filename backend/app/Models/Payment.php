<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'payment_gateway',
        'gateway_transaction_id',
        'payment_method',
        'amount',
        'status',
        'paid_at',
        'raw_response',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'raw_response' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
