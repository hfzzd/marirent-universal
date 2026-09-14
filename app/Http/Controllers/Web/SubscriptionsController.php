<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MerchantSubscription;
use App\Models\User;
use App\Notifications\SubscriptionPaymentAwaitingVerification;
use App\Notifications\SubscriptionPaid;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class SubscriptionsController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $svc = app(SubscriptionService::class);

        $merchants = Merchant::with('owner')->orderBy('created_at', 'desc')->get();

        if ($status === 'commission') {
            $merchants = $merchants->filter(fn ($m) => $m->billing_plan === 'commission');
        } elseif ($status === 'subscription') {
            $merchants = $merchants->filter(fn ($m) => $m->billing_plan === 'subscription');
        } elseif ($status === 'active') {
            $merchants = $merchants->filter(fn ($m) => $m->billing_plan === 'subscription' && !$svc->isOverdue($m));
        } elseif ($status === 'overdue') {
            $merchants = $merchants->filter(fn ($m) => $m->billing_plan === 'subscription' && $svc->isOverdue($m));
        }

        $rows = $merchants->map(function (Merchant $m) use ($svc) {
            $bill = $svc->currentBill($m);

            return (object) [
                'merchant' => $m,
                'owner' => $m->owner,
                'billing_plan' => $m->billing_plan,
                'fee' => (float) $m->subscription_fee,
                'subscription_until' => $m->subscription_until,
                'overdue' => $m->billing_plan === 'subscription' && $svc->isOverdue($m),
                'current_bill' => $bill,
            ];
        })->values();

        $counts = (object) [
            'all' => Merchant::count(),
            'commission' => Merchant::where('billing_plan', 'commission')->count(),
            'subscription' => Merchant::where('billing_plan', 'subscription')->count(),
            'overdue' => Merchant::where('billing_plan', 'subscription')
                ->where(fn ($q) => $q->whereNull('subscription_until')->orWhere('subscription_until', '<', now()))
                ->count(),
            'pending_verify' => MerchantSubscription::whereIn('status', ['pending', 'overdue'])
                ->whereNotNull('proof_photo')
                ->count(),
        ];

        return view('superadmin.subscriptions.index', compact('rows', 'status', 'counts'));
    }

    public function show(Merchant $merchant)
    {
        $svc = app(SubscriptionService::class);
        $merchant->load(['owner', 'subscriptions.verifiedBy', 'subscriptions.merchant']);
        $subscriptions = $merchant->subscriptions;

        return view('superadmin.subscriptions.show', compact('merchant', 'subscriptions', 'svc'));
    }

    public function storePlan(Request $request, Merchant $merchant)
    {
        $validated = $request->validate([
            'billing_plan' => 'required|in:commission,subscription',
            'subscription_fee' => 'nullable|numeric|min:0',
        ]);

        $svc = app(SubscriptionService::class);

        if ($validated['billing_plan'] === 'subscription') {
            $svc->pivotToSubscription($merchant, (float) ($validated['subscription_fee'] ?? 0));

            return back()->with('success', sprintf(
                'Toko "%s" kini memakai plan SUBSCRIPTION (Rp %s/bulan). Tagihan pertama dibuat.',
                $merchant->name,
                number_format((float) $merchant->subscription_fee, 0, ',', '.')
            ));
        }

        $svc->pivotToCommission($merchant);

        return back()->with('success', sprintf(
            'Toko "%s" kembali memakai plan KOMISI per transaksi.',
            $merchant->name
        ));
    }

    public function storeBill(Request $request, Merchant $merchant)
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        MerchantSubscription::create([
            'merchant_id' => $merchant->id,
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'amount' => $validated['amount'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Tagihan manual berhasil dibuat untuk ' . $merchant->name . '.');
    }

    public function verify(MerchantSubscription $subscription)
    {
        if ($subscription->isPaid()) {
            return back()->with('error', 'Tagihan ini sudah diverifikasi.');
        }

        $merchant = app(SubscriptionService::class)->verifyPayment($subscription, Auth::id());

        $userIds = app(SubscriptionService::class)->getAccountUserIds($merchant);
        Notification::send(User::whereIn('id', $userIds)->get(), new SubscriptionPaid($subscription->fresh()));

        return back()->with('success', 'Pembayaran subscription ' . $merchant->name . ' diverifikasi. Akses aktif hingga ' . $merchant->subscription_until->format('d M Y'));
    }

    public function reject(Request $request, MerchantSubscription $subscription)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        app(SubscriptionService::class)->rejectPayment($subscription, $validated['rejection_reason']);

        return back()->with('success', 'Pembayaran ditolak. Merchant dapat mengirim ulang bukti.');
    }
}