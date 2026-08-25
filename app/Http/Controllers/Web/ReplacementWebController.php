<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\VehicleReplacement;
use App\Models\Vehicle;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReplacementWebController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleReplacement::with(['booking', 'originalVehicle', 'replacementVehicle', 'requestedBy', 'approvedBy']);

        $role = Auth::user()->role;

        if ($role === 'driver') {
            $driver = \App\Models\Driver::where('user_id', Auth::id())->first();
            if ($driver) {
                $query->whereHas('booking', fn($q) => $q->where('driver_id', $driver->id));
            }
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $replacements = $query->latest()->paginate(15);

        return view('replacements.index', compact('replacements'));
    }

    public function create(Request $request)
    {
        $role = Auth::user()->role;
        if (!in_array($role, ['driver'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengajukan penggantian kendaraan');
        }

        $query = Booking::whereIn('status', ['confirmed', 'ongoing']);

        $driver = \App\Models\Driver::where('user_id', Auth::id())->first();
        if ($driver) {
            $query->where('driver_id', $driver->id);
        }

        $bookings = $query->with(['vehicle', 'user', 'driver.user'])->get();
        $vehicles = Vehicle::where('status', 'available')->where('is_active', true)->with('category')->get();

        return view('replacements.create', compact('bookings', 'vehicles'));
    }

    public function store(Request $request)
    {
        $role = Auth::user()->role;
        if (!in_array($role, ['driver'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengajukan penggantian kendaraan');
        }

        $rules = [
            'booking_id' => 'required|exists:bookings,id',
            'replacement_vehicle_id' => 'required|exists:vehicles,id',
            'reason' => 'required|string|max:2000',
            'handover_notes' => 'nullable|string|max:1000',
            'initial_vehicle_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'final_vehicle_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];

        $validated = $request->validate($rules);

        $booking = Booking::findOrFail($validated['booking_id']);

        $driver = \App\Models\Driver::where('user_id', Auth::id())->first();
        if (!$driver || $booking->driver_id !== $driver->id) {
            abort(403);
        }

        $replacementVehicle = Vehicle::findOrFail($validated['replacement_vehicle_id']);
        $originalVehicle = $booking->vehicle;

        $priceDiff = 0;
        if ($originalVehicle && $replacementVehicle->daily_price > $originalVehicle->daily_price) {
            $days = max(1, $booking->start_date->diffInDays($booking->end_date));
            $priceDiff = ($replacementVehicle->daily_price - $originalVehicle->daily_price) * $days;
        }

        $initialPhoto = $request->file('initial_vehicle_photo')->store('replacements', 'public');
        $finalPhoto = null;
        if ($request->hasFile('final_vehicle_photo')) {
            $finalPhoto = $request->file('final_vehicle_photo')->store('replacements', 'public');
        }

        VehicleReplacement::create([
            'booking_id' => $booking->id,
            'original_vehicle_id' => $booking->vehicle_id,
            'replacement_vehicle_id' => $replacementVehicle->id,
            'requested_by' => Auth::id(),
            'status' => 'pending',
            'reason' => $validated['reason'],
            'price_difference' => $priceDiff,
            'handover_type' => 'lepas_kunci',
            'handover_notes' => $validated['handover_notes'] ?? null,
            'initial_vehicle_photo' => $initialPhoto,
            'final_vehicle_photo' => $finalPhoto,
        ]);

        return redirect()->route('replacements.index')->with('success', 'Permintaan penggantian kendaraan dibuat');
    }

    public function approve(VehicleReplacement $replacement)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403, 'Hanya superadmin dan owner yang dapat menyetujui');
        }

        $replacement->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'actual_handover_at' => now(),
        ]);

        $booking = $replacement->booking;

        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'available']);
        }

        $booking->update([
            'vehicle_id' => $replacement->replacement_vehicle_id,
            'final_price' => $booking->final_price + $replacement->price_difference,
        ]);

        $replacement->replacementVehicle->update(['status' => 'rented']);

        return back()->with('success', 'Penggantian kendaraan disetujui. Kendaraan baru: ' . $replacement->replacementVehicle->name);
    }

    public function reject(VehicleReplacement $replacement)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403, 'Hanya superadmin dan owner yang dapat menolak');
        }

        $replacement->update(['status' => 'rejected', 'approved_by' => Auth::id()]);
        return back()->with('success', 'Permintaan ditolak');
    }
}
