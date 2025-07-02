<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Category; // Pastikan namespace model sudah benar
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Menampilkan daftar semua kategori, diurutkan dari yang terbaru.
     */
    public function index()
    {
        // Mengembalikan data langsung, karena frontend akan menanganinya.
        return Category::latest()->get();
    }

    /**
     * Menyimpan kategori baru ke dalam database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255|unique:categories',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
             'is_active' => 'required|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['category_name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    /**
     * Menampilkan detail satu kategori.
     */
    public function show(Category $category)
    {
        return $category;
    }

    /**
     * Memperbarui kategori yang sudah ada.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            // Menggunakan Rule::unique untuk mengabaikan ID saat ini saat validasi
            'category_name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
             'description' => 'nullable|string',
             'is_active' => 'required|boolean',
            // Menggunakan 'sometimes' agar validasi gambar hanya berjalan jika ada file baru
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['category_name']);

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada untuk menghemat storage
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($validated);
        return response()->json($category);
    }

    /**
     * Menghapus kategori dari database.
     */
    public function destroy(Category $category)
    {
        // Hapus juga gambar yang terkait dari storage
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();
        // Mengembalikan respons kosong dengan status 204 No Content, standar untuk delete
        return response()->json(null, 204);
    }
}
