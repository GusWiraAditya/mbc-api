<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Admin\Category;
use App\Models\Admin\Color;
use App\Models\Admin\Material;
use App\Models\Admin\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Mengambil semua data master untuk filter di halaman koleksi.
     */
    public function getFilterMasterData()
    {
        return response()->json([
            'categories' => Category::where('is_active', true)->get(['id', 'category_name', 'slug']),
            'colors' => Color::all(['id', 'name', 'hex_code']),
            'materials' => Material::all(['id', 'name']),
        ]);
    }

    /**
     * Mengambil produk dengan filter, sorting, dan paginasi.
     */
    public function getProducts(Request $request)
    {
        // Mulai query dengan produk yang aktif dan relasi yang dibutuhkan
        $query = Product::query()->where('is_active', true)->with(['category', 'variants.images']);

        // 1. Filter Pencarian Teks
        $query->when($request->search, function ($q, $search) {
            $q->where('product_name', 'like', "%{$search}%");
        });

        // 2. Filter Kategori (berdasarkan array ID)
        $query->when($request->categories, function ($q, $categories) {
            $q->whereIn('category_id', explode(',', $categories));
        });

        // 3. Filter Gender
        $query->when($request->genders, function ($q, $genders) {
            $q->whereIn('gender', explode(',', $genders));
        });

        // 4. Filter Warna & Material
        $query->when($request->colors, function ($q, $colors) {
            $q->whereHas('variants', fn($vq) => $vq->whereIn('color_id', explode(',', $colors)));
        });
        $query->when($request->materials, function ($q, $materials) {
            $q->whereHas('variants', fn($vq) => $vq->whereIn('material_id', explode(',', $materials)));
        });

        // 5. Filter Harga
        $query->when($request->min_price || $request->max_price, function ($q) use ($request) {
            $q->whereHas('variants', function ($vq) use ($request) {
                if ($request->min_price) $vq->where('price', '>=', $request->min_price);
                if ($request->max_price) $vq->where('price', '<=', $request->max_price);
            });
        });
        
        // --- REVISI 1: BLOK FILTER KETERSEDIAAN DIHAPUS ---
        // Logika untuk $request->availability sudah dihapus dari sini.

        // 6. Sorting (Sebelumnya no. 7)
        $sortBy = $request->sort_by ?? 'newest';
        switch ($sortBy) {
            case 'price_asc':
                // REVISI: Urutkan berdasarkan kolom baru yang super cepat
                $query->orderBy('min_price', 'asc');
                break;
            case 'price_desc':
                // REVISI: Urutkan berdasarkan kolom baru yang super cepat
                $query->orderBy('max_price', 'desc');
                break;
            default: // 'newest'
                $query->latest();
                break;
        }

        // --- REVISI 2: UBAH PAGINASI MENJADI 9 ---
        $products = $query->paginate(9)->withQueryString();

        return response()->json($products);
    }
}