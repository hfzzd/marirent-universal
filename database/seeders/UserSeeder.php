<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $owner1 = User::create([
            'name' => 'Owner MariRent',
            'email' => 'owner@mariarental.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'phone' => '081234567891',
            'address' => 'Jl. Bisnis No. 10, Jakarta',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $admin1 = User::create([
            'name' => 'Admin MariRent',
            'email' => 'admin@mariarental.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'owner_id' => $owner1->id,
            'phone' => '081234567899',
            'address' => 'Jl. Admin No. 11, Jakarta',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $owner2 = User::create([
            'name' => 'Owner RentCars Corp',
            'email' => 'owner2@mariarental.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'phone' => '081234568001',
            'address' => 'Jl. Armada No. 20, Bekasi',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $admin2 = User::create([
            'name' => 'Admin RentCars',
            'email' => 'admin2@mariarental.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'owner_id' => $owner2->id,
            'phone' => '081234568002',
            'address' => 'Jl. Armada No. 21, Bekasi',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@mariarental.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'phone' => '081234567890',
            'address' => 'Jl. Raya Utama No. 1, Jakarta',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Customer Test',
            'email' => 'user@mariarental.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'phone' => '081234567892',
            'address' => 'Jl. Pelanggan No. 5, Bandung',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Budi Driver',
            'email' => 'driver@mariarental.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
            'phone' => '081234567893',
            'address' => 'Jl. Supir No. 7, Surabaya',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Andi Driver',
            'email' => 'driver2@mariarental.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
            'phone' => '081234567894',
            'address' => 'Jl. Driver No. 12, Yogyakarta',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Rina Inspector',
            'email' => 'inspector@mariarental.com',
            'password' => Hash::make('password'),
            'role' => 'inspector',
            'phone' => '081234567895',
            'address' => 'Jl. Inspeksi No. 3, Bandung',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}