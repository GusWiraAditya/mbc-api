<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Admin\Product;
use App\Models\Admin\ProductImage;
use App\Models\Admin\Category;
use App\Models\User;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $admin = User::role('admin')->first(); // menggunakan Spatie Role

        if (!$admin) {
            $this->command->warn('Admin belum tersedia. Pastikan user dengan role "admin" sudah dibuat.');
            return;
        }

        if ($categories->isEmpty()) {
            $this->command->warn('Kategori belum tersedia. Jalankan CategorySeeder terlebih dahulu.');
            return;
        }

        $products = [
            [
                'product_name' => 'Kaos Polos Katun',
                'description' => 'Kaos polos berbahan katun yang nyaman dipakai sehari-hari.',
                'price' => 75000,
                'stock' => 100,
            ],
            [
                'product_name' => 'Celana Jeans Slim Fit',
                'description' => 'Celana jeans model slim fit untuk tampilan kasual.',
                'price' => 180000,
                'stock' => 50,
            ],
            [
                'product_name' => 'Jaket Hoodie Zipper',
                'description' => 'Hoodie dengan resleting depan dan bahan fleece tebal.',
                'price' => 250000,
                'stock' => 30,
            ],
        ];

        foreach ($products as $data) {
            $category = $categories->random();

            $product = Product::create([
                'category_id'   => $category->id,
                'admin_id'      => $admin->id,
                'product_name'  => $data['product_name'],
                'slug'          => Str::slug($data['product_name']) . '-' . uniqid(),
                'description'   => $data['description'],
                'price'         => $data['price'],
                'stock'         => $data['stock'],
            ]);

            // Tambahkan 2 gambar dummy
            for ($i = 1; $i <= 2; $i++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => 'products/sample_' . rand(1, 5) . '.jpg',
                ]);
            }
        }
    }
}
