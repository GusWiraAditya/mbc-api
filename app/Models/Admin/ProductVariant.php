<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $color_id
 * @property int|null $size_id
 * @property int|null $material_id
 * @property string $sku
 * @property string|null $price
 * @property int $stock
 * @property int $weight
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin\Color|null $color
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Admin\ProductImage> $images
 * @property-read int|null $images_count
 * @property-read \App\Models\Admin\Material|null $material
 * @property-read \App\Models\Admin\Product $product
 * @property-read \App\Models\Admin\Size|null $size
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereColorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereSizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereWeight($value)
 * @mixin \Eloquent
 */
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
