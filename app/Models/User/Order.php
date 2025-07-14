<?php
// File: app/Models/User/Order.php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'shipping_address',
        'order_number',
        'subtotal',
        'shipping_cost',
        'discount_amount',
        'grand_total',
        'shipping_courier',
        'shipping_service',
        'shipping_etd',
        'shipping_tracking_number',
        'payment_status',
        'payment_gateway',
        'midtrans_transaction_id',
        'paid_at',
        'fraud_status',
        'payment_type',
        'midtrans_snap_token',
        'order_status',
        'delivered_at'
    ];
    protected $casts = [
        'shipping_address' => 'array',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}