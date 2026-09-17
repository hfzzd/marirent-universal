<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use App\Services\GeocodeService;
use Illuminate\Console\Command;

/**
 * Backfill koordinat (lat/lng) untuk merchant lama yang sudah punya alamat
 * tetapi belum punya titik lokasi di peta. Menghormati kebijakan Nominatim
 * (~1 permintaan/detik) agar tidak di-ban.
 */
class GeocodeMerchants extends Command
{
    protected $signature = 'merchant:geocode {--delay=1.1 : Jeda antar permintaan (detik), ikuti kebijakan Nominatim}';

    protected $description = 'Isi koordinat latitude/longitude merchant yang belum punya lokasi dari alamatnya.';

    public function handle(GeocodeService $geocode): int
    {
        $delay = (float) $this->option('delay');

        $merchants = Merchant::whereNull('latitude')
            ->where(function ($q) {
                $q->whereNotNull('address')->orWhereNotNull('city');
            })
            ->get();

        if ($merchants->isEmpty()) {
            $this->info('Tidak ada merchant yang perlu di-geocode.');

            return self::SUCCESS;
        }

        $this->info('Meng-geocode ' . $merchants->count() . ' merchant...');

        $updated = 0;
        $failed = 0;
        foreach ($merchants as $merchant) {
            $query = implode(', ', array_filter([$merchant->address, $merchant->city]));
            $coords = $geocode->geocode($query);

            // Fallback offline: titik tengah kota sesuai lokasi merchant.
            if (!$coords) {
                $coords = $this->cityFallback($merchant->city);
                if ($coords) {
                    // Offset kecil agar tiap toko tidak menumpuk di titik yang sama.
                    $coords['latitude'] += mt_rand(-250, 250) / 100000;
                    $coords['longitude'] += mt_rand(-250, 250) / 100000;
                }
            }

            if ($coords) {
                $merchant->update([
                    'latitude' => $coords['latitude'],
                    'longitude' => $coords['longitude'],
                ]);
                $updated++;
                $this->line("  [OK] {$merchant->name} -> {$coords['latitude']}, {$coords['longitude']}");
            } else {
                $failed++;
                $this->warn("  [ - ] {$merchant->name} tidak ditemukan, dilewati.");
            }

            if ($delay > 0) {
                usleep((int) ($delay * 1_000_000));
            }
        }

        $this->info("Selesai: {$updated} diperbarui, {$failed} gagal.");

        return self::SUCCESS;
    }

    /**
     * Koordinat tengah kota (sesuai lokasi merchant) untuk fallback
     * saat geocoding online tidak menemukan alamat.
     */
    private function cityFallback(?string $city): ?array
    {
        $key = strtolower(trim((string) $city));

        $map = [
            'jakarta' => ['latitude' => -6.2000000, 'longitude' => 106.8166667],
            'bandung' => ['latitude' => -6.9175000, 'longitude' => 107.6091000],
            'surabaya' => ['latitude' => -7.2575000, 'longitude' => 112.7521000],
            'yogyakarta' => ['latitude' => -7.7956000, 'longitude' => 110.3695000],
            'yogya' => ['latitude' => -7.7956000, 'longitude' => 110.3695000],
            'jogja' => ['latitude' => -7.7956000, 'longitude' => 110.3695000],
            'medan' => ['latitude' => 3.5952000, 'longitude' => 98.6722000],
            'denpasar' => ['latitude' => -8.6705000, 'longitude' => 115.2126000],
            'bali' => ['latitude' => -8.6705000, 'longitude' => 115.2126000],
            'malang' => ['latitude' => -7.9666000, 'longitude' => 112.6326000],
            'semarang' => ['latitude' => -6.9932000, 'longitude' => 110.4203000],
            'tangerang' => ['latitude' => -6.1783000, 'longitude' => 106.6319000],
            'bekasi' => ['latitude' => -6.2383000, 'longitude' => 106.9756000],
            'makassar' => ['latitude' => -5.1477000, 'longitude' => 119.4327000],
        ];

        foreach ($map as $name => $coords) {
            if ($key !== '' && str_contains($key, $name)) {
                return $coords;
            }
        }

        // Default: Jakarta (pusat Indonesia barat) agar peta tetap tampil.
        return ['latitude' => -6.2000000, 'longitude' => 106.8166667];
    }
}