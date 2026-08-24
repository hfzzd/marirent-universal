<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\User;
use App\Models\Camera;
use App\Models\Phone;
use App\Models\CampingEquipment;
use App\Models\VehicleReplacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        $swappableVehicles = collect();
        if ($booking->vehicle_id
            && in_array($booking->status, ['confirmed', 'ongoing'])
            && in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            $swappableVehicles = Vehicle::with('category')
                ->where('status', 'available')
                ->where('is_active', true)
                ->where('id', '!=', $booking->vehicle_id)
                ->orderBy('name')
                ->get();
        }

        return view('bookings.show', compact('booking', 'swappableVehicles'));
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

    public function manualCreate()
    {
        $customers = User::where('role', 'user')->orderBy('name')->get();
        $vehicles = Vehicle::with('category')->whereIn('status', ['available'])->where('is_active', true)->orderBy('name')->get();
        $cameras = Camera::whereIn('status', ['available'])->where('is_active', true)->orderBy('name')->get();
        $phones = Phone::whereIn('status', ['available'])->where('is_active', true)->orderBy('name')->get();
        $campings = CampingEquipment::whereIn('status', ['available'])->where('is_active', true)->orderBy('name')->get();
        $drivers = Driver::with('user')->get()->sortBy(fn($d) => $d->user->name ?? '')->values();

        return view('bookings.manual-create', compact(
            'customers', 'vehicles', 'cameras', 'phones', 'campings', 'drivers'
        ));
    }

    public function manualStore(Request $request)
    {
        $validated = $request->validate([
            'customer_mode' => 'required|in:existing,new',
            'user_id' => 'required_if:customer_mode,existing|nullable|exists:users,id',
            'guest_name' => 'required_if:customer_mode,new|nullable|string|max:255',
            'guest_phone' => 'required_if:customer_mode,new|nullable|string|max:20',
            'item_kind' => 'required|in:mobil,motor,kamera,hp,tenda',
            'item_id' => 'required|integer|min:1',
            'rental_type' => 'required|in:hourly,daily,weekly,monthly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pickup_location' => 'nullable|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'driver_id' => 'nullable|exists:drivers,id',
            'discount' => 'nullable|numeric|min:0',
            'payment_due_date' => 'nullable|date',
            'payment_status' => 'required|in:unpaid,partial,paid',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Resolusi pelanggan: pakai akun lama atau buat walk-in baru
        if ($validated['customer_mode'] === 'existing') {
            $userId = $validated['user_id'];
        } else {
            $email = 'walkin.' . strtolower(preg_replace('/[^a-z0-9]/i', '', $validated['guest_name'])) . Str::random(4) . '@marirent.local';
            $user = User::create([
                'name' => $validated['guest_name'],
                'email' => $email,
                'password' => Hash::make(Str::random(16)),
                'phone' => $validated['guest_phone'],
                'role' => 'user',
                'is_active' => true,
            ]);
            $userId = $user->id;
        }

        // Resolusi item sewa
        $categoryId = null;
        $itemType = null;
        $itemId = null;
        $vehicle = null;
        $basePrice = 0;
        $dailyRate = 0;
        $hourlyRate = 0;
        $allowDriver = false;

        switch ($validated['item_kind']) {
            case 'mobil':
            case 'motor':
                $categorySlug = $validated['item_kind'] === 'mobil' ? 'mobil' : 'motor';
                $query = Vehicle::where('id', $validated['item_id'])
                    ->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
                $vehicle = $query->first();
                if (!$vehicle) {
                    return back()->with('error', 'Kendaraan tidak ditemukan')->withInput();
                }
                if ($vehicle->status !== 'available') {
                    return back()->with('error', 'Kendaraan sedang tidak tersedia')->withInput();
                }
                $categoryId = $vehicle->category_id;
                $dailyRate = (float) $vehicle->daily_price;
                $hourlyRate = (float) $vehicle->hourly_price;
                $allowDriver = (bool) $vehicle->with_driver;
                break;

            case 'kamera':
                $item = Camera::find($validated['item_id']);
                $itemType = Camera::class;
                break;
            case 'hp':
                $item = Phone::find($validated['item_id']);
                $itemType = Phone::class;
                break;
            case 'tenda':
                $item = CampingEquipment::find($validated['item_id']);
                $itemType = CampingEquipment::class;
                break;
        }

        if ($itemType) {
            if (!$item || !in_array($item->status, ['available'])) {
                return back()->with('error', 'Barang sedang tidak tersedia')->withInput();
            }
            $categoryId = $item->category_id;
            $itemId = $item->id;
            $dailyRate = (float) $item->daily_price;
            $hourlyRate = (float) $item->hourly_price;
        }

        // Hitung biaya
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);

        if ($validated['rental_type'] === 'hourly') {
            $hours = max(1, $startDate->diffInHours($endDate));
            $totalPrice = $hourlyRate > 0 ? $hourlyRate * $hours : $dailyRate * max(1, (int) ceil($hours / 24));
        } else {
            $rate = match ($validated['rental_type']) {
                'weekly' => $this->weeklyRateFor($itemType ? $item : $vehicle),
                'monthly' => $this->monthlyRateFor($itemType ? $item : $vehicle),
                default => $dailyRate,
            };
            $days = max(1, $startDate->diffInDays($endDate));
            $units = match ($validated['rental_type']) {
                'weekly' => (int) ceil($days / 7),
                'monthly' => (int) ceil($days / 30),
                default => $days,
            };
            $totalPrice = $rate * $units;
        }

        // Driver (hanya kendaraan)
        $driverId = null;
        $driverPrice = 0;
        $withDriver = false;
        if ($vehicle && !empty($validated['driver_id']) && $allowDriver) {
            $driver = Driver::find($validated['driver_id']);
            if ($driver) {
                $driverId = $driver->id;
                $withDriver = true;
                $driverDays = max(1, $startDate->diffInDays($endDate));
                $driverPrice = ($vehicle->with_driver_daily_price ?? 0) * $driverDays;
            }
        }

        $discount = min((float) ($validated['discount'] ?? 0), $totalPrice + $driverPrice);
        $finalPrice = max(0, $totalPrice + $driverPrice - $discount);

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $userId,
            'vehicle_id' => $vehicle?->id,
            'driver_id' => $driverId,
            'category_id' => $categoryId,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'rental_type' => $validated['rental_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'pickup_location' => $validated['pickup_location'] ?? null,
            'dropoff_location' => $validated['dropoff_location'] ?? null,
            'with_driver' => $withDriver,
            'base_price' => $totalPrice,
            'driver_price' => $driverPrice,
            'total_price' => $totalPrice,
            'discount' => $discount,
            'final_price' => $finalPrice,
            'status' => 'confirmed',
            'payment_status' => $validated['payment_status'],
            'payment_due_date' => $validated['payment_due_date'] ?? now()->addDays(2)->toDateString(),
            'source' => 'manual',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Kunci unit
        if ($vehicle) {
            $vehicle->update(['status' => 'reserved']);
        } elseif ($itemType && $itemId) {
            $item->update(['status' => 'reserved']);
        }

        if ($driverId) {
            Driver::where('id', $driverId)->update(['status' => 'on_trip']);
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking manual berhasil dibuat! Kode: ' . $booking->booking_code);
    }

    private function weeklyRateFor($model): float
    {
        return (float) ($model->weekly_price ?: $model->daily_price * 7);
    }

    private function monthlyRateFor($model): float
    {
        return (float) ($model->monthly_price ?: $model->daily_price * 30);
    }

    public function replaceVehicle(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'replacement_vehicle_id' => 'required|exists:vehicles,id',
            'reason' => 'nullable|string|max:500',
        ]);

        if (!$booking->vehicle_id || !in_array($booking->status, ['confirmed', 'ongoing'])) {
            return back()->with('error', 'Penggantian hanya untuk booking kendaraan yang aktif');
        }

        if ((int) $validated['replacement_vehicle_id'] === (int) $booking->vehicle_id) {
            return back()->with('error', 'Kendaraan pengganti harus berbeda dari kendaraan saat ini');
        }

        $newVehicle = Vehicle::findOrFail($validated['replacement_vehicle_id']);
        if ($newVehicle->status !== 'available') {
            return back()->with('error', 'Kendaraan pengganti tidak tersedia');
        }

        $oldVehicle = $booking->vehicle;
        $days = max(1, $booking->start_date->diffInDays($booking->end_date));
        $priceDiff = max(0, ((float) $newVehicle->daily_price - (float) $oldVehicle->daily_price) * $days);

        // Langsung tukar unit
        $oldVehicle->update(['status' => 'available']);
        $newVehicle->update(['status' => $booking->status === 'ongoing' ? 'rented' : 'reserved']);

        $booking->update([
            'vehicle_id' => $newVehicle->id,
            'final_price' => (float) $booking->final_price + $priceDiff,
        ]);

        // Catat riwayat penggantian sebagai sudah disetujui
        VehicleReplacement::create([
            'booking_id' => $booking->id,
            'original_vehicle_id' => $oldVehicle->id,
            'replacement_vehicle_id' => $newVehicle->id,
            'requested_by' => Auth::id(),
            'approved_by' => Auth::id(),
            'status' => 'approved',
            'reason' => $validated['reason'] ?? 'Penggantian langsung oleh operator',
            'price_difference' => $priceDiff,
        ]);

        return back()->with('success', "Kendaraan diganti ke {$newVehicle->name}" . ($priceDiff > 0 ? ' (selisih Rp ' . number_format($priceDiff, 0, ',', '.') . ')' : ''));
    }
}
