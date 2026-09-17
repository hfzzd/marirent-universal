<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Isi logo untuk setiap merchant yang belum punya logo.
 * Logo berupa SVG inisial toko di atas gradasi sky-blue,
 * disimpan ke disk public (storage/app/public/merchant-logos).
 */
class EnsureMerchantLogos extends Command
{
    protected $signature = 'merchant:ensure-logos';

    protected $description = 'Buatkan logo untuk setiap merchant yang belum memiliki logo.';

    public function handle(): int
    {
        $merchants = Merchant::whereNull('logo')->orWhere('logo', '')->get();

        if ($merchants->isEmpty()) {
            $this->info('Semua merchant sudah memiliki logo.');

            return self::SUCCESS;
        }

        $this->info('Membuat logo untuk ' . $merchants->count() . ' merchant...');

        foreach ($merchants as $merchant) {
            $merchant->update(['logo' => $this->makeLogo($merchant->name)]);
            $this->line("  [OK] {$merchant->name}");
        }

        $this->info('Selesai.');

        return self::SUCCESS;
    }

    private function makeLogo(string $storeName): string
    {
        $slug = Str::slug($storeName) ?: Str::random(6);
        $path = 'merchant-logos/' . $slug . '-' . Str::random(4) . '.svg';

        $words = preg_split('/\s+/', trim($storeName));
        $initials = strtoupper(substr($words[0] ?? 'M', 0, 1) . substr($words[1] ?? 'R', 0, 1));
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256" viewBox="0 0 256 256">'
            . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
            . '<stop offset="0" stop-color="#38bdf8"/><stop offset="1" stop-color="#0284c7"/>'
            . '</linearGradient></defs>'
            . '<rect width="256" height="256" rx="56" fill="url(#g)"/>'
            . '<circle cx="200" cy="56" r="60" fill="#ffffff" opacity="0.12"/>'
            . '<circle cx="52" cy="208" r="44" fill="#ffffff" opacity="0.10"/>'
            . '<text x="128" y="148" font-family="Arial, sans-serif" font-size="96" font-weight="bold" fill="#ffffff" text-anchor="middle">'
            . htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') . '</text></svg>';
        Storage::disk('public')->put($path, $svg);

        return $path;
    }
}
