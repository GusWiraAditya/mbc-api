<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke pengguna yang memiliki alamat ini
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // --- Detail Alamat ---
            $table->string('label'); // Cth: "Rumah", "Kantor", "Apartemen"
            $table->string('recipient_name');
            $table->string('phone_number');
            
            // --- Informasi untuk RajaOngkir & Tampilan ---
            // Kita simpan ID untuk API dan Nama untuk tampilan cepat
            $table->unsignedBigInteger('province_id');
            $table->string('province_name');
            $table->unsignedBigInteger('city_id');
            $table->string('city_name');

             $table->unsignedBigInteger('district_id');
        $table->string('district_name');

            $table->unsignedBigInteger('subdistrict_id');
            $table->string('subdistrict_name');
            
            $table->text('address_detail'); // Nama jalan, nomor rumah, RT/RW, dll.
            $table->string('postal_code')->nullable();

            // --- Penanda Alamat Utama ---
            // Boolean untuk menandai alamat default/utama pengguna
            $table->boolean('is_primary')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
