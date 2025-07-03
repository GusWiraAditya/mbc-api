<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin\Order;
use App\Models\Admin\OrderItem;
use App\Models\Admin\Payment;
use App\Models\User;
use App\Models\Admin\Product;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil user dan produk dari database
        $user = User::first(); // pastikan minimal ada 1 user
        $products = Product::with('variants')->get(); // pastikan ada produk dan variannya

        if (!$user || $products->isEmpty()) {
            $this->command->warn('Seeder Order dilewati: user atau produk kosong.');
            return;
        }

        for ($i = 1; $i <= 3; $i++) {
            $order = Order::create([
                'user_id' => $user->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'total_amount' => 0, // di-update nanti setelah item
                'status' => 'dibayar',
                'shipping_address' => 'Jl. Contoh Alamat No. 123, Kota Contoh',
                'shipping_courier' => 'JNE',
                'shipping_tracking_number' => 'JNE' . rand(100000, 999999),
            ]);

            $total = 0;

            // Tambahkan item ke order
            for ($j = 0; $j < rand(1, 3); $j++) {
                $product = $products->random();
                $variant = $product->variants->first(); // ambil varian pertama

                if (!$variant) continue;

                $quantity = rand(1, 3);
                $price = $variant->price;
                $subtotal = $quantity * $price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            // Update total
            $order->update(['total_amount' => $total]);

            // Buat payment dummy
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'midtrans',
                'payment_status' => 'paid',
                'transaction_id' => 'MIDTRANS-' . strtoupper(Str::random(6)),
                'payment_type' => 'bank_transfer',
                'va_number' => '1234567890',
                'paid_at' => now(),
                'raw_response' => [
                    'status_code' => 200,
                    'status_message' => 'Success',
                ],
            ]);
        }

        $this->command->info('Seeder order, order_items, payment berhasil dijalankan.');
    }
}
