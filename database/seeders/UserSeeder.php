<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@mariarental.com',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'phone' => '081234567890',
                'address' => 'Jl. Raya Utama No. 1, Jakarta',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Owner MariRent',
                'email' => 'owner@mariarental.com',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'phone' => '081234567891',
                'address' => 'Jl. Bisnis No. 10, Jakarta',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Customer Test',
                'email' => 'user@mariarental.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '081234567892',
                'address' => 'Jl. Pelanggan No. 5, Bandung',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Budi Driver',
                'email' => 'driver@mariarental.com',
                'password' => Hash::make('password'),
                'role' => 'driver',
                'phone' => '081234567893',
                'address' => 'Jl. Supir No. 7, Surabaya',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Andi Driver',
                'email' => 'driver2@mariarental.com',
                'password' => Hash::make('password'),
                'role' => 'driver',
                'phone' => '081234567894',
                'address' => 'Jl. Driver No. 12, Yogyakarta',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
