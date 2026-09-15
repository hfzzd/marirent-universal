<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Merchant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DuplicateIdentityTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(?string $phone = null, string $role = 'user'): User
    {
        return User::create([
            'name' => 'User ' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'phone' => $phone,
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_web_register_rejects_duplicate_phone(): void
    {
        $this->makeUser('081234567101');

        $this->from('/register')->post('/register', [
            'name' => 'Dup Phone',
            'email' => 'dup-ph@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '+6281234567101',
        ])->assertSessionHasErrors('phone');
    }

    public function test_web_register_merchant_rejects_duplicate_phone(): void
    {
        $this->makeUser('081234567102');

        $this->from('/register-merchant')->post('/register-merchant', [
            'name' => 'Dup Merchant',
            'email' => 'dup-m@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '081234567102',
            'store_name' => 'Toko Duplikat',
        ])->assertSessionHasErrors('phone');
    }

    public function test_api_register_rejects_duplicate_phone(): void
    {
        $this->makeUser('081234567103');

        $this->postJson('/api/register', [
            'name' => 'Dup Api',
            'email' => 'dup-api@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '0812-3456-7103',
        ])->assertStatus(422)->assertJsonValidationErrors('phone');
    }

    public function test_superadmin_merchant_store_rejects_duplicate_phone(): void
    {
        $super = User::where('role', 'superadmin')->firstOrFail();
        $this->makeUser('081234567104');

        $this->actingAs($super)->post('/superadmin/merchants', [
            'name' => 'Owner Dup',
            'email' => 'dup-owner@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '6281234567104',
            'store_name' => 'Toko Dup Owner',
        ])->assertSessionHasErrors('phone');
    }

    public function test_owner_store_admin_rejects_duplicate_phone(): void
    {
        $owner = $this->makeUser('081234567105', 'owner');
        Company::ensureForOwner($owner);
        $this->makeUser('081234567106');

        $this->actingAs($owner)->post('/owner/store/admins', [
            'name' => 'Admin Dup',
            'email' => 'dup-admin@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '081234567106',
        ])->assertSessionHasErrors('phone');
    }

    public function test_driver_store_rejects_duplicate_phone_and_license(): void
    {
        $owner = $this->makeUser('081234567107', 'owner');
        Company::ensureForOwner($owner);

        $this->actingAs($owner)->post('/drivers', [
            'name' => 'Driver Satu',
            'email' => 'driver-1@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '081234567108',
            'license_number' => 'LIC-11111',
            'daily_salary' => 100000,
        ])->assertRedirect();

        $this->actingAs($owner)->post('/drivers', [
            'name' => 'Driver Dua',
            'email' => 'driver-2@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '081234567108',
            'license_number' => 'LIC-22222',
            'daily_salary' => 100000,
        ])->assertSessionHasErrors('phone');

        $this->actingAs($owner)->post('/drivers', [
            'name' => 'Driver Tiga',
            'email' => 'driver-3@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '081234567109',
            'license_number' => 'LIC-11111',
            'daily_salary' => 100000,
        ])->assertSessionHasErrors('license_number');
    }

    public function test_driver_update_allows_own_phone_but_rejects_others(): void
    {
        $owner = $this->makeUser('081234567110', 'owner');
        Company::ensureForOwner($owner);

        $this->actingAs($owner)->post('/drivers', [
            'name' => 'Driver U',
            'email' => 'driver-u@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '081234567111',
            'daily_salary' => 100000,
        ])->assertRedirect();

        $driver = Driver::where('owner_id', $owner->id)->first();

        $this->actingAs($owner)->put('/drivers/' . $driver->id, [
            'name' => 'Driver U',
            'phone' => '081234567111',
            'daily_salary' => 110000,
        ])->assertSessionHasNoErrors();

        $this->makeUser('081234567112');

        $this->actingAs($owner)->put('/drivers/' . $driver->id, [
            'name' => 'Driver U',
            'phone' => '081234567112',
            'daily_salary' => 110000,
        ])->assertSessionHasErrors('phone');
    }

    public function test_api_driver_store_rejects_duplicate_license(): void
    {
        $owner = $this->makeUser('081234567113', 'owner');
        Company::ensureForOwner($owner);

        $userA = $this->makeUser('081234567114');
        $userB = $this->makeUser('081234567115');
        $this->actingAs($owner)->postJson('/api/drivers', [
            'user_id' => $userA->id,
            'license_number' => 'LIC-API-1',
            'daily_salary' => 100000,
        ])->assertStatus(201);

        $this->actingAs($owner)->postJson('/api/drivers', [
            'user_id' => $userB->id,
            'license_number' => 'LIC-API-1',
            'daily_salary' => 100000,
        ])->assertStatus(422)->assertJsonValidationErrors('license_number');

        $this->actingAs($owner)->postJson('/api/drivers', [
            'user_id' => $userA->id,
            'license_number' => 'LIC-API-2',
            'daily_salary' => 100000,
        ])->assertStatus(422)->assertJsonValidationErrors('user_id');
    }

    public function test_profile_update_rejects_phone_of_another_user(): void
    {
        $user = $this->makeUser('081234567116');
        $other = $this->makeUser('081234567117');

        $this->actingAs($user)->put('/dashboard/profile', [
            'name' => $user->name,
            'phone' => $other->phone,
        ])->assertSessionHasErrors('phone');
    }

    public function test_api_profile_update_rejects_phone_of_another_user(): void
    {
        $user = $this->makeUser('081234567118');
        $other = $this->makeUser('081234567119');

        $this->actingAs($user, 'sanctum')->putJson('/api/profile', [
            'name' => $user->name,
            'phone' => '6281234567119',
        ])->assertStatus(422)->assertJsonValidationErrors('phone');
    }

    public function test_demo_request_rejects_duplicate_email_and_phone(): void
    {
        $this->post('/jadwal-demo', [
            'name' => 'Demo A',
            'email' => 'demo-a@example.test',
            'phone' => '081234567120',
            'business_name' => 'Bisnis A',
            'preferred_date' => now()->addDays(2)->format('Y-m-d'),
            'preferred_time' => '10:00',
        ])->assertSessionHasNoErrors();

        $this->post('/jadwal-demo', [
            'name' => 'Demo B',
            'email' => 'demo-a@example.test',
            'phone' => '081234567121',
            'business_name' => 'Bisnis B',
            'preferred_date' => now()->addDays(2)->format('Y-m-d'),
            'preferred_time' => '10:00',
        ])->assertSessionHasErrors('email');

        $this->post('/jadwal-demo', [
            'name' => 'Demo C',
            'email' => 'demo-c@example.test',
            'phone' => '081234567120',
            'business_name' => 'Bisnis C',
            'preferred_date' => now()->addDays(2)->format('Y-m-d'),
            'preferred_time' => '10:00',
        ])->assertSessionHasErrors('phone');
    }

    public function test_phone_is_normalized_to_e164_on_save(): void
    {
        $user = $this->makeUser('0812-3456-7121');
        $this->assertSame('6281234567121', $user->fresh()->phone);

        $user2 = User::create([
            'name' => 'Normalized E164',
            'email' => uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'phone' => '+62 812-3456-7122',
            'role' => 'user',
        ]);
        $this->assertSame('6281234567122', $user2->phone);
    }

    public function test_raw_duplicate_phone_insert_throws(): void
    {
        $this->makeUser('081234567123');

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('users')->insert([
            'name' => 'Raw Dup',
            'email' => 'raw-dup@example.test',
            'password' => Hash::make('password'),
            'phone' => '6281234567123',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_walkin_booking_reuses_existing_user_by_phone(): void
    {
        $super = User::where('role', 'superadmin')->firstOrFail();
        $vehicle = Vehicle::where('status', 'available')->where('is_active', true)->firstOrFail();
        $existing = $this->makeUser('081234567124');

        $before = User::where('phone', '6281234567124')->count();

        $this->actingAs($super)->post('/bookings/manual-store', [
            'customer_mode' => 'new',
            'guest_name' => 'Walk-in Reuse',
            'guest_phone' => '0812-3456-7124',
            'item_kind' => $vehicle->category->slug === 'motor' ? 'motor' : 'mobil',
            'item_id' => $vehicle->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(2)->format('Y-m-d H:i'),
            'payment_status' => 'unpaid',
            'payment_plan' => 'dp50',
        ])->assertRedirect();

        $this->assertSame($before, User::where('phone', '6281234567124')->count());
        $this->assertTrue(
            \App\Models\Booking::where('user_id', $existing->id)->latest('id')->first() instanceof \App\Models\Booking
        );
    }

    public function test_merchant_and_company_unique_phone_indexes_exist(): void
    {
        try {
            User::where('phone', '081234567125')->delete();
        } catch (\Throwable $e) {
            //
        }

        $owner = $this->makeUser('081234567125', 'owner');
        Merchant::create([
            'user_id' => $owner->id,
            'slug' => 'merch-' . uniqid(),
            'name' => $owner->name,
            'phone' => '081234567125',
            'commission_rate' => 10,
            'is_active' => true,
            'status' => 'active',
        ]);
        $merchant = Merchant::where('user_id', $owner->id)->first();
        $this->assertSame('6281234567125', $merchant->fresh()->phone);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('merchants')->insert([
            'user_id' => $owner->id,
            'slug' => 'merch-dup-' . uniqid(),
            'name' => 'Merchant Dup Phone',
            'phone' => '6281234567125',
            'commission_rate' => 10,
            'is_active' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}