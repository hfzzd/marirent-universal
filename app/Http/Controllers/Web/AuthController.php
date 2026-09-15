<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Company;
use App\Models\Merchant;
use App\Models\User;
use App\Notifications\SubscriptionOverdue;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->dashboardRedirect());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login_identifier' => 'required_without:email|string|max:255',
            'email' => 'nullable|string|max:255',
            'password' => 'required|string',
        ]);

        $identifier = $request->login_identifier ?? $request->email;

        if (str_contains($identifier, '@')) {
            $credentials = [
                'email' => strtolower(trim($identifier)),
                'password' => $request->password,
            ];
        } else {
            $credentials = [
                'phone' => \App\Support\Phone::normalize($identifier),
                'password' => $request->password,
            ];
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->regenerateToken();

            $user = Auth::user();
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['login_identifier' => 'Akun tidak aktif']);
            }

            if (app(SubscriptionService::class)->isOverdueForUser($user)) {
                $this->sendOverdueLoginNotice($user);
                return redirect()->route('subscriptions.notice')
                    ->with('error', 'Akun Anda diblokir karena tagihan subscription belum dibayar. Silakan selesaikan pembayaran billing untuk mengaktifkan kembali akses.');
            }

            return redirect()->intended($this->dashboardRedirect());
        }

        return back()->withErrors(['login_identifier' => 'Email / No. HP atau password salah'])->onlyInput('login_identifier');
    }

    private function sendOverdueLoginNotice(User $user): void
    {
        $merchant = app(SubscriptionService::class)->merchantForUser($user);
        if (!$merchant) {
            return;
        }

        $already = $user->notifications()
            ->where('data->type', 'subscription_overdue')
            ->whereDate('created_at', today())
            ->exists();

        if (!$already) {
            $user->notify(new SubscriptionOverdue($merchant));
        }
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect($this->dashboardRedirect());
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge(['phone' => \App\Support\Phone::normalize($request->input('phone'))]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:30|unique:users,phone',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['role'] = 'user';

        \App\Models\User::create($validated);

        return redirect()->route('login')->with('success', 'Registrasi berhasil, silakan login');
    }

    public function showRegisterMerchant()
    {
        if (Auth::check()) {
            return redirect($this->dashboardRedirect());
        }
        $categories = Category::where('is_active', true)->get();
        return view('auth.register-merchant', compact('categories'));
    }

    public function registerMerchant(Request $request)
    {
        $request->merge(['phone' => \App\Support\Phone::normalize($request->input('phone'))]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:30|unique:users,phone',
            'category_id' => 'nullable|exists:categories,id',
            'store_name' => 'required|string|max:120',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'role' => 'owner',
            ]);

            Merchant::create([
                'user_id' => $user->id,
                'slug' => $this->uniqueMerchantSlug($validated['store_name']),
                'name' => $validated['store_name'],
                'commission_rate' => 10,
                'is_active' => false,
                'status' => 'pending',
            ]);

            Company::ensureForOwner($user);
        });

        return redirect()->route('login')
            ->with('success', 'Pendaftaran merchant berhasil. Menunggu verifikasi admin sebelum toko Anda aktif.');
    }

    private function uniqueMerchantSlug(string $name): string
    {
        $slug = Str::slug($name) ?: Str::random(6);
        $base = $slug;
        $i = 1;
        while (Merchant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . ($i++);
        }
        return $slug;
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function dashboardRedirect()
    {
        $role = Auth::user()->role;

        return match($role) {
            'user' => route('home'),
            default => route('dashboard'),
        };
    }
}
