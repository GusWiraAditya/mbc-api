<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\Admin\Category;
use App\Http\Controllers\Controller;

class PublicCategoryController extends Controller
{
    public function top()
    {
        $topCategories = Category::where('is_active', true)
            // Menghitung jumlah produk yang 'is_active' di setiap kategori
            ->withCount(['products' => function ($query) {
                $query->where('is_active', true);
            }])
            // Urutkan berdasarkan jumlah produk terbanyak
            ->orderBy('products_count', 'desc')
            // Ambil 3 teratas
            ->take(3)
            ->get();

        return response()->json($topCategories);
    }

}
