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

        return view('superadmin.monitoring', compact('status'));
    }

    public function scheduler()
    {
        return view('superadmin.scheduler');
    }

    public function schedulerEvents(Request $request)
    {
        $start = $request->input('start') ? \Carbon\Carbon::parse($request->input('start')) : now()->startOfMonth();
        $end = $request->input('end') ? \Carbon\Carbon::parse($request->input('end')) : now()->endOfMonth();

        $events = [];
        $bookings = Booking::with(['user', 'vehicle', 'invoice', 'invoice.payments'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->whereNotNull('actual_start_date')
                         ->whereBetween('actual_start_date', [$start, $end]);
                  })
                  ->orWhere(function ($q3) use ($start, $end) {
                      $q3->whereNotNull('actual_end_date')
                         ->whereBetween('actual_end_date', [$start, $end]);
                  });
            })
            ->get();

        foreach ($bookings as $b) {
            $userName = $b->user->name ?? '-';
            $vehicleName = $b->vehicle->name ?? ($b->category->name ?? '-');
            $code = $b->booking_code;
            $label = $code . ' - ' . $userName;

            if ($b->start_date && $b->start_date->between($start, $end)) {
                $events[] = [
                    'id' => 'start-' . $b->id,
                    'title' => $label . ' Mulai',
                    'start' => $b->start_date->toIso8601String(),
                    'color' => '#3b82f6',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'mulai',
                        'booking_code' => $code,
                        'user_name' => $userName,
                        'vehicle_name' => $vehicleName,
                        'booking_id' => $b->id,
                        'start_date' => $b->start_date->format('d M Y'),
                        'end_date' => $b->end_date ? $b->end_date->format('d M Y') : '-',
                    ],
                ];
            }

            if ($b->end_date && $b->end_date->between($start, $end)) {
                $events[] = [
                    'id' => 'end-' . $b->id,
                    'title' => $label . ' Selesai',
                    'start' => $b->end_date->toIso8601String(),
                    'color' => '#6366f1',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'selesai',
                        'booking_code' => $code,
                        'user_name' => $userName,
                        'vehicle_name' => $vehicleName,
                        'booking_id' => $b->id,
                    ],
                ];
            }

            if ($b->actual_start_date && $b->actual_start_date->between($start, $end)) {
                $events[] = [
                    'id' => 'pickup-' . $b->id,
                    'title' => $label . ' Penjemputan',
                    'start' => $b->actual_start_date->toIso8601String(),
                    'color' => '#06b6d4',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'penjemputan',
                        'booking_code' => $code,
                        'user_name' => $userName,
                        'vehicle_name' => $vehicleName,
                        'booking_id' => $b->id,
                        'pickup_location' => $b->pickup_location ?? '-',
                    ],
                ];
            }

            if ($b->actual_end_date && $b->actual_end_date->between($start, $end)) {
                $events[] = [
                    'id' => 'return-' . $b->id,
                    'title' => $label . ' Pemulangan',
                    'start' => $b->actual_end_date->toIso8601String(),
                    'color' => '#8b5cf6',
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'pemulangan',
                        'booking_code' => $code,
                        'user_name' => $userName,
                        'vehicle_name' => $vehicleName,
                        'booking_id' => $b->id,
                        'dropoff_location' => $b->dropoff_location ?? '-',
                    ],
                ];
            }

            if ($b->invoice) {
                $invoice = $b->invoice;
                $paidAmount = $invoice->payments->where('status', 'verified')->sum('amount');

                if ($invoice->status === 'paid') {
                    $events[] = [
                        'id' => 'paid-' . $b->id,
                        'title' => $label . ' Lunas',
                        'start' => ($invoice->paid_at ?? $b->start_date)->toIso8601String(),
                        'color' => '#22c55e',
                        'textColor' => '#ffffff',
                        'extendedProps' => [
                            'type' => 'lunas',
                            'booking_code' => $code,
                            'user_name' => $userName,
                            'vehicle_name' => $vehicleName,
                            'booking_id' => $b->id,
                            'amount' => $invoice->total_amount,
                            'paid_amount' => $paidAmount,
                        ],
                    ];
                } elseif ($invoice->status === 'partial' || ($paidAmount > 0 && $paidAmount < $invoice->total_amount)) {
                    $events[] = [
                        'id' => 'partial-' . $b->id,
                        'title' => $label . ' Sebagian',
                        'start' => ($invoice->due_date ?? $b->start_date)->toIso8601String(),
                        'color' => '#f59e0b',
                        'textColor' => '#ffffff',
                        'extendedProps' => [
                            'type' => 'sebagian',
                            'booking_code' => $code,
                            'user_name' => $userName,
                            'vehicle_name' => $vehicleName,
                            'booking_id' => $b->id,
                            'amount' => $invoice->total_amount,
                            'paid_amount' => $paidAmount,
                            'due_amount' => $invoice->total_amount - $paidAmount,
                        ],
                    ];
                } elseif ($invoice->status !== 'paid' && $invoice->due_date && $invoice->due_date->isPast()) {
                    $events[] = [
                        'id' => 'overdue-' . $b->id,
                        'title' => $label . ' Terlambat',
                        'start' => $invoice->due_date->toIso8601String(),
                        'color' => '#ef4444',
                        'textColor' => '#ffffff',
                        'extendedProps' => [
                            'type' => 'terlambat',
                            'booking_code' => $code,
                            'user_name' => $userName,
                            'vehicle_name' => $vehicleName,
                            'booking_id' => $b->id,
                            'amount' => $invoice->total_amount,
                            'paid_amount' => $paidAmount,
                            'due_amount' => $invoice->total_amount - $paidAmount,
                            'due_date' => $invoice->due_date->format('d M Y'),
                        ],
                    ];
                }
            }
        }

        // Maintenance Events
        $maintenances = Maintenance::with(['vehicle'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('scheduled_date', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->whereNotNull('completed_date')
                         ->whereBetween('completed_date', [$start, $end]);
                  });
            })
            ->get();

        foreach ($maintenances as $m) {
            $vehicleName = $m->vehicle->name ?? '-';
            $typeLabels = ['routine' => 'Rutin', 'repair' => 'Perbaikan', 'inspection' => 'Inspeksi', 'emergency' => 'Darurat'];
            $priorityColors = ['low' => '#22c55e', 'medium' => '#f59e0b', 'high' => '#f97316', 'urgent' => '#ef4444'];
            $priorityColor = $priorityColors[$m->priority] ?? '#6b7280';

            $events[] = [
                'id' => 'maintenance-' . $m->id,
                'title' => '🔧 ' . $m->title,
                'start' => $m->scheduled_date->toIso8601String(),
                'color' => $priorityColor,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'maintenance',
                    'maintenance_code' => $m->maintenance_code,
                    'vehicle_name' => $vehicleName,
                    'maintenance_type' => $typeLabels[$m->type] ?? $m->type,
                    'priority' => $m->priority,
                    'status' => $m->status,
                    'estimated_cost' => $m->estimated_cost,
                    'technician' => $m->technician ?? '-',
                ],
            ];
        }

        return response()->json($events);
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

    public function absen()
    {
        $drivers = \App\Models\Driver::with('user')->where('is_active', true)->get();
        return view('superadmin.absen', compact('drivers'));
    }

    public function monitoringVehicle(Request $request)
    {
        $vehicles = \App\Models\Vehicle::with('category', 'owner')->latest()->paginate(20);
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
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'category_id' => 'nullable|exists:categories,id',
            'store_name' => 'required|string|max:120',
            'city' => 'nullable|string|max:80',
            'address' => 'nullable|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $merchant = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'role' => 'owner',
            ]);

            $merchant = Merchant::create([
                'user_id' => $user->id,
                'slug' => $this->uniqueMerchantSlug($validated['store_name']),
                'name' => $validated['store_name'],
                'city' => $validated['city'] ?? null,
                'address' => $validated['address'] ?? null,
                'commission_rate' => $validated['commission_rate'] ?? 10,
                'is_active' => false,
                'status' => 'pending',
            ]);

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
            'phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:120',
            'website' => 'nullable|url|max:120',
            'instagram' => 'nullable|string|max:120',
            'city' => 'nullable|string|max:80',
            'address' => 'nullable|string|max:255',
            'pickup_address' => 'nullable|string|max:255',
            'operational_hours' => 'nullable|string|max:80',
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $merchant->update($validated);

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
}
