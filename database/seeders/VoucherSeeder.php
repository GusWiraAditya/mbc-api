<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('voucher')->insert([
            [
                'code' => 'DISKON10',
                'type' => 'percent',
                'value' => 10,
                'start_at' => Carbon::now()->toDateString(),
                'end_at' => Carbon::now()->addDays(30)->toDateString(),
                'minimum_purchase' => 50000,
                'usage_limit' => 100,
                'for_all_products' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'HEMAT25K',
                'type' => 'fixed',
                'value' => 25000,
                'start_at' => Carbon::now()->toDateString(),
                'end_at' => Carbon::now()->addDays(15)->toDateString(),
                'minimum_purchase' => 100000,
                'usage_limit' => 50,
                'for_all_products' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
