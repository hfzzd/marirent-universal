<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->merge(['phone' => \App\Support\Phone::normalize($request->input('phone'))]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:30|unique:users,phone',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'user',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data' => ['user' => $user, 'token' => $token],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_identifier' => 'required_without_all:email,phone|string|max:255',
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'password' => 'required',
        ]);

        $identifier = $request->login_identifier ?? $request->email ?? $request->phone;

        if (str_contains($identifier, '@')) {
            $field = 'email';
            $value = strtolower(trim($identifier));
        } else {
            $field = 'phone';
            $value = \App\Support\Phone::normalize($identifier);
        }

        $user = User::where($field, $value)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                $field => ['Email / No. HP atau password salah.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['success' => false, 'message' => 'Akun anda tidak aktif'], 403);
        }

        if (app(SubscriptionService::class)->isOverdueForUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Akun diblokir karena tagihan subscription belum dibayar. Silakan selesaikan pembayaran billing.',
                'error_code' => 'subscription_overdue',
                'redirect_to' => '/api/subscriptions/due',
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => ['user' => $user, 'token' => $token],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $request->merge(['phone' => \App\Support\Phone::normalize($request->input('phone'))]);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:30|unique:users,phone,' . $user->id,
            'address' => 'nullable|string|max:500',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => $user,
        ]);
    }
}
