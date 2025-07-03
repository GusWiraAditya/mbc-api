<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $table = 'voucher';

    protected $fillable = [
        'code',
        'type',
        'value',
        'start_at',
        'end_at',
        'minimum_purchase',
        'usage_limit',
        'for_all_products',
    ];

    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
        'for_all_products' => 'boolean',
    ];
}
