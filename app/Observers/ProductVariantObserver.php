<?php

namespace App\Observers;

use App\Models\Admin\ProductVariant;

class ProductVariantObserver
{
    protected function updateProductPriceRange(ProductVariant $variant)
    {
        $product = $variant->product;
        if ($product) {
            $prices = $product->variants()->pluck('price');
            $product->min_price = $prices->min() ?? 0;
            $product->max_price = $prices->max() ?? 0;
            $product->save();
        }
    }

    public function saved(ProductVariant $productVariant): void
    {
        $this->updateProductPriceRange($productVariant);
    }

    public function deleted(ProductVariant $productVariant): void
    {
        $this->updateProductPriceRange($productVariant);
    }
}