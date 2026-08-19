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
        $query = VehicleReplacement::with(['booking', 'originalVehicle', 'replacementVehicle', 'requestedBy']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $replacements = $query->latest()->paginate(15);

        return view('replacements.index', compact('replacements'));
    }

    public function create(Request $request)
    {
        $bookings = Booking::whereIn('status', ['confirmed', 'ongoing'])->get();
        $vehicles = Vehicle::where('status', 'available')->where('is_active', true)->get();

        return view('replacements.create', compact('bookings', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'replacement_vehicle_id' => 'required|exists:vehicles,id',
            'reason' => 'required|string|max:2000',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        $replacementVehicle = Vehicle::findOrFail($validated['replacement_vehicle_id']);

        $priceDiff = 0;
        if ($replacementVehicle->daily_price > $booking->vehicle->daily_price) {
            $days = max(1, $booking->start_date->diffInDays($booking->end_date));
            $priceDiff = ($replacementVehicle->daily_price - $booking->vehicle->daily_price) * $days;
        }

        VehicleReplacement::create([
            'booking_id' => $booking->id,
            'original_vehicle_id' => $booking->vehicle_id,
            'replacement_vehicle_id' => $replacementVehicle->id,
            'requested_by' => Auth::id(),
            'status' => 'pending',
            'reason' => $validated['reason'],
            'price_difference' => $priceDiff,
        ]);

        return redirect()->route('replacements.index')->with('success', 'Permintaan penggantian kendaraan dibuat');
    }

    public function approve(VehicleReplacement $replacement)
    {
        $replacement->update(['status' => 'approved', 'approved_by' => Auth::id()]);

        $booking = $replacement->booking;
        $booking->vehicle->update(['status' => 'available']);
        $booking->update([
            'vehicle_id' => $replacement->replacement_vehicle_id,
            'final_price' => $booking->final_price + $replacement->price_difference,
        ]);
        $replacement->replacementVehicle->update(['status' => 'rented']);

        return back()->with('success', 'Penggantian kendaraan disetujui');
    }

    public function reject(VehicleReplacement $replacement)
    {
        $replacement->update(['status' => 'rejected', 'approved_by' => Auth::id()]);
        return back()->with('success', 'Permintaan ditolak');
    }
}
