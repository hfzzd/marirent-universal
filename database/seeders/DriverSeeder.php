<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = [
            [
                'user_id' => User::where('email', 'driver@mariarental.com')->first()->id,
                'license_number' => 'SIM-A-001234',
                'license_type' => 'A Umum',
                'license_expiry' => '2027-12-31',
                'rating' => 4.85,
                'total_trips' => 124,
                'is_available' => true,
                'daily_rate' => 150000,
                'salary_type' => 'daily',
                'status' => 'active',
            ],
            [
                'user_id' => User::where('email', 'driver2@mariarental.com')->first()->id,
                'license_number' => 'SIM-A-005678',
                'license_type' => 'A Umum',
                'license_expiry' => '2028-06-30',
                'rating' => 4.72,
                'total_trips' => 87,
                'is_available' => true,
                'daily_rate' => 140000,
                'salary_type' => 'daily',
                'status' => 'active',
            ],
        ];

        foreach ($drivers as $driver) {
            Driver::create($driver);
        }
    }
}
