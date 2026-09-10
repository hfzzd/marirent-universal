<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MerchantProfileController extends Controller
{
    public function show()
    {
        $owner = Auth::user();

        abort_unless($owner->isOwner(), 403);

        $merchant = Merchant::firstOrCreate(
            ['user_id' => $owner->id],
            [
                'slug' => $this->uniqueSlug($owner->name),
                'name' => $owner->name,
                'commission_rate' => 10,
                'is_active' => true,
                'status' => 'active',
            ]
        );
        Company::ensureForOwner($owner);

        $stats = [
            'total_units' => \App\Models\Vehicle::where('owner_id', $owner->id)->count()
                + \App\Models\Phone::where('owner_id', $owner->id)->count()
                + \App\Models\Camera::where('owner_id', $owner->id)->count()
                + \App\Models\CampingEquipment::where('owner_id', $owner->id)->count()
                + \App\Models\Playstation::where('owner_id', $owner->id)->count()
                + \App\Models\Drone::where('owner_id', $owner->id)->count()
                + \App\Models\MusicalInstrument::where('owner_id', $owner->id)->count(),
            'total_revenue' => \App\Models\Invoice::where('owner_id', $owner->id)->where('status', 'paid')->sum('total_amount'),
            'net_revenue' => \App\Models\Invoice::where('owner_id', $owner->id)->where('status', 'paid')->sum('merchant_revenue'),
            'platform_fee' => \App\Models\Invoice::where('owner_id', $owner->id)->where('status', 'paid')->sum('platform_fee'),
        ];

        $admins = $merchant->admins()->orderBy('name')->get();

        return view('merchant.profile', compact('merchant', 'stats', 'admins'));
    }

    public function storeAdmin(Request $request)
    {
        $owner = Auth::user();
        abort_unless($owner->isOwner(), 403);

        $merchant = Merchant::firstOrCreate(
            ['user_id' => $owner->id],
            ['slug' => $this->uniqueSlug($owner->name), 'name' => $owner->name]
        );
        Company::ensureForOwner($owner);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'admin',
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);

        return back()->with('admin_success', 'Akun admin ' . $admin->name . ' berhasil didaftarkan untuk toko ' . $merchant->name . '.');
    }

    public function destroyAdmin(Request $request, User $admin)
    {
        $owner = Auth::user();
        abort_unless($owner->isOwner(), 403);
        abort_unless((int) $admin->owner_id === (int) $owner->id, 403);

        $name = $admin->name;
        $admin->delete();

        return back()->with('admin_success', 'Akun admin ' . $name . ' berhasil dihapus dari toko Anda.');
    }

    public function update(Request $request)
    {
        $owner = Auth::user();
        abort_unless($owner->isOwner(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:120',
            'website' => 'nullable|url|max:120',
            'instagram' => 'nullable|string|max:120',
            'city' => 'nullable|string|max:80',
            'address' => 'nullable|string|max:255',
            'pickup_address' => 'nullable|string|max:255',
            'operational_hours' => 'nullable|string|max:80',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $merchant = Merchant::firstOrCreate(
            ['user_id' => $owner->id],
            ['slug' => $this->uniqueSlug($owner->name), 'name' => $owner->name]
        );
        $company = Company::ensureForOwner($owner);

        $data = collect($validated)->except('commission_rate')->all();

        if ($owner->isPlatformAdmin() && isset($validated['commission_rate'])) {
            $data['commission_rate'] = $validated['commission_rate'];
        }

        $merchant->update($data);
        $company->update($data);

        return back()->with('merchant_success', 'Profil toko berhasil diperbarui.');
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name) ?: Str::random(6);
        $base = $slug;
        $i = 1;
        while (Merchant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . ($i++);
        }
        return $slug;
    }
}
