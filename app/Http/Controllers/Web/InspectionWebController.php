<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspectionWebController extends Controller
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

        $inspections = $query->latest()->paginate(15);

        return view('inspections.index', compact('inspections'));
    }

    public function create(Request $request)
    {
        $bookings = Booking::whereIn('status', ['confirmed', 'ongoing'])->get();
        $booking = $request->booking_id ? Booking::find($request->booking_id) : null;

        return view('inspections.create', compact('bookings', 'booking'));
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
            'notes' => 'nullable|string|max:2000',
            'recommendations' => 'nullable|string|max:2000',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        $validated['vehicle_id'] = $booking->vehicle_id;
        $validated['inspector_id'] = Auth::id();

        Inspection::create($validated);

        return redirect()->route('inspections.index')->with('success', 'Inspeksi berhasil dicatat');
    }

    public function show(Inspection $inspection)
    {
        $inspection->load(['booking', 'vehicle', 'inspector']);
        return view('inspections.show', compact('inspection'));
    }
}
