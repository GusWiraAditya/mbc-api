<?php

namespace App\Models\Admin;

use App\Models\User;
use App\Models\Admin\Category;
use App\Models\Admin\ProductImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * 
 *
 * @property int $id
 * @property int $category_id
 * @property int $admin_id
 * @property string $slug
 * @property string $product_name
 * @property string|null $description
 * @property string|null $gender
 * @property float $min_price
 * @property float $max_price
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $admin
 * @property-read Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Admin\ProductVariant> $variants
 * @property-read int|null $variants_count
 * @property-read \App\Models\Admin\ProductVoucher|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Admin\Voucher> $vouchers
 * @property-read int|null $vouchers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Product extends Model
{
    use HasFactory;
    protected $table = 'product';
    protected $fillable = [
        'category_id',
        'admin_id',
        'slug',
        'product_name',
        'description',
        'gender',
        'is_active',
        'min_price', // <-- TAMBAHKAN INI
        'max_price', // <-- TAMBAHKAN INI
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'category_id' => 'integer',
        'min_price' => 'float', // <-- TAMBAHKAN INI
        'max_price' => 'float', // <-- TAMBAHKAN INI // Contoh cast lain yang baik
    ];
    // REVISI: Relasi utama sekarang ke varian, bukan gambar.
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'product_voucher')
                    ->using(ProductVoucher::class);
    }
}
