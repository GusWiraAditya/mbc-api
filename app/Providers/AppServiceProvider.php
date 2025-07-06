<?php

namespace App\Providers;

use App\Models\Admin\ProductVariant;
use Illuminate\Support\ServiceProvider;
use App\Observers\ProductVariantObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // --- REVISI: TAMBAHKAN BARIS INI UNTUK MENDAFTARKAN OBSERVER ---
        ProductVariant::observe(ProductVariantObserver::class);
    }
}
