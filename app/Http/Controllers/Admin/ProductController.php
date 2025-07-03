<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Admin\Product;
use Illuminate\Validation\Rule; // REVISI: Tambahkan impor ini
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
        // REVISI: Membuat aturan validasi SKU lebih eksplisit dan aman
        $validated = $request->validate([
            'product_name' => 'required|string|max:255|unique:product,product_name',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'gender' => 'required|in:men,women,unisex',
            'is_active' => 'required|boolean',
            'variants' => 'required|array|min:1',
            'variants.*.price' => 'required|numeric|min:1',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.color_id' => 'required|exists:colors,id',
            'variants.*.size_id' => 'required|exists:sizes,id',
            'variants.*.material_id' => 'required|exists:materials,id',
            // Aturan 'unique' di sini sudah benar untuk method store
            'variants.*.sku' => 'required|string|max:255|unique:product_variants,sku',
            'variants.*.images' => 'nullable|array',
            'variants.*.images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $product = DB::transaction(function () use ($request, $validated) {
            $product = Product::create([
                'product_name' => $validated['product_name'],
                'category_id' => $validated['category_id'],
                'description' => $validated['description'] ?? null,
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
        // REVISI: Memisahkan validasi untuk SKU agar bisa dinamis
        $validatedData = $request->validate([
            'product_name' => ['required', 'string', 'max:255', Rule::unique('product')->ignore($product->id)],
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'gender' => 'required|in:men,women,unisex',
            'is_active' => 'required|boolean',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.price' => 'required|numeric|min:1',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.color_id' => 'required|exists:colors,id',
            'variants.*.size_id' => 'required|exists:sizes,id',
            'variants.*.material_id' => 'required|exists:materials,id',
            'variants.*.sku' => 'required|string|max:255', // Validasi 'unique' dipisahkan
            'variants.*.images' => 'nullable|array',
            'variants.*.images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'deleted_variant_ids' => 'nullable|array',
            'deleted_variant_ids.*' => 'exists:product_variants,id',
            'variants.*.deleted_image_ids' => 'nullable|array',
            'variants.*.deleted_image_ids.*' => 'exists:product_images,id',
        ]);

        // Validasi unik untuk SKU secara dinamis
        foreach ($validatedData['variants'] as $index => $variantData) {
            $variantId = $variantData['id'] ?? null;
            $validator = validator($variantData, [
                'sku' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('product_variants', 'sku')->ignore($variantId),
                ],
            ]);

            if ($validator->fails()) {
                // Jika validasi gagal, kembalikan response error
                return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
            }
        }

        DB::transaction(function () use ($request, $validatedData, $product) {
            // 1. Update data produk utama
            $product->update([
                'product_name' => $validatedData['product_name'],
                'slug' => Str::slug($validatedData['product_name']) . '-' . $product->id,
                'category_id' => $validatedData['category_id'],
                'description' => $validatedData['description'] ?? null,
                'gender' => $validatedData['gender'],
                'is_active' => $validatedData['is_active'],
            ]);

            // 2. Proses setiap varian yang dikirim dari frontend
            foreach ($validatedData['variants'] as $index => $variantData) {
                $variant = $product->variants()->updateOrCreate(
                    ['id' => $variantData['id'] ?? null],
                    $variantData
                );

                // 3. Hapus gambar lama jika ada
                if (!empty($variantData['deleted_image_ids'])) {
                    $imagesToDelete = ProductImage::whereIn('id', $variantData['deleted_image_ids'])->get();
                    foreach($imagesToDelete as $image) {
                        Storage::disk('public')->delete($image->path);
                        $image->delete();
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

            // 5. Hapus varian yang ada di 'deleted_variant_ids'
            if (!empty($validatedData['deleted_variant_ids'])) {
                $variantsToDelete = $product->variants()->whereIn('id', $validatedData['deleted_variant_ids'])->get();
                foreach ($variantsToDelete as $variant) {
                    foreach ($variant->images as $image) {
                        Storage::disk('public')->delete($image->path);
                    }
                    $variant->delete();
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