<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke pengguna yang memiliki keranjang
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Relasi ke varian produk yang spesifik (INI YANG PALING PENTING)
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');

            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            // Membuat setiap VARIAN produk per pengguna menjadi unik.
            // Ini mencegah pengguna menambahkan "Baju Merah L" dua kali sebagai baris terpisah.
            // Jika mereka menambahkannya lagi, yang terjadi adalah penambahan kuantitas.
            $table->unique(['user_id', 'product_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
