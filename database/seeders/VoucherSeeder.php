<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Insert Vouchers
        $vouchers = [
            [
                'code' => 'DISC50K',
                'name' => 'Discount 50K',
                'description' => 'Potongan Rp50.000 untuk transaksi',
                'type' => 'fixed_transaction',
                'stacking_group' => 'transaction_discount',
                'value' => 50000.00,
                'max_discount' => null,
                'min_purchase' => 200000.00,
                'start_date' => '2025-07-01 00:00:00',
                'end_date' => '2025-07-31 23:59:59',
                'usage_limit' => 100,
                'times_used' => 0,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PERCENT10',
                'name' => 'Diskon 10%',
                'description' => 'Diskon 10% untuk semua item',
                'type' => 'percent_item',
                'stacking_group' => 'item_discount',
                'value' => 10.00,
                'max_discount' => 75000.00,
                'min_purchase' => 150000.00,
                'start_date' => '2025-07-01 00:00:00',
                'end_date' => '2025-07-31 23:59:59',
                'usage_limit' => 200,
                'times_used' => 0,
                'usage_limit_per_user' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FREESHIPID',
                'name' => 'Gratis Ongkir ID',
                'description' => 'Gratis ongkir seluruh Indonesia',
                'type' => 'free_shipping',
                'stacking_group' => 'shipping_discount',
                'value' => 0.00,
                'max_discount' => null,
                'min_purchase' => 50000.00,
                'start_date' => '2025-07-01 00:00:00',
                'end_date' => '2025-08-15 23:59:59',
                'usage_limit' => 300,
                'times_used' => 0,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'NEWUSER25',
                'name' => 'New User 25%',
                'description' => 'Diskon 25% untuk pengguna baru',
                'type' => 'percent_transaction',
                'stacking_group' => 'transaction_discount',
                'value' => 25.00,
                'max_discount' => 100000.00,
                'min_purchase' => 0.00,
                'start_date' => '2025-07-01 00:00:00',
                'end_date' => '2025-12-31 23:59:59',
                'usage_limit' => 500,
                'times_used' => 0,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ITEMFIX30K',
                'name' => 'Potongan Produk 30K',
                'description' => 'Potongan Rp30.000 untuk produk tertentu',
                'type' => 'fixed_item',
                'stacking_group' => 'item_discount',
                'value' => 30000.00,
                'max_discount' => null,
                'min_purchase' => 0.00,
                'start_date' => '2025-07-01 00:00:00',
                'end_date' => '2025-07-31 23:59:59',
                'usage_limit' => 100,
                'times_used' => 0,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'UNIQUE25',
                'name' => 'Voucher Spesial',
                'description' => 'Voucher unik untuk 1 pengguna',
                'type' => 'fixed_transaction',
                'stacking_group' => 'unique',
                'value' => 25000.00,
                'max_discount' => null,
                'min_purchase' => 50000.00,
                'start_date' => '2025-07-10 00:00:00',
                'end_date' => '2025-08-10 23:59:59',
                'usage_limit' => 1,
                'times_used' => 0,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SUMMER15',
                'name' => 'Summer Sale 15%',
                'description' => 'Diskon 15% semua transaksi',
                'type' => 'percent_transaction',
                'stacking_group' => 'transaction_discount',
                'value' => 15.00,
                'max_discount' => 60000.00,
                'min_purchase' => 100000.00,
                'start_date' => '2025-07-01 00:00:00',
                'end_date' => '2025-07-31 23:59:59',
                'usage_limit' => 300,
                'times_used' => 0,
                'usage_limit_per_user' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FREESHIP50',
                'name' => 'Ongkir Free 50K',
                'description' => 'Gratis ongkir hingga Rp50.000',
                'type' => 'free_shipping',
                'stacking_group' => 'shipping_discount',
                'value' => 0.00,
                'max_discount' => 50000.00,
                'min_purchase' => 100000.00,
                'start_date' => '2025-07-01 00:00:00',
                'end_date' => '2025-08-01 23:59:59',
                'usage_limit' => 200,
                'times_used' => 0,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FLASH100K',
                'name' => 'Flash Sale 100K',
                'description' => 'Voucher potongan Rp100.000',
                'type' => 'fixed_transaction',
                'stacking_group' => 'transaction_discount',
                'value' => 100000.00,
                'max_discount' => null,
                'min_purchase' => 300000.00,
                'start_date' => '2025-07-15 00:00:00',
                'end_date' => '2025-07-15 23:59:59',
                'usage_limit' => 50,
                'times_used' => 0,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BDAYGIFT20',
                'name' => 'Birthday Gift 20%',
                'description' => 'Diskon ulang tahun pelanggan 20%',
                'type' => 'percent_transaction',
                'stacking_group' => 'unique',
                'value' => 20.00,
                'max_discount' => 50000.00,
                'min_purchase' => 0.00,
                'start_date' => '2025-07-01 00:00:00',
                'end_date' => '2025-12-31 23:59:59',
                'usage_limit' => 1,
                'times_used' => 0,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('vouchers')->insert($vouchers);

        // 2. Relasi category_voucher
        $categoryVoucherMap = [
            ['Tas Selempang', 'DISC50K'],
            ['Tas Tote', 'PERCENT10'],
            ['Tas Ransel', 'PERCENT10'],
            ['Tas Messenger', 'ITEMFIX30K'],
            ['Tas Laptop', 'SUMMER15'],
            ['Tas Pesta', 'BDAYGIFT20'],
            ['Tas Crossbody', 'NEWUSER25'],
            ['Tas Handbag', 'BDAYGIFT20'],
        ];

        foreach ($categoryVoucherMap as [$categoryName, $voucherCode]) {
            $categoryId = DB::table('categories')->where('category_name', $categoryName)->value('id');
            $voucherId = DB::table('vouchers')->where('code', $voucherCode)->value('id');

            if ($categoryId && $voucherId) {
                DB::table('category_voucher')->insertOrIgnore([
                    'category_id' => $categoryId,
                    'voucher_id' => $voucherId,
                ]);
            }
        }

        // 3. Relasi product_voucher
        $productVoucherMap = [
            ['Tas Messenger Kulit Klasik "The Urbanite"', 'ITEMFIX30K'],
            ['Tas Ransel Kulit "Voyager" untuk Petualang Kota', 'PERCENT10'],
            ['Tote Bag Kulit Elegan "The Maven"', 'PERCENT10'],
            ['Tas Laptop Kulit Profesional "The Executive"', 'SUMMER15'],
            ['Clutch Pesta Kulit "Starlight"', 'BDAYGIFT20'],
            ['Tas Crossbody Kulit Ringkas "The Nomad"', 'NEWUSER25'],
            ['Handbag Kulit Wanita "The Duchess"', 'BDAYGIFT20'],
            ['Tas Selempang Kanvas "The Explorer"', 'DISC50K'],
        ];

        foreach ($productVoucherMap as [$productName, $voucherCode]) {
            $productId = DB::table('product')->where('product_name', $productName)->value('id');
            $voucherId = DB::table('vouchers')->where('code', $voucherCode)->value('id');

            if ($productId && $voucherId) {
                DB::table('product_voucher')->insertOrIgnore([
                    'product_id' => $productId,
                    'voucher_id' => $voucherId,
                ]);
            }
        }
    }
}
