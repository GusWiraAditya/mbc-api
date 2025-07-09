<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * 
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property float $value
 * @property float|null $max_discount
 * @property float|null $min_purchase
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property int|null $usage_limit
 * @property int $times_used
 * @property int|null $usage_limit_per_user
 * @property string|null $stacking_group
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin\ProductVoucher|\App\Models\Admin\CategoryVoucher|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Admin\Category> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Admin\Product> $products
 * @property-read int|null $products_count
 * @method static Builder<static>|Voucher newModelQuery()
 * @method static Builder<static>|Voucher newQuery()
 * @method static Builder<static>|Voucher query()
 * @method static Builder<static>|Voucher valid()
 * @method static Builder<static>|Voucher whereCode($value)
 * @method static Builder<static>|Voucher whereCreatedAt($value)
 * @method static Builder<static>|Voucher whereDescription($value)
 * @method static Builder<static>|Voucher whereEndDate($value)
 * @method static Builder<static>|Voucher whereId($value)
 * @method static Builder<static>|Voucher whereIsActive($value)
 * @method static Builder<static>|Voucher whereMaxDiscount($value)
 * @method static Builder<static>|Voucher whereMinPurchase($value)
 * @method static Builder<static>|Voucher whereName($value)
 * @method static Builder<static>|Voucher whereStackingGroup($value)
 * @method static Builder<static>|Voucher whereStartDate($value)
 * @method static Builder<static>|Voucher whereTimesUsed($value)
 * @method static Builder<static>|Voucher whereType($value)
 * @method static Builder<static>|Voucher whereUpdatedAt($value)
 * @method static Builder<static>|Voucher whereUsageLimit($value)
 * @method static Builder<static>|Voucher whereUsageLimitPerUser($value)
 * @method static Builder<static>|Voucher whereValue($value)
 * @mixin \Eloquent
 */
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
        'times_used',
        'usage_limit_per_user',
        'stacking_group',
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
