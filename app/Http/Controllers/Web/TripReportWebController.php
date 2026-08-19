<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TripReport;
use App\Models\Booking;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripReportWebController extends Controller
{
    public function index(Request $request)
    {
        $query = TripReport::with(['booking', 'driver.user', 'vehicle']);

        if (Auth::user()->role === 'driver') {
            $driver = Driver::where('user_id', Auth::id())->first();
            if ($driver) {
                $query->where('driver_id', $driver->id);
            }
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate(15);

        return view('reports.index', compact('reports'));
    }

    public function create(Request $request)
    {
        $bookings = Booking::whereIn('status', ['ongoing'])->get();

        return view('reports.create', compact('bookings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'start_odometer' => 'nullable|numeric|min:0',
            'end_odometer' => 'nullable|numeric|min:0',
            'fuel_cost' => 'nullable|numeric|min:0',
            'toll_cost' => 'nullable|numeric|min:0',
            'parking_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'issues_reported' => 'nullable|string|max:2000',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        $validated['vehicle_id'] = $booking->vehicle_id;
        $validated['driver_id'] = $booking->driver_id;

        if (!empty($validated['start_odometer']) && !empty($validated['end_odometer'])) {
            $validated['total_distance'] = $validated['end_odometer'] - $validated['start_odometer'];
        }

        $validated['status'] = !empty($validated['issues_reported']) ? 'has_issues' : 'completed';

        $report = TripReport::create($validated);
        $report->calculateTotalCost();

        return redirect()->route('reports.index')->with('success', 'Laporan perjalanan berhasil dibuat');
    }

    public function show(TripReport $tripReport)
    {
        $tripReport->load(['booking', 'driver.user', 'vehicle']);
        return view('reports.show', compact('tripReport'));
    }
}
