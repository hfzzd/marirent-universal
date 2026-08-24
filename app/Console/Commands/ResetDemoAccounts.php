<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetDemoAccounts extends Command
{
    protected $signature = 'demo:reset {--password=password : Password baru untuk semua akun demo}';

    protected $description = 'Reset password dan aktifkan semua akun demo';

    public function handle(): void
    {
        $password = $this->option('password');

        $accounts = [
            'admin@marirent.com' => 'superadmin',
            'owner@marirent.com' => 'owner',
            'outdoor.owner@marirent.com' => 'owner',
            'user@marirent.com' => 'user',
            'driver@marirent.com' => 'driver',
        ];

        $this->info('Mereset akun demo...');
        $this->newLine();

        foreach ($accounts as $email => $role) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update([
                    'password' => Hash::make($password),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
                $this->line("  ✓ <info>{$email}</info> ({$role}) — password direset");
            } else {
                $this->line("  ✗ <comment>{$email}</comment> tidak ditemukan di database");
            }
        }

        $this->newLine();
        $this->info('Selesai! Semua akun demo dapat digunakan dengan password: <comment>'.$password.'</comment>');
        $this->newLine();
        $this->table(
            ['Role', 'Email', 'Password'],
            collect($accounts)->map(fn ($role, $email) => [$role, $email, $password])->values()->toArray()
        );
    }
}
