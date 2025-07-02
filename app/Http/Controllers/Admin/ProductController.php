<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Admin\Product;
use Illuminate\Validation\Rule;
use App\Models\Admin\ProductImage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Menampilkan semua produk dengan semua relasi yang diperlukan.
     */
    public function index()
    {
        return Product::with([
            'category',
            'variants.color',
            'variants.size',
            'variants.material',
            'variants.images'
        ])->latest()->get();
    }

    /**
     * Menyimpan produk baru beserta semua varian dan gambarnya dalam satu transaksi.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255|unique:product',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'gender' => 'required|in:men,women,unisex',
            'is_active' => 'required|boolean',
            'variants' => 'required|array|min:1',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.color_id' => 'nullable|exists:colors,id',
            'variants.*.size_id' => 'nullable|exists:sizes,id',
            'variants.*.material_id' => 'nullable|exists:materials,id',
            'variants.*.images' => 'required|array|min:1',
            'variants.*.images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $product = DB::transaction(function () use ($request, $validated) {
            $product = Product::create([
                'product_name' => $validated['product_name'],
                'category_id' => $validated['category_id'],
                'description' => $validated['description'],
                'gender' => $validated['gender'],
                'is_active' => $validated['is_active'],
                'slug' => Str::slug($validated['product_name']) . '-' . uniqid(),
                'admin_id' => Auth::id(),
            ]);

            foreach ($validated['variants'] as $index => $variantData) {
                $variant = $product->variants()->create($variantData);
                if (isset($request->file('variants')[$index]['images'])) {
                    foreach ($request->file('variants')[$index]['images'] as $imageFile) {
                        $path = $imageFile->store('product-images', 'public');
                        $variant->images()->create(['path' => $path]);
                    }
                }
            }
            return $product;
        });

        return response()->json($product->load(['variants.images', 'category']), 201);
    }

    public function show(Product $product)
    {
        return $product->load(['variants.images', 'variants.color', 'variants.size', 'variants.material', 'category']);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:255', Rule::unique('product')->ignore($product->id)],
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'gender' => 'required|in:men,women,unisex',
            'is_active' => 'required|boolean',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|exists:product_variants,id', // Untuk varian yang sudah ada
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.color_id' => 'nullable|exists:colors,id',
            'variants.*.size_id' => 'nullable|exists:sizes,id',
            'variants.*.material_id' => 'nullable|exists:materials,id',
            'variants.*.images' => 'nullable|array', // Gambar baru mungkin tidak selalu ada
            'variants.*.images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'deleted_variant_ids' => 'nullable|array', // ID varian yang dihapus
            'deleted_variant_ids.*' => 'exists:product_variants,id',
            'variants.*.deleted_image_ids' => 'nullable|array', // ID gambar yang dihapus
            'variants.*.deleted_image_ids.*' => 'exists:product_images,id',
        ]);

        DB::transaction(function () use ($request, $validated, $product) {
            // 1. Update data produk utama
            $product->update([
                'product_name' => $validated['product_name'],
                'slug' => Str::slug($validated['product_name']) . '-' . $product->id, // update slug juga
                'category_id' => $validated['category_id'],
                'description' => $validated['description'],
                'gender' => $validated['gender'],
                'is_active' => $validated['is_active'],
            ]);

            $incomingVariantIds = [];

            // 2. Proses setiap varian yang dikirim dari frontend
            foreach ($validated['variants'] as $index => $variantData) {
                // Update atau buat varian baru
                $variant = $product->variants()->updateOrCreate(
                    ['id' => $variantData['id'] ?? null], // Kondisi pencarian
                    $variantData // Data untuk di-update atau dibuat
                );

                $incomingVariantIds[] = $variant->id;

                // 3. Hapus gambar lama jika ada
                if (!empty($variantData['deleted_image_ids'])) {
                    foreach ($variantData['deleted_image_ids'] as $imageId) {
                        $image = ProductImage::find($imageId);
                        if ($image) {
                            Storage::disk('public')->delete($image->path);
                            $image->delete();
                        }
                    }
                }

                // 4. Tambah gambar baru jika ada
                if (isset($request->file('variants')[$index]['images'])) {
                    foreach ($request->file('variants')[$index]['images'] as $imageFile) {
                        $path = $imageFile->store('product-images', 'public');
                        $variant->images()->create(['path' => $path]);
                    }
                }
            }

            // 5. Hapus varian yang tidak ada lagi di request
            // (Ini adalah cara sederhana. Cara yang lebih aman adalah dengan 'deleted_variant_ids')
            if (!empty($validated['deleted_variant_ids'])) {
                $variantsToDelete = $product->variants()->whereIn('id', $validated['deleted_variant_ids'])->get();
                foreach ($variantsToDelete as $variant) {
                    // Hapus gambar terkait dari storage
                    foreach ($variant->images as $image) {
                        Storage::disk('public')->delete($image->path);
                    }
                    $variant->delete(); // Ini akan menghapus gambar dari DB juga karena cascade
                }
            }
        });

        return response()->json($product->load(['category', 'variants.color', 'variants.size', 'variants.material', 'variants.images']));
    }

    public function destroy(Product $product)
    {
        DB::transaction(function () use ($product) {
            foreach ($product->variants as $variant) {
                foreach ($variant->images as $image) {
                    Storage::disk('public')->delete($image->path);
                }
            }
            $product->delete();
        });

        return response()->json(null, 204);
    }
}
