<?php

namespace App\Models\Admin;

use App\Models\User;
use App\Models\Admin\Category;
use App\Models\Admin\ProductImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


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
