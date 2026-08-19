<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|string|email|max:255|unique:users',
            'password'          => 'required|string|min:8|confirmed',
            'phone'             => 'required|string|max:20',
            'address'           => 'nullable|string|max:500',
        ]);

        try {
            $user = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'phone'      => $request->phone,
                'address'    => $request->address,
                'role'       => 'user',
                'is_active'  => true,
            ]);

            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Registrasi berhasil! Selamat datang.');
        } catch (\Exception $e) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Gagal membuat akun. Silakan coba lagi.']);
        }
    }
}
