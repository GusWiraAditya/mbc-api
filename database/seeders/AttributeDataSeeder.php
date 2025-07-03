<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttributeDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Categories
        DB::table('categories')->insert([
            [
                'category_name' => 'Tas Selempang',
                'slug' => Str::slug('Tas Selempang'),
                'description' => 'Temukan koleksi tas selempang kulit premium dengan desain elegan dan fungsional. Cocok untuk aktivitas harian hingga acara formal.',
                // 'image' => 'categories/selempang.jpg',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Tas Tote',
                'slug' => Str::slug('Tas Tote'),
                'description' => 'Tas tote kulit asli berkapasitas besar dengan gaya modern. Ideal untuk kerja, kuliah, dan aktivitas sehari-hari.',
                // 'image' => 'categories/tote.jpg',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Tas Ransel',
                'slug' => Str::slug('Tas Ransel'),
                'description' => 'Ransel kulit stylish dan multifungsi, dirancang untuk kenyamanan dan tampilan profesional dalam satu tas.',
                // 'image' => 'categories/ransel.jpg',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Tas Messenger',
                'slug' => Str::slug('Tas Messenger'),
                'description' => 'Tas messenger kulit berkualitas untuk tampilan santai atau formal. Praktis untuk kerja dan kuliah.',
                // 'image' => 'categories/messenger.jpg',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Tas Laptop',
                'slug' => Str::slug('Tas Laptop'),
                'description' => 'Tas laptop kulit elegan dengan ruang aman untuk perangkat dan dokumen. Gaya profesional untuk pekerja modern.',
                // 'image' => 'categories/laptop.jpg',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Tas Pesta',
                'slug' => Str::slug('Tas Pesta'),
                'description' => 'Tas pesta kecil dari kulit berkualitas, cocok untuk acara formal dan gaya elegan.',
                // 'image' => 'categories/pesta.jpg',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Tas Crossbody',
                'slug' => Str::slug('Tas Crossbody'),
                'description' => 'Tas crossbody kulit ringan dan modis, ideal untuk mobilitas tinggi dan gaya kasual.',
                // 'image' => 'categories/crossbody.jpg',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Tas Handbag',
                'slug' => Str::slug('Tas Handbag'),
                'description' => 'Handbag kulit asli dengan desain feminin dan profesional. Cocok untuk wanita aktif dan elegan.',
                // 'image' => 'categories/handbag.jpg',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Colors
        DB::table('colors')->insert([
            ['name' => 'Coklat Tua', 'hex_code' => '#4B3621', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Hitam', 'hex_code' => '#000000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Coklat Muda', 'hex_code' => '#A0522D', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Navy', 'hex_code' => '#000080', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Marun', 'hex_code' => '#800000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pink Pastel', 'hex_code' => '#FFC0CB', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Abu Gelap', 'hex_code' => '#2F4F4F', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Putih Gading', 'hex_code' => '#F8F8FF', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Sizes
        DB::table('sizes')->insert([
            [
                'name' => 'Small',
                'code' => 'S',
                'description' => 'Ukuran kecil cocok untuk membawa barang esensial seperti ponsel dan dompet. Dimensi ± 20–25 cm panjang.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Medium',
                'code' => 'M',
                'description' => 'Ukuran sedang untuk kebutuhan harian. Muat tablet, dompet besar, dan botol minum kecil.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Large',
                'code' => 'L',
                'description' => 'Ukuran besar untuk laptop dan dokumen A4. Cocok sebagai tas kerja atau kuliah.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Extra Large',
                'code' => 'XL',
                'description' => 'Ukuran ekstra besar untuk perjalanan atau membawa banyak barang. Muat laptop 17 inci.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Materials
        DB::table('materials')->insert([
            [
                'name' => 'Kulit Asli',
                'description' => 'Bahan kulit sapi asli berkualitas tinggi yang tahan lama dan mewah. Memberikan kesan eksklusif dan elegan.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Kulit Sintetis',
                'description' => 'Alternatif ramah lingkungan dari kulit asli. Tampilannya menyerupai kulit asli namun lebih ringan dan mudah dirawat.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Kulit Kanvas',
                'description' => 'Kombinasi bahan kulit dan kanvas yang kuat dan tahan air. Cocok untuk tas kasual dan outdoor.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
