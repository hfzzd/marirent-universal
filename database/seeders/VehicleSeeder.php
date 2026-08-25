<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\Category;
use App\Models\User;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('role', 'owner')->first();
        $mobilCat = Category::where('slug', 'mobil')->first();
        $motorCat = Category::where('slug', 'motor')->first();

        // ── MOBIL (50) ────────────────────────────────────────────
        $mobil = [
            ['name' => 'Toyota Avanza 1.5 G CVT', 'brand' => 'Toyota', 'model' => 'Avanza', 'year' => 2024, 'color' => 'Putih', 'plate' => 'B 1001 ABC', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 350000, 'driver' => 200000],
            ['name' => 'Toyota Avanza 1.3 E MT', 'brand' => 'Toyota', 'model' => 'Avanza', 'year' => 2023, 'color' => 'Silver', 'plate' => 'B 1002 ABC', 'seats' => 7, 'trans' => 'manual', 'fuel' => 'gasoline', 'daily' => 300000, 'driver' => 200000],
            ['name' => 'Toyota Innova Reborn 2.4 V', 'brand' => 'Toyota', 'model' => 'Innova Reborn', 'year' => 2023, 'color' => 'Hitam', 'plate' => 'B 1003 ABC', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'diesel', 'daily' => 550000, 'driver' => 250000],
            ['name' => 'Toyota Innova Zenix HEV', 'brand' => 'Toyota', 'model' => 'Innova Zenix', 'year' => 2024, 'color' => 'White Pearl', 'plate' => 'B 1004 ABC', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'hybrid', 'daily' => 600000, 'driver' => 250000],
            ['name' => 'Toyota Fortuner VRZ', 'brand' => 'Toyota', 'model' => 'Fortuner', 'year' => 2024, 'color' => 'Hitam', 'plate' => 'B 1005 ABC', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'diesel', 'daily' => 850000, 'driver' => 300000],
            ['name' => 'Toyota Rush TRD Sportivo', 'brand' => 'Toyota', 'model' => 'Rush', 'year' => 2023, 'color' => 'Merah', 'plate' => 'B 1006 ABC', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 400000, 'driver' => 200000],
            ['name' => 'Toyota Yaris TRD Sportivo', 'brand' => 'Toyota', 'model' => 'Yaris', 'year' => 2024, 'color' => 'Merah', 'plate' => 'B 1007 ABC', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 325000, 'driver' => 175000],
            ['name' => 'Toyota Camry 2.5 V', 'brand' => 'Toyota', 'model' => 'Camry', 'year' => 2024, 'color' => 'Putih', 'plate' => 'B 1008 ABC', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 750000, 'driver' => 300000],
            ['name' => 'Toyota Alphard 2.5 G', 'brand' => 'Toyota', 'model' => 'Alphard', 'year' => 2024, 'color' => 'Putih', 'plate' => 'B 1009 ABC', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 1800000, 'driver' => 400000],
            ['name' => 'Toyota HiAce Commuter', 'brand' => 'Toyota', 'model' => 'HiAce', 'year' => 2023, 'color' => 'Putih', 'plate' => 'B 1010 ABC', 'seats' => 16, 'trans' => 'manual', 'fuel' => 'diesel', 'daily' => 1200000, 'driver' => 350000],

            ['name' => 'Honda Brio RS CVT', 'brand' => 'Honda', 'model' => 'Brio', 'year' => 2024, 'color' => 'Merah', 'plate' => 'B 1011 DEF', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 275000, 'driver' => 175000],
            ['name' => 'Honda Brio Satya E MT', 'brand' => 'Honda', 'model' => 'Brio', 'year' => 2023, 'color' => 'Putih', 'plate' => 'B 1012 DEF', 'seats' => 5, 'trans' => 'manual', 'fuel' => 'gasoline', 'daily' => 250000, 'driver' => 175000],
            ['name' => 'Honda Jazz RS CVT', 'brand' => 'Honda', 'model' => 'Jazz', 'year' => 2023, 'color' => 'Sonic Gray', 'plate' => 'B 1013 DEF', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 350000, 'driver' => 175000],
            ['name' => 'Honda HR-V 1.5 RS', 'brand' => 'Honda', 'model' => 'HR-V', 'year' => 2024, 'color' => 'Hitam', 'plate' => 'B 1014 DEF', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 500000, 'driver' => 200000],
            ['name' => 'Honda CR-V 1.5 Turbo', 'brand' => 'Honda', 'model' => 'CR-V', 'year' => 2024, 'color' => 'Putih', 'plate' => 'B 1015 DEF', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 700000, 'driver' => 250000],
            ['name' => 'Honda Civic RS Turbo', 'brand' => 'Honda', 'model' => 'Civic', 'year' => 2024, 'color' => 'Hitam', 'plate' => 'B 1016 DEF', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 650000, 'driver' => 250000],
            ['name' => 'Honda BR-V 1.5 i-VTEC', 'brand' => 'Honda', 'model' => 'BR-V', 'year' => 2024, 'color' => 'Silver', 'plate' => 'B 1017 DEF', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 450000, 'driver' => 200000],
            ['name' => 'Honda WR-V RS', 'brand' => 'Honda', 'model' => 'WR-V', 'year' => 2024, 'color' => 'Hitam', 'plate' => 'B 1018 DEF', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 375000, 'driver' => 175000],
            ['name' => 'Honda Accord 1.5 Turbo', 'brand' => 'Honda', 'model' => 'Accord', 'year' => 2024, 'color' => 'Putih', 'plate' => 'B 1019 DEF', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 800000, 'driver' => 300000],
            ['name' => 'Honda N7X RS', 'brand' => 'Honda', 'model' => 'N7X', 'year' => 2024, 'color' => 'Orange', 'plate' => 'B 1020 DEF', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 500000, 'driver' => 200000],

            ['name' => 'Daihatsu Xenia 1.5 R CVT', 'brand' => 'Daihatsu', 'model' => 'Xenia', 'year' => 2024, 'color' => 'Putih', 'plate' => 'B 1021 GHI', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 300000, 'driver' => 175000],
            ['name' => 'Daihatsu Terios R CVT', 'brand' => 'Daihatsu', 'model' => 'Terios', 'year' => 2024, 'color' => 'Hitam', 'plate' => 'B 1022 GHI', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 400000, 'driver' => 200000],
            ['name' => 'Daihatsu Ayla 1.2 R CVT', 'brand' => 'Daihatsu', 'model' => 'Ayla', 'year' => 2024, 'color' => 'Merah', 'plate' => 'B 1023 GHI', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 225000, 'driver' => 150000],
            ['name' => 'Daihatsu Rocky 1.0T', 'brand' => 'Daihatsu', 'model' => 'Rocky', 'year' => 2024, 'color' => 'Hitam', 'plate' => 'B 1024 GHI', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 350000, 'driver' => 175000],
            ['name' => 'Daihatsu Sigra 1.2 R', 'brand' => 'Daihatsu', 'model' => 'Sigra', 'year' => 2023, 'color' => 'Silver', 'plate' => 'B 1025 GHI', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 275000, 'driver' => 150000],
            ['name' => 'Daihatsu Taruna CVT', 'brand' => 'Daihatsu', 'model' => 'Taruna', 'year' => 2024, 'color' => 'Putih', 'plate' => 'B 1026 GHI', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 325000, 'driver' => 175000],

            ['name' => 'Suzuki Ertiga GL MT', 'brand' => 'Suzuki', 'model' => 'Ertiga', 'year' => 2023, 'color' => 'Putih', 'plate' => 'B 1027 JKL', 'seats' => 7, 'trans' => 'manual', 'fuel' => 'gasoline', 'daily' => 300000, 'driver' => 175000],
            ['name' => 'Suzuki XL7 Alpha AT', 'brand' => 'Suzuki', 'model' => 'XL7', 'year' => 2024, 'color' => 'Hitam', 'plate' => 'B 1028 JKL', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 375000, 'driver' => 200000],
            ['name' => 'Suzuki Jimny Sierra AT', 'brand' => 'Suzuki', 'model' => 'Jimny', 'year' => 2024, 'color' => 'Kinetic Yellow', 'plate' => 'B 1029 JKL', 'seats' => 4, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 550000, 'driver' => 250000],
            ['name' => 'Suzuki S-Presso MT', 'brand' => 'Suzuki', 'model' => 'S-Presso', 'year' => 2023, 'color' => 'Merah', 'plate' => 'B 1030 JKL', 'seats' => 5, 'trans' => 'manual', 'fuel' => 'gasoline', 'daily' => 200000, 'driver' => 150000],
            ['name' => 'Suzuki Baleno AT', 'brand' => 'Suzuki', 'model' => 'Baleno', 'year' => 2024, 'color' => 'Putih', 'plate' => 'B 1031 JKL', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 300000, 'driver' => 175000],
            ['name' => 'Suzuki Grand Vitara AT', 'brand' => 'Suzuki', 'model' => 'Grand Vitara', 'year' => 2024, 'color' => 'Hitam', 'plate' => 'B 1032 JKL', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 475000, 'driver' => 200000],
            ['name' => 'Suzuki APV Arena SGX', 'brand' => 'Suzuki', 'model' => 'APV', 'year' => 2023, 'color' => 'Silver', 'plate' => 'B 1033 JKL', 'seats' => 8, 'trans' => 'manual', 'fuel' => 'gasoline', 'daily' => 350000, 'driver' => 175000],

            ['name' => 'Mitsubishi Xpander Ultimate AT', 'brand' => 'Mitsubishi', 'model' => 'Xpander', 'year' => 2024, 'color' => 'Putih', 'plate' => 'B 1034 MNO', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 400000, 'driver' => 200000],
            ['name' => 'Mitsubishi Xpander Cross', 'brand' => 'Mitsubishi', 'model' => 'Xpander Cross', 'year' => 2024, 'color' => 'Hitam', 'plate' => 'B 1035 MNO', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 450000, 'driver' => 200000],
            ['name' => 'Mitsubishi Pajero Sport Dakar', 'brand' => 'Mitsubishi', 'model' => 'Pajero Sport', 'year' => 2024, 'color' => 'Hitam', 'plate' => 'B 1036 MNO', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'diesel', 'daily' => 900000, 'driver' => 300000],
            ['name' => 'Mitsubishi Outlander PHEV', 'brand' => 'Mitsubishi', 'model' => 'Outlander', 'year' => 2024, 'color' => 'White', 'plate' => 'B 1037 MNO', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'hybrid', 'daily' => 1100000, 'driver' => 350000],
            ['name' => 'Mitsubishi Colt Diesel', 'brand' => 'Mitsubishi', 'model' => 'Colt Diesel', 'year' => 2023, 'color' => 'Kuning', 'plate' => 'B 1038 MNO', 'seats' => 3, 'trans' => 'manual', 'fuel' => 'diesel', 'daily' => 400000, 'driver' => 200000],

            ['name' => 'Hyundai Stargazer Prime', 'brand' => 'Hyundai', 'model' => 'Stargazer', 'year' => 2024, 'color' => 'Midnight Black', 'plate' => 'B 1039 PQR', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 450000, 'driver' => 200000],
            ['name' => 'Hyundai Creta Prime', 'brand' => 'Hyundai', 'model' => 'Creta', 'year' => 2024, 'color' => 'Titan Gray', 'plate' => 'B 1040 PQR', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 475000, 'driver' => 200000],
            ['name' => 'Hyundai Ioniq 5', 'brand' => 'Hyundai', 'model' => 'Ioniq 5', 'year' => 2024, 'color' => 'Titan Gray', 'plate' => 'B 1041 PQR', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'electric', 'daily' => 800000, 'driver' => 300000],
            ['name' => 'Hyundai Tucson Ultimate', 'brand' => 'Hyundai', 'model' => 'Tucson', 'year' => 2024, 'color' => 'Phantom Black', 'plate' => 'B 1042 PQR', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 700000, 'driver' => 250000],

            ['name' => 'Kia Sonet Gravity', 'brand' => 'Kia', 'model' => 'Sonet', 'year' => 2024, 'color' => 'White', 'plate' => 'B 1043 STU', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 375000, 'driver' => 175000],
            ['name' => 'Kia Seltos GT-Line', 'brand' => 'Kia', 'model' => 'Seltos', 'year' => 2024, 'color' => 'Black', 'plate' => 'B 1044 STU', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 500000, 'driver' => 200000],
            ['name' => 'Kia Carnival Prestige', 'brand' => 'Kia', 'model' => 'Carnival', 'year' => 2024, 'color' => 'Snow White', 'plate' => 'B 1045 STU', 'seats' => 8, 'trans' => 'automatic', 'fuel' => 'diesel', 'daily' => 1200000, 'driver' => 350000],
            ['name' => 'Kia EV6 GT-Line', 'brand' => 'Kia', 'model' => 'EV6', 'year' => 2024, 'color' => 'Runway Red', 'plate' => 'B 1046 STU', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'electric', 'daily' => 900000, 'driver' => 300000],

            ['name' => 'Mazda CX-5 Touring', 'brand' => 'Mazda', 'model' => 'CX-5', 'year' => 2024, 'color' => 'Soul Red', 'plate' => 'B 1047 VWX', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 750000, 'driver' => 250000],
            ['name' => 'Mazda 2 Hatchback', 'brand' => 'Mazda', 'model' => 'Mazda2', 'year' => 2024, 'color' => 'Crystal White', 'plate' => 'B 1048 VWX', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 400000, 'driver' => 200000],
            ['name' => 'Nissan Livina VL AT', 'brand' => 'Nissan', 'model' => 'Livina', 'year' => 2024, 'color' => 'White', 'plate' => 'B 1049 YZA', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 350000, 'driver' => 175000],
            ['name' => 'Nissan X-Trail VE', 'brand' => 'Nissan', 'model' => 'X-Trail', 'year' => 2024, 'color' => 'Bronze', 'plate' => 'B 1050 YZA', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 650000, 'driver' => 250000],

            ['name' => 'Toyota Raize 1.0T GR', 'brand' => 'Toyota', 'model' => 'Raize', 'year' => 2024, 'color' => 'Turquoise Blue', 'plate' => 'B 1051 ZZZ', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 325000, 'driver' => 175000],
            ['name' => 'Toyota Veloz 1.5 CVT', 'brand' => 'Toyota', 'model' => 'Veloz', 'year' => 2024, 'color' => ' Platinum White', 'plate' => 'B 1052 ZZZ', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 375000, 'driver' => 200000],
            ['name' => 'Toyota Agya 1.2 GR Sport', 'brand' => 'Toyota', 'model' => 'Agya', 'year' => 2024, 'color' => 'Red', 'plate' => 'B 1053 ZZZ', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 225000, 'driver' => 150000],
            ['name' => 'Toyota Land Cruiser 300', 'brand' => 'Toyota', 'model' => 'Land Cruiser', 'year' => 2024, 'color' => 'Precious White', 'plate' => 'B 1054 ZZZ', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'diesel', 'daily' => 2500000, 'driver' => 500000],
            ['name' => 'Honda Freed G CVT', 'brand' => 'Honda', 'model' => 'Freed', 'year' => 2024, 'color' => 'Premium Sunlight White', 'plate' => 'B 1055 ZZZ', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 425000, 'driver' => 200000],
            ['name' => 'Honda ZR-V 1.5 Turbo', 'brand' => 'Honda', 'model' => 'ZR-V', 'year' => 2024, 'color' => 'Canyon River Blue', 'plate' => 'B 1056 ZZZ', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 575000, 'driver' => 225000],
            ['name' => 'Mitsubishi Xpander GLS AT', 'brand' => 'Mitsubishi', 'model' => 'Xpander', 'year' => 2024, 'color' => 'Quartz White', 'plate' => 'B 1057 ZZZ', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 375000, 'driver' => 200000],
            ['name' => 'Daihatsu Rocky 1.2 Turbo', 'brand' => 'Daihatsu', 'model' => 'Rocky', 'year' => 2024, 'color' => 'SCใจ BRONZE', 'plate' => 'B 1058 ZZZ', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 350000, 'driver' => 175000],
            ['name' => 'Suzuki Carry Pick Up', 'brand' => 'Suzuki', 'model' => 'Carry', 'year' => 2024, 'color' => 'Putih', 'plate' => 'B 1059 ZZZ', 'seats' => 3, 'trans' => 'manual', 'fuel' => 'gasoline', 'daily' => 250000, 'driver' => 150000],
            ['name' => 'Hyundai Stargazer X', 'brand' => 'Hyundai', 'model' => 'Stargazer X', 'year' => 2024, 'color' => 'Ocean inds', 'plate' => 'B 1060 ZZZ', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 500000, 'driver' => 225000],
            ['name' => 'Kia Carens Luxury', 'brand' => 'Kia', 'model' => 'Carens', 'year' => 2024, 'color' => 'Gravity Gray', 'plate' => 'B 1061 ZZZ', 'seats' => 7, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 475000, 'driver' => 200000],
            ['name' => 'Mazda CX-30 Premium', 'brand' => 'Mazda', 'model' => 'CX-30', 'year' => 2024, 'color' => 'Machine Gray', 'plate' => 'B 1062 ZZZ', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 625000, 'driver' => 250000],
            ['name' => 'Nissan Kicks e-POWER', 'brand' => 'Nissan', 'model' => 'Kicks', 'year' => 2024, 'color' => 'Two-Tone Black', 'plate' => 'B 1063 ZZZ', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'hybrid', 'daily' => 550000, 'driver' => 225000],
            ['name' => 'Suzuki Jimny 5-Door MT', 'brand' => 'Suzuki', 'model' => 'Jimny', 'year' => 2024, 'color' => 'Kinetic Yellow', 'plate' => 'B 1064 ZZZ', 'seats' => 5, 'trans' => 'manual', 'fuel' => 'gasoline', 'daily' => 600000, 'driver' => 275000],
            ['name' => 'Honda Civic eHEV RS', 'brand' => 'Honda', 'model' => 'Civic', 'year' => 2024, 'color' => 'Canyon River Blue', 'plate' => 'B 1065 ZZZ', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'hybrid', 'daily' => 850000, 'driver' => 325000],
            ['name' => 'Toyota bZ4X', 'brand' => 'Toyota', 'model' => 'bZ4X', 'year' => 2024, 'color' => 'Precious White', 'plate' => 'B 1066 ZZZ', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'electric', 'daily' => 950000, 'driver' => 350000],
            ['name' => 'Hyundai Ioniq 6', 'brand' => 'Hyundai', 'model' => 'Ioniq 6', 'year' => 2024, 'color' => 'Optimistic Blue', 'plate' => 'B 1067 ZZZ', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'electric', 'daily' => 900000, 'driver' => 325000],
            ['name' => 'Mitsubishi Outlander Sport', 'brand' => 'Mitsubishi', 'model' => 'Outlander Sport', 'year' => 2024, 'color' => 'Quartz White', 'plate' => 'B 1068 ZZZ', 'seats' => 5, 'trans' => 'automatic', 'fuel' => 'gasoline', 'daily' => 475000, 'driver' => 200000],
            ['name' => 'Daihatsu Xenia 1.5 R MT', 'brand' => 'Daihatsu', 'model' => 'Xenia', 'year' => 2024, 'color' => 'Silver Metallic', 'plate' => 'B 1069 ZZZ', 'seats' => 7, 'trans' => 'manual', 'fuel' => 'gasoline', 'daily' => 275000, 'driver' => 175000],
        ];

        foreach ($mobil as $v) {
            Vehicle::create([
                'category_id' => $mobilCat->id,
                'owner_id' => $owner->id,
                'name' => $v['name'],
                'slug' => \Illuminate\Support\Str::slug($v['name']) . '-' . \Illuminate\Support\Str::random(5),
                'brand' => $v['brand'],
                'model' => $v['model'],
                'year' => $v['year'],
                'color' => $v['color'],
                'license_plate' => $v['plate'],
                'description' => $v['name'] . ', kondisi prima dan terawat.',
                'daily_price' => $v['daily'],
                'weekly_price' => $v['daily'] * 6,
                'monthly_price' => $v['daily'] * 25,
                'hourly_price' => round($v['daily'] * 0.15),
                'with_driver_daily_price' => $v['driver'],
                'status' => 'available',
                'condition' => 'excellent',
                'seats' => $v['seats'],
                'transmission' => $v['trans'],
                'fuel_type' => $v['fuel'],
                'with_driver' => $v['seats'] >= 4,
                'is_active' => true,
            ]);
        }

        // ── MOTOR (50) ────────────────────────────────────────────
        $motor = [
            ['name' => 'Honda Vario 160 CBS', 'brand' => 'Honda', 'model' => 'Vario 160', 'year' => 2024, 'color' => 'Biru', 'plate' => 'B 2001 AAA', 'daily' => 75000],
            ['name' => 'Honda Vario 125 CBS', 'brand' => 'Honda', 'model' => 'Vario 125', 'year' => 2024, 'color' => 'Putih', 'plate' => 'B 2002 AAA', 'daily' => 65000],
            ['name' => 'Honda PCX 160 ABS', 'brand' => 'Honda', 'model' => 'PCX 160', 'year' => 2024, 'color' => 'Hitam', 'plate' => 'B 2003 AAA', 'daily' => 110000],
            ['name' => 'Honda ADV 160 ABS', 'brand' => 'Honda', 'model' => 'ADV 160', 'year' => 2024, 'color' => 'Tough Matt', 'plate' => 'B 2004 AAA', 'daily' => 115000],
            ['name' => 'Honda Beat Street', 'brand' => 'Honda', 'model' => 'Beat Street', 'year' => 2024, 'color' => 'Merah', 'plate' => 'B 2005 AAA', 'daily' => 50000],
            ['name' => 'Honda Scoopy Stylish', 'brand' => 'Honda', 'model' => 'Scoopy', 'year' => 2024, 'color' => 'Cream', 'plate' => 'B 2006 AAA', 'daily' => 55000],
            ['name' => 'Honda Stylo 160', 'brand' => 'Honda', 'model' => 'Stylo 160', 'year' => 2024, 'color' => 'Royal Matte Green', 'plate' => 'B 2007 AAA', 'daily' => 80000],
            ['name' => 'Honda CRF150L', 'brand' => 'Honda', 'model' => 'CRF150L', 'year' => 2024, 'color' => 'Extreme Red', 'plate' => 'B 2008 AAA', 'daily' => 100000],
            ['name' => 'Honda CB150X', 'brand' => 'Honda', 'model' => 'CB150X', 'year' => 2024, 'color' => 'Volcano Matte Black', 'plate' => 'B 2009 AAA', 'daily' => 100000],
            ['name' => 'Honda CBR150R ABS', 'brand' => 'Honda', 'model' => 'CBR150R', 'year' => 2024, 'color' => 'Dominator Matte Black', 'plate' => 'B 2010 AAA', 'daily' => 110000],

            ['name' => 'Yamaha NMAX 155 Connected', 'brand' => 'Yamaha', 'model' => 'NMAX', 'year' => 2024, 'color' => 'Putih', 'plate' => 'B 2011 BBB', 'daily' => 100000],
            ['name' => 'Yamaha NMAX 155 ABS', 'brand' => 'Yamaha', 'model' => 'NMAX', 'year' => 2024, 'color' => 'Hitam', 'plate' => 'B 2012 BBB', 'daily' => 110000],
            ['name' => 'Yamaha Aerox 155 S', 'brand' => 'Yamaha', 'model' => 'Aerox', 'year' => 2024, 'color' => 'Magma Black', 'plate' => 'B 2013 BBB', 'daily' => 95000],
            ['name' => 'Yamaha Lexi LX 155', 'brand' => 'Yamaha', 'model' => 'Lexi', 'year' => 2024, 'color' => 'Matte Red', 'plate' => 'B 2014 BBB', 'daily' => 90000],
            ['name' => 'Yamaha Mio M3 125', 'brand' => 'Yamaha', 'model' => 'Mio M3', 'year' => 2024, 'color' => 'Biru', 'plate' => 'B 2015 BBB', 'daily' => 50000],
            ['name' => 'Yamaha Grand Filano', 'brand' => 'Yamaha', 'model' => 'Grand Filano', 'year' => 2024, 'color' => 'Luminous White', 'plate' => 'B 2016 BBB', 'daily' => 70000],
            ['name' => 'Yamaha MT-25', 'brand' => 'Yamaha', 'model' => 'MT-25', 'year' => 2024, 'color' => 'Cyan Storm', 'plate' => 'B 2017 BBB', 'daily' => 130000],
            ['name' => 'Yamaha XSR 155', 'brand' => 'Yamaha', 'model' => 'XSR 155', 'year' => 2024, 'color' => 'White', 'plate' => 'B 2018 BBB', 'daily' => 110000],
            ['name' => 'Yamaha V-Ixion R', 'brand' => 'Yamaha', 'model' => 'V-Ixion', 'year' => 2024, 'color' => 'Matte Black', 'plate' => 'B 2019 BBB', 'daily' => 100000],
            ['name' => 'Yamaha WR 155 R', 'brand' => 'Yamaha', 'model' => 'WR 155', 'year' => 2024, 'color' => 'Yamaha Blue', 'plate' => 'B 2020 BBB', 'daily' => 110000],

            ['name' => 'Suzuki Nex II', 'brand' => 'Suzuki', 'model' => 'Nex II', 'year' => 2024, 'color' => 'Titan Black', 'plate' => 'B 2021 CCC', 'daily' => 50000],
            ['name' => 'Suzuki Address', 'brand' => 'Suzuki', 'model' => 'Address', 'year' => 2024, 'color' => 'Biru', 'plate' => 'B 2022 CCC', 'daily' => 48000],
            ['name' => 'Suzuki GSX-R150', 'brand' => 'Suzuki', 'model' => 'GSX-R150', 'year' => 2024, 'color' => 'Triton Blue', 'plate' => 'B 2023 CCC', 'daily' => 100000],
            ['name' => 'Suzuki Burgman Street 125', 'brand' => 'Suzuki', 'model' => 'Burgman', 'year' => 2024, 'color' => 'Matt Black', 'plate' => 'B 2024 CCC', 'daily' => 80000],

            ['name' => 'Kawasaki Ninja ZX-25R SE', 'brand' => 'Kawasaki', 'model' => 'ZX-25R', 'year' => 2024, 'color' => 'Lime Green', 'plate' => 'B 2025 DDD', 'daily' => 200000],
            ['name' => 'Kawasaki Ninja 250 ABS', 'brand' => 'Kawasaki', 'model' => 'Ninja 250', 'year' => 2024, 'color' => 'Lime Green', 'plate' => 'B 2026 DDD', 'daily' => 150000],
            ['name' => 'Kawasaki W175 SE', 'brand' => 'Kawasaki', 'model' => 'W175', 'year' => 2024, 'color' => 'Metallic Flat Spark Black', 'plate' => 'B 2027 DDD', 'daily' => 90000],
            ['name' => 'Kawasaki KLX 150', 'brand' => 'Kawasaki', 'model' => 'KLX 150', 'year' => 2024, 'color' => 'Hijau', 'plate' => 'B 2028 DDD', 'daily' => 80000],
            ['name' => 'Kawasaki Versys-X 250', 'brand' => 'Kawasaki', 'model' => 'Versys-X', 'year' => 2024, 'color' => 'Pearl Stardust White', 'plate' => 'B 2029 DDD', 'daily' => 140000],
            ['name' => 'Kawasaki Vulcan S', 'brand' => 'Kawasaki', 'model' => 'Vulcan S', 'year' => 2024, 'color' => 'Flat Black', 'plate' => 'B 2030 DDD', 'daily' => 150000],

            ['name' => 'Vespa Sprint 150', 'brand' => 'Vespa', 'model' => 'Sprint', 'year' => 2024, 'color' => 'Nero Vulcano', 'plate' => 'B 2031 EEE', 'daily' => 100000],
            ['name' => 'Vespa Primavera 150', 'brand' => 'Vespa', 'model' => 'Primavera', 'year' => 2024, 'color' => 'Verde Pastello', 'plate' => 'B 2032 EEE', 'daily' => 100000],
            ['name' => 'Vespa GTS Super 300', 'brand' => 'Vespa', 'model' => 'GTS Super', 'year' => 2024, 'color' => 'Grigio Sincero', 'plate' => 'B 2033 EEE', 'daily' => 150000],
            ['name' => 'Vespa LX 125 i-Get', 'brand' => 'Vespa', 'model' => 'LX 125', 'year' => 2024, 'color' => 'Blu Midnight', 'plate' => 'B 2034 EEE', 'daily' => 80000],

            ['name' => 'Honda Beat', 'brand' => 'Honda', 'model' => 'Beat', 'year' => 2024, 'color' => 'Putih', 'plate' => 'B 2035 FFF', 'daily' => 45000],
            ['name' => 'Honda Scoopy Pop', 'brand' => 'Honda', 'model' => 'Scoopy', 'year' => 2024, 'color' => 'Hitam', 'plate' => 'B 2036 FFF', 'daily' => 55000],
            ['name' => 'Honda Dio 110', 'brand' => 'Honda', 'model' => 'Dio', 'year' => 2024, 'color' => 'Merah', 'plate' => 'B 2037 FFF', 'daily' => 45000],
            ['name' => 'Yamaha GTZ 125', 'brand' => 'Yamaha', 'model' => 'GTZ 125', 'year' => 2024, 'color' => 'Silver', 'plate' => 'B 2038 FFF', 'daily' => 50000],
            ['name' => 'Honda CRF250 Rally', 'brand' => 'Honda', 'model' => 'CRF250 Rally', 'year' => 2024, 'color' => 'Extreme Red', 'plate' => 'B 2039 FFF', 'daily' => 150000],
            ['name' => 'Kawasaki Z250 ABS', 'brand' => 'Kawasaki', 'model' => 'Z250', 'year' => 2024, 'color' => 'Metallic Flat Spark Black', 'plate' => 'B 2040 FFF', 'daily' => 130000],

            ['name' => 'Honda CBR250RR', 'brand' => 'Honda', 'model' => 'CBR250RR', 'year' => 2024, 'color' => 'Bravery Mat Red', 'plate' => 'B 2041 GGG', 'daily' => 180000],
            ['name' => 'Yamaha R25 ABS', 'brand' => 'Yamaha', 'model' => 'R25', 'year' => 2024, 'color' => 'Yamaha Blue', 'plate' => 'B 2042 GGG', 'daily' => 140000],
            ['name' => 'Kawasaki Z650 ABS', 'brand' => 'Kawasaki', 'model' => 'Z650', 'year' => 2024, 'color' => 'Candy Plasma Blue', 'plate' => 'B 2043 GGG', 'daily' => 200000],
            ['name' => 'Kawasaki W250', 'brand' => 'Kawasaki', 'model' => 'W250', 'year' => 2024, 'color' => 'Metallic Spark Black', 'plate' => 'B 2044 GGG', 'daily' => 120000],
            ['name' => 'Yamaha Tracer 900 GT', 'brand' => 'Yamaha', 'model' => 'Tracer 900', 'year' => 2024, 'color' => 'Icon Performance', 'plate' => 'B 2045 GGG', 'daily' => 250000],
            ['name' => 'Honda CB650R ABS', 'brand' => 'Honda', 'model' => 'CB650R', 'year' => 2024, 'color' => 'Matt Gunpowder Black', 'plate' => 'B 2046 GGG', 'daily' => 230000],
            ['name' => 'Yamaha XMAX Connected', 'brand' => 'Yamaha', 'model' => 'XMAX', 'year' => 2024, 'color' => 'Matte Black', 'plate' => 'B 2047 GGG', 'daily' => 130000],
            ['name' => 'Honda Forza 250', 'brand' => 'Honda', 'model' => 'Forza', 'year' => 2024, 'color' => 'Gunmetal Black', 'plate' => 'B 2048 GGG', 'daily' => 140000],
            ['name' => 'Vespa GTS Super Sport 150', 'brand' => 'Vespa', 'model' => 'GTS Super Sport', 'year' => 2024, 'color' => 'Rosso', 'plate' => 'B 2049 GGG', 'daily' => 120000],
            ['name' => 'Suzuki GSX-S150', 'brand' => 'Suzuki', 'model' => 'GSX-S150', 'year' => 2024, 'color' => 'Metallic Triton Blue', 'plate' => 'B 2050 GGG', 'daily' => 90000],

            ['name' => 'Honda Vario 160 ABS', 'brand' => 'Honda', 'model' => 'Vario 160', 'year' => 2024, 'color' => 'Matte Gunpowder Black', 'plate' => 'B 2051 HHH', 'daily' => 85000],
            ['name' => 'Honda PCX 160 CBS', 'brand' => 'Honda', 'model' => 'PCX 160', 'year' => 2024, 'color' => 'Eternal Blue', 'plate' => 'B 2052 HHH', 'daily' => 100000],
            ['name' => 'Yamaha NMAX Turbo', 'brand' => 'Yamaha', 'model' => 'NMAX', 'year' => 2024, 'color' => 'Magma Black', 'plate' => 'B 2053 HHH', 'daily' => 120000],
            ['name' => 'Yamaha Aerox 155 R-Version', 'brand' => 'Yamaha', 'model' => 'Aerox', 'year' => 2024, 'color' => 'Icon Blue', 'plate' => 'B 2054 HHH', 'daily' => 105000],
            ['name' => 'Kawasaki Ninja ZX-25R Mono', 'brand' => 'Kawasaki', 'model' => 'ZX-25R', 'year' => 2024, 'color' => 'Metallic Flat Spark Black', 'plate' => 'B 2055 HHH', 'daily' => 180000],
            ['name' => 'Kawasaki Ninja 250 SL', 'brand' => 'Kawasaki', 'model' => 'Ninja 250 SL', 'year' => 2024, 'color' => 'Lime Green', 'plate' => 'B 2056 HHH', 'daily' => 130000],
            ['name' => 'Honda CRF250L', 'brand' => 'Honda', 'model' => 'CRF250L', 'year' => 2024, 'color' => 'Extreme Red', 'plate' => 'B 2057 HHH', 'daily' => 140000],
            ['name' => 'Yamaha WR 250 R', 'brand' => 'Yamaha', 'model' => 'WR 250', 'year' => 2024, 'color' => 'Team Yamaha Blue', 'plate' => 'B 2058 HHH', 'daily' => 160000],
            ['name' => 'Kawasaki Versys 650', 'brand' => 'Kawasaki', 'model' => 'Versys 650', 'year' => 2024, 'color' => 'Candy Burnt Orange', 'plate' => 'B 2059 HHH', 'daily' => 250000],
            ['name' => 'Honda ADV 160 CBS', 'brand' => 'Honda', 'model' => 'ADV 160', 'year' => 2024, 'color' => 'Tough Matte Red', 'plate' => 'B 2060 HHH', 'daily' => 105000],
            ['name' => 'Vespa Primavera S 150', 'brand' => 'Vespa', 'model' => 'Primavera S', 'year' => 2024, 'color' => 'Nero Vulcano', 'plate' => 'B 2061 HHH', 'daily' => 115000],
            ['name' => 'Vespa Sprint S 150', 'brand' => 'Vespa', 'model' => 'Sprint S', 'year' => 2024, 'color' => 'Rosso Scarlatto', 'plate' => 'B 2062 HHH', 'daily' => 115000],
            ['name' => 'Yamaha TMAX 560', 'brand' => 'Yamaha', 'model' => 'TMAX', 'year' => 2024, 'color' => 'Dark Bluish Grey', 'plate' => 'B 2063 HHH', 'daily' => 220000],
            ['name' => 'Honda Forza 350', 'brand' => 'Honda', 'model' => 'Forza', 'year' => 2024, 'color' => 'Matt Gunpowder Black', 'plate' => 'B 2064 HHH', 'daily' => 180000],
            ['name' => 'Suzuki V-Strom 250 SX', 'brand' => 'Suzuki', 'model' => 'V-Strom', 'year' => 2024, 'color' => 'Champion Yellow', 'plate' => 'B 2065 HHH', 'daily' => 130000],
            ['name' => 'Kawasaki Eliminator 400', 'brand' => 'Kawasaki', 'model' => 'Eliminator', 'year' => 2024, 'color' => 'Metallic Flat Spark Black', 'plate' => 'B 2066 HHH', 'daily' => 170000],
            ['name' => 'Yamaha MT-03 ABS', 'brand' => 'Yamaha', 'model' => 'MT-03', 'year' => 2024, 'color' => 'Ice Fluo-Vermillion', 'plate' => 'B 2067 HHH', 'daily' => 140000],
            ['name' => 'Honda CB300R ABS', 'brand' => 'Honda', 'model' => 'CB300R', 'year' => 2024, 'color' => 'Matt Gunpowder Black', 'plate' => 'B 2068 HHH', 'daily' => 150000],
            ['name' => 'Suzuki Katana 1000', 'brand' => 'Suzuki', 'model' => 'Katana', 'year' => 2024, 'color' => 'Metallic Triton Blue', 'plate' => 'B 2069 HHH', 'daily' => 300000],
        ];

        foreach ($motor as $v) {
            Vehicle::create([
                'category_id' => $motorCat->id,
                'owner_id' => $owner->id,
                'name' => $v['name'],
                'slug' => \Illuminate\Support\Str::slug($v['name']) . '-' . \Illuminate\Support\Str::random(5),
                'brand' => $v['brand'],
                'model' => $v['model'],
                'year' => $v['year'],
                'color' => $v['color'],
                'license_plate' => $v['plate'],
                'description' => $v['name'] . ', kondisi prima dan terawat.',
                'daily_price' => $v['daily'],
                'weekly_price' => $v['daily'] * 6,
                'monthly_price' => $v['daily'] * 25,
                'hourly_price' => round($v['daily'] * 0.15),
                'with_driver_daily_price' => 0,
                'status' => 'available',
                'condition' => 'excellent',
                'seats' => 2,
                'transmission' => 'automatic',
                'fuel_type' => 'gasoline',
                'with_driver' => false,
                'is_active' => true,
            ]);
        }

        echo "Vehicle seeder completed!\n";
        echo "  - " . Vehicle::where('category_id', $mobilCat->id)->count() . " mobil\n";
        echo "  - " . Vehicle::where('category_id', $motorCat->id)->count() . " motor\n";
    }
}
