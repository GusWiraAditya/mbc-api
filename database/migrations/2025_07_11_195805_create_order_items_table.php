<?php

// File: ..._create_order_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke pesanan induk
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Relasi ke varian produk yang dibeli
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('set null');

            // --- SNAPSHOT DATA PRODUK SAAT PEMBELIAN ---
            // Ini sangat penting untuk menjaga keakuratan riwayat pesanan.
            $table->string('product_name');
            $table->string('variant_name');
            $table->unsignedInteger('quantity');
            $table->decimal('price', 15, 2); // Harga per item saat itu
            $table->unsignedInteger('weight'); // Berat per item saat itu

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};