<?php

namespace App\Models\Admin;

use App\Models\User;
use App\Models\Admin\Category;
use App\Models\Admin\ProductImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Product extends Model {
     use HasFactory;
    protected $table = 'product';
    protected $fillable = [
        'category_id', 'admin_id', 'slug', 'product_name', 'description',
        'gender', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'category_id' => 'integer', // Contoh cast lain yang baik
    ];
    // REVISI: Relasi utama sekarang ke varian, bukan gambar.
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

}

