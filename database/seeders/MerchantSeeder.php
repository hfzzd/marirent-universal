<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Merchant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MerchantSeeder extends Seeder
{
    public function run(): void
    {
        $owners = User::where('role', 'owner')->get();

        foreach ($owners as $owner) {
            if (Merchant::where('user_id', $owner->id)->exists()) {
                continue;
            }

            $category = $owner->category;
            $categoryName = $category?->name;
            $baseName = $categoryName ? "Rental " . $categoryName : $owner->name;

            Merchant::create([
                'user_id' => $owner->id,
                'slug' => $this->uniqueSlug($baseName),
                'name' => $baseName,
                'description' => 'Toko resmi ' . ($categoryName ?? $owner->name) . ' di platform MariRent. Unit terpilih dan diperiksa sebelum disewakan.',
                'logo' => $this->makeLogo($baseName),
                'phone' => $owner->phone,
                'city' => 'Jakarta',
                'address' => 'Jakarta, Indonesia',
                'operational_hours' => '08.00 - 20.00',
                'latitude' => -6.2000000 + (mt_rand(-300, 300) / 10000),
                'longitude' => 106.8166667 + (mt_rand(-300, 300) / 10000),
                'commission_rate' => 10,
                'is_active' => true,
                'status' => 'active',
            ]);

            Company::ensureForOwner($owner);
        }

        echo 'MerchantSeeder: created stores for ' . $owners->count() . " owners\n";
    }

    /**
     * Buat logo SVG tiap merchant (inisial toko di atas gradasi sky-blue).
     */
    private function makeLogo(string $storeName): string
    {
        $slug = Str::slug($storeName) ?: Str::random(6);
        $path = 'merchant-logos/' . $slug . '.svg';

        if (!Storage::disk('public')->exists($path)) {
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
        }

        return $path;
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $base = $slug ?: Str::random(6);
        $candidate = $base;
        $i = 1;
        while (Merchant::where('slug', $candidate)->exists()) {
            $candidate = $base . '-' . ($i++);
        }
        return $candidate;
    }
}
