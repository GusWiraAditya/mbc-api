<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Product;

class PublicProductController extends Controller
{
   public function featured()
    {
        $products = Product::with([
                'category',
                'variants.images' // Kita hanya butuh gambar untuk thumbnail
            ])
            ->where('is_active', true) // Hanya ambil produk yang aktif
            ->latest() // Urutkan dari yang paling baru
            ->take(8)    // Ambil maksimal 8 produk
            ->get();

        return response()->json($products);
    }

    public function show(Product $product)
    {
        // Load semua relasi yang dibutuhkan oleh halaman detail
        return $product->load([
            'category', 
            'variants.color', 
            'variants.size', 
            'variants.material', 
            'variants.images'
        ]);
    }

    /**
     * Menampilkan produk terkait dari kategori yang sama.
     */
    public function related(Product $product)
    {
        return Product::where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id) // Kecualikan produk itu sendiri
            ->with(['variants.images']) // Load relasi yang perlu saja
            ->inRandomOrder()
            ->take(4)
            ->get();
    }
}
