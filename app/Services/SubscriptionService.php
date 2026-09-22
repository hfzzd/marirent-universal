<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Merchant;
use App\Models\MerchantSubscription;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Logika berlangganan (subscription) merchant.
 *
 * Model billing:
 *  - commission  : komisi 10% per transaksi (perilaku default, tanpa biaya bulanan)
 *  - subscription: biaya flat bulanan (mis. Rp500.000/bulan), tanpa potongan komisi
 *
 * Status aktif ditentukan oleh kolom `merchant.subscription_until`.
 * Jika lebih kecil dari sekarang => akun merchant (semua role di bawahnya) diblokir.
 */
class SubscriptionService
{
    public function defaultFee(): float
    {
        $fee = Setting::get('subscription_fee', 500000);

        return $fee !== null && (float) $fee > 0 ? (float) $fee : 500000;
    }

    /**
     * Profil merchant yang menaungi sebuah akun (owner/admin/driver/staff/inspector).
     */
    public function merchantForUser(?User $user): ?Merchant
    {
        if (!$user) {
            return null;
        }

        $ownerId = $user->merchantIdForIsolation()
            ?? ($user->isMerchantStaff() ? $user->merchantId() : null);

        if (!$ownerId) {
            return null;
        }

        return Merchant::where('user_id', (int) $ownerId)->first();
    }

    public function isOverdue(Merchant $merchant): bool
    {
        return $merchant->billing_plan === 'subscription'
            && ($merchant->subscription_until === null || $merchant->subscription_until->lt(now()));
    }

    /**
     * Apakah akun ini (merchant atau non-merchant yang berada di bawah merchant) sedang diblokir.
     * Superadmin & user biasa tidak pernah diblokir.
     */
    public function isOverdueForUser(?User $user): bool
    {
        if (!$user || $user->isSuperAdmin() || $user->isUser()) {
            return false;
        }

        if (!$user->isMerchantStaff()
            && !in_array($user->role, ['driver', 'staff', 'employee', 'inspector'], true)) {
            return false;
        }

        $merchant = $this->merchantForUser($user);

        return $merchant !== null && $this->isOverdue($merchant);
    }

    /**
     * Id seluruh akun yang berada di bawah merchant (owner + admin + staff/driver/inspector/employee).
     */
    public function getAccountUserIds(Merchant $merchant): array
    {
        $ownerId = (int) $merchant->user_id;

        $ids = collect([$ownerId]);
        $ids = $ids->merge(User::where('owner_id', $ownerId)->pluck('id'));
        $ids = $ids->merge(
            Driver::withoutGlobalScopes()->where('owner_id', $ownerId)->pluck('user_id')
        );

        return $ids->filter()->unique()->values()->all();
    }

    /**
     * Tagihan berlangganan yang harus dibayar sekarang (unpaid paling awal).
     */
    public function currentBill(Merchant $merchant): ?MerchantSubscription
    {
        return $merchant->subscriptions()
            ->reorder()
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('period_start')
            ->orderBy('id')
            ->first();
    }

    /**
     * Pastikan merchant subscription selalu punya tagihan yang harus dibayar.
     * Jika belum ada tagihan unpaid, buat tagihan baru.
     */
    public function ensureCurrentBill(Merchant $merchant): ?MerchantSubscription
    {
        if ($merchant->billing_plan !== 'subscription') {
            return null;
        }

        if ($bill = $this->currentBill($merchant)) {
            return $bill;
        }

        return $this->generateNextBill($merchant);
    }

    public function generateNextBill(Merchant $merchant, ?Carbon $periodStart = null): MerchantSubscription
    {
        $start = $periodStart ?? now()->startOfDay();
        $fee = (float) ($merchant->subscription_fee ?: $this->defaultFee());

        return MerchantSubscription::create([
            'merchant_id' => $merchant->id,
            'period_start' => $start->toDateString(),
            'period_end' => $start->copy()->addMonth()->toDateString(),
            'amount' => $fee,
            'status' => 'pending',
        ]);
    }

    /**
     * Aktifkan plan subscription untuk merchant: set plan, fee, buat tagihan pertama.
     * Merchant mendapat waktu satu bulan untuk membayar tagihan pertama.
     * Idempoten: jika sudah ada tagihan unpaid, pakai ulang agar tidak duplikat.
     */
    public function pivotToSubscription(Merchant $merchant, float $fee): MerchantSubscription
    {
        $fee = $fee > 0 ? $fee : $this->defaultFee();

        $merchant->update([
            'billing_plan' => 'subscription',
            'subscription_fee' => $fee,
            'subscription_until' => now()->addMonth(),
        ]);

        $fresh = $merchant->fresh();

        if ($existing = $this->currentBill($fresh)) {
            return $existing;
        }

        return $this->generateNextBill($fresh);
    }

    /**
     * Pindah kembali ke plan komisi per transaksi.
     */
    public function pivotToCommission(Merchant $merchant): void
    {
        $merchant->update([
            'billing_plan' => 'commission',
            'subscription_until' => null,
        ]);
    }

    /**
     * Verifikasi pembayaran tagihan -> perpanjang akses merchant & buat tagihan berikutnya.
     * Idempoten: verifikasi ulang tagihan lunas tidak membuat tagihan ganda.
     */
    public function verifyPayment(MerchantSubscription $subscription, ?int $verifiedBy = null): Merchant
    {
        $merchant = $subscription->merchant;
        $alreadyPaid = $subscription->isPaid();

        DB::transaction(function () use ($subscription, $verifiedBy, $merchant, $alreadyPaid) {
            if (!$alreadyPaid) {
                $subscription->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'verified_by' => $verifiedBy,
                    'verified_at' => now(),
                ]);
            }

            $subscription->refresh();
            $periodEnd = $subscription->period_end ? Carbon::parse($subscription->period_end)->endOfDay() : now()->endOfDay();

            // Jika tagihan sudah lewat masa (habis masa), beri akses 1 bulan dari sekarang
            // agar 1x bayar langsung memulihkan akses. Jika belum lewat, ikuti period_end.
            if ($periodEnd->isPast()) {
                $newUntil = now()->addMonth()->endOfDay();
            } else {
                $newUntil = $periodEnd->copy();
            }

            if (!$merchant->subscription_until || $newUntil->gt($merchant->subscription_until)) {
                $merchant->update(['subscription_until' => $newUntil]);
            }

            // Tagihan berikutnya: kontinyu dari periode saat ini.
            // Jika habis masa (period_end lewat), mulai dari masa aktif baru
            // agar tidak menumpuk arrears yang sudah lewat.
            $previousStart = $subscription->period_start ? Carbon::parse($subscription->period_start) : now();
            $candidateNext = $previousStart->copy()->addMonth()->startOfDay();
            $freshUntil = $merchant->fresh()->subscription_until
                ? Carbon::parse($merchant->fresh()->subscription_until)->startOfDay()
                : null;

            if ($periodEnd->isPast() && $freshUntil) {
                $nextStart = $freshUntil->copy()->startOfDay();
            } else {
                $nextStart = $candidateNext;
            }

            // Cegah tagihan ganda: abaikan invoice yang sedang diverifikasi,
            // periode bersinggungan di batas tanggal dianggap kontinyu (bukan overlap).
            $nextEnd = $nextStart->copy()->addMonth();
            $overlapExists = MerchantSubscription::where('merchant_id', $merchant->id)
                ->where('id', '!=', $subscription->id)
                ->where('period_start', '<', $nextEnd->toDateString())
                ->where('period_end', '>', $nextStart->toDateString())
                ->exists();

            if (!$overlapExists) {
                $this->generateNextBill($merchant, $nextStart);
            }
        });

        return $merchant->fresh();
    }

    public function rejectPayment(MerchantSubscription $subscription, string $reason): void
    {
        $isExpired = $subscription->period_end
            ? Carbon::parse($subscription->period_end)->startOfDay()->lt(now()->startOfDay())
            : false;

        $subscription->update([
            'status' => $isExpired ? 'overdue' : 'pending',
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Tandai tagihan yang sudah lewat masa berlakunya sebagai overdue.
     */
    public function markExpiredPending(): int
    {
        return MerchantSubscription::where('status', 'pending')
            ->where('period_end', '<', today()->toDateString())
            ->update(['status' => 'overdue']);
    }
}