<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Merchant;
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
                'phone' => $owner->phone,
                'city' => null,
                'operational_hours' => '08.00 - 20.00',
                'commission_rate' => 10,
                'is_active' => true,
                'status' => 'active',
            ]);

            Company::ensureForOwner($owner);
        }

        echo 'MerchantSeeder: created stores for ' . $owners->count() . " owners\n";
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
