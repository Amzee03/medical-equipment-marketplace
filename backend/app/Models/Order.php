<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_id',
        'order_number',
        'order_type',
        'status',
        'subtotal',
        'shipping_cost',
        'total',
        'shipping_method',
        'address_id',
        'shipping_recipient_name',
        'shipping_phone',
        'shipping_full_address',
        'shipping_city',
        'shipping_province',
        'shipping_postal_code',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function rentalAgreement()
    {
        return $this->hasOne(RentalAgreement::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}
