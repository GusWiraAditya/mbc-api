<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'max_discount',
        'min_purchase',
        'start_date',
        'end_date',
        'usage_limit',
        'usage_limit_per_user',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'value' => 'float',
        'max_discount' => 'float',
        'min_purchase' => 'float',
    ];

    /**
     * Relasi many-to-many dengan Product.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_voucher')
            ->using(ProductVoucher::class); // <-- REVISI: Tambahkan ini
    }

    /**
     * Relasi many-to-many dengan Category.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_voucher')
            ->using(CategoryVoucher::class); // <-- REVISI: Tambahkan ini
    }

    /**
     * Scope untuk mengambil voucher yang valid (aktif dan dalam rentang tanggal).
     */
    public function scopeValid(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(function ($q) {
                $q->where('start_date', '<=', now())->orWhereNull('start_date');
            })
            ->where(function ($q) {
                $q->where('end_date', '>=', now())->orWhereNull('end_date');
            });
    }
}
