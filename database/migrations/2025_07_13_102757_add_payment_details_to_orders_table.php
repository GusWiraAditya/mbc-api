<?php

// dalam file migration yang baru dibuat

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
        Schema::table('orders', function (Blueprint $table) {
            // Menyimpan jenis pembayaran (e.g., 'bank_transfer', 'qris', 'credit_card')
            $table->string('payment_type', 50)->nullable()->after('midtrans_transaction_id');

            // Menyimpan status dari Fraud Detection System Midtrans
            $table->string('fraud_status', 20)->nullable()->after('payment_type');

            // Menyimpan waktu pasti pembayaran lunas
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Hapus kolom jika migrasi di-rollback, urutan terbalik dari 'up'
            $table->dropColumn('paid_at');
            $table->dropColumn('fraud_status');
            $table->dropColumn('payment_type');
            $table->dropColumn('midtrans_transaction_id');
        });
    }
};
