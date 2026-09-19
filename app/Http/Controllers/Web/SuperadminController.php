<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Maintenance;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperadminController extends Controller
{
    public function monitoring(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = Booking::with(['user', 'vehicle', 'category']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', $search)
                    ->orWhereHas('vehicle', fn($vq) => $vq->where('name', 'like', $search))
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', $search))
                    ->orWhereHas('category', fn($cq) => $cq->where('name', 'like', $search));
            });
        }

        $bookings = $query->latest()->paginate(25)->withQueryString();

        return view('superadmin.monitoring', compact('status', 'bookings'));
    }

    /**
     * Alias takscoped (seluruh data) ke SchedulerService.
     * Route aktif: scheduler.events (SchedulerController).
     */
    public function schedulerEvents(Request $request)
    {
        $service = app(\App\Services\SchedulerService::class);
        $start = $request->input('start') ? \Carbon\Carbon::parse($request->input('start')) : now()->startOfMonth();
        $end = $request->input('end') ? \Carbon\Carbon::parse($request->input('end')) : now()->endOfMonth();

        return response()->json($service->events($start, $end));
    }

    public function finance()
    {
        $query = \App\Models\Invoice::where('status', 'paid')
            ->where('owner_id', '!=', null)
            ->where('type', '!=', 'driver_salary');

        $totalRevenue = (float) $query->sum('total_amount');
        $totalPlatformFee = (float) $query->sum('platform_fee');
        $totalMerchantRevenue = (float) $query->sum('merchant_revenue');

        $merchantBreakdown = \App\Models\Invoice::where('status', 'paid')
            ->where('owner_id', '!=', null)
            ->where('type', '!=', 'driver_salary')
            ->selectRaw('owner_id, COUNT(*) as invoice_count, SUM(total_amount) as total_amount, SUM(platform_fee) as platform_fee, SUM(merchant_revenue) as merchant_revenue')
            ->groupBy('owner_id')
            ->get()
            ->map(function ($row) {
                $merchant = \App\Models\Merchant::where('user_id', $row->owner_id)->first();
                return (object) [
                    'owner_id' => $row->owner_id,
                    'name' => $merchant?->name ?? (\App\Models\User::find($row->owner_id)?->name ?? 'Merchant #' . $row->owner_id),
                    'slug' => $merchant?->slug,
                    'invoice_count' => $row->invoice_count,
                    'total_amount' => $row->total_amount,
                    'platform_fee' => $row->platform_fee,
                    'merchant_revenue' => $row->merchant_revenue,
                ];
            })
            ->sortByDesc('platform_fee')
            ->values();

        return view('superadmin.finance', compact(
            'totalRevenue',
            'totalPlatformFee',
            'totalMerchantRevenue',
            'merchantBreakdown'
        ));
    }

    public function absen(Request $request)
    {
        $query = \App\Models\Driver::with('user')->where('is_active', true);

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('license_number', 'like', $search)
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', $search)->orWhere('email', 'like', $search));
            });
        }

        $drivers = $query->get();
        return view('superadmin.absen', compact('drivers'));
    }

    public function monitoringVehicle(Request $request)
    {
        $query = \App\Models\Vehicle::with('category', 'owner')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('license_plate', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vehicles = $query->paginate(20)->withQueryString();
        $totalAvailable = \App\Models\Vehicle::where('status', 'available')->count();
        $totalRented = \App\Models\Vehicle::where('status', 'rented')->count();
        $totalMaintenance = \App\Models\Vehicle::where('status', 'maintenance')->count();
        $totalReserved = \App\Models\Vehicle::where('status', 'reserved')->count();

        return view('superadmin.monitoring-vehicle', compact(
            'vehicles', 'totalAvailable', 'totalRented', 'totalMaintenance', 'totalReserved'
        ));
    }

    public function motor(Request $request)
    {
        $query = \App\Models\Vehicle::whereHas('category', fn($q) => $q->where('slug', 'motor'))->with('category', 'owner');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('license_plate', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $motorVehicles = $query->latest()->paginate(15);

        return view('superadmin.motor', compact('motorVehicles'));
    }

    public function elektronik()
    {
        return redirect()->route('superadmin.elektronik.type', 'kamera');
    }

    public function merchants(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = Merchant::with('owner')->withCount('vehicles');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('slug', 'like', $search)
                    ->orWhere('city', 'like', $search)
                    ->orWhereHas('owner', fn($uq) => $uq->where('name', 'like', $search)->orWhere('email', 'like', $search));
            });
        }

        $merchants = $query->orderBy('created_at', 'desc')->get();

        return view('superadmin.merchants.index', compact('merchants', 'status'));
    }

    public function merchantCreate()
    {
        $categories = \App\Models\Category::where('is_active', true)->get();
        return view('superadmin.merchants.create', compact('categories'));
    }

    public function merchantStore(Request $request)
    {
        $request->merge(['phone' => \App\Support\Phone::normalize($request->input('phone'))]);

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:30|unique:users,phone',
            'category_id' => 'nullable|exists:categories,id',
            'store_name' => 'required|string|max:120',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'city' => 'nullable|string|max:80',
            'address' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:80',
            'bank_account_number' => 'nullable|string|max:40',
            'bank_account_holder' => 'nullable|string|max:120',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|required_with:latitude|numeric|between:-180,180',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $merchant = DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'role' => 'owner',
            ]);

            $logoPath = $request->hasFile('logo')
                ? $request->file('logo')->store('merchant-logos', 'public')
                : null;

            $merchant = Merchant::create([
                'user_id' => $user->id,
                'slug' => $this->uniqueMerchantSlug($validated['store_name']),
                'name' => $validated['store_name'],
                'logo' => $logoPath,
                'city' => $validated['city'] ?? null,
                'address' => $validated['address'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'bank_account_holder' => $validated['bank_account_holder'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'commission_rate' => $validated['commission_rate'] ?? 10,
                'is_active' => false,
                'status' => 'pending',
            ]);

            if (!$merchant->hasCoordinates() && !empty($validated['address'] ?? null)) {
                $coords = app(\App\Services\GeocodeService::class)->geocode(
                    implode(', ', array_filter([$validated['address'] ?? '', $validated['city'] ?? '']))
                );
                if ($coords) {
                    $merchant->update(['latitude' => $coords['latitude'], 'longitude' => $coords['longitude']]);
                }
            }

            Company::ensureForOwner($user);

            return $merchant;
        });

        return redirect()->route('superadmin.merchants')
            ->with('success', 'Toko "' . $merchant->name . '" berhasil dibuat (status pending).');
    }

    public function merchantVerify(int $id)
    {
        $merchant = Merchant::findOrFail($id);
        $merchant->update([
            'status' => 'active',
            'is_active' => true,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Toko "' . $merchant->name . '" telah diverifikasi.');
    }

    public function merchantSuspend(int $id)
    {
        $merchant = Merchant::findOrFail($id);
        $merchant->update([
            'status' => 'suspended',
            'is_active' => false,
        ]);

        return back()->with('success', 'Toko "' . $merchant->name . '" ditangguhkan.');
    }

    public function merchantActivate(int $id)
    {
        $merchant = Merchant::findOrFail($id);
        if ($merchant->status === 'pending') {
            return back()->with('error', 'Verifikasi toko terlebih dahulu.');
        }
        $merchant->update([
            'status' => 'active',
            'is_active' => true,
        ]);

        return back()->with('success', 'Toko "' . $merchant->name . '" diaktifkan.');
    }

    public function merchantUpdate(Request $request, int $id)
    {
        $merchant = Merchant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:120',
            'website' => 'nullable|url|max:120',
            'instagram' => 'nullable|string|max:120',
            'city' => 'nullable|string|max:80',
            'address' => 'nullable|string|max:255',
            'pickup_address' => 'nullable|string|max:255',
            'operational_hours' => 'nullable|string|max:80',
            'bank_name' => 'nullable|string|max:80',
            'bank_account_number' => 'nullable|string|max:40',
            'bank_account_holder' => 'nullable|string|max:120',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|required_with:latitude|numeric|between:-180,180',
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $data = collect($validated)->except('logo')->all();
        if ($request->hasFile('logo')) {
            if ($merchant->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($merchant->logo);
            }
            $data['logo'] = $request->file('logo')->store('merchant-logos', 'public');
        }
        // Tanpa pin manual tapi alamat berubah & belum ada koordinat: geocode otomatis.
        if (!isset($data['latitude']) && (!empty($data['address'] ?? null) || !empty($data['city'] ?? null))) {
            if (!$merchant->hasCoordinates() || ($data['address'] ?? null) !== $merchant->address || ($data['city'] ?? null) !== $merchant->city) {
                $coords = app(\App\Services\GeocodeService::class)->geocode(
                    implode(', ', array_filter([$data['address'] ?? $merchant->address, $data['city'] ?? $merchant->city]))
                );
                if ($coords) {
                    $data['latitude'] = $coords['latitude'];
                    $data['longitude'] = $coords['longitude'];
                }
            }
        }

        $merchant->update($data);

        return back()->with('success', 'Toko "' . $merchant->name . '" diperbarui.');
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

    /**
     * Daftar permintaan jadwal demo dari halaman publik.
     */
    public function demoRequests(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = \App\Models\DemoRequest::query();
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('business_name', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('notes', 'like', $search);
            });
        }

        $demos = $query->latest()->paginate(15)->withQueryString();
        $pendingCount = \App\Models\DemoRequest::where('status', 'pending')->count();

        return view('superadmin.demo-requests.index', compact('demos', 'status', 'pendingCount'));
    }

    /**
     * Ubah status permintaan demo (pending/contacted/scheduled/done/cancelled).
     */
    public function demoRequestUpdate(Request $request, int $id)
    {
        $demo = \App\Models\DemoRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,contacted,scheduled,done,cancelled',
        ]);

        $demo->update($validated);

        return back()->with('success', 'Status demo "' . $demo->name . '" diubah menjadi ' . $validated['status'] . '.');
    }
}
