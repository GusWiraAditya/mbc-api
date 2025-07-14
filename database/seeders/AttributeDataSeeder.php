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
                'category_name' => 'Shoulder Bags',
                'slug' => Str::slug('Shoulder Bags'),
                'description' => 'Discover our collection of premium leather shoulder bags with elegant and functional designs. Perfect for daily activities to formal events.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Tote Bags',
                'slug' => Str::slug('Tote Bags'),
                'description' => 'Large capacity genuine leather tote bags with a modern style. Ideal for work, college, and everyday activities.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Backpacks',
                'slug' => Str::slug('Backpacks'),
                'description' => 'Stylish and multifunctional leather backpacks, designed for comfort and a professional look in one bag.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Messenger Bags',
                'slug' => Str::slug('Messenger Bags'),
                'description' => 'Quality leather messenger bags for a casual or formal look. Practical for work and college.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Laptop Bags',
                'slug' => Str::slug('Laptop Bags'),
                'description' => 'Elegant leather laptop bags with secure compartments for devices and documents. A professional style for the modern worker.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Clutch Bags',
                'slug' => Str::slug('Clutch Bags'),
                'description' => 'Small party bags made from quality leather, perfect for formal events and an elegant style.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Crossbody Bags',
                'slug' => Str::slug('Crossbody Bags'),
                'description' => 'Lightweight and fashionable leather crossbody bags, ideal for high mobility and a casual style.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_name' => 'Handbags',
                'slug' => Str::slug('Handbags'),
                'description' => 'Genuine leather handbags with a feminine and professional design. Suitable for active and elegant women.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // database/seeders/AttributeDataSeeder.php

        // Colors
        DB::table('colors')->insert([
            ['name' => 'Dark Brown', 'hex_code' => '#4B3621', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Black', 'hex_code' => '#000000', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Light Brown', 'hex_code' => '#A0522D', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Sizes
        DB::table('sizes')->insert([
            [
                'name' => 'Small',
                'code' => 'S',
                'description' => 'Small size suitable for carrying essentials like a phone and wallet. Dimensions approx. 20–25 cm in length.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Medium',
                'code' => 'M',
                'description' => 'Medium size for daily needs. Fits a tablet, a large wallet, and a small water bottle.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Large',
                'code' => 'L',
                'description' => 'Large size for a laptop and A4 documents. Suitable as a work or college bag.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Extra Large',
                'code' => 'XL',
                'description' => 'Extra-large size for travel or carrying many items. Fits a 17-inch laptop.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Materials
        DB::table('materials')->insert([
            [
                'name' => 'Genuine Leather',
                'description' => 'High-quality genuine cowhide that is durable and luxurious. Provides an exclusive and elegant impression.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Synthetic Leather',
                'description' => 'An eco-friendly alternative to genuine leather. Its appearance resembles real leather but is lighter and easier to maintain.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Canvas Leather',
                'description' => 'A combination of strong and waterproof leather and canvas materials. Suitable for casual and outdoor bags.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
