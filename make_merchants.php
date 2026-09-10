<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Company;
use App\Models\Merchant;
use App\Models\User;

$created = 0;

foreach (Company::orderBy('id')->get() as $company) {
    if (Merchant::where('user_id', $company->user_id)->exists()) {
        continue;
    }

    $owner = User::find($company->user_id);
    if (!$owner) {
        echo "SKIP (no owner): {$company->name}\n";
        continue;
    }

    Merchant::create([
        'user_id' => $company->user_id,
        'slug' => $company->slug,
        'name' => $company->name,
        'description' => $company->description,
        'logo' => $company->logo,
        'banner' => $company->banner,
        'phone' => $company->phone,
        'company_email' => $company->company_email,
        'website' => $company->website,
        'instagram' => $company->instagram,
        'address' => $company->address,
        'city' => $company->city,
        'pickup_address' => $company->pickup_address,
        'operational_hours' => $company->operational_hours,
        'commission_rate' => $company->commission_rate ?: rand(7, 12),
        'is_active' => $company->is_active,
        'status' => $company->status ?: 'active',
        'verified_at' => $company->verified_at ?: now(),
    ]);

    echo "CREATED merchant untuk '{$company->name}' (owner {$owner->email})\n";
    $created++;
}

echo "Dibuat {$created} merchant baru.\n";