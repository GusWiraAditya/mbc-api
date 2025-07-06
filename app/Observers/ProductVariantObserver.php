<?php

namespace App\Observers;

use App\Models\Admin\ProductVariant;

class ProductVariantObserver
{
    /**
     * Fungsi internal untuk menghitung ulang dan menyimpan rentang harga produk.
     */
    protected function updateProductPriceRange(ProductVariant $variant): void
    {
        // Ambil produk induk dari varian yang berubah
        $product = $variant->product;

        // Pastikan produk induknya ada
        if ($product) {
            // Ambil semua harga dari semua varian yang masih ada untuk produk ini
            $prices = $product->variants()->pluck('price');

            // Hitung min & max, lalu simpan ke produk induk
            $product->min_price = $prices->min() ?? 0;
            $product->max_price = $prices->max() ?? 0;
            $product->save();
        }
    }

    /**
     * Menangani event "saved" (setelah created dan updated).
     */
    public function saved(ProductVariant $productVariant): void
    {
        $this->updateProductPriceRange($productVariant);
    }

    /**
     * Menangani event "deleted".
     */
    public function deleted(ProductVariant $productVariant): void
    {
        $this->updateProductPriceRange($productVariant);
    }

    /**
     * Menangani event "restored" (jika Anda menggunakan soft deletes).
     */
    public function restored(ProductVariant $productVariant): void
    {
        $this->updateProductPriceRange($productVariant);
    }

    /**
     * Menangani event "forceDeleted" (jika Anda menggunakan soft deletes).
     */
    public function forceDeleted(ProductVariant $productVariant): void
    {
        $this->updateProductPriceRange($productVariant);
    }
}