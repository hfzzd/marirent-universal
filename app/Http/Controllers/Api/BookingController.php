<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Booking::with(['vehicle', 'driver.user', 'category']);

        if ($user->role === 'user') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'driver') {
            $driver = Driver::where('user_id', $user->id)->first();
            if ($driver) {
                $query->where('driver_id', $driver->id);
            }
        } elseif ($user->role === 'owner') {
            $query->whereHas('vehicle', fn($q) => $q->where('owner_id', $user->id));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(min($request->get('per_page', 15), 50));

        return response()->json(['success' => true, 'data' => $bookings]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'rental_type' => 'required|in:hourly,daily,weekly,monthly',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'pickup_location' => 'nullable|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'with_driver' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        if ($vehicle->status !== 'available') {
            return response()->json([
                'success' => false, 'message' => 'Kendaraan tidak tersedia',
            ], 422);
        }

        $basePrice = $vehicle->getPriceForType($validated['rental_type']);
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);

        $days = max(1, $startDate->diffInDays($endDate));
        if ($validated['rental_type'] === 'hourly') {
            $hours = max(1, $startDate->diffInHours($endDate));
            $totalPrice = $vehicle->hourly_price * $hours;
        } else {
            $totalPrice = $basePrice * $days;
        }

        $driverPrice = 0;
        if (!empty($validated['with_driver']) && $vehicle->with_driver) {
            $driverPrice = ($vehicle->with_driver_daily_price ?? 0) * $days;
        }

        $finalPrice = $totalPrice + $driverPrice;

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $request->user()->id,
            'vehicle_id' => $vehicle->id,
            'driver_id' => null,
            'category_id' => $vehicle->category_id,
            'item_type' => null,
            'item_id' => null,
            'rental_type' => $validated['rental_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'pickup_location' => $validated['pickup_location'] ?? null,
            'dropoff_location' => $validated['dropoff_location'] ?? null,
            'with_driver' => $validated['with_driver'] ?? false,
            'base_price' => $totalPrice,
            'driver_price' => $driverPrice,
            'total_price' => $totalPrice,
            'final_price' => $finalPrice,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => $validated['notes'] ?? null,
        ]);

        $vehicle->update(['status' => 'reserved']);

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibuat',
            'data' => $booking->load(['vehicle', 'category']),
        ], 201);
    }

    public function show(string $bookingCode)
    {
        $booking = Booking::with(['vehicle', 'driver.user', 'category', 'invoice', 'tripReport', 'inspection'])
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $booking]);
    }

    public function confirm(Request $request, string $bookingCode)
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        if ($booking->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Booking tidak dapat dikonfirmasi'], 422);
        }

        $booking->update([
            'status' => 'confirmed',
            'driver_id' => $request->get('driver_id'),
        ]);

        if ($request->get('driver_id')) {
            Driver::where('id', $request->get('driver_id'))->update(['status' => 'on_trip']);
        }

        $booking->vehicle->update(['status' => 'reserved']);

        return response()->json([
            'success' => true, 'message' => 'Booking dikonfirmasi', 'data' => $booking,
        ]);
    }

    public function startTrip(Request $request, string $bookingCode)
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        if ($booking->status !== 'confirmed') {
            return response()->json(['success' => false, 'message' => 'Booking harus dikonfirmasi dulu'], 422);
        }

        $booking->update([
            'status' => 'ongoing',
            'actual_start_date' => now(),
        ]);
        $booking->vehicle->update(['status' => 'rented']);

        return response()->json([
            'success' => true, 'message' => 'Perjalanan dimulai', 'data' => $booking,
        ]);
    }

    public function complete(string $bookingCode)
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        if ($booking->status !== 'ongoing') {
            return response()->json(['success' => false, 'message' => 'Perjalanan belum dimulai'], 422);
        }

        $booking->update([
            'status' => 'completed',
            'actual_end_date' => now(),
        ]);
        $booking->vehicle->update(['status' => 'available']);

        if ($booking->driver_id) {
            Driver::where('id', $booking->driver_id)->update(['status' => 'off_duty']);
        }

        return response()->json([
            'success' => true, 'message' => 'Perjalanan selesai', 'data' => $booking,
        ]);
    }

    public function cancel(Request $request, string $bookingCode)
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return response()->json(['success' => false, 'message' => 'Booking tidak dapat dibatalkan'], 422);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->get('reason', 'Dibatalkan oleh pengguna'),
        ]);
        $booking->vehicle->update(['status' => 'available']);

        return response()->json([
            'success' => true, 'message' => 'Booking dibatalkan', 'data' => $booking,
        ]);
    }
}
