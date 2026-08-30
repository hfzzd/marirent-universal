<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Booking;
use App\Models\Driver;
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
        $query = Inspection::with(['booking', 'vehicle', 'inspector', 'reportedBy', 'assignedTo']);
        $user = Auth::user();

        if ($user->isInspector()) {
            $query->where(fn($q) => $q->where('inspector_id', $user->id)->orWhere('assigned_to', $user->id));
        } elseif ($user->isMerchantStaff()) {
            $merchantId = $user->merchantId();
            $categoryId = $user->merchantCategoryId();
            $query->whereHas('vehicle', fn($vq) => $vq->where('owner_id', $merchantId)->when($categoryId, fn($q) => $q->where('category_id', $categoryId)))
                ->orWhereHas('booking.vehicle', fn($vq) => $vq->where('owner_id', $merchantId)->when($categoryId, fn($q) => $q->where('category_id', $categoryId)));
        } elseif ($user->isDriver()) {
            $driver = Driver::where('user_id', $user->id)->first();
            if ($driver) {
                $query->whereHas('booking', fn($bq) => $bq->where('driver_id', $driver->id));
            }
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

        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Driver melihat laporannya sendiri, sisanya lihat semua sesuai merchant
        $inspections = $query->latest()->paginate(15);

        return view('inspections.index', compact('inspections'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        $bookings = Booking::whereIn('status', ['confirmed', 'ongoing'])
            ->with(['user', 'vehicle', 'category', 'bookingItems'])
            ->get();

        if ($user->isDriver()) {
            $driver = Driver::where('user_id', $user->id)->first();
            $bookings = $bookings->where('driver_id', $driver?->id)->values();
        } elseif ($user->isMerchantStaff()) {
            $bookings = fn() => Booking::whereIn('status', ['confirmed', 'ongoing'])
                ->forMerchantCategory($user->merchantId(), $user->merchantCategoryId())
                ->with(['user', 'vehicle', 'category', 'bookingItems'])
                ->get();
            $bookings = $bookings();
        }

        $booking = $request->booking_id ? Booking::with(['vehicle', 'bookingItems'])->find($request->booking_id) : null;
        if ($booking && $user->isMerchantStaff() && !$this->bookingForMerchant($booking, $user)) {
            abort(403);
        }

        $vehicles = $this->vehiclesForUser($user);
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
        $user = Auth::user();

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

        if ($user->isMerchantStaff() && !$this->bookingForMerchant($booking, $user)) {
            abort(403);
        }

        $scope = $validated['scope'];
        $itemId = $validated['inspection_item_id'];

        $itemType = match($scope) {
            'kendaraan' => Vehicle::class,
            'elektronik' => $this->resolveElectronicsType($itemId),
            'camping' => CampingEquipment::class,
            default => Vehicle::class,
        };

        $vehicleId = null;
        if ($scope === 'kendaraan') {
            $vehicleId = $itemId;
        }

        $isDriver = $user->isDriver();

        $data = [
            'booking_id' => $booking->id,
            'vehicle_id' => $vehicleId,
            'inspector_id' => $isDriver ? null : $user->id,
            'reported_by' => $isDriver ? $user->id : null,
            'status' => $isDriver ? 'reported' : 'open',
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

        $inspection = Inspection::create($data);

        if ($isDriver && $vehicleId) {
            $vehicle = Vehicle::find($vehicleId);
            if ($vehicle && $vehicle->condition === 'excellent' && !empty($validated['damage_items'])) {
                $vehicle->update(['condition' => 'good']);
            }
        }

        if ($isDriver) {
            return redirect()->route('inspections.show', $inspection)->with('success', 'Laporan inspeksi driver berhasil dikirim ke inspector.');
        }

        return redirect()->route('inspections.index')->with('success', 'Inspeksi berhasil dicatat');
    }

    public function start(Inspection $inspection)
    {
        $user = Auth::user();
        $this->authorizeInspector($inspection);

        if ($inspection->status === 'completed') {
            return back()->with('error', 'Inspeksi sudah selesai');
        }

        $inspection->update([
            'status' => 'processing',
            'assigned_to' => $user->id,
            'inspector_id' => $inspection->inspector_id ?: $user->id,
        ]);

        return back()->with('success', 'Inspeksi sedang dikerjakan inspector');
    }

    public function complete(Request $request, Inspection $inspection)
    {
        $user = Auth::user();
        $this->authorizeInspector($inspection);

        if ($inspection->status === 'completed') {
            return back()->with('error', 'Inspeksi sudah selesai');
        }

        $validated = $request->validate([
            'resolution_notes' => 'nullable|string|max:2000',
            'completeness' => 'nullable|array',
            'completeness.*' => 'string|max:255',
        ]);

        $inspection->update([
            'status' => 'completed',
            'assigned_to' => $inspection->assigned_to ?: $user->id,
            'inspector_id' => $inspection->inspector_id ?: $user->id,
            'resolution_notes' => $validated['resolution_notes'] ?? null,
            'completeness' => $validated['completeness'] ?? $inspection->completeness,
        ]);

        return back()->with('success', 'Inspeksi selesai dikerjakan');
    }

    public function show(Inspection $inspection)
    {
        $inspection->load(['booking', 'booking.user', 'booking.vehicle', 'vehicle', 'inspector', 'reportedBy', 'assignedTo']);

        if (Auth::user()->isMerchantStaff() && !$this->inspectionForMerchant($inspection, Auth::user())) {
            abort(403);
        }

        return view('inspections.show', compact('inspection'));
    }

    private function authorizeInspector(Inspection $inspection): void
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isMerchantStaff() && !$user->isInspector()) {
            abort(403);
        }
        if ($user->isMerchantStaff() && !$this->inspectionForMerchant($inspection, $user)) {
            abort(403);
        }
    }

    private function inspectionForMerchant(Inspection $inspection, $user): bool
    {
        $merchantId = $user->merchantId();
        $categoryId = $user->merchantCategoryId();

        if (!$merchantId) {
            return true;
        }

        $matchesMerchant = false;
        if ($inspection->vehicle) {
            $matchesMerchant = (int) $inspection->vehicle->owner_id === $merchantId;
        } elseif ($inspection->booking?->vehicle) {
            $matchesMerchant = (int) $inspection->booking->vehicle->owner_id === $merchantId;
        } else {
            return true;
        }

        if (!$matchesMerchant) {
            return false;
        }

        if ($categoryId) {
            $vehicle = $inspection->vehicle ?? $inspection->booking?->vehicle;
            return $vehicle && (int) $vehicle->category_id === $categoryId;
        }

        return true;
    }

    private function bookingForMerchant(Booking $booking, $user): bool
    {
        $merchantId = $user->merchantId();
        $categoryId = $user->merchantCategoryId();

        if (!$merchantId) {
            return true;
        }

        if ($categoryId && !$booking->belongsToCategory($categoryId)) {
            return false;
        }

        if ($booking->vehicle) {
            return (int) $booking->vehicle->owner_id === $merchantId;
        }
        if ($booking->item_id && $booking->item_type) {
            $item = $booking->item;
            return $item && (int) $item->owner_id === $merchantId;
        }

        foreach ($booking->childBookings as $child) {
            if ($this->bookingForMerchant($child, $user)) {
                return true;
            }
        }

        return true;
    }

    private function vehiclesForUser($user)
    {
        $query = Vehicle::where('status', '!=', 'maintenance');
        if ($user->isMerchantStaff()) {
            $query->where('owner_id', $user->merchantId());
        }
        if ($categoryId = $user->merchantCategoryId()) {
            $query->where('category_id', $categoryId);
        }
        return $query->get();
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