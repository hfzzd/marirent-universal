<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Company;
use App\Models\Merchant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SchedulerCategoryTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(string $role, ?string $categorySlug = null, ?int $ownerId = null): User
    {
        return User::create([
            'name' => ucfirst($role) . ' ' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => $role,
            'owner_id' => $ownerId,
            'category_id' => $categorySlug ? Category::where('slug', $categorySlug)->value('id') : null,
            'is_active' => true,
        ]);
    }

    private function makeMerchant(User $owner): Merchant
    {
        $merchant = Merchant::create([
            'user_id' => $owner->id,
            'slug' => 'toko-' . uniqid(),
            'name' => 'Toko ' . uniqid(),
            'is_active' => true,
            'status' => 'active',
        ]);
        Company::ensureForOwner($owner);

        return $merchant;
    }

    private function makeVehicle(User $owner, string $categorySlug): Vehicle
    {
        $catId = Category::where('slug', $categorySlug)->value('id');

        return Vehicle::create([
            'category_id' => $catId,
            'owner_id' => $owner->id,
            'name' => 'Unit ' . uniqid(),
            'slug' => 'unit-' . uniqid(),
            'brand' => 'TestBrand',
            'license_plate' => 'T ' . mt_rand(1000, 9999) . ' ' . strtoupper(substr(uniqid(), -2)),
            'daily_price' => 100000,
            'status' => 'available',
            'is_active' => true,
        ]);
    }

    private function makeBooking(User $customer, Vehicle $vehicle, string $code): Booking
    {
        return Booking::create([
            'booking_code' => $code,
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'category_id' => $vehicle->category_id,
            'rental_type' => 'daily',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(2),
            'base_price' => 200000,
            'total_price' => 200000,
            'final_price' => 200000,
            'status' => 'ongoing',
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_scheduler_page_accessible_per_role(): void
    {
        $superadmin = $this->makeUser('superadmin');
        $owner = $this->makeUser('owner', 'motor');
        $this->makeMerchant($owner);
        $admin = $this->makeUser('admin', 'motor', $owner->id);
        $user = $this->makeUser('user');
        $driver = $this->makeUser('driver');

        $this->actingAs($superadmin)->get('/scheduler')->assertOk();
        $this->actingAs($owner)->get('/scheduler')->assertOk()->assertSee('Motor');
        $this->actingAs($admin)->get('/scheduler')->assertOk();
        $this->actingAs($user)->get('/scheduler')->assertForbidden();
        $this->actingAs($driver)->get('/scheduler')->assertForbidden();
    }

    public function test_owner_events_scoped_to_own_merchant_and_category(): void
    {
        $customer = $this->makeUser('user');
        $ownerMotor = $this->makeUser('owner', 'motor');
        $this->makeMerchant($ownerMotor);
        $ownerMobil = $this->makeUser('owner', 'mobil');
        $this->makeMerchant($ownerMobil);

        $codeA = 'SCH-A-' . strtoupper(substr(uniqid(), -5));
        $codeB = 'SCH-B-' . strtoupper(substr(uniqid(), -5));
        $this->makeBooking($customer, $this->makeVehicle($ownerMotor, 'motor'), $codeA);
        $this->makeBooking($customer, $this->makeVehicle($ownerMobil, 'mobil'), $codeB);

        $params = ['start' => now()->subMonth()->toDateString(), 'end' => now()->addMonth()->toDateString()];

        $this->actingAs($ownerMotor)->getJson('/scheduler/events?' . http_build_query($params))
            ->assertOk()
            ->assertSee($codeA)
            ->assertDontSee($codeB);
    }

    public function test_replacement_menus_split_by_merchant_category(): void
    {
        $ownerMotor = $this->makeUser('owner', 'motor');
        $this->makeMerchant($ownerMotor);
        $ownerHp = $this->makeUser('owner', 'sewa-hp');
        $this->makeMerchant($ownerHp);
        $adminHp = $this->makeUser('admin', 'sewa-hp', $ownerHp->id);

        // Merchant motor: hanya Penggantian Kendaraan
        $this->actingAs($ownerMotor)->get('/replacements')->assertOk();
        $this->actingAs($ownerMotor)->get('/item-replacements')->assertForbidden();

        // Merchant HP: hanya Penggantian Unit
        $this->actingAs($ownerHp)->get('/replacements')->assertForbidden();
        $this->actingAs($ownerHp)->get('/item-replacements')->assertOk();

        // Admin toko HP boleh membuka form penggantian unit
        $this->actingAs($adminHp)->get('/item-replacements/create?type=hp')->assertOk();
    }

    public function test_vehicle_replacement_form_matches_unit_form_design(): void
    {
        $superadmin = $this->makeUser('superadmin');

        $this->actingAs($superadmin)->get('/replacements/create')
            ->assertOk()
            ->assertSee('Detail Kerusakan', false)
            ->assertSee('previewPhoto', false)
            ->assertSee('Kondisi Unit Asal', false);
    }

    public function test_quick_modals_visible_for_owner_and_admin(): void
    {
        $ownerMotor = $this->makeUser('owner', 'motor');
        $this->makeMerchant($ownerMotor);
        $adminMotor = $this->makeUser('admin', 'motor', $ownerMotor->id);

        foreach ([$ownerMotor, $adminMotor] as $account) {
            $this->actingAs($account)->get('/inspections')->assertOk()->assertSee('Inspeksi Cepat', false);
            $this->actingAs($account)->get('/trip-reports')->assertOk()->assertSee('Laporan Cepat', false);
            $this->actingAs($account)->get('/replacements')->assertOk()->assertSee('Ajukan Penggantian', false);
        }

        // Admin toko kendaraan boleh membuka form lengkap penggantian kendaraan
        $this->actingAs($adminMotor)->get('/replacements/create')->assertOk();

        $ownerHp = $this->makeUser('owner', 'sewa-hp');
        $this->makeMerchant($ownerHp);
        $this->actingAs($ownerHp)->get('/item-replacements')->assertOk()->assertSee('Penggantian Cepat', false);
    }

    public function test_merchant_bank_page_and_update(): void
    {
        $owner = $this->makeUser('owner', 'motor');
        $this->makeMerchant($owner);
        $admin = $this->makeUser('admin', 'motor', $owner->id);

        $this->actingAs($owner)->get('/owner/bank')->assertOk()->assertSee('Rekening Bank', false);
        $this->actingAs($admin)->get('/owner/bank')->assertOk();

        $this->actingAs($owner)->put('/owner/bank', [
            'bank_name' => 'Mandiri',
            'bank_account_number' => '9876543210',
            'bank_account_holder' => 'Toko Bank',
        ])->assertRedirect();

        $merchant = Merchant::where('user_id', $owner->id)->firstOrFail();
        $this->assertEquals('Mandiri', $merchant->bank_name);
        $this->assertEquals('9876543210', $merchant->bank_account_number);
        $this->assertEquals('Toko Bank', $merchant->bank_account_holder);
    }

    public function test_merchant_bank_fields_saved_to_merchant_and_company(): void
    {
        $owner = $this->makeUser('owner', 'motor');
        $this->actingAs($owner);

        $this->put('/owner/store', [
            'name' => 'Toko Rekening',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Toko Rekening',
        ])->assertRedirect();

        $merchant = Merchant::where('user_id', $owner->id)->firstOrFail();
        $company = Company::where('user_id', $owner->id)->firstOrFail();

        $this->assertEquals('BCA', $merchant->bank_name);
        $this->assertEquals('1234567890', $merchant->bank_account_number);
        $this->assertEquals('Toko Rekening', $merchant->bank_account_holder);
        $this->assertEquals('BCA', $company->bank_name);
        $this->assertEquals('1234567890', $company->bank_account_number);
    }
}
