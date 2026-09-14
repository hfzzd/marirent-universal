<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SubscriptionPaymentAwaitingVerification;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class SubscriptionController extends Controller
{
    public function due(Request $request)
    {
        $svc = app(SubscriptionService::class);
        $merchant = $svc->merchantForUser($request->user());

        if (!$merchant || $merchant->billing_plan !== 'subscription') {
            return response()->json([
                'success' => true,
                'data' => ['billing_plan' => $merchant?->billing_plan ?? 'none', 'subscribed' => false],
            ]);
        }

        $overdue = $svc->isOverdue($merchant);
        $bill = $svc->currentBill($merchant);

        return response()->json([
            'success' => true,
            'data' => [
                'merchant' => $merchant->name,
                'billing_plan' => 'subscription',
                'subscribed' => !$overdue,
                'overdue' => $overdue,
                'active_until' => $merchant->subscription_until?->toDateString(),
                'current_bill' => $bill ? [
                    'id' => $bill->id,
                    'amount' => (float) $bill->amount,
                    'period_start' => $bill->period_start->toDateString(),
                    'period_end' => $bill->period_end->toDateString(),
                    'status' => $bill->status,
                    'method' => $bill->method,
                    'reference_number' => $bill->reference_number,
                ] : null,
            ],
        ]);
    }

    public function pay(Request $request)
    {
        $svc = app(SubscriptionService::class);
        $merchant = $svc->merchantForUser($request->user());
        abort_unless($merchant, 403);

        $bill = $svc->currentBill($merchant);
        abort_unless($bill, 404);

        $validated = $request->validate([
            'method' => 'required|in:cash,transfer,ewallet,credit_card,other',
            'reference_number' => 'nullable|string|max:100',
            'proof_photo' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $bill->update([
            'method' => $validated['method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'proof_photo' => $validated['proof_photo'],
            'notes' => $validated['notes'] ?? null,
            'rejection_reason' => null,
        ]);

        Notification::send(
            User::where('role', 'superadmin')->get(),
            new SubscriptionPaymentAwaitingVerification($bill->fresh())
        );

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran terkirim. Menunggu verifikasi admin.',
        ]);
    }
}