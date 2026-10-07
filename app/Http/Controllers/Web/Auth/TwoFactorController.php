<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Google2FALaravel\Facades\Google2FA as Google2FAFacade;

class TwoFactorController extends Controller
{
    public function showSetup()
    {
        $user = Auth::user();

        if ($user->two_factor_confirmed_at) {
            return redirect()->route('dashboard')->with('info', '2FA sudah diaktifkan.');
        }

        $secret = $user->two_factor_secret ?: Google2FAFacade::generateSecretKey();
        $qrCodeUrl = Google2FAFacade::getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('auth.2fa.setup', compact('secret', 'qrCodeUrl'));
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        $secret = $user->two_factor_secret;
        if (!$secret) {
            return back()->withErrors(['code' => 'Setup 2FA tidak lengkap. Silakan ulangi.']);
        }

        $valid = Google2FAFacade::verifyKey($secret, $request->code);
        if (!$valid) {
            return back()->withErrors(['code' => 'Kode 2FA tidak valid.']);
        }

        $recoveryCodes = collect(range(1, 8))->map(fn () => strtoupper(bin2hex(random_bytes(4))))->implode(',');

        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', '2FA berhasil diaktifkan.')
            ->with('recovery_codes', explode(',', $recoveryCodes));
    }

    public function showVerify()
    {
        $user = Auth::user();
        
        if (!$user->two_factor_confirmed_at) {
            return redirect()->route('dashboard');
        }

        if (!session('2fa_pending')) {
            return redirect()->route('dashboard');
        }

        return view('auth.2fa.verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if (!session('2fa_pending')) {
            return redirect()->route('dashboard');
        }

        $valid = Google2FAFacade::verifyKey($user->two_factor_secret, $request->code);

        if (!$valid && $user->two_factor_recovery_codes) {
            $codes = explode(',', $user->two_factor_recovery_codes);
            if (in_array(strtoupper($request->code), $codes, true)) {
                $valid = true;
                $codes = array_diff($codes, [strtoupper($request->code)]);
                $user->update(['two_factor_recovery_codes' => implode(',', $codes)]);
            }
        }

        if (!$valid) {
            return back()->withErrors(['code' => 'Kode 2FA atau recovery code tidak valid.']);
        }

        $request->session()->forget('2fa_pending');
        $request->session()->put('2fa_verified', true);

        return redirect()->intended(route('dashboard'));
    }

    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password tidak sesuai.']);
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()->route('dashboard')->with('success', '2FA dinonaktifkan.');
    }
}