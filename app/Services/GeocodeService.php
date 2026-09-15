<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Geocoding alamat ke koordinat (latitude/longitude) tanpa API key
 * menggunakan Nominatim (OpenStreetMap). Hanya dipanggil saat owner
 * menyimpan alamat toko, sehingga beban kueri tetap kecil.
 */
class GeocodeService
{
    private string $baseUrl = 'https://nominatim.openstreetmap.org/search';

    public function __construct(private ?string $userAgent = null)
    {
        $this->userAgent ??= 'MariRent/1.0 (contact: ' . config('app.url', 'localhost') . ')';
    }

    public function geocode(string $query): ?array
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        try {
            $response = Http::withHeaders(['User-Agent' => $this->userAgent])
                ->timeout(6)
                ->get($this->baseUrl, [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => 'id',
                ]);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $results = $response->json();
        $first = is_array($results) ? ($results[0] ?? null) : null;
        $lat = $first['lat'] ?? null;
        $lng = $first['lon'] ?? null;

        if ($lat === null || $lng === null) {
            return null;
        }

        return [
            'latitude' => (float) $lat,
            'longitude' => (float) $lng,
        ];
    }
}