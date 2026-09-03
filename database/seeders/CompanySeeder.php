<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Company;
use App\Models\Vehicle;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;
use App\Models\Playstation;
use App\Models\Drone;
use App\Models\MusicalInstrument;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            // ===== Kategori Mobil =====
            [
                'store' => 'Jaya Abadi Rent Car',
                'owner' => 'Rudi Jaya',
                'email' => 'company.jayarent@marirent.com',
                'category' => 'mobil',
                'city' => 'Jakarta',
                'description' => 'Penyedia sewa mobil LCGC hingga MPV dengan armada terawat dan harga terjangkau di Jakarta.',
                'address' => 'Jl. MH Thamrin Kav. 12, Jakarta Pusat',
                'pickup' => 'Jl. MH Thamrin Kav. 12, Jakarta Pusat',
                'phone' => '081211110001',
            ],
            [
                'store' => 'Gunung Mas Auto Rental',
                'owner' => 'Hendra Wijaya',
                'email' => 'company.gunungmas@marirent.com',
                'category' => 'mobil',
                'city' => 'Bandung',
                'description' => 'Rental mobil Bandung dengan layanan antar-jemput dan unit SUV untuk wisata.',
                'address' => 'Jl. Dago No. 27, Bandung',
                'pickup' => 'Jl. Dago No. 27, Bandung',
                'phone' => '081211110002',
            ],
            // ===== Kategori Motor =====
            [
                'store' => 'Berkah Motor Rental',
                'owner' => 'Agus Salim',
                'email' => 'company.berkahmotor@marirent.com',
                'category' => 'motor',
                'city' => 'Surabaya',
                'description' => 'Rental motor matic dan sport untuk kebutuhan harian maupun touring di Surabaya.',
                'address' => 'Jl. Ahmad Yani No. 154, Surabaya',
                'pickup' => 'Jl. Ahmad Yani No. 154, Surabaya',
                'phone' => '081211110003',
            ],
            [
                'store' => 'Cakrawala Motor Touring',
                'owner' => 'Bagus Pratama',
                'email' => 'company.cakrawala@marirent.com',
                'category' => 'motor',
                'city' => 'Yogyakarta',
                'description' => 'Penyewaan motor untuk wisata dan touring Jogja dengan kondisi prima.',
                'address' => 'Jl. Malioboro No. 8, Yogyakarta',
                'pickup' => 'Jl. Malioboro No. 8, Yogyakarta',
                'phone' => '081211110004',
            ],
            // ===== Kategori HP =====
            [
                'store' => 'TechShare Smartphone Rental',
                'owner' => 'Putri Maharani',
                'email' => 'company.techshare@marirent.com',
                'category' => 'sewa-hp',
                'city' => 'Jakarta',
                'description' => 'Sewa smartphone flagship terbaru untuk kebutuhan konten, kerja, atau trial.',
                'address' => 'Ruko Kelapa Gading Blok C No. 21, Jakarta Utara',
                'pickup' => 'Ruko Kelapa Gading Blok C No. 21, Jakarta Utara',
                'phone' => '081211110005',
            ],
            [
                'store' => 'GadgetLoan Indonesia',
                'owner' => 'Steven Kurniawan',
                'email' => 'company.gadgetloan@marirent.com',
                'category' => 'sewa-hp',
                'city' => 'Medan',
                'description' => 'Rental HP Android dan iPhone dengan garansi perangkat dan opsi DP ringan.',
                'address' => 'Jl. Gatot Subroto No. 45, Medan',
                'pickup' => 'Jl. Gatot Subroto No. 45, Medan',
                'phone' => '081211110006',
            ],
            // ===== Kategori Kamera =====
            [
                'store' => 'LensaPro Studio Rental',
                'owner' => 'Dewi Anggraini',
                'email' => 'company.lensapro@marirent.com',
                'category' => 'sewa-kamera',
                'city' => 'Denpasar',
                'description' => 'Sewa kamera mirrorless, DSLR, dan lensa lengkap untuk foto prewedding di Bali.',
                'address' => 'Jl. Raya Kuta No. 78, Denpasar',
                'pickup' => 'Jl. Raya Kuta No. 78, Denpasar',
                'phone' => '081211110007',
            ],
            [
                'store' => 'VisualStory Camera Rental',
                'owner' => 'Fajar Ramadhan',
                'email' => 'company.visualstory@marirent.com',
                'category' => 'sewa-kamera',
                'city' => 'Bandung',
                'description' => 'Penyediaan kamera dan peralatan sinematografi untuk produksi film dan event.',
                'address' => 'Jl. Buah Batu No. 99, Bandung',
                'pickup' => 'Jl. Buah Batu No. 99, Bandung',
                'phone' => '081211110008',
            ],
            // ===== Kategori Tenda/Camping =====
            [
                'store' => 'Outdoor Gear Rentals',
                'owner' => 'Dimas Prasetyo',
                'email' => 'company.outdoorgear@marirent.com',
                'category' => 'sewa-tenda',
                'city' => 'Malang',
                'description' => 'Rental perlengkapan camping di kaki Bromo: tenda, matras, sleeping bag, kompor.',
                'address' => 'Jl. Ijen No. 12, Malang',
                'pickup' => 'Jl. Ijen No. 12, Malang',
                'phone' => '081211110009',
            ],
            [
                'store' => 'Alam Camping Supply',
                'owner' => 'Nadia Kusuma',
                'email' => 'company.alamcamping@marirent.com',
                'category' => 'sewa-tenda',
                'city' => 'Semarang',
                'description' => 'Rental alat camping dan outdoor lengkap untuk kebutuhan keluarga dan komunitas.',
                'address' => 'Jl. Pandanaran No. 30, Semarang',
                'pickup' => 'Jl. Pandanaran No. 30, Semarang',
                'phone' => '081211110010',
            ],
            // ===== Kategori Playstation =====
            [
                'store' => 'GameZone Console Rental',
                'owner' => 'Reza Firmansyah',
                'email' => 'company.gamezone@marirent.com',
                'category' => 'sewa-ps',
                'city' => 'Tangerang',
                'description' => 'Sewa konsol Playstation beserta controller dan game untuk acara atau koleksi.',
                'address' => 'Ruko BSD Serpong Blok D No. 5, Tangerang',
                'pickup' => 'Ruko BSD Serpong Blok D No. 5, Tangerang',
                'phone' => '081211110011',
            ],
            [
                'store' => 'BermainStudio Entertainment',
                'owner' => 'Gita Lestari',
                'email' => 'company.bermainstudio@marirent.com',
                'category' => 'sewa-ps',
                'city' => 'Bekasi',
                'description' => 'Rental PS5 dan PS4 untuk bazar, ulang tahun, dan gathering perusahaan.',
                'address' => 'Jl. Sumatera No. 18, Bekasi',
                'pickup' => 'Jl. Sumatera No. 18, Bekasi',
                'phone' => '081211110012',
            ],
            // ===== Kategori Drone =====
            [
                'store' => 'AeroVision Drone Rental',
                'owner' => 'Yoga Setiawan',
                'email' => 'company.aerovision@marirent.com',
                'category' => 'sewa-drone',
                'city' => 'Surabaya',
                'description' => 'Sewa drone dengan pilot berpengalaman untuk kebutuhan aerial photography.',
                'address' => 'Jl. Pemuda No. 66, Surabaya',
                'pickup' => 'Jl. Pemuda No. 66, Surabaya',
                'phone' => '081211110013',
            ],
            [
                'store' => 'SkyCapture Drone Service',
                'owner' => 'Andika Wicaksono',
                'email' => 'company.skycapture@marirent.com',
                'category' => 'sewa-drone',
                'city' => 'Makassar',
                'description' => 'Penyewaan drone untuk event, properti, dan dokumentasi lahan di Makassar.',
                'address' => 'Jl. Sudirman No. 54, Makassar',
                'pickup' => 'Jl. Sudirman No. 54, Makassar',
                'phone' => '081211110014',
            ],
            // ===== Kategori Alat Musik =====
            [
                'store' => 'Melodi Musik Rental',
                'owner' => 'Ryan Hartanto',
                'email' => 'company.melodi@marirent.com',
                'category' => 'sewa-alat-musik',
                'city' => 'Bandung',
                'description' => 'Sewa gitar, keyboard, drum untuk band event, studio, dan latihan.',
                'address' => 'Jl. Braga No. 33, Bandung',
                'pickup' => 'Jl. Braga No. 33, Bandung',
                'phone' => '081211110015',
            ],
            [
                'store' => 'NadaNusantara Music',
                'owner' => 'Sari Amelia',
                'email' => 'company.nadanusantara@marirent.com',
                'category' => 'sewa-alat-musik',
                'city' => 'Surabaya',
                'description' => 'Penyediaan alat musik berkualitas untuk kebutuhan acara dan sekolah musik.',
                'address' => 'Jl. Darmo No. 22, Surabaya',
                'pickup' => 'Jl. Darmo No. 22, Surabaya',
                'phone' => '081211110016',
            ],
        ];

        foreach ($companies as $data) {
            $category = \App\Models\Category::where('slug', $data['category'])->first();

            if (!$category) {
                echo "Warning: Kategori '{$data['category']}' tidak ditemukan, dilewati.\n";
                continue;
            }

            if (User::where('email', $data['email'])->exists()) {
                continue;
            }

            // 1. User owner
            $owner = User::create([
                'name' => $data['owner'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'owner',
                'category_id' => $category->id,
                'phone' => $data['phone'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // 2. Merchant / toko
            $merchant = Merchant::create([
                'user_id' => $owner->id,
                'slug' => $this->uniqueSlug($data['store']),
                'name' => $data['store'],
                'description' => $data['description'],
                'company_email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'city' => $data['city'],
                'pickup_address' => $data['pickup'],
                'operational_hours' => '08.00 - 20.00',
                'commission_rate' => mt_rand(7, 12),
                'is_active' => true,
                'status' => 'active',
                'verified_at' => now(),
            ]);

            // 2b. Company profile (tabel khusus `companies`)
            \App\Models\Company::firstOrCreate(
                ['user_id' => $owner->id],
                $merchant->only([
                    'slug', 'name', 'description', 'logo', 'banner', 'phone', 'company_email',
                    'website', 'instagram', 'address', 'city', 'pickup_address', 'operational_hours',
                    'commission_rate', 'is_active', 'status', 'verified_at',
                ])
            );

            // 3. Admin merchant
            $adminEmail = 'admin-' . Str::slug($data['store']) . '@marirent.com';
            if (!User::where('email', $adminEmail)->exists()) {
                User::create([
                    'name' => 'Admin ' . $data['store'],
                    'email' => $adminEmail,
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'owner_id' => $owner->id,
                    'category_id' => $category->id,
                    'phone' => $data['phone'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            }

            // 4. Unit inventori sesuai kategori
            $this->seedInventory($category->slug, $owner->id, $category->id);

            echo "Company '{$data['store']}' (owner {$data['email']}) dibuat - {$data['city']}\n";
        }
    }

    private function seedInventory(string $slug, int $ownerId, int $categoryId): void
    {
        switch ($slug) {
            case 'mobil':
                Vehicle::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'Toyota Innova Zenix', 'slug' => $this->uniqueSlug('toyota-innova-zenix-' . $ownerId),
                    'brand' => 'Toyota', 'model' => 'Innova Zenix Hybrid', 'year' => 2024,
                    'color' => 'Silver', 'license_plate' => $this->uniquePlate('B'),
                    'description' => 'MPV premium hybrid, nyaman untuk keluarga atau travel.',
                    'daily_price' => 700000, 'with_driver_daily_price' => 850000,
                    'seats' => 7, 'transmission' => 'automatic', 'fuel_type' => 'hybrid', 'with_driver' => true,
                    'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
                ]);
                Vehicle::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'Daihatsu Xenia', 'slug' => $this->uniqueSlug('daihatsu-xenia-' . $ownerId),
                    'brand' => 'Daihatsu', 'model' => 'Xenia R', 'year' => 2022,
                    'color' => 'White', 'license_plate' => $this->uniquePlate('B'),
                    'description' => 'MPV ekonomis 7 seater untuk kebutuhan keluarga.',
                    'daily_price' => 350000, 'with_driver_daily_price' => 500000,
                    'seats' => 7, 'transmission' => 'automatic', 'fuel_type' => 'bensin', 'with_driver' => true,
                    'status' => 'available', 'condition' => 'good', 'is_active' => true,
                ]);
                break;

            case 'motor':
                Vehicle::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'Yamaha NMAX 155', 'slug' => $this->uniqueSlug('yamaha-nmax-' . $ownerId),
                    'brand' => 'Yamaha', 'model' => 'NMAX 155', 'year' => 2023,
                    'color' => 'Matte Black', 'license_plate' => $this->uniquePlate('L'),
                    'description' => 'Motor matic premium dengan TCS, cocok untuk harian.',
                    'daily_price' => 120000, 'with_driver_daily_price' => null,
                    'seats' => 2, 'transmission' => 'automatic', 'fuel_type' => 'bensin', 'with_driver' => false,
                    'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
                ]);
                Vehicle::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'Honda Vario 160', 'slug' => $this->uniqueSlug('honda-vario-' . $ownerId),
                    'brand' => 'Honda', 'model' => 'Vario 160', 'year' => 2022,
                    'color' => 'Red', 'license_plate' => $this->uniquePlate('L'),
                    'description' => 'Matic 160cc sporty dan irit untuk mobilitas harian.',
                    'daily_price' => 100000, 'with_driver_daily_price' => null,
                    'seats' => 2, 'transmission' => 'automatic', 'fuel_type' => 'bensin', 'with_driver' => false,
                    'status' => 'available', 'condition' => 'good', 'is_active' => true,
                ]);
                break;

            case 'sewa-hp':
                Phone::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'iPhone 15 Pro', 'slug' => $this->uniqueSlug('iphone-15-pro-' . $ownerId),
                    'brand' => 'Apple', 'phone_model' => 'iPhone 15 Pro', 'storage_capacity' => '128GB',
                    'ram' => '8GB', 'color' => 'Natural Titanium',
                    'description' => 'Flagship iPhone, titanium, kamera pro untuk content creator.',
                    'daily_price' => 150000, 'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
                ]);
                Phone::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'Samsung Z Flip 5', 'slug' => $this->uniqueSlug('samsung-zflip5-' . $ownerId),
                    'brand' => 'Samsung', 'phone_model' => 'Z Flip 5', 'storage_capacity' => '256GB',
                    'ram' => '8GB', 'color' => 'Mint',
                    'description' => 'HP lipat compact, stylish dan fleksibel.',
                    'daily_price' => 125000, 'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
                ]);
                break;

            case 'sewa-kamera':
                Camera::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'Fujifilm X-T5', 'slug' => $this->uniqueSlug('fujifilm-xt5-' . $ownerId),
                    'brand' => 'Fujifilm', 'camera_model' => 'X-T5', 'sensor_size' => 'APS-C',
                    'lens_included' => '18-55mm', 'accessories' => ['battery', 'charger', 'bag'],
                    'description' => 'Mirrorless stylish dengan film simulation, favorit content creator.',
                    'daily_price' => 300000, 'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
                ]);
                Camera::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'DJI Osmo Pocket 3', 'slug' => $this->uniqueSlug('dji-osmo-pocket3-' . $ownerId),
                    'brand' => 'DJI', 'camera_model' => 'Osmo Pocket 3', 'sensor_size' => '1"',
                    'lens_included' => 'Built-in', 'accessories' => ['mount', 'charging case'],
                    'description' => 'Kamera gimbal pocket untuk vlogging dan video 4K.',
                    'daily_price' => 150000, 'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
                ]);
                break;

            case 'sewa-tenda':
                CampingEquipment::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'Eiger Alpine Pro Tent', 'slug' => $this->uniqueSlug('eiger-alpine-pro-' . $ownerId),
                    'brand' => 'Eiger', 'equipment_model' => 'Torro 2P', 'type' => 'Tenda Dome',
                    'capacity' => 4, 'weight' => '3.6 kg', 'material' => 'Polyester 210D',
                    'description' => 'Tenda dome 4 orang waterproof, kuat dan lapang.',
                    'daily_price' => 90000, 'status' => 'available', 'condition' => 'good', 'is_active' => true,
                ]);
                CampingEquipment::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'Naturehike Sleeping Pad', 'slug' => $this->uniqueSlug('naturehike-sleepingpad-' . $ownerId),
                    'brand' => 'Naturehike', 'equipment_model' => 'Air Cushion', 'type' => 'Matras',
                    'capacity' => 1, 'weight' => '0.5 kg', 'material' => 'Nylon Ripstop',
                    'description' => 'Matras tiup nyaman dan ringan untuk camping.',
                    'daily_price' => 35000, 'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
                ]);
                break;

            case 'sewa-ps':
                Playstation::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'PlayStation 5', 'slug' => $this->uniqueSlug('ps5-' . $ownerId),
                    'brand' => 'Sony', 'console_model' => 'PS5 Slim', 'storage_capacity' => '1TB',
                    'controllers_count' => 2, 'color' => 'White', 'accessories' => ['controller', 'cable', 'stand'],
                    'description' => 'PS5 Slim 1TB dengan 2 controller, siap untuk acara atau koleksi.',
                    'daily_price' => 150000, 'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
                ]);
                break;

            case 'sewa-drone':
                Drone::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'DJI Mini 4 Pro', 'slug' => $this->uniqueSlug('dji-mini4pro-' . $ownerId),
                    'brand' => 'DJI', 'drone_model' => 'Mini 4 Pro', 'camera_resolution' => '4K/60fps',
                    'flight_time' => '34 menit', 'max_range' => '20 km', 'weight' => '249 g',
                    'accessories' => ['remote', '3 battery', 'charger'],
                    'description' => 'Drone mini sub-250g dengan kamera 4K, wajib coba.',
                    'daily_price' => 200000, 'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
                ]);
                break;

            case 'sewa-alat-musik':
                MusicalInstrument::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'Fender Stratocaster', 'slug' => $this->uniqueSlug('fender-strat-' . $ownerId),
                    'brand' => 'Fender', 'instrument_type' => 'gitar', 'instrument_model' => 'Player Stratocaster',
                    'color' => 'Olympic White', 'accessories' => ['gigbag', 'strap', 'kabel'],
                    'description' => 'Gitar elektrik klasik untuk rekaman dan panggung.',
                    'daily_price' => 120000, 'status' => 'available', 'condition' => 'good', 'is_active' => true,
                ]);
                MusicalInstrument::create([
                    'category_id' => $categoryId, 'owner_id' => $ownerId,
                    'name' => 'Yamaha PSR-E373', 'slug' => $this->uniqueSlug('yamaha-psr-e373-' . $ownerId),
                    'brand' => 'Yamaha', 'instrument_type' => 'keyboard', 'instrument_model' => 'PSR-E373',
                    'color' => 'Black', 'accessories' => ['stand', 'adaptor'],
                    'description' => 'Keyboard 61-tombol dengan 622 suara, cocok belajar dan tampil.',
                    'daily_price' => 100000, 'status' => 'available', 'condition' => 'excellent', 'is_active' => true,
                ]);
                break;
        }
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name) ?: Str::random(6);
        $base = $slug;
        $i = 1;
        while (Merchant::where('slug', $slug)->exists()
            || \App\Models\Vehicle::where('slug', $slug)->exists()
            || \App\Models\Phone::where('slug', $slug)->exists()
            || \App\Models\Camera::where('slug', $slug)->exists()
            || \App\Models\CampingEquipment::where('slug', $slug)->exists()
            || \App\Models\Playstation::where('slug', $slug)->exists()
            || \App\Models\Drone::where('slug', $slug)->exists()
            || \App\Models\MusicalInstrument::where('slug', $slug)->exists()
        ) {
            $slug = $base . '-' . ($i++);
        }
        return $slug;
    }

    private function uniquePlate(string $region): string
    {
        $letters = 'ABCDEFGHJKLMNPRSTUVWXYZ';
        do {
            $plate = $region . ' ' . mt_rand(1000, 9999) . ' ' . $letters[mt_rand(0, 23)] . $letters[mt_rand(0, 23)];
        } while (\App\Models\Vehicle::where('license_plate', $plate)->exists());
        return $plate;
    }
}
