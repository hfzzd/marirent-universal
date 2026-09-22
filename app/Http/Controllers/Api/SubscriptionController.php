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
    private function billPayload($bill): ?array
    {
        if (!$bill) {
            return null;
        }

        return [
            'id' => $bill->id,
            'amount' => (float) $bill->amount,
            'period_start' => $bill->period_start?->toDateString(),
            'period_end' => $bill->period_end?->toDateString(),
            'status' => $bill->status,
            'method' => $bill->method,
            'reference_number' => $bill->reference_number,
            'rejection_reason' => $bill->rejection_reason,
            'has_proof' => !empty($bill->proof_photo),
            'proof_url' => $bill->proof_photo ? asset('storage/' . $bill->proof_photo) : null,
            'paid_at' => $bill->paid_at?->toDateTimeString(),
            'verified_at' => $bill->verified_at?->toDateTimeString(),
        ];
    }

    public function due(Request $request)
    {
        $svc = app(SubscriptionService::class);
        $merchant = $svc->merchantForUser($request->user());

        if (!$merchant) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung ke merchant manapun.',
            ], 403);
        }

        if ($merchant->billing_plan !== 'subscription') {
            return response()->json([
                'success' => true,
                'data' => [
                    'merchant_id' => $merchant->id,
                    'merchant' => $merchant->name,
                    'billing_plan' => $merchant->billing_plan ?? 'commission',
                    'subscribed' => true,
                    'overdue' => false,
                    'active_until' => $merchant->subscription_until?->toDateString(),
                    'current_bill' => null,
                ],
            ]);
        }

        // Samakan dengan web: pastikan selalu ada tagihan di semua perangkat.
        $svc->ensureCurrentBill($merchant->fresh());
        $merchant->refresh();

        $overdue = $svc->isOverdue($merchant);
        $bill = $svc->currentBill($merchant);

        return response()->json([
            'success' => true,
            'data' => [
                'merchant_id' => $merchant->id,
                'merchant' => $merchant->name,
                'billing_plan' => 'subscription',
                'subscription_fee' => (float) ($merchant->subscription_fee ?: $svc->defaultFee()),
                'subscribed' => !$overdue,
                'overdue' => $overdue,
                'active_until' => $merchant->subscription_until?->toDateString(),
                'current_bill' => $this->billPayload($bill),
            ],
        ]);
    }

    /**
     * Riwayat tagihan untuk aplikasi mobile / semua perangkat.
     * GET /api/subscriptions/history
     */
    public function history(Request $request)
    {
        $svc = app(SubscriptionService::class);
        $merchant = $svc->merchantForUser($request->user());

        if (!$merchant) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung ke merchant manapun.',
            ], 403);
        }

        $subscriptions = $merchant->subscriptions()
            ->reorder()
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'merchant_id' => $merchant->id,
                'merchant' => $merchant->name,
                'billing_plan' => $merchant->billing_plan,
                'active_until' => $merchant->subscription_until?->toDateString(),
                'overdue' => $svc->isOverdue($merchant),
                'subscriptions' => $subscriptions->map(fn ($s) => $this->billPayload($s))->values(),
            ],
        ]);
    }

    public function pay(Request $request)
    {
        $svc = app(SubscriptionService::class);
        $merchant = $svc->merchantForUser($request->user());

        if (!$merchant) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung ke merchant manapun.',
            ], 403);
        }

        $bill = $svc->ensureCurrentBill($merchant);

        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada tagihan yang perlu dibayar.',
            ], 404);
        }

        // Terima file upload (multipart dari HP) ATAU path/base64 string (legacy mobile).
        $isFileUpload = $request->hasFile('proof_photo');

        $validated = $request->validate([
            'method' => 'required|in:cash,transfer,ewallet,credit_card,other',
            'reference_number' => 'nullable|string|max:100',
            'proof_photo' => $isFileUpload
                ? 'required|image|mimes:jpg,jpeg,png,webp|max:5120'
                : 'required|string|max:2000',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($isFileUpload) {
            $proofPath = $request->file('proof_photo')->store('payments/proof', 'public');
        } else {
            $proofPath = $validated['proof_photo'];
        }

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

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran terkirim. Menunggu verifikasi admin.',
            'data' => ['current_bill' => $this->billPayload($bill->fresh())],
        ]);
    }
}
