<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;
use App\Models\Playstation;
use App\Models\Drone;
use App\Models\MusicalInstrument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspectionWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Inspection::with(['booking', 'vehicle', 'inspector']);

        if (Auth::user()->role === 'inspector') {
            $query->where('inspector_id', Auth::id());
        }

        if ($request->booking_id) {
            $query->where('booking_id', $request->booking_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->scope) {
            $query->where('scope', $request->scope);
        }

        $inspections = $query->latest()->paginate(15);

        return view('inspections.index', compact('inspections'));
    }

    public function create(Request $request)
    {
        $bookings = Booking::whereIn('status', ['confirmed', 'ongoing'])
            ->with(['user', 'vehicle', 'category', 'bookingItems'])
            ->get();

        $booking = $request->booking_id ? Booking::with(['vehicle', 'bookingItems'])->find($request->booking_id) : null;

        $vehicles = Vehicle::where('status', '!=', 'maintenance')->get();
        $phones = Phone::where('status', '!=', 'maintenance')->get();
        $cameras = Camera::where('status', '!=', 'maintenance')->get();
        $equipments = CampingEquipment::where('status', '!=', 'maintenance')->get();
        $playstations = Playstation::where('status', '!=', 'maintenance')->get();
        $drones = Drone::where('status', '!=', 'maintenance')->get();
        $instruments = MusicalInstrument::where('status', '!=', 'maintenance')->get();

        return view('inspections.create', compact('bookings', 'booking', 'vehicles', 'phones', 'cameras', 'equipments', 'playstations', 'drones', 'instruments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'type' => 'required|in:pre_rental,post_rental',
            'scope' => 'required|in:kendaraan,elektronik,camping',
            'inspection_item_id' => 'required|integer',
            'usage_duration_hours' => 'nullable|integer|min:0',
            'overall_condition' => 'nullable|integer|min:1|max:10',
            'exterior_condition' => 'nullable|integer|min:1|max:10',
            'interior_condition' => 'nullable|integer|min:1|max:10',
            'engine_condition' => 'nullable|integer|min:1|max:10',
            'tire_condition' => 'nullable|integer|min:1|max:10',
            'brake_condition' => 'nullable|integer|min:1|max:10',
            'electrical_condition' => 'nullable|integer|min:1|max:10',
            'fuel_level' => 'nullable|numeric|min:0|max:100',
            'odometer_reading' => 'nullable|numeric|min:0',
            'damage_items' => 'nullable|array',
            'damage_items.*' => 'string|max:255',
            'completeness' => 'nullable|array',
            'completeness.*' => 'string|max:255',
            'notes' => 'nullable|string|max:2000',
            'recommendations' => 'nullable|string|max:2000',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        $scope = $validated['scope'];
        $itemId = $validated['inspection_item_id'];

        $itemType = match($scope) {
            'kendaraan' => Vehicle::class,
            'elektronik' => $this->resolveElectronicsType($itemId),
            'camping' => CampingEquipment::class,
            default => Vehicle::class,
        };

        if ($scope === 'elektronik') {
            $itemType = $this->resolveElectronicsType($itemId);
        }

        $vehicleId = null;
        if ($scope === 'kendaraan') {
            $vehicleId = $itemId;
        }

        $data = [
            'booking_id' => $booking->id,
            'vehicle_id' => $vehicleId,
            'inspector_id' => Auth::id(),
            'type' => $validated['type'],
            'scope' => $scope,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'overall_condition' => $validated['overall_condition'] ?? null,
            'exterior_condition' => $validated['exterior_condition'] ?? null,
            'interior_condition' => $validated['interior_condition'] ?? null,
            'engine_condition' => $validated['engine_condition'] ?? null,
            'tire_condition' => $validated['tire_condition'] ?? null,
            'brake_condition' => $validated['brake_condition'] ?? null,
            'electrical_condition' => $validated['electrical_condition'] ?? null,
            'fuel_level' => $validated['fuel_level'] ?? null,
            'odometer_reading' => $validated['odometer_reading'] ?? null,
            'usage_duration_hours' => $validated['usage_duration_hours'] ?? null,
            'damage_items' => $validated['damage_items'] ?? null,
            'completeness' => $validated['completeness'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'recommendations' => $validated['recommendations'] ?? null,
        ];

        Inspection::create($data);

        return redirect()->route('inspections.index')->with('success', 'Inspeksi berhasil dicatat');
    }

    public function show(Inspection $inspection)
    {
        $inspection->load(['booking', 'vehicle', 'inspector']);
        return view('inspections.show', compact('inspection'));
    }

    private function resolveElectronicsType(int $itemId): string
    {
        if (Phone::find($itemId)) return Phone::class;
        if (Camera::find($itemId)) return Camera::class;
        if (Playstation::find($itemId)) return Playstation::class;
        if (Drone::find($itemId)) return Drone::class;
        if (MusicalInstrument::find($itemId)) return MusicalInstrument::class;
        return Phone::class;
    }
}
