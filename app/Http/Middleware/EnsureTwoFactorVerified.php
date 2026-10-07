<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pastikan user yang mengaktifkan 2FA sudah melewati verifikasi
 * pada sesi ini sebelum mengakses area terproteksi.
 *
 * Tanpa ini, user bisa melewati halaman verifikasi dengan langsung
 * membuka URL dashboard setelah login (session 2fa_pending saja
 * tidak cukup menghentikan akses).
 */
class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        // Halaman verifikasi 2FA & logout harus selalu bisa diakses
        // (jika tidak, terjadi redirect loop).
        if ($request->routeIs('2fa.verify') || $request->is('dashboard/2fa/verify')) || $request->routeIs('logout')) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && $user->two_factor_confirmed_at && !$request->session()->get('2fa_verified')) {
            return redirect()->route('2fa.verify')
                ->with('error', 'Verifikasi kode 2FA terlebih dahulu.');
        }

        return $next($request);
    }
}
