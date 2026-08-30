<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Playstation;
use App\Models\Drone;
use App\Models\MusicalInstrument;
use App\Models\User;
use Illuminate\Support\Str;

class AdditionalProductsSeeder extends Seeder
{
    public function run(): void
    {
        $fallbackOwner = User::where('role', 'owner')->first() ?? User::first();

        $psCat = Category::firstOrCreate(
            ['slug' => 'sewa-ps'],
            [
                'name' => 'Sewa Playstation',
                'description' => 'Sewa konsol Playstation beserta controller dan aksesoris untuk hiburan.',
                'icon' => 'gamepad',
                'is_active' => true,
            ]
        );
        $droneCat = Category::firstOrCreate(
            ['slug' => 'sewa-drone'],
            [
                'name' => 'Sewa Drone',
                'description' => 'Sewa drone untuk fotografi dan videografi dari udara.',
                'icon' => 'drone',
                'is_active' => true,
            ]
        );
        $musikCat = Category::firstOrCreate(
            ['slug' => 'sewa-alat-musik'],
            [
                'name' => 'Sewa Alat Musik',
                'description' => 'Sewa gitar, keyboard, dan alat musik lain untuk event maupun latihan.',
                'icon' => 'guitar',
                'is_active' => true,
            ]
        );

        $ownerPs = $psCat
            ? User::where('role', 'owner')->where('category_id', $psCat->id)->first()
            : null;
        $ownerDrone = $droneCat
            ? User::where('role', 'owner')->where('category_id', $droneCat->id)->first()
            : null;
        $ownerMusik = $musikCat
            ? User::where('role', 'owner')->where('category_id', $musikCat->id)->first()
            : null;

        $ownerPs ??= $fallbackOwner;
        $ownerDrone ??= $fallbackOwner;
        $ownerMusik ??= $fallbackOwner;

        $playstations = [
            ['name' => 'PlayStation 5 Slim', 'brand' => 'Sony', 'console_model' => 'PS5 Slim', 'storage' => '1TB', 'controllers' => 2, 'color' => 'White', 'daily' => 150000, 'desc' => 'PlayStation 5 Slim 1TB dengan 2 controller DualSense, lengkap untuk gaming next-gen.'],
            ['name' => 'PlayStation 5', 'brand' => 'Sony', 'console_model' => 'PS5', 'storage' => '825GB', 'controllers' => 2, 'color' => 'White', 'daily' => 165000, 'desc' => 'PlayStation 5 standar 825GB, controller DualSense haptic feedback, SSD super cepat.'],
            ['name' => 'PlayStation 4 Pro', 'brand' => 'Sony', 'console_model' => 'PS4 Pro', 'storage' => '1TB', 'controllers' => 2, 'color' => 'Black', 'daily' => 90000, 'desc' => 'PlayStation 4 Pro 1TB, 4K HDR, cocok untuk game librari yang luas.'],
            ['name' => 'PlayStation 4 Slim', 'brand' => 'Sony', 'console_model' => 'PS4 Slim', 'storage' => '500GB', 'controllers' => 2, 'color' => 'Black', 'daily' => 75000, 'desc' => 'PlayStation 4 Slim 500GB, ringan dan ekonomis.'],
            ['name' => 'PlayStation 5 + 2 Game', 'brand' => 'Sony', 'console_model' => 'PS5 Bundle', 'storage' => '1TB', 'controllers' => 2, 'color' => 'White', 'daily' => 185000, 'desc' => 'Bundle PlayStation 5 dengan 2 game populer, siap main langsung.'],
        ];
        foreach ($playstations as $i => $p) {
            $image = SeedMediaHelper::resolve('Playstation', $p['name'], $p['console_model'], $p['brand']);
            Playstation::firstOrCreate(['slug' => Str::slug($p['name']) . '-' . ($i + 1)], [
                'category_id' => $psCat->id,
                'owner_id' => $ownerPs->id,
                'name' => $p['name'],
                'brand' => $p['brand'],
                'console_model' => $p['console_model'],
                'storage_capacity' => $p['storage'],
                'controllers_count' => $p['controllers'],
                'color' => $p['color'],
                'accessories' => ['controller', 'hdmi', 'power_cable', 'charger'],
                'description' => $p['desc'],
                'image' => $image,
                'daily_price' => $p['daily'],
                'weekly_price' => $p['daily'] * 6,
                'monthly_price' => $p['daily'] * 25,
                'hourly_price' => round($p['daily'] * 0.15),
                'status' => 'available',
                'condition' => 'good',
                'is_active' => true,
            ]);
        }

        $drones = [
            ['name' => 'DJI Mavic 3 Pro', 'brand' => 'DJI', 'drone_model' => 'Mavic 3 Pro', 'camera' => '4/3 CMOS Hasselblad 20MP', 'flight' => '43 menit', 'range' => '15 km', 'weight' => '958 g', 'daily' => 350000, 'desc' => 'DJI Mavic 3 Pro dengan kamera Hasselblad, video 5.1K, untuk sinematografi profesional.'],
            ['name' => 'DJI Air 3', 'brand' => 'DJI', 'drone_model' => 'Air 3', 'camera' => 'Dual 48MP', 'flight' => '46 menit', 'range' => '20 km', 'weight' => '720 g', 'daily' => 250000, 'desc' => 'DJI Air 3 dual camera, video 4K/60fps, flight time lama, untuk konten kreator.'],
            ['name' => 'DJI Mini 4 Pro', 'brand' => 'DJI', 'drone_model' => 'Mini 4 Pro', 'camera' => '48MP 1/1.3"', 'flight' => '34 menit', 'range' => '20 km', 'weight' => '249 g', 'daily' => 200000, 'desc' => 'DJI Mini 4 Pro, ringan di bawah 250g tanpa butuh izin khusus, kamera 4K/60fps.'],
            ['name' => 'DJI Avata 2', 'brand' => 'DJI', 'drone_model' => 'Avata 2', 'camera' => '1/1.3" 4K/60fps', 'flight' => '23 menit', 'range' => '13 km', 'weight' => '377 g', 'daily' => 275000, 'desc' => 'DJI Avata 2 FPV drone dengan kacamata dan RC motion controller untuk pengalaman terbang imersif.'],
        ];
        foreach ($drones as $i => $d) {
            $image = SeedMediaHelper::resolve('Drone', $d['name'], $d['drone_model'], $d['brand']);
            Drone::firstOrCreate(['slug' => Str::slug($d['name']) . '-' . ($i + 1)], [
                'category_id' => $droneCat->id,
                'owner_id' => $ownerDrone->id,
                'name' => $d['name'],
                'brand' => $d['brand'],
                'drone_model' => $d['drone_model'],
                'camera_resolution' => $d['camera'],
                'flight_time' => $d['flight'],
                'max_range' => $d['range'],
                'weight' => $d['weight'],
                'accessories' => ['battery', 'charger', 'propeller', 'carry_case'],
                'description' => $d['desc'],
                'image' => $image,
                'daily_price' => $d['daily'],
                'weekly_price' => $d['daily'] * 6,
                'monthly_price' => $d['daily'] * 25,
                'hourly_price' => round($d['daily'] * 0.15),
                'status' => 'available',
                'condition' => 'good',
                'is_active' => true,
            ]);
        }

        $instruments = [
            ['name' => 'Gitar Akustik Yamaha F310', 'brand' => 'Yamaha', 'instr_type' => 'gitar', 'instr_model' => 'F310', 'color' => 'Natural', 'daily' => 75000, 'desc' => 'Gitar akustik Yamaha F310, suara hangat, cocok pemula maupun pro.'],
            ['name' => 'Gitar Elektrik Fender Stratocaster', 'brand' => 'Fender', 'instr_type' => 'gitar', 'instr_model' => 'Player Stratocaster', 'color' => 'Sunburst', 'daily' => 175000, 'desc' => 'Gitar elektrik Fender Player Stratocaster, 3 single coil, tone ikonik.'],
            ['name' => 'Keyboard Roland FA-06', 'brand' => 'Roland', 'instr_type' => 'keyboard', 'instr_model' => 'FA-06', 'color' => 'Black', 'daily' => 250000, 'desc' => 'Keyboard workstation Roland FA-06, 61 keys, berbagai sound profesional untuk produksi musik.'],
            ['name' => 'Keyboard Casio CT-S100', 'brand' => 'Casio', 'instr_type' => 'keyboard', 'instr_model' => 'CT-S100', 'color' => 'Black', 'daily' => 50000, 'desc' => 'Keyboard compact Casio CT-S100, ringan, cocok untuk latihan dan acara kecil.'],
            ['name' => 'Gitar Akustik Elektrik Cort', 'brand' => 'Cort', 'instr_type' => 'gitar', 'instr_model' => 'AD810E', 'color' => 'Natural', 'daily' => 80000, 'desc' => 'Gitar akustik-elektrik Cort AD810E dengan preamp, bisa langsung ke sound system.'],
        ];
        foreach ($instruments as $i => $m) {
            $image = SeedMediaHelper::resolve('Musik', $m['name'], $m['instr_model'], $m['brand']);
            MusicalInstrument::firstOrCreate(['slug' => Str::slug($m['name']) . '-' . ($i + 1)], [
                'category_id' => $musikCat->id,
                'owner_id' => $ownerMusik->id,
                'name' => $m['name'],
                'brand' => $m['brand'],
                'instrument_type' => $m['instr_type'],
                'instrument_model' => $m['instr_model'],
                'color' => $m['color'],
                'accessories' => ['softcase', 'tuner'],
                'description' => $m['desc'],
                'image' => $image,
                'daily_price' => $m['daily'],
                'weekly_price' => $m['daily'] * 6,
                'monthly_price' => $m['daily'] * 25,
                'hourly_price' => round($m['daily'] * 0.15),
                'status' => 'available',
                'condition' => 'good',
                'is_active' => true,
            ]);
        }
    }
}
