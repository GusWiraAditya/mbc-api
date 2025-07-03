<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('phone_number');
            $table->string('province');
            $table->string('city');
            $table->string('district'); // kecamatan
            $table->string('postal_code', 10);
            $table->string('street_name');
            $table->text('address_detail')->nullable(); // blok, patokan, dll
            $table->string('address_label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('addresses');
    }
};
