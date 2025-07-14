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
        // 1. Preparation: Clean up old data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        DB::table('product_variants')->truncate();
        DB::table('product_images')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Storage::disk('public')->deleteDirectory('product-images');
        Storage::disk('public')->makeDirectory('product-images');
        
        // 2. Fetch master data
        $adminUser = User::first();
        $categories = Category::all();
        $colors = Color::all();
        $sizes = Size::all();
        $materials = Material::all();

        if ($categories->isEmpty() || $colors->isEmpty() || $sizes->isEmpty() || $materials->isEmpty() || !$adminUser) {
            $this->command->error("Ensure User and Attribute data exist before running this seeder.");
            return;
        }

        // ==========================================================
        // --- REVISION: PRODUCT DATA EXPANDED TO 10 ITEMS ---
        // ==========================================================
        $productsToCreate = [
            [
                'name' => 'Classic Leather Messenger Bag "The Urbanite"',
                'description' => 'Explore the city in style with the Classic Leather Messenger Bag "The Urbanite". Crafted from the finest quality genuine leather, this men\'s shoulder bag offers durability and a professional look. Perfect for work, college, and daily activities. Get the best genuine leather men\'s bag here.',
                'category' => 'Messenger Bags',
                'gender' => 'men',
                'base_image' => 'tas-messenger.jpg'
            ],
            [
                'name' => '"Voyager" Leather Backpack for the Urban Adventurer',
                'description' => 'The "Voyager" Leather Backpack is a loyal partner for every adventure. With a secure laptop compartment and ample storage space, this leather backpack is ideal for travel or everyday use. Its sturdy and elegant design makes it the top choice for a durable leather backpack.',
                'category' => 'Backpacks',
                'gender' => 'unisex',
                'base_image' => 'tas-ransel.jpg'
            ],
            [
                'name' => 'Elegant Leather Tote Bag "The Maven"',
                'description' => 'Look chic and functional with "The Maven" Leather Tote Bag. This women\'s tote has a large capacity to carry all your essentials, from a laptop to makeup. Made from premium, eco-friendly, and easy-to-maintain synthetic leather. The right choice for the modern career woman.',
                'category' => 'Tote Bags',
                'gender' => 'women',
                'base_image' => 'tas-tote.jpg'
            ],
            [
                'name' => 'Professional Leather Laptop Bag "The Executive"',
                'description' => 'Carry your device safely and in style. "The Executive" Leather Laptop Bag is specially designed with padded cushioning to protect laptops up to 15 inches. Its minimalist design and genuine leather material provide a strong professional impression. Find the best leather work bag to support your career.',
                'category' => 'Laptop Bags',
                'gender' => 'unisex',
                'base_image' => 'tas-laptop.jpg'
            ],
            [
                'name' => '"Starlight" Leather Party Clutch',
                'description' => 'Perfect your party look with the "Starlight" Leather Clutch. This luxuriously designed women\'s party bag is made from quality leather with charming metallic accents. Its perfect handheld size makes it a must-have accessory for formal events and special nights.',
                'category' => 'Clutch Bags',
                'gender' => 'women',
                'base_image' => 'tas-pesta.jpg'
            ],
            [
                'name' => '"The Nomad" Compact Leather Crossbody Bag',
                'description' => 'For the active and dynamic you, "The Nomad" Leather Crossbody Bag is the answer. Its compact size is ideal for carrying essentials like a phone, wallet, and keys. This small shoulder bag is the perfect combination of practicality and casual style.',
                'category' => 'Crossbody Bags',
                'gender' => 'unisex',
                'base_image' => 'tas-crossbody.jpg'
            ],
            [
                'name' => '"The Duchess" Women\'s Leather Handbag',
                'description' => '"The Duchess" is the definition of elegance. This women\'s leather handbag has a sturdy structure and neat details, exuding an aura of luxury. An ideal choice for women who appreciate quality and timeless design. Get your dream genuine leather handbag.',
                'category' => 'Handbags',
                'gender' => 'women',
                'base_image' => 'tas-tote.jpg' // Using another image as an example
            ],
            [
                'name' => '"The Explorer" Canvas Shoulder Bag',
                'description' => 'The combination of strong canvas and genuine leather accents makes "The Explorer" shoulder bag the right choice for a casual style. Durable, lightweight, and has many pockets for better organization. This men\'s canvas bag is ready to accompany your every step.',
                'category' => 'Shoulder Bags',
                'gender' => 'men',
                'base_image' => 'tas-messenger.jpg'
            ],
            [
                'name' => '"The Scholar" Minimalist Laptop Backpack',
                'description' => 'Designed for students and professionals, "The Scholar" Backpack offers a minimalist design with maximum functionality. Made from premium, water-resistant synthetic leather, keeping your belongings safe. This laptop backpack is a smart choice for efficiency.',
                'category' => 'Backpacks',
                'gender' => 'unisex',
                'base_image' => 'tas-ransel.jpg'
            ],
            [
                'name' => '"The Weekender" Canvas Tote Bag',
                'description' => 'A large-sized tote bag perfect for weekend getaways or trips to the gym. Made from thick canvas with comfortable genuine leather handles. "The Weekender" is a versatile, strong, and stylish bag for all your needs.',
                'category' => 'Tote Bags',
                'gender' => 'unisex',
                'base_image' => 'tas-tote.jpg'
            ],
        ];

        foreach ($productsToCreate as $productData) {
            $this->command->info("Creating product: {$productData['name']}");

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
            // --- REVISION: CREATE 1 TO 3 VARIANTS RANDOMLY ---
            // ==========================================================
            $numberOfVariants = rand(1, 3);
            for ($i = 0; $i < $numberOfVariants; $i++) {
                $color = $colors->random();
                $size = $sizes->random();
                $material = $materials->random();

                // Create SKU
                $productPart = strtoupper(Str::limit(str_replace('-', '', Str::slug($product->product_name)), 4, ''));
                $colorPart = strtoupper(Str::limit($color->name, 3, ''));
                $sizePart = strtoupper($size->code);
                $materialPart = strtoupper(Str::limit($material->name, 3, ''));
                $sku = preg_replace('/-+/', '-', "{$productPart}-{$colorPart}-{$sizePart}-{$materialPart}");
                
                // Ensure SKU is unique if random duplication occurs
                if (DB::table('product_variants')->where('sku', $sku)->exists()) {
                    $sku .= '-' . Str::random(3);
                }

                $variant = $product->variants()->create([
                    'sku' => $sku,
                    'price' => rand(250000, 950000),
                    'stock' => rand(5, 70),
                    'weight' => rand(500, 1500), // Added weight in grams
                    'color_id' => $color->id,
                    'size_id' => $size->id,
                    'material_id' => $material->id,
                ]);

                // Create 1 or 2 images for each variant
                $numberOfImages = rand(1, 2);
                for ($j = 0; $j < $numberOfImages; $j++) {
                    $sourcePath = storage_path('app/public/seed_images/' . $productData['base_image']);
                    if (file_exists($sourcePath)) {
                        $newFileName = uniqid() . '.jpg';
                        $destinationPath = 'product-images/' . $newFileName;
                        Storage::disk('public')->put($destinationPath, file_get_contents($sourcePath));
                        $variant->images()->create(['path' => $destinationPath]);
                    } else {
                        $this->command->warn("Source image file not found: {$sourcePath}");
                    }
                }
            }
        }
    }
}