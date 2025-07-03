<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User\Cart;
use App\Models\User;
use App\Models\Admin\Product;

class CartSeeder extends Seeder
{
    public function run()
    {
        // Ambil beberapa user dan produk untuk contoh (pastikan sudah ada datanya)
        $users = User::limit(3)->get();
        $products = Product::limit(5)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->info('User atau Product belum ada data, seed Cart dibatalkan.');
            return;
        }

        foreach ($users as $user) {
            foreach ($products as $product) {
                Cart::create([
                    'customer_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 5),
                ]);
            }
        }
    }
}
