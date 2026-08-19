<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleReplacement;
use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ReplacementController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleReplacement::with(['booking', 'originalVehicle', 'replacementVehicle', 'requestedBy', 'approvedBy']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $replacements = $query->latest()->paginate(min($request->get('per_page', 15), 50));

        return response()->json(['success' => true, 'data' => $replacements]);
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

        if ($replacementVehicle->status !== 'available') {
            return response()->json([
                'success' => false, 'message' => 'Kendaraan pengganti tidak tersedia',
            ], 422);
        }

        $priceDiff = 0;
        if ($replacementVehicle->daily_price > $booking->vehicle->daily_price) {
            $days = max(1, $booking->start_date->diffInDays($booking->end_date));
            $priceDiff = ($replacementVehicle->daily_price - $booking->vehicle->daily_price) * $days;
        }

        $replacement = VehicleReplacement::create([
            'booking_id' => $booking->id,
            'original_vehicle_id' => $booking->vehicle_id,
            'replacement_vehicle_id' => $replacementVehicle->id,
            'requested_by' => $request->user()->id,
            'status' => 'pending',
            'reason' => $validated['reason'],
            'price_difference' => $priceDiff,
        ]);

        return response()->json([
            'success' => true, 'message' => 'Permintaan penggantian kendaraan dibuat', 'data' => $replacement,
        ], 201);
    }

    public function approve(Request $request, VehicleReplacement $replacement)
    {
        if ($replacement->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Permintaan sudah diproses'], 422);
        }

        $replacement->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'admin_notes' => $request->get('admin_notes'),
        ]);

        $booking = $replacement->booking;
        $oldVehicle = $booking->vehicle;

        $booking->update([
            'vehicle_id' => $replacement->replacement_vehicle_id,
            'final_price' => $booking->final_price + $replacement->price_difference,
        ]);

        $oldVehicle->update(['status' => 'available']);
        $replacement->replacementVehicle->update(['status' => 'rented']);

        return response()->json([
            'success' => true, 'message' => 'Penggantian kendaraan disetujui', 'data' => $replacement,
        ]);
    }

    public function reject(Request $request, VehicleReplacement $replacement)
    {
        $replacement->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'admin_notes' => $request->get('admin_notes', 'Permintaan ditolak'),
        ]);

        return response()->json([
            'success' => true, 'message' => 'Permintaan ditolak', 'data' => $replacement,
        ]);
    }
}
