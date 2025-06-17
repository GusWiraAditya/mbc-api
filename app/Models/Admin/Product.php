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

    protected $fillable = ['category_id','product_name','description','price','stock','slug','image','admin_id'];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

}

