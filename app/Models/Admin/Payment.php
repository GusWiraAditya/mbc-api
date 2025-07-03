<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_status',
        'transaction_id',
        'payment_type',
        'va_number',
        'paid_at',
        'raw_response',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'raw_response' => 'array',
    ];

    // 🔗 Ke order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
