<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Vehicle;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@marirent.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $owner = User::create([
            'name' => 'Owner Utama',
            'email' => 'owner@marirent.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Admin Utama',
            'email' => 'admin1@marirent.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'owner_id' => $owner->id,
            'phone' => '081234567899',
            'is_active' => true,
        ]);

        $owner2 = User::create([
            'name' => 'Owner RentCars',
            'email' => 'owner2@marirent.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'phone' => '081234568001',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Admin RentCars',
            'email' => 'admin2@marirent.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'owner_id' => $owner2->id,
            'phone' => '081234568002',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Budi Santoso',
            'email' => 'user@marirent.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'phone' => '081987654321',
            'is_active' => true,
        ]);

        $driverUser =         User::create([
            'name' => 'Ahmad Driver',
            'email' => 'driver@marirent.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
            'phone' => '081555666777',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Rina Inspector',
            'email' => 'inspector@marirent.com',
            'password' => Hash::make('password'),
            'role' => 'inspector',
            'phone' => '081555666778',
            'is_active' => true,
        ]);

        // Categories
        $cats = [
            ['name' => 'Mobil', 'slug' => 'mobil', 'description' => 'Sewa mobil untuk perjalanan Anda', 'icon' => 'car', 'is_active' => true],
            ['name' => 'Motor', 'slug' => 'motor', 'description' => 'Sewa motor untuk mobilitas harian', 'icon' => 'bike', 'is_active' => true],
            ['name' => 'Sewa HP', 'slug' => 'sewa-hp', 'description' => 'Sewa HP untuk kebutuhan sementara', 'icon' => 'smartphone', 'is_active' => true],
            ['name' => 'Sewa Kamera', 'slug' => 'sewa-kamera', 'description' => 'Sewa kamera profesional untuk dokumentasi', 'icon' => 'camera', 'is_active' => true],
            ['name' => 'Sewa Tenda', 'slug' => 'sewa-tenda', 'description' => 'Sewa tenda dan alat outdoor untuk camping', 'icon' => 'campground', 'is_active' => true],
            ['name' => 'Sewa Playstation', 'slug' => 'sewa-ps', 'description' => 'Sewa konsol Playstation beserta controller dan aksesoris untuk hiburan.', 'icon' => 'gamepad', 'is_active' => true],
            ['name' => 'Sewa Drone', 'slug' => 'sewa-drone', 'description' => 'Sewa drone untuk fotografi dan videografi dari udara.', 'icon' => 'drone', 'is_active' => true],
            ['name' => 'Sewa Alat Musik', 'slug' => 'sewa-alat-musik', 'description' => 'Sewa gitar, keyboard, dan alat musik lain untuk event maupun latihan.', 'icon' => 'guitar', 'is_active' => true],
        ];
        foreach ($cats as $c) Category::create($c);

        // Account owner per kategori (inventori & merchant dikelola per kategori)
        $this->createPerCategoryOwners();

        $fleetOwner = User::where('email', 'owner-mobil@marirent.com')->first() ?? $owner;
        $ownerHp = User::where('email', 'owner-sewa-hp@marirent.com')->first() ?? $owner;
        $ownerKamera = User::where('email', 'owner-sewa-kamera@marirent.com')->first() ?? $owner;
        $ownerTenda = User::where('email', 'owner-sewa-tenda@marirent.com')->first() ?? $owner;

        // Driver profile
        \App\Models\Driver::create([
            'user_id' => $driverUser->id,
            'owner_id' => $fleetOwner->id,
            'license_number' => 'SIM-A-12345',
            'license_type' => 'A',
            'daily_salary' => 150000,
            'trip_salary' => 50000,
            'status' => 'off_duty',
            'is_active' => true,
        ]);

        $mobilCat = Category::where('slug', 'mobil')->first();
        $motorCat = Category::where('slug', 'motor')->first();
        $hpCat = Category::where('slug', 'sewa-hp')->first();
        $kameraCat = Category::where('slug', 'sewa-kamera')->first();
        $tendaCat = Category::where('slug', 'sewa-tenda')->first();

        // Phones (sample only - bulk in ElectronicsSeeder)
        Phone::create([
            'category_id' => $hpCat->id, 'owner_id' => $ownerHp->id,
            'name' => 'iPhone 15 Pro Max', 'slug' => 'iphone-15-pro-max',
            'brand' => 'Apple', 'phone_model' => 'iPhone 15 Pro Max',
            'storage_capacity' => '256GB', 'ram' => '8GB', 'color' => 'Black Titanium',
            'description' => 'iPhone 15 Pro Max 256GB, chip A17 Pro, kamera 48MP. Cocok untuk konten dan dokumentasi.',
            'daily_price' => 200000, 'weekly_price' => 1200000, 'monthly_price' => 4000000, 'hourly_price' => 35000,
            'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
        ]);
        Phone::create([
            'category_id' => $hpCat->id, 'owner_id' => $ownerHp->id,
            'name' => 'Samsung Galaxy S24 Ultra', 'slug' => 'samsung-galaxy-s24-ultra',
            'brand' => 'Samsung', 'phone_model' => 'Galaxy S24 Ultra',
            'storage_capacity' => '256GB', 'ram' => '12GB', 'color' => 'Titanium Black',
            'description' => 'Samsung Galaxy S24 Ultra 256GB, S-Pen, kamera 200MP, AI features.',
            'daily_price' => 175000, 'weekly_price' => 1050000, 'monthly_price' => 3500000, 'hourly_price' => 30000,
            'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
        ]);
        Phone::create([
            'category_id' => $hpCat->id, 'owner_id' => $ownerHp->id,
            'name' => 'Xiaomi 14 Ultra', 'slug' => 'xiaomi-14-ultra',
            'brand' => 'Xiaomi', 'phone_model' => '14 Ultra',
            'storage_capacity' => '512GB', 'ram' => '16GB', 'color' => 'Black',
            'description' => 'Xiaomi 14 Ultra, kamera Leica, Snapdragon 8 Gen 3, layar 2K AMOLED.',
            'daily_price' => 150000, 'weekly_price' => 900000, 'monthly_price' => 3000000, 'hourly_price' => 25000,
            'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
        ]);

        // Cameras
        Camera::create([
            'category_id' => $kameraCat->id, 'owner_id' => $ownerKamera->id,
            'name' => 'Sony A7 IV Kit Lens', 'slug' => 'sony-a7iv-kit',
            'brand' => 'Sony', 'camera_model' => 'A7 IV',
            'sensor_size' => 'Full Frame', 'lens_included' => '28-70mm f/3.5-5.6',
            'accessories' => ['battery', 'charger', 'strap', 'bag'],
            'description' => 'Sony A7 IV dengan kit lens 28-70mm, full-frame mirrorless 33MP untuk foto & video.',
            'daily_price' => 350000, 'weekly_price' => 2100000, 'monthly_price' => 7000000, 'hourly_price' => 50000,
            'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
        ]);
        Camera::create([
            'category_id' => $kameraCat->id, 'owner_id' => $ownerKamera->id,
            'name' => 'Canon EOS R6 Mark II', 'slug' => 'canon-eos-r6ii',
            'brand' => 'Canon', 'camera_model' => 'EOS R6 Mark II',
            'sensor_size' => 'Full Frame', 'lens_included' => 'Body Only',
            'accessories' => ['battery', 'charger', 'strap'],
            'description' => 'Canon EOS R6 Mark II, kamera hybrid 24.2MP untuk foto dan video profesional.',
            'daily_price' => 325000, 'weekly_price' => 1950000, 'monthly_price' => 6500000, 'hourly_price' => 45000,
            'status' => 'available', 'condition' => 'good', 'is_active' => true,
        ]);
        Camera::create([
            'category_id' => $kameraCat->id, 'owner_id' => $ownerKamera->id,
            'name' => 'GoPro Hero 12 Black', 'slug' => 'gopro-hero-12-black',
            'brand' => 'GoPro', 'camera_model' => 'Hero 12 Black',
            'sensor_size' => '1/1.9"', 'lens_included' => 'Built-in',
            'accessories' => ['mount', 'charger', 'case'],
            'description' => 'GoPro Hero 12 Black, action camera waterproof 5.3K, stabilisasi HyperSmooth 6.0.',
            'daily_price' => 125000, 'weekly_price' => 750000, 'monthly_price' => 2500000, 'hourly_price' => 20000,
            'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
        ]);

        // Camping Equipments
        CampingEquipment::create([
            'category_id' => $tendaCat->id, 'owner_id' => $ownerTenda->id,
            'name' => 'Eiger Torro 4P Tent', 'slug' => 'eiger-torro-4p',
            'brand' => 'Eiger', 'equipment_model' => 'Torro 4P',
            'type' => 'Tenda Dome', 'capacity' => 4, 'weight' => '3.5 kg', 'material' => 'Polyester 210D',
            'description' => 'Tenda dome 4 orang, waterproof, cocok untuk camping keluarga.',
            'daily_price' => 100000, 'weekly_price' => 600000, 'monthly_price' => 2000000, 'hourly_price' => 15000,
            'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
        ]);
        CampingEquipment::create([
            'category_id' => $tendaCat->id, 'owner_id' => $ownerTenda->id,
            'name' => 'Naturehike Cloud Up 2', 'slug' => 'naturehike-cloudup-2',
            'brand' => 'Naturehike', 'equipment_model' => 'Cloud Up 2',
            'type' => 'Tenda Dome', 'capacity' => 2, 'weight' => '1.5 kg', 'material' => 'Ripstop Nylon 20D',
            'description' => 'Tenda ultralight 2 orang, ringan dan compact untuk hiking dan backpacking.',
            'daily_price' => 75000, 'weekly_price' => 450000, 'monthly_price' => 1500000, 'hourly_price' => 12000,
            'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
        ]);
        CampingEquipment::create([
            'category_id' => $tendaCat->id, 'owner_id' => $ownerTenda->id,
            'name' => 'Matras Sleeping Bag Comfort', 'slug' => 'sleeping-bag-comfort',
            'brand' => 'Consina', 'equipment_model' => 'Comfort 300',
            'type' => 'Sleeping Bag', 'capacity' => 1, 'weight' => '1.8 kg', 'material' => 'Polycotton',
            'description' => 'Sleeping bag nyaman untuk suhu 10-30°C, cocok untuk camping di dataran rendah.',
            'daily_price' => 40000, 'weekly_price' => 240000, 'monthly_price' => 800000, 'hourly_price' => 8000,
            'status' => 'available', 'condition' => 'good', 'is_active' => true,
        ]);

        echo "Seed completed!\n";
        echo "Login: admin@marirent.com / password (superadmin)\n";
        echo "Login: owner@marirent.com / password (owner - merchant 1)\n";
        echo "Login: admin1@marirent.com / password (admin merchant 1)\n";
        echo "Login: owner2@marirent.com / password (owner merchant 2)\n";
        echo "Login: user@marirent.com / password (user)\n";
        echo "Login: driver@marirent.com / password (driver)\n";

        // Run demo data
        $this->call(DemoSeeder::class);

        // Run bulk vehicle seed data (50 mobil + 50 motor)
        $this->call(VehicleSeeder::class);

        // Run bulk electronics seed data (50+ per category)
        $this->call(ElectronicsSeeder::class);

        // Run additional products seed (Playstation, Drone, Alat Musik)
        $this->call(AdditionalProductsSeeder::class);

        // Seed foto katalog brand sesuai kategori & brand (semua gambar)
        $this->call(BrandCatalogPhotoSeeder::class);

        // Pastikan tidak ada item produk yang kosong gambar
        $this->backfillItemImages();

        $this->createPerCategoryAdmins();
    }

    private function backfillItemImages(): void
    {
        $mobilCat = Category::where('slug', 'mobil')->first();
        $motorCat = Category::where('slug', 'motor')->first();

        $defs = [
            ['items' => Vehicle::where('category_id', $mobilCat?->id)->get(), 'folder' => 'Mobil/Mobil'],
            ['items' => Vehicle::where('category_id', $motorCat?->id)->get(), 'folder' => 'Motor/Motor'],
            ['items' => Phone::all(), 'folder' => 'Hp'],
            ['items' => Camera::all(), 'folder' => 'Kamera'],
            ['items' => CampingEquipment::all(), 'folder' => 'tenda'],
            ['items' => \App\Models\Playstation::all(), 'folder' => 'Playstation'],
            ['items' => \App\Models\Drone::all(), 'folder' => 'Drone'],
            ['items' => \App\Models\MusicalInstrument::all(), 'folder' => 'Musik'],
        ];

        $updated = 0;
        foreach ($defs as $def) {
            foreach ($def['items'] as $item) {
                if ($item->image) {
                    continue;
                }
                $model = $item->model
                    ?? $item->phone_model
                    ?? $item->camera_model
                    ?? $item->equipment_model
                    ?? $item->console_model
                    ?? $item->drone_model
                    ?? $item->instrument_model
                    ?? null;
                $path = SeedMediaHelper::resolve($def['folder'], (string) $item->name, $model, (string) $item->brand);
                if ($path) {
                    $item->update(['image' => $path]);
                    $updated++;
                }
            }
        }

        echo "Backfill item images: {$updated} items\n";
    }

    private function createPerCategoryOwners(): void
    {
        $categorySlugs = ['mobil', 'motor', 'sewa-hp', 'sewa-kamera', 'sewa-tenda', 'sewa-ps', 'sewa-drone', 'sewa-alat-musik'];
        $phoneBase = 81200000;

        foreach ($categorySlugs as $index => $slug) {
            $category = Category::where('slug', $slug)->first();

            if (!$category) {
                continue;
            }

            $email = 'owner-' . $slug . '@marirent.com';

            if (User::where('email', $email)->exists()) {
                continue;
            }

            User::create([
                'name' => 'Owner ' . $category->name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'owner',
                'category_id' => $category->id,
                'phone' => '0' . ($phoneBase + $index),
                'is_active' => true,
            ]);

            echo "Login: {$email} / password (owner kategori {$category->name})\n";
        }
    }

    private function createPerCategoryAdmins(): void
    {
        $owner1 = User::where('email', 'owner@marirent.com')->first();

        if (!$owner1) {
            return;
        }

        $categorySlugs = ['sewa-hp', 'sewa-kamera', 'sewa-tenda', 'sewa-ps', 'sewa-drone', 'sewa-alat-musik', 'mobil', 'motor'];
        $phoneBase = 81000000;

        foreach ($categorySlugs as $index => $slug) {
            $category = Category::where('slug', $slug)->first();

            if (!$category) {
                continue;
            }

            $email = 'admin-' . $slug . '@marirent.com';

            if (User::where('email', $email)->exists()) {
                continue;
            }

            $categoryOwner = User::where('role', 'owner')
                ->where('category_id', $category->id)
                ->first();
            $merchantId = $categoryOwner?->id ?? $owner1->id;

            User::create([
                'name' => 'Admin ' . $category->name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'admin',
                'owner_id' => $merchantId,
                'category_id' => $category->id,
                'phone' => '0' . ($phoneBase + $index),
                'is_active' => true,
            ]);

            echo "Login: {$email} / password (admin kategori {$category->name})\n";
        }
    }
}
