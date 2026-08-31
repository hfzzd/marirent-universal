<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

        return view('merchant.profile', compact('merchant', 'stats'));
    }

    public function update(Request $request)
    {
        $owner = Auth::user();
        abort_unless($owner->isOwner(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:30',
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

        $data = collect($validated)->except('commission_rate')->all();

        if ($owner->isPlatformAdmin() && isset($validated['commission_rate'])) {
            $data['commission_rate'] = $validated['commission_rate'];
        }

        $merchant->update($data);

        return back()->with('success', 'Profil toko berhasil diperbarui.');
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
