<?php

namespace App\Models\User;

use App\Models\User;
use App\Models\Admin\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart'; // nama tabel sesuai migrasi

    protected $fillable = [
        'customer_id',
        'product_id',
        'quantity',
    ];

    // Relasi ke User (customer)
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Relasi ke Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
