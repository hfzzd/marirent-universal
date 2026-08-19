<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Rental;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Rental::with('user', 'vehicle', 'driver');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rental_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vehicle', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($user->isUser()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isDriver()) {
            $driver = Driver::where('user_id', $user->id)->first();
            if ($driver) {
                $query->where('driver_id', $driver->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->isOwner()) {
            $query->whereHas('vehicle', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        }

        $rentals = $query->latest()->paginate(15)->withQueryString();

        return view('rentals.index', compact('rentals'));
    }

    public function create()
    {
        $user = Auth::user();
        $vehicles = Vehicle::where('is_active', true)
            ->where('status', 'available')
            ->with('category')
            ->get();

        $drivers = Driver::where('is_available', true)
            ->where('status', 'active')
            ->with('user')
            ->get();

        return view('rentals.create', compact('vehicles', 'drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'        => 'required|exists:vehicles,id',
            'driver_id'         => 'nullable|exists:drivers,id',
            'start_date'        => 'required|date|after_or_equal:today',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'pickup_location'   => 'required|string|max:500',
            'dropoff_location'  => 'required|string|max:500',
            'purpose'           => 'nullable|string|max:500',
            'with_driver'       => 'boolean',
            'notes'             => 'nullable|string|max:1000',
        ]);

        try {
            $vehicle = Vehicle::findOrFail($request->vehicle_id);

            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $totalDays = max(1, $startDate->diffInDays($endDate));

            $dailyRate = $vehicle->daily_rate;
            $subtotal = $dailyRate * $totalDays;
            $driverFee = 0;

            if ($request->boolean('with_driver') && $request->filled('driver_id')) {
                $driver = Driver::findOrFail($request->driver_id);
                $driverFee = $driver->daily_rate * $totalDays;
            }

            $tax = $subtotal * 0.11;
            $totalAmount = $subtotal + $driverFee + $tax;

            $rental = Rental::create([
                'user_id'           => Auth::id(),
                'vehicle_id'        => $vehicle->id,
                'driver_id'         => $request->driver_id,
                'category_type'     => $vehicle->category->name ?? 'Mobil',
                'start_date'        => $request->start_date,
                'end_date'          => $request->end_date,
                'pickup_location'   => $request->pickup_location,
                'dropoff_location'  => $request->dropoff_location,
                'purpose'           => $request->purpose,
                'with_driver'       => $request->boolean('with_driver'),
                'status'            => 'pending',
                'daily_rate'        => $dailyRate,
                'total_days'        => $totalDays,
                'subtotal'          => $subtotal,
                'driver_fee'        => $driverFee,
                'discount'          => 0,
                'tax'               => $tax,
                'total_amount'      => $totalAmount,
                'notes'             => $request->notes,
            ]);

            return redirect()->route('rentals.show', $rental->id)
                ->with('success', 'Sewa berhasil dibuat. Menunggu konfirmasi.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat sewa: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $rental = Rental::with('user', 'vehicle', 'driver.user', 'invoice', 'inspection', 'tripReport')
            ->findOrFail($id);

        $user = Auth::user();

        if ($user->isUser() && $rental->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        return view('rentals.show', compact('rental'));
    }

    public function edit($id)
    {
        $rental = Rental::findOrFail($id);
        $user = Auth::user();

        if ($user->isUser() && $rental->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        if (!in_array($rental->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Sewa tidak dapat diedit pada status saat ini.');
        }

        $vehicles = Vehicle::where('is_active', true)
            ->with('category')
            ->get();

        $drivers = Driver::where('is_available', true)
            ->where('status', 'active')
            ->with('user')
            ->get();

        return view('rentals.edit', compact('rental', 'vehicles', 'drivers'));
    }

    public function update(Request $request, $id)
    {
        $rental = Rental::findOrFail($id);
        $user = Auth::user();

        if ($user->isUser() && $rental->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        if (!in_array($rental->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Sewa tidak dapat diperbarui pada status saat ini.');
        }

        $request->validate([
            'vehicle_id'        => 'required|exists:vehicles,id',
            'driver_id'         => 'nullable|exists:drivers,id',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'pickup_location'   => 'required|string|max:500',
            'dropoff_location'  => 'required|string|max:500',
            'purpose'           => 'nullable|string|max:500',
            'with_driver'       => 'boolean',
            'notes'             => 'nullable|string|max:1000',
        ]);

        try {
            $vehicle = Vehicle::findOrFail($request->vehicle_id);

            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $totalDays = max(1, $startDate->diffInDays($endDate));

            $dailyRate = $vehicle->daily_rate;
            $subtotal = $dailyRate * $totalDays;
            $driverFee = 0;

            if ($request->boolean('with_driver') && $request->filled('driver_id')) {
                $driver = Driver::findOrFail($request->driver_id);
                $driverFee = $driver->daily_rate * $totalDays;
            }

            $tax = $subtotal * 0.11;
            $totalAmount = $subtotal + $driverFee + $tax - $rental->discount;

            $rental->update([
                'vehicle_id'        => $vehicle->id,
                'driver_id'         => $request->driver_id,
                'category_type'     => $vehicle->category->name ?? 'Mobil',
                'start_date'        => $request->start_date,
                'end_date'          => $request->end_date,
                'pickup_location'   => $request->pickup_location,
                'dropoff_location'  => $request->dropoff_location,
                'purpose'           => $request->purpose,
                'with_driver'       => $request->boolean('with_driver'),
                'daily_rate'        => $dailyRate,
                'total_days'        => $totalDays,
                'subtotal'          => $subtotal,
                'driver_fee'        => $driverFee,
                'tax'               => $tax,
                'total_amount'      => $totalAmount,
                'notes'             => $request->notes,
            ]);

            return redirect()->route('rentals.show', $rental->id)
                ->with('success', 'Sewa berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui sewa: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        $rental = Rental::findOrFail($id);
        $user = Auth::user();

        if ($user->isUser() && $rental->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        if (in_array($rental->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Sewa tidak dapat dibatalkan pada status saat ini.');
        }

        $request->validate([
            'cancelled_reason' => 'required|string|max:1000',
        ]);

        try {
            $rental->update([
                'status'            => 'cancelled',
                'cancelled_reason'  => $request->cancelled_reason,
            ]);

            return redirect()->route('rentals.show', $rental->id)
                ->with('success', 'Sewa berhasil dibatalkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan sewa: ' . $e->getMessage());
        }
    }

    public function confirm($id)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $rental = Rental::findOrFail($id);

        if ($rental->status !== 'pending') {
            return back()->with('error', 'Hanya sewa dengan status pending yang dapat dikonfirmasi.');
        }

        try {
            $rental->update(['status' => 'confirmed']);

            return redirect()->route('rentals.show', $rental->id)
                ->with('success', 'Sewa berhasil dikonfirmasi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengonfirmasi sewa: ' . $e->getMessage());
        }
    }

    public function complete($id)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $rental = Rental::findOrFail($id);

        if (!in_array($rental->status, ['ongoing', 'confirmed'])) {
            return back()->with('error', 'Sewa tidak dapat diselesaikan pada status saat ini.');
        }

        try {
            $rental->update([
                'status'         => 'completed',
                'actual_return'  => now(),
            ]);

            Vehicle::where('id', $rental->vehicle_id)->update(['status' => 'available']);

            return redirect()->route('rentals.show', $rental->id)
                ->with('success', 'Sewa berhasil diselesaikan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyelesaikan sewa: ' . $e->getMessage());
        }
    }
}
