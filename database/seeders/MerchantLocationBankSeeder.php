<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Merchant;
use Illuminate\Database\Seeder;

/**
 * Isi koordinat peta & rekening bank untuk setiap merchant.
 *
 * - Koordinat dikurasi per toko sesuai alamat/kotanya (idempotent:
 *   hanya mengisi yang masih kosong agar data kustom owner tidak tertimpa).
 * - Rekening bank deterministik per merchant (bank bergantian
 *   BCA/Mandiri/BRI/BNI, nomor unik per merchant).
 * - Baris companies ikut disinkronkan.
 */
class MerchantLocationBankSeeder extends Seeder
{
    /**
     * [nama toko => [latitude, longitude]] sesuai alamat masing-masing.
     */
    private const COORDS = [
        // ===== 16 toko CompanySeeder =====
        'Jaya Abadi Rent Car' => [-6.1954000, 106.8230000], // Jl. MH Thamrin, Jakarta Pusat
        'Gunung Mas Auto Rental' => [-6.8850000, 107.6130000], // Jl. Dago, Bandung
        'Berkah Motor Rental' => [-7.3113000, 112.7290000], // Jl. Ahmad Yani, Surabaya
        'Cakrawala Motor Touring' => [-7.7929000, 110.3658000], // Jl. Malioboro, Yogyakarta
        'TechShare Smartphone Rental' => [-6.1580000, 106.9060000], // Kelapa Gading, Jakarta Utara
        'GadgetLoan Indonesia' => [3.5952000, 98.6722000], // Jl. Gatot Subroto, Medan
        'LensaPro Studio Rental' => [-8.7184000, 115.1689000], // Jl. Raya Kuta, Denpasar
        'VisualStory Camera Rental' => [-6.9490000, 107.6350000], // Jl. Buah Batu, Bandung
        'Outdoor Gear Rentals' => [-7.9645000, 112.6210000], // Jl. Ijen, Malang
        'Alam Camping Supply' => [-6.9875000, 110.4210000], // Jl. Pandanaran, Semarang
        'GameZone Console Rental' => [-6.3025000, 106.6520000], // BSD Serpong, Tangerang
        'BermainStudio Entertainment' => [-6.2383000, 106.9756000], // Jl. Sumatera, Bekasi
        'AeroVision Drone Rental' => [-7.2575000, 112.7521000], // Jl. Pemuda, Surabaya
        'SkyCapture Drone Service' => [-5.1487000, 119.4319000], // Jl. Sudirman, Makassar
        'Melodi Musik Rental' => [-6.9175000, 107.6091000], // Jl. Braga, Bandung
        'NadaNusantara Music' => [-7.2842000, 112.7340000], // Jl. Darmo, Surabaya
        // ===== Toko tambahan =====
        'Carismo Rent Bandung' => [-6.9400000, 107.6500000], // Jl. Soekarno-Hatta, Bandung
        'Merdeka Rent Car' => [-6.2340000, 106.8330000], // Jl. Rasuna Said, Jakarta Selatan
        'Khartika Auto 88' => [-7.2750000, 112.7360000], // Jl. Diponegoro, Surabaya
        'Laju Motor Nusantara' => [-8.7050000, 115.1680000], // Jl. Sunset Road, Denpasar
        'Gaspol Matic Rental' => [-6.8750000, 107.6000000], // Jl. Setiabudi, Bandung
        'SmartLoan Gadget' => [-7.2590000, 112.7400000], // Jl. Tunjungan, Surabaya
        'PinjamHP Indonesia' => [-7.7450000, 110.3750000], // Jl. Kaliurang, Yogyakarta
        'KlikLensa Camera Rental' => [-6.2850000, 106.7950000], // Jl. Fatmawati, Jakarta Selatan
        'FrameFokus Photography' => [-7.2600000, 112.7450000], // Jl. Gubernur Suryo, Surabaya
        'GunungBerat Outdoor' => [-6.8950000, 107.6050000], // Jl. Cihampelas, Bandung
        'TendaKita Camping' => [-7.8150000, 110.3650000], // Jl. Parangtritis, Yogyakarta
        'NextGen Console Rental' => [-6.2600000, 106.8150000], // Kemang, Jakarta Selatan
        'PlayMates Gaming' => [-6.9900000, 110.4250000], // Jl. Pemuda, Semarang
        'DroneKita Indonesia' => [-6.9200000, 107.6050000], // Jl. Asia Afrika, Bandung
        'LangitDrone Aerial' => [3.5850000, 98.6800000], // Jl. Imam Bonjol, Medan
        'Symphony Alat Musik' => [-7.9700000, 112.6250000], // Jl. Ciliwung, Malang
        'Nusantara Kargo & Rental' => [-6.9850000, 110.4150000], // Jl. Sisingamangaraja, Semarang
        'Teras Kota Outdoor' => [-6.2450000, 107.0050000], // Galaxy, Bekasi
    ];

    /**
     * Titik tengah kota untuk fallback (toko tanpa alamat spesifik).
     */
    private const CITY_FALLBACK = [
        'jakarta' => [-6.2000000, 106.8166667],
        'bandung' => [-6.9175000, 107.6091000],
        'surabaya' => [-7.2575000, 112.7521000],
        'yogyakarta' => [-7.7956000, 110.3695000],
        'medan' => [3.5952000, 98.6722000],
        'denpasar' => [-8.6705000, 115.2126000],
        'malang' => [-7.9666000, 112.6326000],
        'semarang' => [-6.9932000, 110.4203000],
        'tangerang' => [-6.1783000, 106.6319000],
        'bekasi' => [-6.2383000, 106.9756000],
        'makassar' => [-5.1477000, 119.4327000],
    ];

    private const BANKS = ['BCA', 'Mandiri', 'BRI', 'BNI'];

    public function run(): void
    {
        $coordsFilled = 0;
        $banksFilled = 0;

        foreach (Merchant::orderBy('id')->get() as $merchant) {
            $updates = [];

            if ($merchant->latitude === null || $merchant->longitude === null) {
                [$lat, $lng] = $this->coordsFor($merchant);
                $updates['latitude'] = $lat;
                $updates['longitude'] = $lng;
                $coordsFilled++;
            }

            if (empty($merchant->bank_name) || empty($merchant->bank_account_number)) {
                $bank = $this->bankFor($merchant);
                $updates['bank_name'] = $bank['bank_name'];
                $updates['bank_account_number'] = $bank['bank_account_number'];
                $updates['bank_account_holder'] = $bank['bank_account_holder'];
                $banksFilled++;
            }

            if ($updates !== []) {
                $merchant->update($updates);
                Company::where('user_id', $merchant->user_id)->update($updates);
            }
        }

        echo "MerchantLocationBankSeeder: {$coordsFilled} koordinat + {$banksFilled} rekening diisi\n";
    }

    /**
     * Koordinat toko: kurasi per nama, fallback titik kota + offset kecil
     * agar pin tiap toko tidak menumpuk.
     *
     * @return array{float,float}
     */
    private function coordsFor(Merchant $merchant): array
    {
        if (isset(self::COORDS[$merchant->name])) {
            return self::COORDS[$merchant->name];
        }

        $city = strtolower(trim((string) $merchant->city));
        foreach (self::CITY_FALLBACK as $name => $coords) {
            if ($city !== '' && str_contains($city, $name)) {
                return [$coords[0] + $this->offset($merchant->id), $coords[1] + $this->offset($merchant->id + 7)];
            }
        }

        // Tanpa kota: default Jakarta + offset per merchant.
        return [-6.2000000 + $this->offset($merchant->id), 106.8166667 + $this->offset($merchant->id + 7)];
    }

    private function offset(int $seed): int|float
    {
        return (($seed * 37) % 500 - 250) / 100000;
    }

    /**
     * Rekening deterministik & unik per merchant.
     *
     * @return array{bank_name:string,bank_account_number:string,bank_account_holder:string}
     */
    private function bankFor(Merchant $merchant): array
    {
        $bank = self::BANKS[$merchant->id % count(self::BANKS)];
        $n = str_pad((string) (($merchant->id * 137) % 1000000), 6, '0', STR_PAD_LEFT);

        $account = match ($bank) {
            'BCA' => '8210' . $n,
            'Mandiri' => '89000' . str_pad((string) (($merchant->id * 1379) % 100000000), 8, '0', STR_PAD_LEFT),
            'BRI' => '002101' . str_pad((string) (($merchant->id * 7919) % 1000000000), 9, '0', STR_PAD_LEFT),
            default => '988' . str_pad((string) (($merchant->id * 104729) % 10000000), 7, '0', STR_PAD_LEFT), // BNI
        };

        return [
            'bank_name' => $bank,
            'bank_account_number' => $account,
            'bank_account_holder' => $merchant->owner?->name ?? $merchant->name,
        ];
    }
}
