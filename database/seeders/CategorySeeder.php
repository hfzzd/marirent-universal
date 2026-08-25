<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Mobil',
                'slug' => 'mobil',
                'description' => 'Sewa mobil untuk perjalanan darat, tersedia berbagai tipe dari city car hingga MPV.',
                'icon' => 'car',
                'is_active' => true,
            ],
            [
                'name' => 'Motor',
                'slug' => 'motor',
                'description' => 'Sewa motor untuk mobilitas harian dan perjalanan cepat.',
                'icon' => 'motorcycle',
                'is_active' => true,
            ],
            [
                'name' => 'Sewa HP',
                'slug' => 'sewa-hp',
                'description' => 'Sewa smartphone terbaru untuk kebutuhan sementara.',
                'icon' => 'smartphone',
                'is_active' => true,
            ],
            [
                'name' => 'Sewa Kamera',
                'slug' => 'sewa-kamera',
                'description' => 'Sewa kamera profesional dan aksesoris untuk dokumentasi.',
                'icon' => 'camera',
                'is_active' => true,
            ],
            [
                'name' => 'Sewa Tenda',
                'slug' => 'sewa-tenda',
                'description' => 'Sewa tenda dan alat outdoor untuk camping.',
                'icon' => 'campground',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
