<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;

/**
 * Memblokir akun di bawah merchant yang sedang menunggak subscription.
 *
 * Web : arahkan ke halaman pembayaran billing (subscriptions.due).
 * API : tolak dengan 403 (subscription_overdue) kecuali endpoint subscription.
 */
class EnsureSubscriptionPaid
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user() ?? auth()->user();

        if (!$user || !app(SubscriptionService::class)->isOverdueForUser($user)) {
            return $next($request);
        }

        $routeName = $request->route() ? $request->route()->getName() : null;

        if ($request->expectsJson()) {
            if ($routeName && str_starts_with($routeName, 'api.subscriptions.')) {
                return $next($request);
            }

            return response()->json([
                'success' => false,
                'message' => 'Akun diblokir karena tagihan subscription belum dibayar. Silakan selesaikan pembayaran billing.',
                'error_code' => 'subscription_overdue',
                'redirect_to' => '/api/subscriptions/due',
            ], 403);
        }

        if ($this->isAllowedWebRoute($routeName)) {
            return $next($request);
        }

        return redirect()->route('subscriptions.due')
            ->with('error', 'Akun Anda diblokir karena tagihan subscription belum dibayar. Silakan selesaikan pembayaran billing terlebih dahulu.');
    }

    private function isAllowedWebRoute(?string $routeName): bool
    {
        if (!$routeName) {
            return false;
        }

        return str_starts_with($routeName, 'subscriptions.')
            || str_starts_with($routeName, 'notifications.')
            || $routeName === 'logout';
    }
}