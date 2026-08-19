<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['vehicle', 'driver.user', 'category', 'user']);

        if (Auth::user()->role === 'user') {
            $query->where('user_id', Auth::id());
        } elseif (Auth::user()->role === 'driver') {
            $driver = \App\Models\Driver::where('user_id', Auth::id())->first();
            if ($driver) {
                $query->where('driver_id', $driver->id);
            }
        } elseif (Auth::user()->role === 'owner') {
            $query->where(function ($q) {
                $q->whereHas('vehicle', fn($vq) => $vq->where('owner_id', Auth::id()))
                  ->orWhere('item_id', '!=', null);
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15);

        return view('bookings.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $vehicle = Vehicle::with(['category', 'owner'])->where('slug', $request->vehicle)->orWhere('id', $request->vehicle)->firstOrFail();

        if ($vehicle->status !== 'available') {
            return back()->with('error', 'Kendaraan ini sedang tidak tersedia');
        }

        $drivers = Driver::where('status', 'off_duty')->with('user')->get();

        return view('bookings.create', compact('vehicle', 'drivers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'rental_type' => 'required|in:hourly,daily,weekly,monthly',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pickup_location' => 'nullable|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'with_driver' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'ktp_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        if ($vehicle->status !== 'available') {
            return back()->with('error', 'Kendaraan tidak tersedia')->withInput();
        }

        $basePrice = $vehicle->getPriceForType($validated['rental_type']);
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);

        if ($validated['rental_type'] === 'hourly') {
            $hours = max(1, $startDate->diffInHours($endDate));
            $totalPrice = $vehicle->hourly_price * $hours;
            $days = 0;
        } else {
            $days = max(1, $startDate->diffInDays($endDate));
            $totalPrice = $basePrice * $days;
        }

        $driverPrice = 0;
        if (!empty($validated['with_driver']) && $vehicle->with_driver) {
            $driverDays = max(1, $startDate->diffInDays($endDate));
            $driverPrice = ($vehicle->with_driver_daily_price ?? 0) * $driverDays;
        }

        $finalPrice = $totalPrice + $driverPrice;

        $ktpPath = null;
        if ($request->hasFile('ktp_photo')) {
            $ktpPath = $request->file('ktp_photo')->store('ktp', 'public');
        }

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => Auth::id(),
            'vehicle_id' => $vehicle->id,
            'driver_id' => null,
            'category_id' => $vehicle->category_id,
            'rental_type' => $validated['rental_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'pickup_location' => $validated['pickup_location'] ?? null,
            'dropoff_location' => $validated['dropoff_location'] ?? null,
            'with_driver' => $validated['with_driver'] ?? false,
            'base_price' => $totalPrice,
            'driver_price' => $driverPrice,
            'total_price' => $totalPrice,
            'discount' => 0,
            'final_price' => $finalPrice,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => $validated['notes'] ?? null,
            'ktp_photo' => $ktpPath,
        ]);

        $vehicle->update(['status' => 'reserved']);

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking berhasil dibuat! Kode booking: ' . $booking->booking_code);
    }

    public function show(Booking $booking)
    {
        $booking->load(['vehicle', 'driver.user', 'category', 'user', 'invoice', 'tripReport', 'inspection', 'payments']);

        return view('bookings.show', compact('booking'));
    }

    public function uploadKtp(Request $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'ktp_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $path = $request->file('ktp_photo')->store('ktp', 'public');
        $booking->update(['ktp_photo' => $path]);

        return back()->with('success', 'Foto KTP berhasil diunggah');
    }

    public function confirm(Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Booking tidak dapat dikonfirmasi');
        }

        $booking->update(['status' => 'confirmed']);
        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'reserved']);
        }

        return back()->with('success', 'Booking berhasil dikonfirmasi');
    }

    public function cancel(Booking $booking)
    {
        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Booking tidak dapat dibatalkan');
        }

        $booking->update(['status' => 'cancelled']);
        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'available']);
        }

        return back()->with('success', 'Booking berhasil dibatalkan');
    }

    public function startTrip(Booking $booking)
    {
        if ($booking->status !== 'confirmed') {
            return back()->with('error', 'Booking harus dikonfirmasi dulu');
        }

        $booking->update(['status' => 'ongoing', 'actual_start_date' => now()]);
        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'rented']);
        }

        return back()->with('success', 'Perjalanan dimulai');
    }

    public function complete(Booking $booking)
    {
        if ($booking->status !== 'ongoing') {
            return back()->with('error', 'Perjalanan belum dimulai');
        }

        $booking->update(['status' => 'completed', 'actual_end_date' => now()]);
        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'available']);
        }

        if ($booking->driver_id) {
            \App\Models\Driver::where('id', $booking->driver_id)->update(['status' => 'off_duty']);
        }

        return back()->with('success', 'Perjalanan selesai');
    }
}
