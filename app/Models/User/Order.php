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

    /**
     * The attributes that are mass assignable.
     */
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
        'midtrans_snap_token',
        'order_status',
    ];

    /**
     * The attributes that should be cast.
     * Kita beri tahu Laravel untuk secara otomatis mengubah kolom
     * 'shipping_address' dari JSON menjadi array PHP.
     */
    protected $casts = [
        'shipping_address' => 'array',
    ];

    /**
     * Relasi: Satu pesanan dimiliki oleh satu User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Satu pesanan memiliki banyak item pesanan.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}