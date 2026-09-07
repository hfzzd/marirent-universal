<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Company;
use App\Models\Merchant;
use App\Models\User;
use App\Models\Category;

echo "=== KATEGORI ==-\n";
foreach (Category::orderBy('id')->get() as $c) {
    $companies = Company::whereIn('user_id', User::where('category_id', $c->id)->pluck('id'))->count();
    echo "  #{$c->id} {$c->slug}: companies={$companies}\n";
}

echo "\n=== COMPANY TANPA OWNER / TANPA KATEGORI ===\n";
foreach (Company::get() as $company) {
    $owner = User::find($company->user_id);
    if (!$owner) {
        echo "  [NO-OWNER] company #{$company->id} '{$company->name}' user_id={$company->user_id}\n";
        continue;
    }
    if (!$owner->category_id && !$company->category_id) {
        echo "  [NO-CATEGORY] '{$company->name}' owner: {$owner->name} ({$owner->email}) role={$owner->role}\n";
    }
}

echo "\n=== MERCHANTS TANPA COMPANY / TANPA OWNER ===\n";
foreach (Merchant::get() as $m) {
    $hasCompany = Company::where('user_id', $m->user_id)->exists();
    $owner = User::find($m->user_id);
    if (!$hasCompany || !$owner) {
        echo "  merchant #{$m->id} '{$m->name}' user_id={$m->user_id} owner=" . ($owner ? $owner->email . ' role=' . $owner->role : 'MISSING') . "\n";
    }
}

echo "\n=== CATEGORIES TANPA AKUN OWNER (katalog tanpa company) ===\n";
foreach (Category::orderBy('id')->get() as $c) {
    $owners = User::where('category_id', $c->id)->where('role', 'owner')->count();
    $admins = User::where('category_id', $c->id)->where('role', 'admin')->count();
    $merchants = Merchant::whereIn('user_id', User::where('category_id', $c->id)->pluck('id'))->count();
    echo "  {$c->slug}: owners={$owners} admins={$admins} merchants={$merchants}\n";
}

echo "\n=== MERCHANT vs COMPANY vs OWNER ===\n";
foreach (Merchant::orderBy('id')->get() as $m) {
    $c = Company::where('user_id', $m->user_id)->first();
    $u = User::find($m->user_id);
    echo "  M#{$m->id} '{$m->name}' owner=" . ($u ? $u->email . ' (' . $u->role . ')' : 'MISSING') . " company=" . ($c ? 'Y' : 'NO') . "\n";
}

echo "\n=== ORPHAN PRODUCTS (owner tidak punya company) ===\n";
$tables = [
    'vehicles' => ['name', 'owner_id'],
    'phones' => ['name', 'owner_id'],
    'cameras' => ['name', 'owner_id'],
    'playstations' => ['name', 'owner_id'],
    'camping_equipments' => ['name', 'owner_id'],
    'drones' => ['name', 'owner_id'],
    'musical_instruments' => ['name', 'owner_id'],
];
$ownerCounts = [];
foreach ($tables as $t => $cols) {
    $rows = DB::table($t)->where('is_active', true)->get();
    foreach ($rows as $r) {
        $hasMerchant = Merchant::where('user_id', $r->owner_id)->exists();
        if (!$hasMerchant) {
            $ownerCounts[$r->owner_id] = ($ownerCounts[$r->owner_id] ?? 0) + 1;
        }
    }
}
foreach ($ownerCounts as $oid => $cnt) {
    $u = User::find($oid);
    $m = Merchant::where('user_id', $oid)->exists();
    $c = Company::where('user_id', $oid)->exists();
    echo "  owner_id={$oid} email=" . ($u ? $u->email : 'MISSING') . " role=" . ($u ? $u->role : '-') . " products={$cnt} merchant=" . ($m ? 'Y' : 'NO') . " company=" . ($c ? 'Y' : 'NO') . "\n";
}

echo "\n=== COMPANY TANPA MERCHANT (ada company tapi tak tampil di katalog) ===\n";
foreach (Company::orderBy('id')->get() as $c) {
    if (!Merchant::where('user_id', $c->user_id)->exists()) {
        $u = User::find($c->user_id);
        echo "  C#{$c->id} '{$c->name}' owner=" . ($u ? $u->email : 'MISSING') . "\n";
    }
}

echo "\n=== OWNER TANPA MERCHANT TAPI ADA PRODUCT ===\n";
foreach (User::where('role', 'owner')->get() as $u) {
    $hasMerchant = Merchant::where('user_id', $u->id)->exists();
    $hasCompany = Company::where('user_id', $u->id)->exists();
    if (!$hasMerchant) {
        echo "  O#{$u->id} {$u->email} company=" . ($hasCompany ? 'Y' : 'NO') . " category_id=" . ($u->category_id ?? '-') . "\n";
    }
}