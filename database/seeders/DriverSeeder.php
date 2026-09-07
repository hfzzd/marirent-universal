<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureOwnerCompanies();

        $companies = Company::orderBy('name')->get();

        $templates = [
            ['position' => 'Driver', 'license_type' => 'A', 'trip_salary' => 50000],
            ['position' => 'Driver', 'license_type' => 'B1', 'trip_salary' => 75000],
            ['position' => 'Karyawan', 'license_type' => null, 'trip_salary' => 0],
        ];

        $created = 0;
        foreach ($companies as $company) {
            $ownerId = (int) $company->user_id;
            $slugBase = $company->slug ?: ('company-' . $ownerId);

            foreach ($templates as $i => $tpl) {
                $suffix = $i + 1;
                $email = "staff-{$slugBase}-{$suffix}@marirent.com";

                if (User::where('email', $email)->exists()) {
                    continue;
                }

                $isDriver = $tpl['position'] === 'Driver';

                $user = User::create([
                    'name' => $this->randomName($isDriver ? 'Driver' : 'Karyawan', $company->city),
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'driver',
                    'owner_id' => $ownerId,
                    'phone' => '08' . mt_rand(1000000000, 9999999999),
                    'address' => $company->address,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                Driver::create([
                    'user_id' => $user->id,
                    'owner_id' => $ownerId,
                    'company_id' => $company->id,
                    'position' => $tpl['position'],
                    'license_number' => $isDriver ? $this->uniqueLicense() : null,
                    'license_type' => $tpl['license_type'],
                    'license_expiry' => $isDriver ? now()->addYears(2)->format('Y-m-d') : null,
                    'daily_salary' => $isDriver ? mt_rand(12, 18) * 10000 : mt_rand(7, 12) * 10000,
                    'trip_salary' => $tpl['trip_salary'],
                    'status' => 'off_duty',
                    'is_active' => true,
                ]);

                $created++;
            }

            echo "DriverSeeder: {$company->name} (city: " . ($company->city ?? '-') . ") seeded\n";
        }

        // Backfill company_id untuk driver lama yang belum punya company
        $backfilled = 0;
        Driver::withTrashed()->whereNull('company_id')->each(function (Driver $driver) use (&$backfilled) {
            $company = Company::where('user_id', $driver->owner_id)->first();
            if ($company) {
                $driver->update(['company_id' => $company->id]);
                $backfilled++;
            }
        });

        echo "DriverSeeder selesai: {$created} akun baru, {$backfilled} driver lama di-backfill company.\n";
    }

    private function ensureOwnerCompanies(): void
    {
        foreach (User::where('role', 'owner')->get() as $owner) {
            if (Company::where('user_id', $owner->id)->exists()) {
                continue;
            }

            $merchant = Merchant::where('user_id', $owner->id)->first();
            $data = $merchant
                ? $merchant->only([
                    'slug', 'name', 'description', 'logo', 'banner', 'phone', 'company_email',
                    'website', 'instagram', 'address', 'city', 'pickup_address', 'operational_hours',
                    'commission_rate', 'is_active', 'status', 'verified_at',
                ])
                : ['name' => 'Company ' . $owner->name, 'city' => null];

            Company::create(array_merge(['user_id' => $owner->id], $data));
        }
    }

    private function uniqueLicense(): string
    {
        do {
            $license = strtoupper(substr(md5(uniqid()), 0, 8)); // placeholder unik
        } while (Driver::where('license_number', $license)->exists());
        return $license;
    }

    private function randomName(string $position, ?string $city): string
    {
        $first = ['Budi', 'Andi', 'Rudi', 'Dedi', 'Agus', 'Joko', 'Eko', 'Yudi', 'Fajar', 'Dimas', 'Rizky', 'Bayu'];
        $last = ['Santoso', 'Pratama', 'Wijaya', 'Nugroho', 'Saputra', 'Hidayat', 'Ramadhan', 'Kurniawan', 'Setiawan', 'Maulana'];
        $name = $first[array_rand($first)] . ' ' . $last[array_rand($last)];
        if ($city) {
            $name .= ' (' . $position . ' ' . $city . ')';
        }
        return $name;
    }
}