<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Booking;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Inspection::with(['booking', 'vehicle', 'inspector']);

        if ($request->booking_id) {
            $query->where('booking_id', $request->booking_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $inspections = $query->latest()->paginate(min($request->get('per_page', 15), 50));

        return response()->json(['success' => true, 'data' => $inspections]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'type' => 'required|in:pre_rental,post_rental',
            'exterior_condition' => 'required|integer|min:1|max:10',
            'interior_condition' => 'required|integer|min:1|max:10',
            'engine_condition' => 'required|integer|min:1|max:10',
            'tire_condition' => 'required|integer|min:1|max:10',
            'brake_condition' => 'required|integer|min:1|max:10',
            'electrical_condition' => 'required|integer|min:1|max:10',
            'overall_condition' => 'required|integer|min:1|max:10',
            'fuel_level' => 'required|numeric|min:0|max:100',
            'odometer_reading' => 'nullable|numeric|min:0',
            'damages' => 'nullable|array',
            'damages.*' => 'string',
            'photos' => 'nullable|array',
            'notes' => 'nullable|string|max:2000',
            'recommendations' => 'nullable|string|max:2000',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        $validated['vehicle_id'] = $booking->vehicle_id;
        $validated['inspector_id'] = $request->user()->id;

        $inspection = Inspection::create($validated);

        if (!empty($validated['damages']) && count($validated['damages']) > 0) {
            $booking->vehicle->update(['condition' => 'fair']);
        }

        return response()->json([
            'success' => true, 'message' => 'Inspeksi berhasil dicatat', 'data' => $inspection,
        ], 201);
    }

    public function show(Inspection $inspection)
    {
        $inspection->load(['booking', 'vehicle', 'inspector']);
        return response()->json(['success' => true, 'data' => $inspection]);
    }

    public function byBooking(string $bookingCode)
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();
        $inspections = Inspection::with(['vehicle', 'inspector'])
            ->where('booking_id', $booking->id)
            ->get();

        return response()->json(['success' => true, 'data' => $inspections]);
    }
}
