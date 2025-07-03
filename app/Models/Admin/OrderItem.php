<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    // 🔗 Ke order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // 👜 Ke produk
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
