<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'company_name', 'value' => 'MariRental', 'group' => 'general'],
            ['key' => 'company_email', 'value' => 'info@mariarental.com', 'group' => 'general'],
            ['key' => 'company_phone', 'value' => '021-1234-5678', 'group' => 'general'],
            ['key' => 'company_address', 'value' => 'Jl. Raya Utama No. 100, Jakarta Selatan, DKI Jakarta 12345', 'group' => 'general'],
            ['key' => 'company_website', 'value' => 'https://mariarental.com', 'group' => 'general'],
            ['key' => 'tax_rate', 'value' => '11', 'group' => 'finance'],
            ['key' => 'currency', 'value' => 'IDR', 'group' => 'finance'],
            ['key' => 'currency_symbol', 'value' => 'Rp', 'group' => 'finance'],
            ['key' => 'invoice_prefix', 'value' => 'INV', 'group' => 'finance'],
            ['key' => 'rental_prefix', 'value' => 'RNT', 'group' => 'finance'],
            ['key' => 'payment_methods', 'value' => 'Transfer Bank,Cash,DANA,GoPay,OVO,ShopeePay', 'group' => 'finance'],
            ['key' => 'late_fee_per_day', 'value' => '50000', 'group' => 'finance'],
            ['key' => 'commission_rate', 'value' => '10', 'group' => 'finance'],
            ['key' => 'cancellation_hours', 'value' => '24', 'group' => 'rental'],
            ['key' => 'max_rental_days', 'value' => '90', 'group' => 'rental'],
            ['key' => 'min_rental_days', 'value' => '1', 'group' => 'rental'],
            ['key' => 'driver_daily_rate', 'value' => '150000', 'group' => 'driver'],
            ['key' => 'driver_commission_rate', 'value' => '10', 'group' => 'driver'],
            ['key' => 'smtp_host', 'value' => 'smtp.gmail.com', 'group' => 'mail'],
            ['key' => 'smtp_port', 'value' => '587', 'group' => 'mail'],
            ['key' => 'smtp_username', 'value' => 'noreply@mariarental.com', 'group' => 'mail'],
            ['key' => 'smtp_encryption', 'value' => 'tls', 'group' => 'mail'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
