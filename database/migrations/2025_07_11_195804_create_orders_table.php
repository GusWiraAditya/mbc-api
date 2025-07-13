<?php
// File: ..._create_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke pengguna yang melakukan pesanan
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Snapshot alamat pengiriman. Kita simpan sebagai JSON untuk arsip.
            // Ini mencegah masalah jika pengguna menghapus alamat aslinya.
            $table->json('shipping_address');

            // Nomor pesanan yang unik dan mudah dibaca
            $table->string('order_number')->unique();

            // Rincian biaya
            $table->decimal('subtotal', 15, 2);
            $table->decimal('shipping_cost', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);

            // Detail pengiriman
            $table->string('shipping_courier'); // Cth: "JNE"
            $table->string('shipping_service'); // Cth: "REG"
            $table->string('shipping_etd');      // Cth: "1-2 hari"
            $table->string('shipping_tracking_number')->nullable();

            // Status pembayaran & pesanan
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->string('payment_gateway')->default('midtrans');
            $table->string('midtrans_snap_token')->nullable();
            $table->string('midtrans_transaction_id')->nullable();

            $table->enum('order_status', [
                'pending_payment',
                'processing',
                'shipped',
                'completed',
                'cancelled',
                'refunded'
            ])->default('pending_payment');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};