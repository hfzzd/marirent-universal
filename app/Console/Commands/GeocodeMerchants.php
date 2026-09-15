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
}