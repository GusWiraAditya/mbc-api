<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['dibayar', 'dikemas', 'dikirim', 'diterima', 'selesai', 'dibatalkan'])->default('dibayar');
            $table->string('shipping_address');
            $table->string('shipping_courier')->nullable();
            $table->string('shipping_tracking_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('orders');
    }
};