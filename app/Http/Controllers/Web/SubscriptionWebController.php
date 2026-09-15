<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\User;
use App\Notifications\SubscriptionPaymentAwaitingVerification;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class SubscriptionWebController extends Controller
{
    public function index()
    {
        $merchant = $this->resolveMerchant();
        abort_unless($merchant, 403);

        $subscriptions = $merchant->subscriptions()->with('verifiedBy')->get();

        return view('subscriptions.index', compact('merchant', 'subscriptions'));
    }

    public function due()
    {
        $user = Auth::user();
        $svc = app(SubscriptionService::class);
        $merchant = $svc->merchantForUser($user);

        if (!$merchant) {
            return redirect()->route('dashboard');
        }

        // Pastikan selalu ada tagihan yang bisa dibayar.
        $svc->ensureCurrentBill($merchant);

        if (!$svc->isOverdue($merchant)) {
            return redirect()->route('subscriptions.index');
        }

        $bill = $svc->currentBill($merchant);
        $subscriptions = $merchant->subscriptions()->take(5)->get();

        return view('subscriptions.due', compact('merchant', 'bill', 'subscriptions'));
    }

    /**
     * Halaman khusus notifikasi "segera bayar" untuk akun yang sedang menunggak.
     */
    public function notice()
    {
        $user = Auth::user();
        $svc = app(SubscriptionService::class);
        $merchant = $svc->merchantForUser($user);

        if (!$merchant) {
            return redirect()->route('dashboard');
        }

        $svc->ensureCurrentBill($merchant);

        if (!$svc->isOverdue($merchant)) {
            return redirect()->route('subscriptions.index');
        }

        $bill = $svc->currentBill($merchant);
        $subscriptions = $merchant->subscriptions()->orderByDesc('period_start')->take(5)->get();

        return view('subscriptions.notice', compact('merchant', 'bill', 'subscriptions'));
    }

    public function pay(Request $request)
    {
        $user = Auth::user();
        $svc = app(SubscriptionService::class);
        $merchant = $svc->merchantForUser($user);
        abort_unless($merchant, 403);

        $bill = $svc->currentBill($merchant);
        abort_unless($bill, 404);

        $validated = $request->validate([
            'method' => 'required|in:cash,transfer,ewallet,credit_card,other',
            'reference_number' => 'nullable|string|max:100',
            'proof_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $proofPath = $request->file('proof_photo')->store('payments/proof', 'public');

        $bill->update([
            'method' => $validated['method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'proof_photo' => $proofPath,
            'notes' => $validated['notes'] ?? null,
            'rejection_reason' => null,
        ]);

        Notification::send(
            User::where('role', 'superadmin')->get(),
            new SubscriptionPaymentAwaitingVerification($bill->fresh())
        );

        return back()->with('success', 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin untuk mengaktifkan kembali akses.');
    }

    private function resolveMerchant(): ?Merchant
    {
        return app(SubscriptionService::class)->merchantForUser(Auth::user());
    }
}