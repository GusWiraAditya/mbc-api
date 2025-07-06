<?php

namespace App\Models\Admin;

use App\Models\Admin\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = ['category_name', 'slug', 'description', 'image', 'is_active'];
    protected $casts = [
        'is_active' => 'boolean', // Contoh cast lain yang baik
    ];
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'category_voucher')
            ->using(CategoryVoucher::class);
    }
}
