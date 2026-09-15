<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Merchant;
use App\Models\MerchantSubscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use DatabaseTransactions;

    private function makeRoleUser(string $email, string $role, int $ownerId = null): User
    {
        return User::create([
            'name' => ucfirst($role) . ' ' . $email,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
            'owner_id' => $ownerId,
            'is_active' => true,
        ]);
    }

    private function makeOwner(string $email): User
    {
        return User::create([
            'name' => 'Owner ' . $email,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);
    }

    private function makeAdmin(string $email, User $owner): User
    {
        return User::create([
            'name' => 'Admin ' . $email,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'admin',
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);
    }

    private function makeMerchant(User $owner): Merchant
    {
        $merchant = Merchant::create([
            'user_id' => $owner->id,
            'name' => 'Toko ' . uniqid(),
            'slug' => 'toko-' . uniqid(),
            'description' => 'Toko test',
            'phone' => '081234567890',
            'address' => 'Jl. Test No. 1',
            'city' => 'Jakarta',
            'is_active' => true,
            'status' => 'active',
            'billing_plan' => 'commission',
            'subscription_fee' => 500000,
        ]);
        $owner->update(['merchant_id' => $merchant->id]);
        return $merchant;
    }

    public function test_active_subscription_allows_dashboard_access(): void
    {
        $owner = $this->makeOwner('act_sub_owner@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);

        $this->actingAs($owner)->get('/dashboard')->assertOk();
    }

    public function test_overdue_redirects_owner_to_billing_notice(): void
    {
        $owner = $this->makeOwner('overdue_owner@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);
        DB::table('merchants')->where('id', $merchant->id)->update([
            'subscription_until' => now()->subDay(),
        ]);

        $this->actingAs($owner)->get('/dashboard')->assertRedirect(route('subscriptions.notice'));
    }

    public function test_overdue_blocks_all_merchant_accounts(): void
    {
        $owner = $this->makeOwner('block_owner@example.test');
        $merchant = $this->makeMerchant($owner);
        $admin = $this->makeAdmin('block_admin@example.test', $owner);

        $driverUser = $this->makeRoleUser('block_driver@example.test', 'driver', $owner->id);
        $staff = $this->makeRoleUser('block_staff@example.test', 'staff', $owner->id);
        $employee = $this->makeRoleUser('block_employee@example.test', 'employee', $owner->id);
        $inspector = $this->makeRoleUser('block_inspector@example.test', 'inspector', $owner->id);

        $company = Company::create([
            'user_id' => $owner->id,
            'name' => 'PT Block Test',
            'slug' => 'pt-block-' . uniqid(),
            'city' => 'Jakarta',
            'is_active' => true,
            'status' => 'active',
        ]);

        Driver::create([
            'user_id' => $driverUser->id,
            'owner_id' => $owner->id,
            'company_id' => $company->id,
            'position' => 'Driver',
            'license_number' => 'SIM-BLOCK-1',
            'daily_salary' => 150000,
            'status' => 'off_duty',
            'is_active' => true,
        ]);

        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);
        DB::table('merchants')->where('id', $merchant->id)->update([
            'subscription_until' => now()->subDay(),
        ]);

        foreach ([$owner, $admin, $driverUser, $staff, $employee, $inspector] as $user) {
            $this->actingAs($user)->get('/dashboard')
                ->assertRedirect(route('subscriptions.notice'), "Role {$user->role} should be blocked");
        }
    }

    public function test_api_login_overdue_returns_403_subscription_overdue(): void
    {
        $owner = $this->makeOwner('api_overdue_owner@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);
        DB::table('merchants')->where('id', $merchant->id)->update([
            'subscription_until' => now()->subDay(),
        ]);

        $this->postJson('/api/login', [
            'email' => 'api_overdue_owner@example.test',
            'password' => 'password',
        ])->assertStatus(403)
          ->assertJson([
              'error_code' => 'subscription_overdue',
              'redirect_to' => '/api/subscriptions/due',
          ]);
    }

    public function test_commission_plan_merchant_not_affected_by_subscription(): void
    {
        $owner = $this->makeOwner('comm_owner@example.test');
        $merchant = $this->makeMerchant($owner);

        $this->actingAs($owner)->get('/dashboard')->assertOk();
        $this->assertTrue($merchant->fresh()->billing_plan === 'commission');
    }

    public function test_pivot_to_subscription_sets_grace_period_and_creates_bill(): void
    {
        $owner = $this->makeOwner('pivot_owner@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);

        $svc->pivotToSubscription($merchant, 500000);
        $merchant->refresh();

        $this->assertSame('subscription', $merchant->billing_plan);
        $this->assertNotNull($merchant->subscription_until);
        $this->assertTrue($merchant->subscription_until->isFuture());
        $this->assertDatabaseHas('merchant_subscriptions', [
            'merchant_id' => $merchant->id,
            'status' => 'pending',
        ]);
    }

    public function test_due_page_renders_and_creates_bill_when_missing(): void
    {
        $owner = $this->makeOwner('due_page_owner@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);
        DB::table('merchants')->where('id', $merchant->id)->update([
            'subscription_until' => now()->subDay(),
        ]);
        MerchantSubscription::where('merchant_id', $merchant->id)->update(['status' => 'paid']);

        $this->actingAs($owner)->get(route('subscriptions.due'))
            ->assertOk()
            ->assertSee('Tagihan');
        $this->assertDatabaseHas('merchant_subscriptions', [
            'merchant_id' => $merchant->id,
            'status' => 'pending',
        ]);
    }

    public function test_notice_page_renders_alert_and_pay_form(): void
    {
        $owner = $this->makeOwner('notice_page_owner@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);
        DB::table('merchants')->where('id', $merchant->id)->update([
            'subscription_until' => now()->subDay(),
        ]);

        $this->actingAs($owner)->get(route('subscriptions.notice'))
            ->assertOk()
            ->assertSee('AKUN DIBLOKIR — SEGERA BAYAR SUBSCRIPTION')
            ->assertSee('MENUNGGAK')
            ->assertSee(route('subscriptions.pay'))
            ->assertSee('name="proof_photo"', false);

        $this->actingAs($owner)->get(route('subscriptions.notice'))
            ->assertSee('name="method"', false);
    }

    public function test_payment_store_sets_pending_and_notifies_superadmin(): void
    {
        Storage::fake('public');
        $owner = $this->makeOwner('pay_sub_owner@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);
        $bill = $svc->currentBill($merchant);

        $this->actingAs($owner)->post(route('subscriptions.pay'), [
            'method' => 'transfer',
            'reference_number' => 'REF-SUB-1',
            'proof_photo' => UploadedFile::fake()->image('bukti.jpg', 400, 300),
            'notes' => 'Transfer via BCA',
        ])->assertRedirect();

        $bill->refresh();
        $this->assertSame('transfer', $bill->method);
        $this->assertSame('REF-SUB-1', $bill->reference_number);
        $this->assertNotNull($bill->proof_photo);

        $super = User::where('role', 'superadmin')->firstOrFail();
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $super->id,
            'type' => \App\Notifications\SubscriptionPaymentAwaitingVerification::class,
        ]);
    }

    public function test_superadmin_verify_extends_subscription_and_notifies_merchant(): void
    {
        $owner = $this->makeOwner('verify_owner@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);
        $bill = $svc->currentBill($merchant);
        $bill->update([
            'method' => 'transfer',
            'reference_number' => 'REF-VERIFY-1',
            'proof_photo' => base64_encode('bukti'),
        ]);

        $super = User::where('role', 'superadmin')->firstOrFail();
        $beforeUntil = $merchant->fresh()->subscription_until;

        $this->actingAs($super)->post(route('superadmin.subscriptions.verify', $bill))->assertRedirect();
        $merchant->refresh();

        $this->assertTrue($merchant->subscription_until->greaterThan($beforeUntil));
        $this->assertSame('paid', $bill->fresh()->status);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $owner->id,
            'type' => \App\Notifications\SubscriptionPaid::class,
        ]);
    }
}