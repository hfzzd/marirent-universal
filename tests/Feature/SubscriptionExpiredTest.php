<?php

namespace Tests\Feature;

use App\Models\Merchant;
use App\Models\MerchantSubscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SubscriptionExpiredTest extends TestCase
{
    use DatabaseTransactions;

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

    private function makeMerchant(User $owner): Merchant
    {
        $merchant = Merchant::create([
            'user_id' => $owner->id,
            'name' => 'Toko ' . uniqid(),
            'slug' => 'toko-' . uniqid(),
            'description' => 'Toko expired test',
            'phone' => '085777001122',
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

    public function test_expired_pending_bill_marked_overdue(): void
    {
        $owner = $this->makeOwner('exp_mark@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);

        $bill = $svc->currentBill($merchant->fresh());
        $bill->update([
            'period_start' => now()->subMonths(2)->toDateString(),
            'period_end' => now()->subDay()->toDateString(),
            'status' => 'pending',
        ]);

        $marked = $svc->markExpiredPending();
        $this->assertGreaterThanOrEqual(1, $marked);
        $this->assertSame('overdue', $bill->fresh()->status);
    }

    public function test_verify_expired_bill_restores_access_with_single_payment(): void
    {
        $owner = $this->makeOwner('exp_restore@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);

        // Simulasi habis masa: until kemarin + bill lewat masa
        DB::table('merchants')->where('id', $merchant->id)->update([
            'subscription_until' => now()->subDay(),
        ]);
        $bill = $svc->currentBill($merchant->fresh());
        $bill->update([
            'period_start' => now()->subMonths(1)->toDateString(),
            'period_end' => now()->subDay()->toDateString(),
            'status' => 'overdue',
            'method' => 'transfer',
            'proof_photo' => 'bukti.jpg',
        ]);

        $this->assertTrue($svc->isOverdue($merchant->fresh()));

        $super = User::where('role', 'superadmin')->firstOrFail();
        $this->actingAs($super)->post(route('superadmin.subscriptions.verify', $bill))->assertRedirect();

        $merchant->refresh();
        $this->assertSame('paid', $bill->fresh()->status);
        $this->assertTrue($merchant->subscription_until->isFuture(), 'Verifikasi tagihan habis masa harus memperpanjang hingga masa depan');
        $this->assertFalse($svc->isOverdue($merchant), 'Setelah bayar 1x, merchant tidak boleh overdue lagi');

        // Akses dashboard pulih
        $this->actingAs($owner)->get('/dashboard')->assertOk();
    }

    public function test_verify_expired_is_idempotent_no_duplicate_next_bill(): void
    {
        $owner = $this->makeOwner('exp_idem@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);
        $bill = $svc->currentBill($merchant->fresh());
        $bill->update([
            'period_start' => now()->subMonths(1)->toDateString(),
            'period_end' => now()->subDay()->toDateString(),
            'status' => 'overdue',
            'method' => 'transfer',
            'proof_photo' => 'bukti.jpg',
        ]);

        $before = MerchantSubscription::where('merchant_id', $merchant->id)->count();
        $svc->verifyPayment($bill->fresh(), 1);
        $afterFirst = MerchantSubscription::where('merchant_id', $merchant->id)->count();
        $svc->verifyPayment($bill->fresh(), 1);
        $afterSecond = MerchantSubscription::where('merchant_id', $merchant->id)->count();

        $this->assertGreaterThanOrEqual($before, $afterFirst);
        $this->assertSame($afterFirst, $afterSecond, 'Verifikasi ulang tidak boleh membuat tagihan ganda');
    }

    public function test_overdue_blocks_booking_and_invoice_pages(): void
    {
        $owner = $this->makeOwner('exp_block@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);
        DB::table('merchants')->where('id', $merchant->id)->update([
            'subscription_until' => now()->subDay(),
        ]);

        $this->actingAs($owner)->get('/bookings')->assertRedirect(route('subscriptions.notice'));
        $this->actingAs($owner)->get('/invoices')->assertRedirect(route('subscriptions.notice'));
        // Halaman subscription tetap bisa diakses untuk bayar
        $this->actingAs($owner)->get(route('subscriptions.index'))->assertOk();
        $this->actingAs($owner)->get(route('subscriptions.notice'))->assertOk();
    }

    public function test_overdue_api_blocks_non_subscription_but_allows_subscription(): void
    {
        $owner = $this->makeOwner('exp_api@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);
        DB::table('merchants')->where('id', $merchant->id)->update([
            'subscription_until' => now()->subDay(),
        ]);

        $token = $owner->createToken('test')->plainTextToken;

        // Endpoint non-subscription diblokir
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/bookings')
            ->assertStatus(403)
            ->assertJson(['error_code' => 'subscription_overdue']);

        // Endpoint subscription tetap boleh
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/subscriptions/due')
            ->assertOk();
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/subscriptions/history')
            ->assertOk();
    }

    public function test_reject_overdue_keeps_overdue_status(): void
    {
        $owner = $this->makeOwner('exp_reject@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);
        $bill = $svc->currentBill($merchant->fresh());
        $bill->update([
            'period_start' => now()->subMonths(1)->toDateString(),
            'period_end' => now()->subDay()->toDateString(),
            'status' => 'overdue',
            'method' => 'transfer',
            'proof_photo' => 'bukti.jpg',
        ]);

        $svc->rejectPayment($bill->fresh(), 'Bukti tidak jelas');
        $this->assertSame('overdue', $bill->fresh()->status);
        $this->assertSame('Bukti tidak jelas', $bill->fresh()->rejection_reason);
    }

    public function test_subscription_process_command_marks_overdue_and_ensures_bill(): void
    {
        $owner = $this->makeOwner('exp_cmd@example.test');
        $merchant = $this->makeMerchant($owner);
        $svc = app(SubscriptionService::class);
        $svc->pivotToSubscription($merchant, 500000);
        $bill = $svc->currentBill($merchant->fresh());
        $bill->update([
            'period_start' => now()->subMonths(1)->toDateString(),
            'period_end' => now()->subDay()->toDateString(),
            'status' => 'pending',
        ]);

        $this->artisan('subscription:process')->assertExitCode(0);
        $this->assertSame('overdue', $bill->fresh()->status);
        $this->assertNotNull($svc->currentBill($merchant->fresh()));
    }
}
