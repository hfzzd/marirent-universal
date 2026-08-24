<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InspectionWebController extends Controller
{
    private function detectScope(?Booking $booking): string
    {
        $slug = $booking?->category?->slug ?? '';

        return match (true) {
            in_array($slug, ['sewa-kamera', 'sewa-hp']) => 'elektronik',
            $slug === 'sewa-tenda' => 'camping',
            default => 'kendaraan',
        };
    }

    public function index(Request $request)
    {
        $query = Inspection::with(['booking', 'vehicle', 'inspector', 'item']);

        if ($request->booking_id) {
            $query->where('booking_id', $request->booking_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->scope) {
            $query->where('scope', $request->scope);
        }

        if (Auth::user()->role === 'inspector') {
            // Inspector tetap bisa melihat semua, tapi miliknya ditandai di view
        }

        $inspections = $query->latest()->paginate(15);

        return view('inspections.index', compact('inspections'));
    }

    public function create(Request $request)
    {
        $bookings = Booking::with(['user', 'vehicle.category', 'category', 'item'])
            ->whereIn('status', ['confirmed', 'ongoing'])
            ->orderByDesc('start_date')
            ->get();

        $booking = $request->booking_id ? Booking::with(['vehicle.category', 'category', 'item'])->find($request->booking_id) : null;

        return view('inspections.create', [
            'bookings' => $bookings,
            'booking' => $booking,
            'scope' => $this->detectScope($booking),
            'damageOptions' => Inspection::DAMAGE_OPTIONS,
            'completenessOptions' => Inspection::COMPLETENESS_OPTIONS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'type' => 'required|in:pre_rental,post_rental',
            'overall_condition' => 'required|integer|min:1|max:10',
            'exterior_condition' => 'nullable|integer|min:1|max:10',
            'interior_condition' => 'nullable|integer|min:1|max:10',
            'engine_condition' => 'nullable|integer|min:1|max:10',
            'tire_condition' => 'nullable|integer|min:1|max:10',
            'brake_condition' => 'nullable|integer|min:1|max:10',
            'electrical_condition' => 'nullable|integer|min:1|max:10',
            'fuel_level' => 'nullable|numeric|min:0|max:100',
            'odometer_reading' => 'nullable|numeric|min:0',
            'usage_duration_hours' => 'nullable|numeric|min:0',
            'damage_items' => 'nullable|array',
            'completeness' => 'nullable|array',
            'notes' => 'nullable|string|max:2000',
            'recommendations' => 'nullable|string|max:2000',
            'photos' => 'nullable|array|max:8',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        $scope = $this->detectScope($booking);

        // Lama pemakaian: pakai input manual, atau hitung otomatis dari tanggal sewa
        if (!empty($validated['usage_duration_hours'])) {
            $durationHours = (int) round($validated['usage_duration_hours']);
        } else {
            $start = $booking->actual_start_date ?? $booking->start_date;
            $end = $booking->actual_end_date ?? ($booking->status === 'ongoing' ? now() : $booking->end_date);
            $durationHours = max(1, (int) ceil($start->diffInHours($end)));
        }

        // Foto bukti
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('inspections', 'public');
            }
        }

        $isVehicle = $scope === 'kendaraan';

        Inspection::create([
            'booking_id' => $booking->id,
            'vehicle_id' => $booking->vehicle_id,
            'item_type' => $booking->item_type,
            'item_id' => $booking->item_id,
            'inspector_id' => Auth::id(),
            'type' => $validated['type'],
            'scope' => $scope,
            'exterior_condition' => $isVehicle ? ($validated['exterior_condition'] ?? 5) : null,
            'interior_condition' => $isVehicle ? ($validated['interior_condition'] ?? 5) : null,
            'engine_condition' => $isVehicle ? ($validated['engine_condition'] ?? 5) : null,
            'tire_condition' => $isVehicle ? ($validated['tire_condition'] ?? 5) : null,
            'brake_condition' => $isVehicle ? ($validated['brake_condition'] ?? 5) : null,
            'electrical_condition' => $isVehicle ? ($validated['electrical_condition'] ?? 5) : null,
            'overall_condition' => $validated['overall_condition'],
            'fuel_level' => $isVehicle ? ($validated['fuel_level'] ?? 100) : null,
            'odometer_reading' => $isVehicle ? ($validated['odometer_reading'] ?? null) : null,
            'usage_duration_hours' => $durationHours,
            'damages' => null,
            'damage_items' => $validated['damage_items'] ?? [],
            'completeness' => !$isVehicle ? ($validated['completeness'] ?? []) : null,
            'photos' => $photoPaths ?: null,
            'notes' => $validated['notes'] ?? null,
            'recommendations' => $validated['recommendations'] ?? null,
        ]);

        return redirect()->route('inspections.index')->with('success', 'Data inspeksi berhasil dicatat');
    }

    public function show(Inspection $inspection)
    {
        $inspection->load(['booking.vehicle.category', 'booking.user', 'vehicle', 'inspector', 'item']);

        return view('inspections.show', [
            'inspection' => $inspection,
            'damageOptions' => Inspection::DAMAGE_OPTIONS,
        ]);
    }
}
