<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TripReport;
use App\Models\Booking;
use Illuminate\Http\Request;

class TripReportController extends Controller
{
    public function index(Request $request)
    {
        $query = TripReport::with(['booking', 'driver.user', 'vehicle']);

        if ($request->booking_id) {
            $query->where('booking_id', $request->booking_id);
        }

        if ($request->driver_id) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate(min($request->get('per_page', 15), 50));

        return response()->json(['success' => true, 'data' => $reports]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'start_odometer' => 'nullable|numeric|min:0',
            'end_odometer' => 'nullable|numeric|min:0|gte:start_odometer',
            'fuel_used' => 'nullable|numeric|min:0',
            'fuel_cost' => 'nullable|numeric|min:0',
            'toll_cost' => 'nullable|numeric|min:0',
            'parking_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'route_points' => 'nullable|array',
            'photos' => 'nullable|array',
            'notes' => 'nullable|string|max:2000',
            'issues_reported' => 'nullable|string|max:2000',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        $validated['vehicle_id'] = $booking->vehicle_id;

        if ($booking->driver_id) {
            $validated['driver_id'] = $booking->driver_id;
        }

        if (!empty($validated['start_odometer']) && !empty($validated['end_odometer'])) {
            $validated['total_distance'] = $validated['end_odometer'] - $validated['start_odometer'];
        }

        $validated['status'] = !empty($validated['issues_reported']) ? 'has_issues' : 'completed';

        $report = TripReport::create($validated);

        $report->calculateTotalCost();

        return response()->json([
            'success' => true, 'message' => 'Laporan perjalanan berhasil dibuat', 'data' => $report,
        ], 201);
    }

    public function show(TripReport $tripReport)
    {
        $tripReport->load(['booking', 'driver.user', 'vehicle']);
        return response()->json(['success' => true, 'data' => $tripReport]);
    }

    public function update(Request $request, TripReport $tripReport)
    {
        $validated = $request->validate([
            'end_odometer' => 'nullable|numeric|min:0',
            'fuel_cost' => 'nullable|numeric|min:0',
            'toll_cost' => 'nullable|numeric|min:0',
            'parking_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'issues_reported' => 'nullable|string|max:2000',
            'status' => 'sometimes|in:in_progress,completed,has_issues',
        ]);

        $tripReport->update($validated);
        $tripReport->calculateTotalCost();

        return response()->json([
            'success' => true, 'message' => 'Laporan perjalanan diperbarui', 'data' => $tripReport,
        ]);
    }
}
