<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'category_name' => 'Elektronik',
                'slug' => Str::slug('Elektronik'),
                'image' => 'elektronik.jpg',
            ],
            [
                'category_name' => 'Pakaian Pria',
                'slug' => Str::slug('Pakaian Pria'),
                'image' => 'pakaian-pria.jpg',
            ],
            [
                'category_name' => 'Pakaian Wanita',
                'slug' => Str::slug('Pakaian Wanita'),
                'image' => 'pakaian-wanita.jpg',
            ],
            [
                'category_name' => 'Peralatan Dapur',
                'slug' => Str::slug('Peralatan Dapur'),
                'image' => 'dapur.jpg',
            ],
        ];

        foreach ($data as $category) {
            Category::create($category);
        }
    }
}
