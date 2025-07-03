<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Admin\Category;
use App\Models\Admin\Color;
use App\Models\Admin\Size;
use App\Models\Admin\Material;
use App\Models\Admin\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // 1. Persiapan: Membersihkan data lama
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        DB::table('product_variants')->truncate();
        DB::table('product_images')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Storage::disk('public')->deleteDirectory('product-images');
        Storage::disk('public')->makeDirectory('product-images');
        
        // 2. Mengambil data master
        $adminUser = User::first();
        $categories = Category::all();
        $colors = Color::all();
        $sizes = Size::all();
        $materials = Material::all();

        if ($categories->isEmpty() || $colors->isEmpty() || $sizes->isEmpty() || $materials->isEmpty() || !$adminUser) {
            $this->command->error("Pastikan data User dan Atribut sudah ada sebelum menjalankan seeder ini.");
            return;
        }

        // ==========================================================
        // --- REVISI: DATA PRODUK DIPERBANYAK MENJADI 10 ---
        // ==========================================================
        $productsToCreate = [
            [
                'name' => 'Tas Messenger Kulit Klasik "The Urbanite"',
                'description' => 'Jelajahi kota dengan gaya menggunakan Tas Messenger Kulit Klasik "The Urbanite". Dibuat dari kulit asli kualitas terbaik, tas selempang pria ini menawarkan durabilitas dan tampilan profesional. Sangat cocok untuk kerja, kuliah, dan aktivitas harian. Beli tas kulit asli pria terbaik hanya di sini.',
                'category' => 'Tas Messenger',
                'gender' => 'men',
                'base_image' => 'tas-messenger.jpg'
            ],
            [
                'name' => 'Tas Ransel Kulit "Voyager" untuk Petualang Kota',
                'description' => 'Tas Ransel Kulit "Voyager" adalah partner setia untuk setiap petualangan. Dengan kompartemen laptop yang aman dan ruang penyimpanan luas, ransel kulit ini ideal untuk traveling atau kegiatan sehari-hari. Desainnya yang kokoh dan elegan menjadikannya pilihan utama ransel kulit tahan lama.',
                'category' => 'Tas Ransel',
                'gender' => 'unisex',
                'base_image' => 'tas-ransel.jpg'
            ],
            [
                'name' => 'Tote Bag Kulit Elegan "The Maven"',
                'description' => 'Tampil chic dan fungsional dengan Tote Bag Kulit "The Maven". Tas tote wanita ini memiliki kapasitas besar untuk membawa semua kebutuhan Anda, mulai dari laptop hingga makeup. Terbuat dari kulit sintetis premium yang ramah lingkungan dan mudah dirawat. Pilihan tepat untuk wanita karir modern.',
                'category' => 'Tas Tote',
                'gender' => 'women',
                'base_image' => 'tas-tote.jpg'
            ],
            [
                'name' => 'Tas Laptop Kulit Profesional "The Executive"',
                'description' => 'Bawa perangkat Anda dengan aman dan bergaya. Tas Laptop Kulit "The Executive" dirancang khusus dengan bantalan empuk untuk melindungi laptop hingga 15 inci. Desain minimalis dan material kulit asli memberikan kesan profesional yang kuat. Temukan tas kerja kulit terbaik untuk menunjang karir Anda.',
                'category' => 'Tas Laptop',
                'gender' => 'unisex',
                'base_image' => 'tas-laptop.jpg'
            ],
            [
                'name' => 'Clutch Pesta Kulit "Starlight"',
                'description' => 'Sempurnakan penampilan pesta Anda dengan Clutch Kulit "Starlight". Tas pesta wanita berdesain mewah ini dibuat dari kulit berkualitas dengan aksen metalik yang menawan. Ukurannya yang pas untuk digenggam menjadikannya aksesori wajib untuk acara formal dan malam spesial.',
                'category' => 'Tas Pesta',
                'gender' => 'women',
                'base_image' => 'tas-pesta.jpg'
            ],
            [
                'name' => 'Tas Crossbody Kulit Ringkas "The Nomad"',
                'description' => 'Untuk Anda yang aktif dan dinamis, Tas Crossbody Kulit "The Nomad" adalah jawabannya. Ukurannya yang ringkas sangat ideal untuk membawa barang esensial seperti ponsel, dompet, dan kunci. Tas selempang kecil ini adalah kombinasi sempurna antara kepraktisan dan gaya kasual.',
                'category' => 'Tas Crossbody',
                'gender' => 'unisex',
                'base_image' => 'tas-crossbody.jpg'
            ],
            [
                'name' => 'Handbag Kulit Wanita "The Duchess"',
                'description' => '"The Duchess" adalah definisi keanggunan. Handbag kulit wanita ini memiliki struktur kokoh dan detail yang rapi, memancarkan aura kemewahan. Pilihan ideal untuk wanita yang menghargai kualitas dan desain abadi. Dapatkan tas tangan kulit asli impian Anda.',
                'category' => 'Tas Handbag',
                'gender' => 'women',
                'base_image' => 'tas-tote.jpg' // Menggunakan gambar lain sebagai contoh
            ],
            [
                'name' => 'Tas Selempang Kanvas "The Explorer"',
                'description' => 'Kombinasi kekuatan kanvas dan aksen kulit asli membuat tas selempang "The Explorer" ini pilihan tepat untuk gaya santai. Tahan lama, ringan, dan memiliki banyak kantong untuk organisasi yang lebih baik. Tas kanvas pria ini siap menemani setiap langkah Anda.',
                'category' => 'Tas Selempang',
                'gender' => 'men',
                'base_image' => 'tas-messenger.jpg'
            ],
            [
                'name' => 'Ransel Laptop Minimalis "The Scholar"',
                'description' => 'Dirancang untuk pelajar dan profesional, Ransel "The Scholar" menawarkan desain minimalis dengan fungsionalitas maksimal. Terbuat dari bahan kulit sintetis premium yang tahan air, menjaga barang-barang Anda tetap aman. Ransel laptop ini adalah pilihan cerdas untuk efisiensi.',
                'category' => 'Tas Ransel',
                'gender' => 'unisex',
                'base_image' => 'tas-ransel.jpg'
            ],
            [
                'name' => 'Tote Bag Kanvas "The Weekender"',
                'description' => 'Tas tote berukuran besar yang sempurna untuk liburan akhir pekan atau ke gym. Dibuat dari bahan kanvas tebal dengan handle dari kulit asli yang nyaman. "The Weekender" adalah tas serbaguna yang kuat dan stylish untuk segala keperluan.',
                'category' => 'Tas Tote',
                'gender' => 'unisex',
                'base_image' => 'tas-tote.jpg'
            ],
        ];


        foreach ($productsToCreate as $productData) {
            $this->command->info("Membuat produk: {$productData['name']}");

            $product = Product::create([
                'product_name' => $productData['name'],
                'slug' => Str::slug($productData['name']) . '-' . uniqid(),
                'description' => $productData['description'],
                'category_id' => $categories->firstWhere('category_name', $productData['category'])->id,
                'gender' => $productData['gender'],
                'is_active' => true,
                'admin_id' => $adminUser->id,
            ]);

            // ==========================================================
            // --- REVISI: BUAT 1 SAMPAI 3 VARIAN SECARA ACAK ---
            // ==========================================================
            $numberOfVariants = rand(1, 3);
            for ($i = 0; $i < $numberOfVariants; $i++) {
                $color = $colors->random();
                $size = $sizes->random();
                $material = $materials->random();

                // Membuat SKU
                $productPart = strtoupper(str_replace(' ', '-', $product->product_name));
                $colorPart = strtoupper(str_replace(' ', '-', $color->name));
                $sizePart = strtoupper($size->code);
                $materialPart = strtoupper(str_replace(' ', '-', $material->name));
                $sku = preg_replace('/-+/', '-', "{$productPart}-{$colorPart}-{$sizePart}-{$materialPart}");
                
                // Pastikan SKU unik jika terjadi duplikasi acak
                if (DB::table('product_variants')->where('sku', $sku)->exists()) {
                    $sku .= '-' . Str::random(3);
                }

                $variant = $product->variants()->create([
                    'sku' => $sku,
                    'price' => rand(250000, 950000),
                    'stock' => rand(5, 70),
                    'color_id' => $color->id,
                    'size_id' => $size->id,
                    'material_id' => $material->id,
                ]);

                // Membuat 1 atau 2 gambar untuk setiap varian
                $numberOfImages = rand(1, 2);
                for ($j = 0; $j < $numberOfImages; $j++) {
                    $sourcePath = storage_path('app/public/seed_images/' . $productData['base_image']);
                    if (file_exists($sourcePath)) {
                        $newFileName = uniqid() . '.jpg';
                        $destinationPath = 'product-images/' . $newFileName;
                        Storage::disk('public')->put($destinationPath, file_get_contents($sourcePath));
                        $variant->images()->create(['path' => $destinationPath]);
                    } else {
                        $this->command->warn("File gambar sumber tidak ditemukan: {$sourcePath}");
                    }
                }
            }
        }
    }
}