<?php

namespace App\Models\Admin;

use App\Models\User;
use App\Models\Admin\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'total_amount',
        'status',
        'shipping_address',
        'shipping_courier',
        'shipping_tracking_number',
    ];

    // 🔗 Relasi ke user (customer)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 📦 Relasi ke detail item
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // 💳 Relasi ke pembayaran
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}