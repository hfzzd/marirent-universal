<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;
use App\Models\Category;
use App\Models\User;

class ElectronicsSeeder extends Seeder
{
    public function run(): void
    {
        $hpCat = Category::where('slug', 'sewa-hp')->first();
        $kameraCat = Category::where('slug', 'sewa-kamera')->first();
        $tendaCat = Category::where('slug', 'sewa-tenda')->first();

        $ownerHp = $hpCat
            ? User::where('role', 'owner')->where('category_id', $hpCat->id)->first()
            : null;
        $ownerKamera = $kameraCat
            ? User::where('role', 'owner')->where('category_id', $kameraCat->id)->first()
            : null;
        $ownerTenda = $tendaCat
            ? User::where('role', 'owner')->where('category_id', $tendaCat->id)->first()
            : null;

        $ownerHp ??= User::where('role', 'owner')->first();
        $ownerKamera ??= $ownerHp;
        $ownerTenda ??= $ownerHp;

        // ── PHONES (50+) ────────────────────────────────────────────
        $phones = [
            // Apple iPhone Series
            ['name' => 'iPhone 15 Pro Max', 'brand' => 'Apple', 'phone_model' => 'iPhone 15 Pro Max', 'storage' => '256GB', 'ram' => '8GB', 'color' => 'Black Titanium', 'daily' => 200000, 'desc' => 'iPhone 15 Pro Max, chip A17 Pro, kamera 48MP Action Button.'],
            ['name' => 'iPhone 15 Pro', 'brand' => 'Apple', 'phone_model' => 'iPhone 15 Pro', 'storage' => '128GB', 'ram' => '8GB', 'color' => 'Natural Titanium', 'daily' => 175000, 'desc' => 'iPhone 15 Pro, titanium design, A17 Pro chip.'],
            ['name' => 'iPhone 15', 'brand' => 'Apple', 'phone_model' => 'iPhone 15', 'storage' => '128GB', 'ram' => '6GB', 'color' => 'Pink', 'daily' => 150000, 'desc' => 'iPhone 15, Dynamic Island, USB-C.'],
            ['name' => 'iPhone 14 Pro Max', 'brand' => 'Apple', 'phone_model' => 'iPhone 14 Pro Max', 'storage' => '256GB', 'ram' => '6GB', 'color' => 'Deep Purple', 'daily' => 160000, 'desc' => 'iPhone 14 Pro Max, Always-On Display, 48MP camera.'],
            ['name' => 'iPhone 14 Pro', 'brand' => 'Apple', 'phone_model' => 'iPhone 14 Pro', 'storage' => '128GB', 'ram' => '6GB', 'color' => 'Space Black', 'daily' => 140000, 'desc' => 'iPhone 14 Pro, A16 Bionic, ProMotion.'],
            ['name' => 'iPhone 14', 'brand' => 'Apple', 'phone_model' => 'iPhone 14', 'storage' => '128GB', 'ram' => '6GB', 'color' => 'Blue', 'daily' => 120000, 'desc' => 'iPhone 14, 12MP dual camera, Crash Detection.'],
            ['name' => 'iPhone 13 Pro Max', 'brand' => 'Apple', 'phone_model' => 'iPhone 13 Pro Max', 'storage' => '256GB', 'ram' => '6GB', 'color' => 'Sierra Blue', 'daily' => 130000, 'desc' => 'iPhone 13 Pro Max, 120Hz ProMotion, macro photography.'],
            ['name' => 'iPhone 13', 'brand' => 'Apple', 'phone_model' => 'iPhone 13', 'storage' => '128GB', 'ram' => '4GB', 'color' => 'Starlight', 'daily' => 100000, 'desc' => 'iPhone 13, A15 Bionic, Cinematic mode.'],
            ['name' => 'iPhone 12', 'brand' => 'Apple', 'phone_model' => 'iPhone 12', 'storage' => '64GB', 'ram' => '4GB', 'color' => 'White', 'daily' => 80000, 'desc' => 'iPhone 12, 5G, MagSafe.'],
            ['name' => 'iPhone SE 2022', 'brand' => 'Apple', 'phone_model' => 'iPhone SE', 'storage' => '128GB', 'ram' => '4GB', 'color' => 'Midnight', 'daily' => 65000, 'desc' => 'iPhone SE 2022, chip A15, 5G compact.'],

            // Samsung Galaxy S Series
            ['name' => 'Samsung Galaxy S24 Ultra', 'brand' => 'Samsung', 'phone_model' => 'Galaxy S24 Ultra', 'storage' => '256GB', 'ram' => '12GB', 'color' => 'Titanium Black', 'daily' => 175000, 'desc' => 'Samsung S24 Ultra, S-Pen, kamera 200MP, Galaxy AI.'],
            ['name' => 'Samsung Galaxy S24+', 'brand' => 'Samsung', 'phone_model' => 'Galaxy S24+', 'storage' => '256GB', 'ram' => '12GB', 'color' => 'Cobalt Violet', 'daily' => 140000, 'desc' => 'Samsung S24+, 6.7 inch AMOLED, AI features.'],
            ['name' => 'Samsung Galaxy S24', 'brand' => 'Samsung', 'phone_model' => 'Galaxy S24', 'storage' => '128GB', 'ram' => '8GB', 'color' => 'Amber Yellow', 'daily' => 120000, 'desc' => 'Samsung S24, compact flagship, AI-powered camera.'],
            ['name' => 'Samsung Galaxy S23 Ultra', 'brand' => 'Samsung', 'phone_model' => 'Galaxy S23 Ultra', 'storage' => '256GB', 'ram' => '12GB', 'color' => 'Phantom Black', 'daily' => 150000, 'desc' => 'Samsung S23 Ultra, 200MP, Snapdragon 8 Gen 2.'],
            ['name' => 'Samsung Galaxy S23', 'brand' => 'Samsung', 'phone_model' => 'Galaxy S23', 'storage' => '128GB', 'ram' => '8GB', 'color' => 'Lavender', 'daily' => 100000, 'desc' => 'Samsung S23, AMOLED 120Hz, Nightography.'],
            ['name' => 'Samsung Galaxy S22 Ultra', 'brand' => 'Samsung', 'phone_model' => 'Galaxy S22 Ultra', 'storage' => '256GB', 'ram' => '12GB', 'color' => 'Green', 'daily' => 120000, 'desc' => 'Samsung S22 Ultra, integrated S-Pen, 108MP.'],

            // Samsung Galaxy A Series
            ['name' => 'Samsung Galaxy A54', 'brand' => 'Samsung', 'phone_model' => 'Galaxy A54', 'storage' => '128GB', 'ram' => '8GB', 'color' => 'Awesome Violet', 'daily' => 55000, 'desc' => 'Samsung A54, IP67, OIS camera, 5000mAh.'],
            ['name' => 'Samsung Galaxy A34', 'brand' => 'Samsung', 'phone_model' => 'Galaxy A34', 'storage' => '128GB', 'ram' => '8GB', 'color' => 'Awesome Silver', 'daily' => 45000, 'desc' => 'Samsung A34, 120Hz AMOLED, 5000mAh.'],
            ['name' => 'Samsung Galaxy A14', 'brand' => 'Samsung', 'phone_model' => 'Galaxy A14', 'storage' => '64GB', 'ram' => '4GB', 'color' => 'Black', 'daily' => 30000, 'desc' => 'Samsung A14, budget-friendly, 50MP camera.'],

            // Xiaomi
            ['name' => 'Xiaomi 14 Ultra', 'brand' => 'Xiaomi', 'phone_model' => '14 Ultra', 'storage' => '512GB', 'ram' => '16GB', 'color' => 'Black', 'daily' => 150000, 'desc' => 'Xiaomi 14 Ultra, Leica quad camera, Snapdragon 8 Gen 3.'],
            ['name' => 'Xiaomi 14', 'brand' => 'Xiaomi', 'phone_model' => '14', 'storage' => '256GB', 'ram' => '12GB', 'color' => 'White', 'daily' => 110000, 'desc' => 'Xiaomi 14, Leica camera, Snapdragon 8 Gen 3.'],
            ['name' => 'Xiaomi 13T Pro', 'brand' => 'Xiaomi', 'phone_model' => '13T Pro', 'storage' => '256GB', 'ram' => '12GB', 'color' => 'Alpine Blue', 'daily' => 85000, 'desc' => 'Xiaomi 13T Pro, 144Hz AMOLED, 108MP camera.'],
            ['name' => 'Xiaomi Redmi Note 13 Pro+', 'brand' => 'Xiaomi', 'phone_model' => 'Redmi Note 13 Pro+', 'storage' => '256GB', 'ram' => '8GB', 'color' => 'Fusion Purple', 'daily' => 50000, 'desc' => 'Redmi Note 13 Pro+, 200MP, 120W fast charging.'],
            ['name' => 'Xiaomi Redmi Note 13', 'brand' => 'Xiaomi', 'phone_model' => 'Redmi Note 13', 'storage' => '128GB', 'ram' => '6GB', 'color' => 'Ice Blue', 'daily' => 35000, 'desc' => 'Redmi Note 13, AMOLED 120Hz, 108MP.'],
            ['name' => 'Xiaomi Poco X6 Pro', 'brand' => 'Xiaomi', 'phone_model' => 'Poco X6 Pro', 'storage' => '256GB', 'ram' => '8GB', 'color' => 'Yellow', 'daily' => 55000, 'desc' => 'Poco X6 Pro, 1.5K AMOLED, Dimensity 8300 Ultra.'],

            // Google Pixel
            ['name' => 'Google Pixel 8 Pro', 'brand' => 'Google', 'phone_model' => 'Pixel 8 Pro', 'storage' => '128GB', 'ram' => '12GB', 'color' => 'Obsidian', 'daily' => 130000, 'desc' => 'Google Pixel 8 Pro, Tensor G3, AI camera magic.'],
            ['name' => 'Google Pixel 8', 'brand' => 'Google', 'phone_model' => 'Pixel 8', 'storage' => '128GB', 'ram' => '8GB', 'color' => 'Hazel', 'daily' => 100000, 'desc' => 'Google Pixel 8, best-in-class computational photography.'],
            ['name' => 'Google Pixel 7a', 'brand' => 'Google', 'phone_model' => 'Pixel 7a', 'storage' => '128GB', 'ram' => '8GB', 'color' => 'Charcoal', 'daily' => 75000, 'desc' => 'Pixel 7a, Tensor G2, 64MP camera, compact.'],

            // OnePlus
            ['name' => 'OnePlus 12', 'brand' => 'OnePlus', 'phone_model' => '12', 'storage' => '256GB', 'ram' => '16GB', 'color' => 'Flowy Emerald', 'daily' => 100000, 'desc' => 'OnePlus 12, Snapdragon 8 Gen 3, 100W charging.'],
            ['name' => 'OnePlus 11', 'brand' => 'OnePlus', 'phone_model' => '11', 'storage' => '256GB', 'ram' => '16GB', 'color' => 'Titan Black', 'daily' => 80000, 'desc' => 'OnePlus 11, Hasselblad camera, 80W SUPERVOOC.'],
            ['name' => 'OnePlus Nord CE 4', 'brand' => 'OnePlus', 'phone_model' => 'Nord CE 4', 'storage' => '128GB', 'ram' => '8GB', 'color' => 'Dark Chrome', 'daily' => 50000, 'desc' => 'OnePlus Nord CE 4, Snapdragon 7 Gen 3, 100W.'],

            // Realme
            ['name' => 'Realme GT 5 Pro', 'brand' => 'Realme', 'phone_model' => 'GT 5 Pro', 'storage' => '256GB', 'ram' => '12GB', 'color' => 'Red Magic', 'daily' => 80000, 'desc' => 'Realme GT 5 Pro, Snapdragon 8 Gen 3, 50MP Sony IMX890.'],
            ['name' => 'Realme 12 Pro+', 'brand' => 'Realme', 'phone_model' => '12 Pro+', 'storage' => '256GB', 'ram' => '8GB', 'color' => 'Navigator Beige', 'daily' => 55000, 'desc' => 'Realme 12 Pro+, periscope telephoto 64MP, Snapdragon 7s Gen 2.'],
            ['name' => 'Realme C67', 'brand' => 'Realme', 'phone_model' => 'C67', 'storage' => '128GB', 'ram' => '6GB', 'color' => 'Sunny Oasis', 'daily' => 25000, 'desc' => 'Realme C67, 108MP, 5000mAh, budget king.'],

            // Oppo
            ['name' => 'Oppo Find X7 Ultra', 'brand' => 'Oppo', 'phone_model' => 'Find X7 Ultra', 'storage' => '256GB', 'ram' => '16GB', 'color' => 'Ocean Blue', 'daily' => 130000, 'desc' => 'Oppo Find X7 Ultra, dual periscope, Hasselblad.'],
            ['name' => 'Oppo Reno 11 Pro', 'brand' => 'Oppo', 'phone_model' => 'Reno 11 Pro', 'storage' => '256GB', 'ram' => '8GB', 'color' => 'Rococo Pearl', 'daily' => 65000, 'desc' => 'Oppo Reno 11 Pro, portrait camera, 80W SUPERVOOC.'],
            ['name' => 'Oppo A78', 'brand' => 'Oppo', 'phone_model' => 'A78', 'storage' => '128GB', 'ram' => '8GB', 'color' => 'Mist Black', 'daily' => 35000, 'desc' => 'Oppo A78, 5000mAh, 67W fast charge.'],

            // Vivo
            ['name' => 'Vivo X100 Pro', 'brand' => 'Vivo', 'phone_model' => 'X100 Pro', 'storage' => '256GB', 'ram' => '16GB', 'color' => 'Asteroid Black', 'daily' => 130000, 'desc' => 'Vivo X100 Pro, ZEISS APO telephoto, Dimensity 9300.'],
            ['name' => 'Vivo V30 Pro', 'brand' => 'Vivo', 'phone_model' => 'V30 Pro', 'storage' => '256GB', 'ram' => '8GB', 'color' => 'Petals Pink', 'daily' => 60000, 'desc' => 'Vivo V30 Pro, ZEISS portrait, 50MP AF front.'],
            ['name' => 'Vivo Y27', 'brand' => 'Vivo', 'phone_model' => 'Y27', 'storage' => '128GB', 'ram' => '6GB', 'color' => 'Stardust Black', 'daily' => 25000, 'desc' => 'Vivo Y27, 5000mAh, design premium.'],

            // Nothing
            ['name' => 'Nothing Phone 2a', 'brand' => 'Nothing', 'phone_model' => 'Phone 2a', 'storage' => '128GB', 'ram' => '8GB', 'color' => 'Black', 'daily' => 60000, 'desc' => 'Nothing Phone 2a, Glyph Interface, Dimensity 7200 Pro.'],
            ['name' => 'Nothing Phone 2', 'brand' => 'Nothing', 'phone_model' => 'Phone 2', 'storage' => '256GB', 'ram' => '12GB', 'color' => 'White', 'daily' => 85000, 'desc' => 'Nothing Phone 2, Snapdragon 8+ Gen 1, Glyph.'],

            // Sony
            ['name' => 'Sony Xperia 1 V', 'brand' => 'Sony', 'phone_model' => 'Xperia 1 V', 'storage' => '256GB', 'ram' => '12GB', 'color' => 'Black', 'daily' => 130000, 'desc' => 'Sony Xperia 1 V, 4K OLED, ZEISS optics, real-time tracking.'],

            // ASUS
            ['name' => 'ASUS ROG Phone 8 Pro', 'brand' => 'ASUS', 'phone_model' => 'ROG Phone 8 Pro', 'storage' => '512GB', 'ram' => '24GB', 'color' => 'Phantom Black', 'daily' => 150000, 'desc' => 'ROG Phone 8 Pro, Snapdragon 8 Gen 3, AirTrigger, gaming beast.'],
            ['name' => 'ASUS Zenfone 11 Ultra', 'brand' => 'ASUS', 'phone_model' => 'Zenfone 11 Ultra', 'storage' => '256GB', 'ram' => '16GB', 'color' => 'Eternal Black', 'daily' => 90000, 'desc' => 'Zenfone 11 Ultra, Snapdragon 8 Gen 3, 6.78 inch.'],

            // Motorola
            ['name' => 'Motorola Edge 50 Pro', 'brand' => 'Motorola', 'phone_model' => 'Edge 50 Pro', 'storage' => '256GB', 'ram' => '12GB', 'color' => 'Moonlight Pearl', 'daily' => 70000, 'desc' => 'Motorola Edge 50 Pro, 144Hz pOLED, 125W charge.'],

            // Infinix
            ['name' => 'Infinix Zero 30 5G', 'brand' => 'Infinix', 'phone_model' => 'Zero 30 5G', 'storage' => '256GB', 'ram' => '8GB', 'color' => 'Fantasy Purple', 'daily' => 40000, 'desc' => 'Infinix Zero 30 5G, 108MP OIS, vlogging camera.'],

            // Tecno
            ['name' => 'Tecno Phantom V Fold', 'brand' => 'Tecno', 'phone_model' => 'Phantom V Fold', 'storage' => '256GB', 'ram' => '12GB', 'color' => 'Black', 'daily' => 90000, 'desc' => 'Tecno Phantom V Fold, foldable flagship, 50MP triple.'],

            // iQOO
            ['name' => 'iQOO 12', 'brand' => 'iQOO', 'phone_model' => '12', 'storage' => '256GB', 'ram' => '12GB', 'color' => 'Alpha', 'daily' => 85000, 'desc' => 'iQOO 12, Snapdragon 8 Gen 3, 144Hz, gaming.'],

            // Poco
            ['name' => 'Poco F6 Pro', 'brand' => 'Poco', 'phone_model' => 'F6 Pro', 'storage' => '512GB', 'ram' => '12GB', 'color' => 'White', 'daily' => 65000, 'desc' => 'Poco F6 Pro, Snapdragon 8 Gen 2, 120W, flagship killer.'],

            // Huawei
            ['name' => 'Huawei Pura 70 Ultra', 'brand' => 'Huawei', 'phone_model' => 'Pura 70 Ultra', 'storage' => '512GB', 'ram' => '16GB', 'color' => 'Green', 'daily' => 140000, 'desc' => 'Huawei Pura 70 Ultra, retractable lens, XMAGE.'],

            // Redmi Note
            ['name' => 'Xiaomi Redmi Note 12 Pro', 'brand' => 'Xiaomi', 'phone_model' => 'Redmi Note 12 Pro', 'storage' => '128GB', 'ram' => '8GB', 'color' => 'Ice Blue', 'daily' => 42000, 'desc' => 'Redmi Note 12 Pro, 108MP OIS, 120Hz AMOLED.'],

            // Samsung Z Series
            ['name' => 'Samsung Galaxy Z Fold 5', 'brand' => 'Samsung', 'phone_model' => 'Galaxy Z Fold 5', 'storage' => '256GB', 'ram' => '12GB', 'color' => 'Icy Blue', 'daily' => 200000, 'desc' => 'Samsung Z Fold 5, foldable, Snapdragon 8 Gen 2, S Pen.'],
            ['name' => 'Samsung Galaxy Z Flip 5', 'brand' => 'Samsung', 'phone_model' => 'Galaxy Z Flip 5', 'storage' => '256GB', 'ram' => '8GB', 'color' => 'Lavender', 'daily' => 130000, 'desc' => 'Samsung Z Flip 5, flip design, Flex Window.'],

            // Extra budget phones
            ['name' => 'Samsung Galaxy A05', 'brand' => 'Samsung', 'phone_model' => 'Galaxy A05', 'storage' => '64GB', 'ram' => '4GB', 'color' => 'Black', 'daily' => 20000, 'desc' => 'Samsung A05, budget phone, 50MP.'],
            ['name' => 'Xiaomi Redmi A3', 'brand' => 'Xiaomi', 'phone_model' => 'Redmi A3', 'storage' => '64GB', 'ram' => '3GB', 'color' => 'Black', 'daily' => 18000, 'desc' => 'Redmi A3, ultra budget, 8MP camera.'],
            ['name' => 'Realme Narzo 70x', 'brand' => 'Realme', 'phone_model' => 'Narzo 70x', 'storage' => '128GB', 'ram' => '6GB', 'color' => 'Ice Blue', 'daily' => 30000, 'desc' => 'Realme Narzo 70x, 120Hz, 5000mAh, 45W.'],
            ['name' => 'Tecno Spark 20 Pro+', 'brand' => 'Tecno', 'phone_model' => 'Spark 20 Pro+', 'storage' => '256GB', 'ram' => '8GB', 'color' => 'Shadow Gold', 'daily' => 28000, 'desc' => 'Tecno Spark 20 Pro+, 108MP, NFC.'],
            ['name' => 'Infinix Hot 40 Pro', 'brand' => 'Infinix', 'phone_model' => 'Hot 40 Pro', 'storage' => '256GB', 'ram' => '8GB', 'color' => 'Palm Blue', 'daily' => 27000, 'desc' => 'Infinix Hot 40 Pro, 108MP, Helio G99.'],
            ['name' => 'Vivo Y100', 'brand' => 'Vivo', 'phone_model' => 'Y100', 'storage' => '128GB', 'ram' => '8GB', 'color' => 'Twilight Gold', 'daily' => 32000, 'desc' => 'Vivo Y100, 5000mAh, 80W fast charge.'],
            ['name' => 'Oppo A58', 'brand' => 'Oppo', 'phone_model' => 'A58', 'storage' => '128GB', 'ram' => '6GB', 'color' => 'Glowing Black', 'daily' => 30000, 'desc' => 'Oppo A58, 50MP, 5000mAh, NFC.'],
            ['name' => 'Samsung Galaxy A15', 'brand' => 'Samsung', 'phone_model' => 'Galaxy A15', 'storage' => '128GB', 'ram' => '6GB', 'color' => 'Blue', 'daily' => 28000, 'desc' => 'Samsung A15, Super AMOLED, 50MP.'],
            ['name' => 'Realme C55', 'brand' => 'Realme', 'phone_model' => 'C55', 'storage' => '128GB', 'ram' => '6GB', 'color' => 'Rainforest', 'daily' => 25000, 'desc' => 'Realme C55, 64MP, mini capsule.'],
        ];

        foreach ($phones as $p) {
            $image = SeedMediaHelper::resolve('Hp', $p['name'], $p['phone_model'], $p['brand']);
            Phone::create([
                'category_id' => $hpCat->id,
                'owner_id' => $ownerHp->id,
                'name' => $p['name'],
                'slug' => \Illuminate\Support\Str::slug($p['name']) . '-' . \Illuminate\Support\Str::random(5),
                'brand' => $p['brand'],
                'phone_model' => $p['phone_model'],
                'storage_capacity' => $p['storage'],
                'ram' => $p['ram'],
                'color' => $p['color'],
                'description' => $p['desc'],
                'image' => $image,
                'daily_price' => $p['daily'],
                'weekly_price' => $p['daily'] * 6,
                'monthly_price' => $p['daily'] * 25,
                'hourly_price' => round($p['daily'] * 0.15),
                'status' => 'available',
                'condition' => 'excellent',
                'is_active' => true,
            ]);
        }

        // ── CAMERAS (50+) ───────────────────────────────────────────
        $cameras = [
            // Sony
            ['name' => 'Sony A7 IV Kit Lens', 'brand' => 'Sony', 'model' => 'A7 IV', 'sensor' => 'Full Frame', 'lens' => '28-70mm f/3.5-5.6', 'daily' => 350000, 'desc' => 'Sony A7 IV, full-frame 33MP, real-time AF, 4K 60fps.'],
            ['name' => 'Sony A7R V', 'brand' => 'Sony', 'model' => 'A7R V', 'sensor' => 'Full Frame', 'lens' => 'Body Only', 'daily' => 450000, 'desc' => 'Sony A7R V, 61MP, AI-based AF, 8K oversampling.'],
            ['name' => 'Sony A7C II', 'brand' => 'Sony', 'model' => 'A7C II', 'sensor' => 'Full Frame', 'lens' => '28-60mm f/4-5.6', 'daily' => 300000, 'desc' => 'Sony A7C II, compact full-frame, 33MP, real-time tracking.'],
            ['name' => 'Sony A6700', 'brand' => 'Sony', 'model' => 'A6700', 'sensor' => 'APS-C', 'lens' => '16-50mm f/3.5-5.6', 'daily' => 200000, 'desc' => 'Sony A6700, 26MP APS-C, AI AF, 4K 120fps.'],
            ['name' => 'Sony ZV-E10 II', 'brand' => 'Sony', 'model' => 'ZV-E10 II', 'sensor' => 'APS-C', 'lens' => '16-50mm f/3.5-5.6', 'daily' => 150000, 'desc' => 'Sony ZV-E10 II, vlogging camera, background defocus.'],
            ['name' => 'Sony FX30', 'brand' => 'Sony', 'model' => 'FX30', 'sensor' => 'APS-C', 'lens' => 'Body Only', 'daily' => 400000, 'desc' => 'Sony FX30, cinema line, 4K 120fps, S-Cinetone.'],
            ['name' => 'Sony A1', 'brand' => 'Sony', 'model' => 'A1', 'sensor' => 'Full Frame', 'lens' => 'Body Only', 'daily' => 600000, 'desc' => 'Sony A1, flagship, 50MP, 30fps, 8K.'],
            ['name' => 'Sony RX100 VII', 'brand' => 'Sony', 'model' => 'RX100 VII', 'sensor' => '1-inch', 'lens' => '24-200mm f/2.8-4.5', 'daily' => 125000, 'desc' => 'Sony RX100 VII, premium compact, 24-200mm zoom.'],

            // Canon
            ['name' => 'Canon EOS R6 Mark II', 'brand' => 'Canon', 'model' => 'EOS R6 Mark II', 'sensor' => 'Full Frame', 'lens' => 'Body Only', 'daily' => 325000, 'desc' => 'Canon R6 II, 24.2MP, 40fps, subject tracking AF.'],
            ['name' => 'Canon EOS R5', 'brand' => 'Canon', 'model' => 'EOS R5', 'sensor' => 'Full Frame', 'lens' => 'Body Only', 'daily' => 450000, 'desc' => 'Canon R5, 45MP, 8K video, 20fps mechanical.'],
            ['name' => 'Canon EOS R8', 'brand' => 'Canon', 'model' => 'EOS R8', 'sensor' => 'Full Frame', 'lens' => '24-50mm f/4.5-6.3', 'daily' => 225000, 'desc' => 'Canon R8, compact full-frame, 24.2MP, 40fps.'],
            ['name' => 'Canon EOS R7', 'brand' => 'Canon', 'model' => 'EOS R7', 'sensor' => 'APS-C', 'lens' => '18-150mm f/3.5-6.3', 'daily' => 175000, 'desc' => 'Canon R7, 32.5MP APS-C, animal/bird AF, 30fps.'],
            ['name' => 'Canon EOS R50', 'brand' => 'Canon', 'model' => 'EOS R50', 'sensor' => 'APS-C', 'lens' => '18-45mm f/4.5-6.3', 'daily' => 120000, 'desc' => 'Canon R50, entry mirrorless, 24.2MP, guided UI.'],
            ['name' => 'Canon PowerShot V10', 'brand' => 'Canon', 'model' => 'PowerShot V10', 'sensor' => '1-inch', 'lens' => 'Built-in', 'daily' => 80000, 'desc' => 'Canon V10, vlog camera, 1-inch sensor, built-in stand.'],

            // Nikon
            ['name' => 'Nikon Z8', 'brand' => 'Nikon', 'model' => 'Z8', 'sensor' => 'Full Frame', 'lens' => 'Body Only', 'daily' => 400000, 'desc' => 'Nikon Z8, 45.7MP stacked sensor, 20fps, 8K.'],
            ['name' => 'Nikon Z6 III', 'brand' => 'Nikon', 'model' => 'Z6 III', 'sensor' => 'Full Frame', 'lens' => '24-70mm f/4', 'daily' => 300000, 'desc' => 'Nikon Z6 III, 24.5MP partially stacked, N-Log.'],
            ['name' => 'Nikon Z50 II', 'brand' => 'Nikon', 'model' => 'Z50 II', 'sensor' => 'APS-C', 'lens' => '16-50mm f/3.5-6.3', 'daily' => 150000, 'desc' => 'Nikon Z50 II, compact APS-C, 20.9MP, eye-detect AF.'],
            ['name' => 'Nikon Z fc', 'brand' => 'Nikon', 'model' => 'Z fc', 'sensor' => 'APS-C', 'lens' => '16-50mm f/3.5-6.3', 'daily' => 130000, 'desc' => 'Nikon Z fc, retro design, 20.9MP, vari-angle LCD.'],

            // Fujifilm
            ['name' => 'Fujifilm X-T5', 'brand' => 'Fujifilm', 'model' => 'X-T5', 'sensor' => 'APS-C', 'lens' => '18-55mm f/2.8-4', 'daily' => 225000, 'desc' => 'Fujifilm X-T5, 40MP X-Trans, film simulations, IBIS.'],
            ['name' => 'Fujifilm X-S20', 'brand' => 'Fujifilm', 'model' => 'X-S20', 'sensor' => 'APS-C', 'lens' => '18-55mm f/2.8-4', 'daily' => 200000, 'desc' => 'Fujifilm X-S20, 26MP, vlog mode, 6.2K video.'],
            ['name' => 'Fujifilm X100VI', 'brand' => 'Fujifilm', 'model' => 'X100VI', 'sensor' => 'APS-C', 'lens' => '23mm f/2 (built-in)', 'daily' => 200000, 'desc' => 'Fujifilm X100VI, 40MP, fixed lens, retro icon.'],
            ['name' => 'Fujifilm X-S10', 'brand' => 'Fujifilm', 'model' => 'X-S10', 'sensor' => 'APS-C', 'lens' => '15-45mm f/3.5-5.6', 'daily' => 150000, 'desc' => 'Fujifilm X-S10, 26MP, IBIS, compact grip.'],
            ['name' => 'Fujifilm GFX 50S II', 'brand' => 'Fujifilm', 'model' => 'GFX 50S II', 'sensor' => 'Medium Format', 'lens' => '35-70mm f/4.5-5.6', 'daily' => 500000, 'desc' => 'Fujifilm GFX 50S II, 51.4MP medium format, IBIS.'],

            // GoPro
            ['name' => 'GoPro Hero 12 Black', 'brand' => 'GoPro', 'model' => 'Hero 12 Black', 'sensor' => '1/1.9"', 'lens' => 'Built-in', 'daily' => 125000, 'desc' => 'GoPro Hero 12, 5.3K, HyperSmooth 6.0, HDR.'],
            ['name' => 'GoPro Hero 11 Black', 'brand' => 'GoPro', 'model' => 'Hero 11 Black', 'sensor' => '1/1.9"', 'lens' => 'Built-in', 'daily' => 100000, 'desc' => 'GoPro Hero 11, 5.3K, HyperSmooth 5.0.'],
            ['name' => 'GoPro Hero 12 Mini', 'brand' => 'GoPro', 'model' => 'Hero 12 Mini', 'sensor' => '1/1.9"', 'lens' => 'Built-in', 'daily' => 90000, 'desc' => 'GoPro Hero 12 Mini, ultra compact, 4K.'],

            // DJI
            ['name' => 'DJI Pocket 3', 'brand' => 'DJI', 'model' => 'Pocket 3', 'sensor' => '1-inch', 'lens' => '20mm f/2.0', 'daily' => 125000, 'desc' => 'DJI Pocket 3, 1-inch sensor, 4K 120fps, 2-inch rotatable screen.'],
            ['name' => 'DJI Osmo Action 4', 'brand' => 'DJI', 'model' => 'Osmo Action 4', 'sensor' => '1/1.3"', 'lens' => 'Built-in', 'daily' => 100000, 'desc' => 'DJI Action 4, 1/1.3" sensor, 4K, waterproof 18m.'],
            ['name' => 'DJI Osmo Mobile 6', 'brand' => 'DJI', 'model' => 'Osmo Mobile 6', 'sensor' => 'N/A', 'lens' => 'N/A', 'daily' => 60000, 'desc' => 'DJI OM 6, smartphone gimbal, ActiveTrack 6.0.'],

            // Panasonic
            ['name' => 'Panasonic Lumix S5 II', 'brand' => 'Panasonic', 'model' => 'Lumix S5 II', 'sensor' => 'Full Frame', 'lens' => '20-60mm f/3.5-5.6', 'daily' => 250000, 'desc' => 'Panasonic S5 II, 24.2MP, phase-detect AF, 6K.'],
            ['name' => 'Panasonic GH6', 'brand' => 'Panasonic', 'model' => 'GH6', 'sensor' => 'Micro Four Thirds', 'lens' => '12-60mm f/3.5-5.6', 'daily' => 200000, 'desc' => 'Panasonic GH6, 25.1MP, 5.7K, ProRes, unlimited recording.'],
            ['name' => 'Panasonic Lumix G9 II', 'brand' => 'Panasonic', 'model' => 'Lumix G9 II', 'sensor' => 'Micro Four Thirds', 'lens' => '12-60mm f/2.8-4', 'daily' => 175000, 'desc' => 'Panasonic G9 II, 25MP, phase-detect AF, 8-stop IBIS.'],

            // Sigma
            ['name' => 'Sigma fp L', 'brand' => 'Sigma', 'model' => 'fp L', 'sensor' => 'Full Frame', 'lens' => '45mm f/2.8', 'daily' => 200000, 'desc' => 'Sigma fp L, 61MP, compact full-frame, 8K.'],
            ['name' => 'Sigma fp', 'brand' => 'Sigma', 'model' => 'fp', 'sensor' => 'Full Frame', 'lens' => '45mm f/2.8', 'daily' => 150000, 'desc' => 'Sigma fp, 24.6MP, cinema-capable, pocketable FF.'],

            // Insta360
            ['name' => 'Insta360 X3', 'brand' => 'Insta360', 'model' => 'X3', 'sensor' => '1/2"', 'lens' => 'Dual fisheye', 'daily' => 100000, 'desc' => 'Insta360 X3, 360 camera, 5.7K 360, invisible selfie stick.'],
            ['name' => 'Insta360 Ace Pro', 'brand' => 'Insta360', 'model' => 'Ace Pro', 'sensor' => '1/1.3"', 'lens' => 'Built-in', 'daily' => 100000, 'desc' => 'Insta360 Ace Pro, action cam, 4K 120fps, Leica lens.'],
            ['name' => 'Insta360 GO 3S', 'brand' => 'Insta360', 'model' => 'GO 3S', 'sensor' => '1/2.3"', 'lens' => 'Built-in', 'daily' => 75000, 'desc' => 'Insta360 GO 3S, ultra tiny, 4K, magnetic mount.'],

            // OM System
            ['name' => 'OM System OM-1 Mark II', 'brand' => 'OM System', 'model' => 'OM-1 II', 'sensor' => 'Micro Four Thirds', 'lens' => '12-40mm f/2.8', 'daily' => 200000, 'desc' => 'OM-1 II, 20MP stacked, IP53, computational photography.'],

            // Leica
            ['name' => 'Leica Q3', 'brand' => 'Leica', 'model' => 'Q3', 'sensor' => 'Full Frame', 'lens' => '28mm f/1.7 (built-in)', 'daily' => 350000, 'desc' => 'Leica Q3, 60MP, 8K video, Summilux lens.'],
            ['name' => 'Leica D-Lux 8', 'brand' => 'Leica', 'model' => 'D-Lux 8', 'sensor' => 'Four Thirds', 'lens' => '10.9-34mm f/1.7-2.8', 'daily' => 150000, 'desc' => 'Leica D-Lux 8, premium compact, fast zoom.'],

            // Camcorder / Video
            ['name' => 'Sony PXW-Z150', 'brand' => 'Sony', 'model' => 'PXW-Z150', 'sensor' => '1-inch', 'lens' => '29-348mm f/2.8-4.5', 'daily' => 350000, 'desc' => 'Sony PXW-Z150, 4K XDCAM, 120fps, 20x zoom.'],
            ['name' => 'Canon XA75', 'brand' => 'Canon', 'model' => 'XA75', 'sensor' => '1/2.3"', 'lens' => '20-600mm', 'daily' => 200000, 'desc' => 'Canon XA75, UHD 4K camcorder, 20x optical zoom.'],

            // Drone cameras
            ['name' => 'DJI Mavic 3 Pro', 'brand' => 'DJI', 'model' => 'Mavic 3 Pro', 'sensor' => '4/3 CMOS + 1/1.3"', 'lens' => '24mm f/2.8 + 70mm', 'daily' => 350000, 'desc' => 'DJI Mavic 3 Pro, triple camera, Hasselblad, 43min flight.'],
            ['name' => 'DJI Mini 4 Pro', 'brand' => 'DJI', 'model' => 'Mini 4 Pro', 'sensor' => '1/1.3"', 'lens' => '24mm f/1.7', 'daily' => 150000, 'desc' => 'DJI Mini 4 Pro, sub-249g, 4K HDR, omnidirectional sensing.'],
            ['name' => 'DJI Air 3', 'brand' => 'DJI', 'model' => 'Air 3', 'sensor' => '1/1.3"', 'lens' => '24mm + 70mm', 'daily' => 200000, 'desc' => 'DJI Air 3, dual camera, 46min flight, omnidirectional.'],

            // Extra
            ['name' => 'Blackmagic Pocket 6K G2', 'brand' => 'Blackmagic', 'model' => 'Pocket 6K G2', 'sensor' => 'Super 35', 'lens' => 'Body Only', 'daily' => 300000, 'desc' => 'Blackmagic Pocket 6K G2, RAW, DaVinci Resolve included.'],
            ['name' => 'RED Komodo 6K', 'brand' => 'RED', 'model' => 'Komodo 6K', 'sensor' => 'Super 35', 'lens' => 'Body Only', 'daily' => 500000, 'desc' => 'RED Komodo, 6K global shutter, compact cinema.'],
        ];

        foreach ($cameras as $c) {
            $image = SeedMediaHelper::resolve('Kamera', $c['name'], $c['model'], $c['brand']);
            Camera::create([
                'category_id' => $kameraCat->id,
                'owner_id' => $ownerKamera->id,
                'name' => $c['name'],
                'slug' => \Illuminate\Support\Str::slug($c['name']) . '-' . \Illuminate\Support\Str::random(5),
                'brand' => $c['brand'],
                'camera_model' => $c['model'],
                'sensor_size' => $c['sensor'],
                'lens_included' => $c['lens'],
                'accessories' => ['battery', 'charger', 'strap', 'bag'],
                'description' => $c['desc'],
                'image' => $image,
                'daily_price' => $c['daily'],
                'weekly_price' => $c['daily'] * 6,
                'monthly_price' => $c['daily'] * 25,
                'hourly_price' => round($c['daily'] * 0.15),
                'status' => 'available',
                'condition' => 'excellent',
                'is_active' => true,
            ]);
        }

        // ── CAMPING EQUIPMENT (50+) ─────────────────────────────────
        $camping = [
            // Tenda
            ['name' => 'Eiger Torro 4P Tent', 'brand' => 'Eiger', 'model' => 'Torro 4P', 'type' => 'Tenda Dome', 'cap' => 4, 'weight' => '3.5 kg', 'material' => 'Polyester 210D', 'daily' => 100000, 'desc' => 'Tenda dome 4 orang, waterproof, cocok camping keluarga.'],
            ['name' => 'Eiger Navigator 3P', 'brand' => 'Eiger', 'model' => 'Navigator 3P', 'type' => 'Tenda Dome', 'cap' => 3, 'weight' => '2.8 kg', 'material' => 'Polyester 190T', 'daily' => 80000, 'desc' => 'Tenda 3 orang, ringan, ventilasi baik.'],
            ['name' => 'Naturehike Cloud Up 2', 'brand' => 'Naturehike', 'model' => 'Cloud Up 2', 'type' => 'Tenda Dome', 'cap' => 2, 'weight' => '1.5 kg', 'material' => 'Ripstop Nylon 20D', 'daily' => 75000, 'desc' => 'Tenda ultralight 2 orang, ringan untuk backpacking.'],
            ['name' => 'Naturehike Cloud Up 3', 'brand' => 'Naturehike', 'model' => 'Cloud Up 3', 'type' => 'Tenda Dome', 'cap' => 3, 'weight' => '1.8 kg', 'material' => 'Ripstop Nylon 20D', 'daily' => 85000, 'desc' => 'Tenda ultralight 3 orang, compact.'],
            ['name' => 'Naturehike Hiby 4', 'brand' => 'Naturehike', 'model' => 'Hiby 4', 'type' => 'Tenda Dome', 'cap' => 4, 'weight' => '2.5 kg', 'material' => 'Polyester 210D', 'daily' => 90000, 'desc' => 'Tenda 4 orang, double layer, anti angin.'],
            ['name' => 'Consina Magnum 2', 'brand' => 'Consina', 'model' => 'Magnum 2', 'type' => 'Tenda Dome', 'cap' => 2, 'weight' => '1.6 kg', 'material' => 'Ripstop Nylon', 'daily' => 65000, 'desc' => 'Consina Magnum 2, ringan, setup cepat.'],
            ['name' => 'Consina Superlight 3', 'brand' => 'Consina', 'model' => 'Superlight 3', 'type' => 'Tenda Dome', 'cap' => 3, 'weight' => '1.9 kg', 'material' => 'Ripstop Nylon 20D', 'daily' => 75000, 'desc' => 'Consina Superlight 3, backpacking tent.'],
            ['name' => 'Consina Quarizona 4P', 'brand' => 'Consina', 'model' => 'Quarizona 4P', 'type' => 'Tenda Dome', 'cap' => 4, 'weight' => '3.2 kg', 'material' => 'Polyester 210D', 'daily' => 95000, 'desc' => 'Consina Quarizona, family tent, luas.'],
            ['name' => 'Jeep Adventure Tent 4P', 'brand' => 'Jeep', 'model' => 'Adventure 4P', 'type' => 'Tenda Dome', 'cap' => 4, 'weight' => '3.8 kg', 'material' => 'Polyester 300D', 'daily' => 110000, 'desc' => 'Jeep Adventure Tent, rugged, heavy duty waterproof.'],
            ['name' => 'Jeep Family Tent 6P', 'brand' => 'Jeep', 'model' => 'Family 6P', 'type' => 'Tenda Family', 'cap' => 6, 'weight' => '5.5 kg', 'material' => 'Polyester 210D', 'daily' => 150000, 'desc' => 'Jeep Family Tent, besar, cocok untuk keluarga besar.'],
            ['name' => 'Marmot Tungsten 4P', 'brand' => 'Marmot', 'model' => 'Tungsten 4P', 'type' => 'Tenda Dome', 'cap' => 4, 'weight' => '3.1 kg', 'material' => 'Polyester 68D', 'daily' => 130000, 'desc' => 'Marmot Tungsten, freestanding, catenary cut floor.'],
            ['name' => 'MSR Hubba Hubba NX 2', 'brand' => 'MSR', 'model' => 'Hubba Hubba NX 2', 'type' => 'Tenda Dome', 'cap' => 2, 'weight' => '1.54 kg', 'material' => 'Nylon 20D', 'daily' => 150000, 'desc' => 'MSR Hubba Hubba, legendary ultralight, 3-season.'],
            ['name' => 'Coleman Sundome 4P', 'brand' => 'Coleman', 'model' => 'Sundome 4P', 'type' => 'Tenda Dome', 'cap' => 4, 'weight' => '4.2 kg', 'material' => 'Polyester 68D', 'daily' => 85000, 'desc' => 'Coleman Sundome, dome tent, ventilated, budget-friendly.'],
            ['name' => 'Coleman Instant Cabin 6P', 'brand' => 'Coleman', 'model' => 'Instant Cabin 6P', 'type' => 'Tenda Cabin', 'cap' => 6, 'weight' => '8.5 kg', 'material' => 'Polyester 150D', 'daily' => 120000, 'desc' => 'Coleman Instant Cabin, setup 60 detik, cabin style.'],
            ['name' => 'The North Face Wawona 4', 'brand' => 'The North Face', 'model' => 'Wawona 4', 'type' => 'Tenda Dome', 'cap' => 4, 'weight' => '3.6 kg', 'material' => 'Polyester 75D', 'daily' => 140000, 'desc' => 'TNF Wawona, large vestibule, 3-season.'],

            // Sleeping Bag
            ['name' => 'Consina Comfort 300', 'brand' => 'Consina', 'model' => 'Comfort 300', 'type' => 'Sleeping Bag', 'cap' => 1, 'weight' => '1.8 kg', 'material' => 'Polycotton', 'daily' => 40000, 'desc' => 'Sleeping bag nyaman suhu 10-30C, dataran rendah.'],
            ['name' => 'Consina Superlight 150', 'brand' => 'Consina', 'model' => 'Superlight 150', 'type' => 'Sleeping Bag', 'cap' => 1, 'weight' => '0.9 kg', 'material' => 'Ripstop Nylon', 'daily' => 50000, 'desc' => 'Sleeping bag ultralight, compact, suhu 5-20C.'],
            ['name' => 'Eiger Adventurer 250', 'brand' => 'Eiger', 'model' => 'Adventurer 250', 'type' => 'Sleeping Bag', 'cap' => 1, 'weight' => '1.2 kg', 'material' => 'Ripstop Nylon', 'daily' => 45000, 'desc' => 'Eiger sleeping bag, mummy shape, suhu 5-25C.'],
            ['name' => 'Naturehike Ultralight 150', 'brand' => 'Naturehike', 'model' => 'Ultralight 150', 'type' => 'Sleeping Bag', 'cap' => 1, 'weight' => '0.7 kg', 'material' => 'Nylon 20D', 'daily' => 55000, 'desc' => 'Naturehike ultralight, backpacking, packable.'],
            ['name' => 'Snugpak Softie Elite 3', 'brand' => 'Snugpak', 'model' => 'Softie Elite 3', 'type' => 'Sleeping Bag', 'cap' => 1, 'weight' => '1.7 kg', 'material' => 'Paratex Micro', 'daily' => 65000, 'desc' => 'Snugpak Elite 3, military grade, suhu -5C.'],
            ['name' => 'Camp Combi 350', 'brand' => 'Camp', 'model' => 'Combi 350', 'type' => 'Sleeping Bag', 'cap' => 1, 'weight' => '1.5 kg', 'material' => 'Polyester', 'daily' => 40000, 'desc' => 'Camp Combi, comfort bag, cocok camping.'],

            // Matras / Sleeping Pad
            ['name' => 'Eiger Ultralight Mat', 'brand' => 'Eiger', 'model' => 'Ultralight Mat', 'type' => 'Sleeping Pad', 'cap' => 1, 'weight' => '0.5 kg', 'material' => 'TPU Nylon', 'daily' => 35000, 'desc' => 'Matras ultralight, self-inflating, compact.'],
            ['name' => 'Consina Camping Mat', 'brand' => 'Consina', 'model' => 'Camping Mat', 'type' => 'Sleeping Pad', 'cap' => 1, 'weight' => '0.6 kg', 'material' => 'EVA Foam', 'daily' => 25000, 'desc' => 'Consina foam mat, tebal, tahan lama.'],
            ['name' => 'Naturehike Sleeping Pad', 'brand' => 'Naturehike', 'model' => 'Sleeping Pad', 'type' => 'Sleeping Pad', 'cap' => 1, 'weight' => '0.45 kg', 'material' => 'Nylon 20D TPU', 'daily' => 40000, 'desc' => 'Naturehike pad, R-value tinggi, ultralight.'],
            ['name' => 'Therm-a-Rest NeoAir XLite', 'brand' => 'Therm-a-Rest', 'model' => 'NeoAir XLite', 'type' => 'Sleeping Pad', 'cap' => 1, 'weight' => '0.35 kg', 'material' => 'Nylon', 'daily' => 60000, 'desc' => 'Therm-a-Rest NeoAir, premium pad, R-value 4.2.'],
            ['name' => 'Klymit Static V2', 'brand' => 'Klymit', 'model' => 'Static V2', 'type' => 'Sleeping Pad', 'cap' => 1, 'weight' => '0.47 kg', 'material' => '75D Polyester', 'daily' => 45000, 'desc' => 'Klymit Static V2, V-chamber design, comfortable.'],

            // Carrier / Backpack
            ['name' => 'Eiger Arjuna 60L', 'brand' => 'Eiger', 'model' => 'Arjuna 60L', 'type' => 'Backpack', 'cap' => 1, 'weight' => '2.0 kg', 'material' => 'Ripstop Nylon 420D', 'daily' => 50000, 'desc' => 'Carrier 60L, rain cover, ventilated back panel.'],
            ['name' => 'Consina Carribean 65L', 'brand' => 'Consina', 'model' => 'Carribean 65L', 'type' => 'Backpack', 'cap' => 1, 'weight' => '2.2 kg', 'material' => 'Polyester 600D', 'daily' => 45000, 'desc' => 'Consina Carribean, 65L, top loading, rain cover.'],
            ['name' => 'Osprey Atmos AG 65', 'brand' => 'Osprey', 'model' => 'Atmos AG 65', 'type' => 'Backpack', 'cap' => 1, 'weight' => '1.9 kg', 'material' => 'Nylon 210D', 'daily' => 80000, 'desc' => 'Osprey Atmos AG, anti-gravity, premium comfort.'],
            ['name' => 'Deuter Aircontact Lite 50+10', 'brand' => 'Deuter', 'model' => 'Aircontact Lite 50+10', 'type' => 'Backpack', 'cap' => 1, 'weight' => '1.95 kg', 'material' => 'Ripstop Nylon', 'daily' => 75000, 'desc' => 'Deuter Aircontact, VariFlex hip fins, adjustable.'],
            ['name' => 'Gregory Baltoro 65', 'brand' => 'Gregory', 'model' => 'Baltoro 65', 'type' => 'Backpack', 'cap' => 1, 'weight' => '2.3 kg', 'material' => 'Nylon 210D', 'daily' => 85000, 'desc' => 'Gregory Baltoro, Response A3, heavy load comfort.'],

            // Lampu Tenda
            ['name' => 'Eiger Camping Lantern', 'brand' => 'Eiger', 'model' => 'Camping Lantern', 'type' => 'Lampu Tenda', 'cap' => 1, 'weight' => '0.3 kg', 'material' => 'ABS + Silicone', 'daily' => 15000, 'desc' => 'Lampu tenda LED, 300 lumen, rechargeable.'],
            ['name' => 'Consina LED Lantern', 'brand' => 'Consina', 'model' => 'LED Lantern', 'type' => 'Lampu Tenda', 'cap' => 1, 'weight' => '0.25 kg', 'material' => 'ABS', 'daily' => 12000, 'desc' => 'Lampu kampung, 200 lumen, USB charge.'],
            ['name' => 'Black Diamond ReVolt 350', 'brand' => 'Black Diamond', 'model' => 'ReVolt 350', 'type' => 'Lampu Tenda', 'cap' => 1, 'weight' => '0.11 kg', 'material' => 'Polycarbonate', 'daily' => 20000, 'desc' => 'Headlamp rechargeable, 350 lumen, red night mode.'],
            ['name' => 'Petzl Actik Core', 'brand' => 'Petzl', 'model' => 'Actik Core', 'type' => 'Lampu Tenda', 'cap' => 1, 'weight' => '0.082 kg', 'material' => 'Polycarbonate', 'daily' => 18000, 'desc' => 'Petzl Actik, headlamp, 450 lumen, hybrid battery.'],

            // Kompor
            ['name' => 'Eiger Portable Stove', 'brand' => 'Eiger', 'model' => 'Portable Stove', 'type' => 'Kompor Camping', 'cap' => 1, 'weight' => '0.35 kg', 'material' => 'Stainless Steel', 'daily' => 20000, 'desc' => 'Kompor portable, butane,windproof.'],
            ['name' => 'Consina Camping Stove', 'brand' => 'Consina', 'model' => 'Camping Stove', 'type' => 'Kompor Camping', 'cap' => 1, 'weight' => '0.4 kg', 'material' => 'Stainless Steel', 'daily' => 18000, 'desc' => 'Kompor camping, gas canister, compact.'],
            ['name' => 'MSR PocketRocket 2', 'brand' => 'MSR', 'model' => 'PocketRocket 2', 'type' => 'Kompor Camping', 'cap' => 1, 'weight' => '0.073 kg', 'material' => 'Stainless Steel', 'daily' => 25000, 'desc' => 'MSR PocketRocket, ultralight stove, 7.2 min boil.'],
            ['name' => 'Jetboil Flash', 'brand' => 'Jetboil', 'model' => 'Flash', 'type' => 'Kompor Camping', 'cap' => 1, 'weight' => '0.375 kg', 'material' => 'Aluminum', 'daily' => 35000, 'desc' => 'Jetboil Flash, integrated system, 100s boil time.'],

            // Tas / Dry Bag
            ['name' => 'Eiger Dry Bag 20L', 'brand' => 'Eiger', 'model' => 'Dry Bag 20L', 'type' => 'Dry Bag', 'cap' => 1, 'weight' => '0.15 kg', 'material' => 'TPU Nylon', 'daily' => 15000, 'desc' => 'Dry bag waterproof 20L, roll-top closure.'],
            ['name' => 'Consina Dry Bag 30L', 'brand' => 'Consina', 'model' => 'Dry Bag 30L', 'type' => 'Dry Bag', 'cap' => 1, 'weight' => '0.2 kg', 'material' => 'TPU Nylon', 'daily' => 18000, 'desc' => 'Consina dry bag, waterproof 30L.'],
            ['name' => 'Sea to Summit Lightweight Dry Sack 8L', 'brand' => 'Sea to Summit', 'model' => 'Lightweight Dry Sack 8L', 'type' => 'Dry Bag', 'cap' => 1, 'weight' => '0.046 kg', 'material' => 'Silnylon', 'daily' => 12000, 'desc' => 'Ultra lightweight dry sack, color-coded.'],

            // Hiking Pole
            ['name' => 'Eiger Trailmaster Pole', 'brand' => 'Eiger', 'model' => 'Trailmaster Pole', 'type' => 'Hiking Pole', 'cap' => 2, 'weight' => '0.48 kg', 'material' => 'Aluminium 7075', 'daily' => 20000, 'desc' => 'Tongkat hiking, adjustable, cork grip.'],
            ['name' => 'Consina Trekking Pole', 'brand' => 'Consina', 'model' => 'Trekking Pole', 'type' => 'Hiking Pole', 'cap' => 2, 'weight' => '0.5 kg', 'material' => 'Aluminium', 'daily' => 15000, 'desc' => 'Tongkat trekking, foldable, anti-shock.'],
            ['name' => 'Black Diamond Trail Ergo Cork', 'brand' => 'Black Diamond', 'model' => 'Trail Ergo Cork', 'type' => 'Hiking Pole', 'cap' => 2, 'weight' => '0.49 kg', 'material' => 'Aluminium', 'daily' => 25000, 'desc' => 'BD Trail Ergo, cork grip, ergonomic angle.'],

            // Extra items
            ['name' => 'Eiger Headnet Mosquito', 'brand' => 'Eiger', 'model' => 'Headnet Mosquito', 'type' => 'Aksesoris', 'cap' => 1, 'weight' => '0.03 kg', 'material' => 'Mosquito Net', 'daily' => 8000, 'desc' => 'Net anti nyamuk, ultralight, compact.'],
            ['name' => 'Consina Camping Chair', 'brand' => 'Consina', 'model' => 'Camping Chair', 'type' => 'Kursi Camping', 'cap' => 1, 'weight' => '1.2 kg', 'material' => 'Aluminium + Polyester', 'daily' => 25000, 'desc' => 'Kursi lipat camping, compact, tahan lama.'],
            ['name' => 'Eiger Camping Table', 'brand' => 'Eiger', 'model' => 'Camping Table', 'type' => 'Meja Camping', 'cap' => 1, 'weight' => '1.5 kg', 'material' => 'Aluminium', 'daily' => 30000, 'desc' => 'Meja lipat aluminium, portable, adjustable height.'],
            ['name' => 'Consina Water Bladder 3L', 'brand' => 'Consina', 'model' => 'Water Bladder 3L', 'type' => 'Hydration', 'cap' => 1, 'weight' => '0.2 kg', 'material' => 'TPU', 'daily' => 15000, 'desc' => 'Hydration bladder 3L, BPA free, easy clean.'],
            ['name' => 'Eiger Carabiner Set', 'brand' => 'Eiger', 'model' => 'Carabiner Set', 'type' => 'Aksesoris', 'cap' => 4, 'weight' => '0.12 kg', 'material' => 'Aluminium Alloy', 'daily' => 10000, 'desc' => 'Set 4 carabiner, lightweight, multi-purpose.'],
        ];

        foreach ($camping as $item) {
            $image = SeedMediaHelper::resolve('tenda', $item['name'], $item['model'], $item['brand']);
            CampingEquipment::create([
                'category_id' => $tendaCat->id,
                'owner_id' => $ownerTenda->id,
                'name' => $item['name'],
                'slug' => \Illuminate\Support\Str::slug($item['name']) . '-' . \Illuminate\Support\Str::random(5),
                'brand' => $item['brand'],
                'equipment_model' => $item['model'],
                'type' => $item['type'],
                'capacity' => $item['cap'],
                'weight' => $item['weight'],
                'material' => $item['material'],
                'description' => $item['desc'],
                'image' => $image,
                'daily_price' => $item['daily'],
                'weekly_price' => $item['daily'] * 6,
                'monthly_price' => $item['daily'] * 25,
                'hourly_price' => round($item['daily'] * 0.15),
                'status' => 'available',
                'condition' => 'excellent',
                'is_active' => true,
            ]);
        }

        echo "Electronics seeder completed!\n";
        echo "  - " . Phone::count() . " phones\n";
        echo "  - " . Camera::count() . " cameras\n";
        echo "  - " . CampingEquipment::count() . " camping equipments\n";
    }
}
