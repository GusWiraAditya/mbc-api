<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;
    protected $table = 'product_variants';
    protected $fillable = [
        'product_id',
        'color_id',
        'size_id',
        'material_id',
        'sku',
        'price',
        'stock',
        'weight'
    ];

    // Relasi ke gambar-gambarnya
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    // Relasi ke induknya
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    // Definisikan juga relasi ke color, size, material jika perlu
    public function color()
    {
        return $this->belongsTo(Color::class);
    }
    public function size()
    {
        return $this->belongsTo(Size::class);
    }
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
