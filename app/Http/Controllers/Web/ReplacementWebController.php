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
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403, 'Hanya superadmin dan owner yang dapat membuat penggantian kendaraan');
        }

        $bookings = Booking::whereIn('status', ['confirmed', 'ongoing'])
            ->whereNotNull('vehicle_id')
            ->with(['vehicle.category', 'user'])
            ->get();

        $vehicles = Vehicle::where('status', 'available')
            ->where('is_active', true)
            ->with('category')
            ->get();

        return view('replacements.create', compact('bookings', 'vehicles'));
    }

    public function store(Request $request)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403, 'Hanya superadmin dan owner yang dapat membuat penggantian kendaraan');
        }

        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'replacement_vehicle_id' => 'required|exists:vehicles,id',
            'reason' => 'required|string|max:2000',
            'price_difference' => 'nullable|numeric',
            'mark_maintenance' => 'nullable|in:0,1',
            'initial_vehicle_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'final_vehicle_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        if (!$booking->vehicle_id) {
            return back()->with('error', 'Booking ini tidak memiliki kendaraan');
        }

        $replacementVehicle = Vehicle::findOrFail($validated['replacement_vehicle_id']);
        if ($replacementVehicle->category_id !== $booking->vehicle->category_id) {
            return back()->with('error', 'Kendaraan pengganti harus dalam kategori yang sama');
        }

        if ($validated['replacement_vehicle_id'] == $booking->vehicle_id) {
            return back()->with('error', 'Kendaraan pengganti tidak boleh sama');
        }

        $initialPhoto = null;
        if ($request->hasFile('initial_vehicle_photo')) {
            $initialPhoto = $request->file('initial_vehicle_photo')->store('vehicle-replacements', 'public');
        }
        $finalPhoto = null;
        if ($request->hasFile('final_vehicle_photo')) {
            $finalPhoto = $request->file('final_vehicle_photo')->store('vehicle-replacements', 'public');
        }

        VehicleReplacement::create([
            'booking_id' => $booking->id,
            'original_vehicle_id' => $booking->vehicle_id,
            'replacement_vehicle_id' => $replacementVehicle->id,
            'requested_by' => Auth::id(),
            'status' => 'pending',
            'reason' => $validated['reason'],
            'price_difference' => $validated['price_difference'] ?? 0,
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
            'swapped_at' => now(),
        ]);

        $booking = $replacement->booking;
        $originalVehicle = Vehicle::find($replacement->original_vehicle_id);
        $replacementVehicle = Vehicle::find($replacement->replacement_vehicle_id);

        if ($booking && $replacementVehicle) {
            $booking->update(['vehicle_id' => $replacement->replacement_vehicle_id]);
            $replacementVehicle->update(['status' => 'reserved']);
        }

        if ($originalVehicle) {
            $originalVehicle->update(['status' => 'maintenance']);
        }

        $priceDiff = (float) $replacement->price_difference;
        if ($priceDiff != 0 && $booking) {
            $booking->update([
                'total_price' => $booking->total_price + $priceDiff,
                'final_price' => $booking->final_price + $priceDiff,
            ]);
        }

        return back()->with('success', 'Penggantian kendaraan disetujui');
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
