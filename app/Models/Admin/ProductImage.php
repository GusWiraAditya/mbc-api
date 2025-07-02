<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductImage extends Model {
    use HasFactory;
    protected $table = 'product_images';
    protected $fillable = ['product_variant_id', 'path'];

    public function variant() {
        return $this->belongsTo(ProductVariant::class);
    }
}


