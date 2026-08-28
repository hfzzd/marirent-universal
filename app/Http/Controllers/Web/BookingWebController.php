<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;
use App\Models\Playstation;
use App\Models\Drone;
use App\Models\MusicalInstrument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['vehicle', 'driver.user', 'category', 'user', 'bookingItems']);

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
            'with_driver' => 'nullable',
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
        } else {
            $days = max(1, $startDate->diffInDays($endDate));
            $totalPrice = $basePrice * $days;
        }

        $withDriver = ($validated['with_driver'] ?? '0') === '1';
        $driverPrice = 0;
        if ($withDriver && $vehicle->with_driver) {
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
            'item_type' => null,
            'item_id' => null,
            'rental_type' => $validated['rental_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'pickup_location' => $validated['pickup_location'] ?? null,
            'dropoff_location' => $validated['dropoff_location'] ?? null,
            'with_driver' => $withDriver,
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

        // Notify superadmin and owner about new booking
        $admins = User::whereIn('role', ['superadmin', 'owner'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\BookingCreated($booking));
        }

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking berhasil dibuat! Kode booking: ' . $booking->booking_code);
    }

    public function createItem(Request $request, string $type, string $item)
    {
        $config = $this->getItemConfig($type);
        $item = $this->getItemModel($type)::where('slug', $item)->orWhere('id', $item)->firstOrFail();

        if ($item->status !== 'available') {
            return back()->with('error', 'Item ini sedang tidak tersedia');
        }

        $insuranceRate = round($item->daily_price * 0.05);
        $depositAmount = round($item->daily_price * 0.3);

        return view('bookings.create-item', compact('item', 'type', 'config', 'insuranceRate', 'depositAmount'));
    }

    public function storeItem(Request $request, string $type)
    {
        $validated = $request->validate([
            'item_id' => 'required|integer',
            'rental_type' => 'required|in:hourly,daily,weekly,monthly',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after_or_equal:start_date',
            'urgency' => 'required|in:normal,urgent,very_urgent',
            'with_insurance' => 'required|in:0,1',
            'accessories' => 'nullable|array',
            'accessories.*' => 'string',
            'payment_plan' => 'required|in:full,dp50',
            'notes' => 'nullable|string|max:1000',
            'ktp_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $config = $this->getItemConfig($type);
        $item = $this->getItemModel($type)::findOrFail($validated['item_id']);

        if ($item->status !== 'available') {
            return back()->with('error', 'Item tidak tersedia')->withInput();
        }

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $duration = $validated['rental_type'] === 'hourly'
            ? max(1, $startDate->diffInHours($endDate))
            : max(1, $startDate->diffInDays($endDate));

        $basePrice = $item->getPriceForType($validated['rental_type']);
        $subtotal = $basePrice * $duration;

        $insuranceRate = round($item->daily_price * 0.05);
        $insuranceFee = $validated['with_insurance'] ? $insuranceRate * $duration : 0;

        $accessoriesCost = 0;
        $accessoriesData = $config['accessories'] ?? [];
        $selectedAccessories = $validated['accessories'] ?? [];
        foreach ($selectedAccessories as $accName) {
            foreach ($accessoriesData as $acc) {
                if ($acc['name'] === $accName) {
                    $accessoriesCost += $acc['price'] * $duration;
                    break;
                }
            }
        }

        $urgencyMultiplier = match($validated['urgency']) {
            'urgent' => 0.10,
            'very_urgent' => 0.20,
            default => 0,
        };
        $urgencyFee = round($subtotal * $urgencyMultiplier);

        $depositAmount = round($item->daily_price * 0.3);

        $totalPrice = $subtotal + $insuranceFee + $accessoriesCost + $urgencyFee + $depositAmount;

        $ktpPath = $request->file('ktp_photo')->store('ktp', 'public');

        $category = $item->category;

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => Auth::id(),
            'vehicle_id' => null,
            'driver_id' => null,
            'category_id' => $category?->id,
            'item_type' => get_class($item),
            'item_id' => $item->id,
            'rental_type' => $validated['rental_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'with_driver' => false,
            'base_price' => $subtotal,
            'driver_price' => 0,
            'deposit_amount' => $depositAmount,
            'insurance_fee' => $insuranceFee,
            'total_price' => $totalPrice,
            'discount' => 0,
            'final_price' => $totalPrice,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'accessories' => $selectedAccessories ?: null,
            'urgency' => $validated['urgency'],
            'with_insurance' => $validated['with_insurance'] === '1',
            'notes' => $validated['notes'] ?? null,
            'ktp_photo' => $ktpPath,
        ]);

        $item->update(['status' => 'reserved']);

        // Notify superadmin and owner about new item booking
        $admins = User::whereIn('role', ['superadmin', 'owner'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\BookingCreated($booking));
        }

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking berhasil dibuat! Kode booking: ' . $booking->booking_code);
    }

    private function getItemConfig(string $type): array
    {
        return match($type) {
            'hp' => [
                'icon' => 'fa-mobile-alt',
                'label' => 'Sewa HP',
                'model' => Phone::class,
                'subtitle' => fn($item) => $item->phone_model,
                'accessories' => [
                    ['name' => 'Casing', 'price' => 5000],
                    ['name' => 'Screen Protector', 'price' => 3000],
                    ['name' => 'Charger Tambahan', 'price' => 5000],
                    ['name' => 'Power Bank', 'price' => 10000],
                    ['name' => 'Earphone', 'price' => 5000],
                ],
            ],
            'kamera' => [
                'icon' => 'fa-camera',
                'label' => 'Sewa Kamera',
                'model' => Camera::class,
                'subtitle' => fn($item) => $item->camera_model,
                'accessories' => [
                    ['name' => 'Lens Tambahan', 'price' => 25000],
                    ['name' => 'Tripod', 'price' => 15000],
                    ['name' => 'Tas Kamera', 'price' => 10000],
                    ['name' => 'Memory Card 64GB', 'price' => 10000],
                    ['name' => 'Battery Extra', 'price' => 8000],
                    ['name' => 'Flash External', 'price' => 15000],
                ],
            ],
            'tenda' => [
                'icon' => 'fa-campground',
                'label' => 'Sewa Alat Camping',
                'model' => CampingEquipment::class,
                'subtitle' => fn($item) => $item->equipment_model,
                'accessories' => [
                    ['name' => 'Tas Carry', 'price' => 8000],
                    ['name' => 'Mounting/Tiang', 'price' => 10000],
                    ['name' => 'Lampu Tenda', 'price' => 5000],
                    ['name' => 'Sleeping Bag', 'price' => 15000],
                    ['name' => 'Matras', 'price' => 8000],
                ],
            ],
            'ps' => [
                'icon' => 'fa-gamepad',
                'label' => 'Sewa Playstation',
                'model' => Playstation::class,
                'subtitle' => fn($item) => $item->console_model,
                'accessories' => [
                    ['name' => 'Controller Tambahan', 'price' => 15000],
                    ['name' => 'Disk Game', 'price' => 20000],
                    ['name' => 'HDMI Tambahan', 'price' => 5000],
                    ['name' => 'TV/Proyektor Mini', 'price' => 30000],
                ],
            ],
            'drone' => [
                'icon' => 'fa-drone',
                'label' => 'Sewa Drone',
                'model' => Drone::class,
                'subtitle' => fn($item) => $item->drone_model,
                'accessories' => [
                    ['name' => 'Battery Extra', 'price' => 30000],
                    ['name' => 'Memory Card 128GB', 'price' => 15000],
                    ['name' => 'Filter ND', 'price' => 10000],
                    ['name' => 'Carry Case', 'price' => 10000],
                ],
            ],
            'musik' => [
                'icon' => 'fa-guitar',
                'label' => 'Sewa Alat Musik',
                'model' => MusicalInstrument::class,
                'subtitle' => fn($item) => $item->instrument_model,
                'accessories' => [
                    ['name' => 'Softcase', 'price' => 5000],
                    ['name' => 'Tuner', 'price' => 5000],
                    ['name' => 'Kabel & Ampli', 'price' => 15000],
                    ['name' => 'Pik/Senar Cadangan', 'price' => 3000],
                ],
            ],
            default => [
                'icon' => 'fa-box',
                'label' => 'Sewa Barang',
                'model' => Phone::class,
                'subtitle' => fn($item) => '-',
                'accessories' => [],
            ],
        };
    }

    private function getItemModel(string $type): string
    {
        return match($type) {
            'hp' => Phone::class,
            'kamera' => Camera::class,
            'tenda' => CampingEquipment::class,
            'ps' => Playstation::class,
            'drone' => Drone::class,
            'musik' => MusicalInstrument::class,
            default => Phone::class,
        };
    }

    public function createMulti()
    {
        $phones = Phone::where('status', 'available')->where('is_active', true)->with('category')->get();
        $cameras = Camera::where('status', 'available')->where('is_active', true)->with('category')->get();
        $equipments = CampingEquipment::where('status', 'available')->where('is_active', true)->with('category')->get();
        $playstations = Playstation::where('status', 'available')->where('is_active', true)->with('category')->get();
        $drones = Drone::where('status', 'available')->where('is_active', true)->with('category')->get();
        $instruments = MusicalInstrument::where('status', 'available')->where('is_active', true)->with('category')->get();

        return view('bookings.create-multi', compact('phones', 'cameras', 'equipments', 'playstations', 'drones', 'instruments'));
    }

    public function storeMulti(Request $request)
    {
        $validated = $request->validate([
            'rental_type' => 'required|in:hourly,daily,weekly,monthly',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after_or_equal:start_date',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:hp,kamera,tenda,ps,drone,musik',
            'items.*.id' => 'required|integer',
            'items.*.with_insurance' => 'required|in:0,1',
            'items.*.accessories' => 'nullable|array',
            'items.*.accessories.*' => 'string',
            'items.*.urgency' => 'required|in:normal,urgent,very_urgent',
            'payment_plan' => 'required|in:full,dp50',
            'notes' => 'nullable|string|max:1000',
            'ktp_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $duration = $validated['rental_type'] === 'hourly'
            ? max(1, $startDate->diffInHours($endDate))
            : max(1, $startDate->diffInDays($endDate));

        $ktpPath = $request->file('ktp_photo')->store('ktp', 'public');

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => Auth::id(),
            'vehicle_id' => null,
            'driver_id' => null,
            'category_id' => null,
            'item_type' => null,
            'item_id' => null,
            'rental_type' => $validated['rental_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'with_driver' => false,
            'base_price' => 0,
            'driver_price' => 0,
            'total_price' => 0,
            'discount' => 0,
            'final_price' => 0,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => $validated['notes'] ?? null,
            'ktp_photo' => $ktpPath,
        ]);

        $totalBookingPrice = 0;

        foreach ($validated['items'] as $itemData) {
            $model = $this->getItemModel($itemData['type']);
            $item = $model::findOrFail($itemData['id']);

            $config = $this->getItemConfig($itemData['type']);
            $basePrice = $item->getPriceForType($validated['rental_type']);
            $subtotal = $basePrice * $duration;

            $insuranceRate = round($item->daily_price * 0.05);
            $insuranceFee = $itemData['with_insurance'] ? $insuranceRate * $duration : 0;

            $accessoriesCost = 0;
            $accessoriesData = $config['accessories'] ?? [];
            $selectedAccessories = $itemData['accessories'] ?? [];
            foreach ($selectedAccessories as $accName) {
                foreach ($accessoriesData as $acc) {
                    if ($acc['name'] === $accName) {
                        $accessoriesCost += $acc['price'] * $duration;
                        break;
                    }
                }
            }

            $urgencyMultiplier = match($itemData['urgency']) {
                'urgent' => 0.10,
                'very_urgent' => 0.20,
                default => 0,
            };
            $urgencyFee = round($subtotal * $urgencyMultiplier);
            $depositAmount = round($item->daily_price * 0.3);

            $itemTotal = $subtotal + $insuranceFee + $accessoriesCost + $urgencyFee + $depositAmount;
            $totalBookingPrice += $itemTotal;

            Booking::create([
                'booking_code' => Booking::generateBookingCode(),
                'user_id' => Auth::id(),
                'vehicle_id' => null,
                'driver_id' => null,
                'category_id' => $item->category_id,
                'item_type' => get_class($item),
                'item_id' => $item->id,
                'rental_type' => $validated['rental_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'with_driver' => false,
                'base_price' => $subtotal,
                'driver_price' => 0,
                'deposit_amount' => $depositAmount,
                'insurance_fee' => $insuranceFee,
                'total_price' => $itemTotal,
                'discount' => 0,
                'final_price' => $itemTotal,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'accessories' => $selectedAccessories ?: null,
                'urgency' => $itemData['urgency'],
                'with_insurance' => $itemData['with_insurance'] === '1',
                'notes' => $validated['notes'] ?? null,
                'ktp_photo' => $ktpPath,
                'parent_booking_id' => $booking->id,
            ]);

            $item->update(['status' => 'reserved']);
        }

        $booking->update([
            'total_price' => $totalBookingPrice,
            'final_price' => $totalBookingPrice,
        ]);

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking multi-item berhasil dibuat! Kode: ' . $booking->booking_code);
    }

    public function manualCreate()
    {
        $customers = \App\Models\User::where('role', 'user')->orderBy('name')->get();
        $vehicles = Vehicle::with('category')->where('status', 'available')->get();
        $drivers = Driver::with('user')->get();
        $cameras = Camera::where('status', 'available')->get();
        $phones = Phone::where('status', 'available')->get();
        $campings = CampingEquipment::where('status', 'available')->get();
        $playstations = Playstation::where('status', 'available')->get();
        $drones = Drone::where('status', 'available')->get();
        $instruments = MusicalInstrument::where('status', 'available')->get();

        return view('bookings.manual-create', compact('customers', 'vehicles', 'drivers', 'cameras', 'phones', 'campings', 'playstations', 'drones', 'instruments'));
    }

    public function manualStore(Request $request)
    {
        $validated = $request->validate([
            'customer_mode' => 'required|in:existing,new',
            'user_id' => 'required_if:customer_mode,existing|nullable|exists:users,id',
            'guest_name' => 'required_if:customer_mode,new|nullable|string|max:255',
            'guest_phone' => 'required_if:customer_mode,new|nullable|string|max:20',
            'item_kind' => 'required|in:mobil,motor,kamera,hp,tenda,ps,drone,musik',
            'item_id' => 'required|integer',
            'rental_type' => 'required|in:hourly,daily,weekly,monthly',
            'driver_id' => 'nullable|exists:drivers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pickup_location' => 'nullable|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'payment_status' => 'required|in:unpaid,partial,paid',
            'payment_due_date' => 'nullable|date',
            'discount' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();

        if ($validated['customer_mode'] === 'new') {
            $guestEmail = 'walkin_' . strtolower(uniqid()) . '@marirent临时';
            $guestUser = \App\Models\User::create([
                'name' => $validated['guest_name'],
                'email' => $guestEmail,
                'phone' => $validated['guest_phone'],
                'password' => bcrypt('password123'),
                'role' => 'user',
            ]);
            $userId = $guestUser->id;
        } else {
            $userId = $validated['user_id'];
        }

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $duration = $validated['rental_type'] === 'hourly'
            ? max(1, $startDate->diffInHours($endDate))
            : max(1, $startDate->diffInDays($endDate));

        $itemId = $validated['item_id'];
        $itemKind = $validated['item_kind'];
        $itemModel = null;
        $item = null;
        $vehicleId = null;
        $itemType = null;

        if (in_array($itemKind, ['mobil', 'motor'])) {
            $item = Vehicle::findOrFail($itemId);
            $vehicleId = $item->id;
        } elseif ($itemKind === 'kamera') {
            $item = Camera::findOrFail($itemId);
            $itemType = Camera::class;
        } elseif ($itemKind === 'hp') {
            $item = Phone::findOrFail($itemId);
            $itemType = Phone::class;
        } elseif ($itemKind === 'tenda') {
            $item = CampingEquipment::findOrFail($itemId);
            $itemType = CampingEquipment::class;
        } elseif ($itemKind === 'ps') {
            $item = Playstation::findOrFail($itemId);
            $itemType = Playstation::class;
        } elseif ($itemKind === 'drone') {
            $item = Drone::findOrFail($itemId);
            $itemType = Drone::class;
        } elseif ($itemKind === 'musik') {
            $item = MusicalInstrument::findOrFail($itemId);
            $itemType = MusicalInstrument::class;
        }

        $basePrice = $item->getPriceForType($validated['rental_type']);
        $totalPrice = $basePrice * $duration;

        $driverPrice = 0;
        $driverId = null;
        if ($validated['driver_id'] && in_array($itemKind, ['mobil', 'motor'])) {
            $driverId = $validated['driver_id'];
            if ($item->with_driver) {
                $driverDays = max(1, $startDate->diffInDays($endDate));
                $driverPrice = ($item->with_driver_daily_price ?? 0) * $driverDays;
            }
        }

        $discount = $validated['discount'] ?? 0;
        $finalPrice = max(0, $totalPrice + $driverPrice - $discount);

        $categoryId = $item->category_id ?? null;

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $userId,
            'vehicle_id' => $vehicleId,
            'driver_id' => $driverId,
            'category_id' => $categoryId,
            'item_type' => $itemType,
            'item_id' => $vehicleId ? null : $itemId,
            'rental_type' => $validated['rental_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'pickup_location' => $validated['pickup_location'] ?? null,
            'dropoff_location' => $validated['dropoff_location'] ?? null,
            'with_driver' => $driverId ? true : false,
            'base_price' => $totalPrice,
            'driver_price' => $driverPrice,
            'total_price' => $totalPrice + $driverPrice,
            'discount' => $discount,
            'final_price' => $finalPrice,
            'status' => 'confirmed',
            'payment_status' => $validated['payment_status'],
            'payment_due_date' => $validated['payment_due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $item->update(['status' => 'reserved']);

        if ($driverId) {
            Driver::where('id', $driverId)->update(['status' => 'on_duty']);
        }

        // Notify the customer about their new booking
        if (isset($guestUser)) {
            $guestUser->notify(new \App\Notifications\BookingCreated($booking));
        } else {
            $booking->user->notify(new \App\Notifications\BookingCreated($booking));
        }

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking manual berhasil dibuat! Kode: ' . $booking->booking_code);
    }

    public function show(Booking $booking)
    {
        $booking->load(['vehicle.category', 'driver.user', 'category', 'user', 'invoice', 'tripReport', 'inspection', 'payments', 'bookingItems']);

        $replacements = $booking->replacements()->with(['originalVehicle', 'replacementVehicle', 'requestedBy'])->get();

        $swappableVehicles = collect();
        if ($booking->vehicle && in_array($booking->status, ['confirmed', 'ongoing']) && in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            $swappableVehicles = Vehicle::where('status', 'available')
                ->where('is_active', true)
                ->where('category_id', $booking->vehicle->category_id)
                ->where('id', '!=', $booking->vehicle_id)
                ->get();
        }

        return view('bookings.show', compact('booking', 'replacements', 'swappableVehicles'));
    }

    public function replaceVehicle(Request $request, Booking $booking)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403);
        }

        if (!in_array($booking->status, ['confirmed', 'ongoing']) || !$booking->vehicle_id) {
            return back()->with('error', 'Booking tidak memenuhi syarat untuk penggantian kendaraan');
        }

        $validated = $request->validate([
            'replacement_vehicle_id' => 'required|exists:vehicles,id',
            'reason' => 'nullable|string|max:500',
            'price_difference' => 'nullable|numeric',
            'mark_maintenance' => 'nullable|in:0,1',
        ]);

        if ($validated['replacement_vehicle_id'] == $booking->vehicle_id) {
            return back()->with('error', 'Kendaraan pengganti tidak boleh sama dengan kendaraan saat ini');
        }

        $replacementVehicle = Vehicle::findOrFail($validated['replacement_vehicle_id']);
        if ($replacementVehicle->category_id !== $booking->vehicle->category_id) {
            return back()->with('error', 'Kendaraan pengganti harus dalam kategori yang sama');
        }

        $originalVehicle = $booking->vehicle;

        \App\Models\VehicleReplacement::create([
            'booking_id' => $booking->id,
            'original_vehicle_id' => $originalVehicle->id,
            'replacement_vehicle_id' => $replacementVehicle->id,
            'requested_by' => Auth::id(),
            'approved_by' => Auth::id(),
            'status' => 'approved',
            'reason' => $validated['reason'] ?? null,
            'price_difference' => $validated['price_difference'] ?? 0,
            'swapped_at' => now(),
        ]);

        $booking->update(['vehicle_id' => $replacementVehicle->id]);

        $replacementVehicle->update(['status' => 'reserved']);
        $originalVehicle->update(['status' => ($validated['mark_maintenance'] ?? '1') === '1' ? 'maintenance' : 'available']);

        $priceDiff = (float) ($validated['price_difference'] ?? 0);
        if ($priceDiff != 0) {
            $booking->update([
                'total_price' => $booking->total_price + $priceDiff,
                'final_price' => $booking->final_price + $priceDiff,
            ]);
        }

        return back()->with('success', 'Kendaraan berhasil diganti');
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

        $oldStatus = $booking->status;
        $booking->update(['status' => 'confirmed']);
        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'reserved']);
        }

        $booking->user->notify(new \App\Notifications\BookingStatusChanged($booking, $oldStatus, 'confirmed'));

        return back()->with('success', 'Booking berhasil dikonfirmasi');
    }

    public function cancel(Booking $booking)
    {
        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Booking tidak dapat dibatalkan');
        }

        $oldStatus = $booking->status;
        $booking->update(['status' => 'cancelled']);
        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'available']);
        }
        if ($booking->item_id && $booking->item_type) {
            $item = $booking->item_type::find($booking->item_id);
            if ($item) $item->update(['status' => 'available']);
        }

        $booking->user->notify(new \App\Notifications\BookingCancelled($booking));

        return back()->with('success', 'Booking berhasil dibatalkan');
    }

    public function startTrip(Booking $booking)
    {
        if ($booking->status !== 'confirmed') {
            return back()->with('error', 'Booking harus dikonfirmasi dulu');
        }

        $oldStatus = $booking->status;
        $booking->update(['status' => 'ongoing', 'actual_start_date' => now()]);
        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'rented']);
        }
        if ($booking->item_id && $booking->item_type) {
            $item = $booking->item_type::find($booking->item_id);
            if ($item) $item->update(['status' => 'rented']);
        }

        $booking->user->notify(new \App\Notifications\BookingStatusChanged($booking, $oldStatus, 'ongoing'));

        return back()->with('success', 'Perjalanan dimulai');
    }

    public function complete(Booking $booking)
    {
        if ($booking->status !== 'ongoing') {
            return back()->with('error', 'Perjalanan belum dimulai');
        }

        $oldStatus = $booking->status;
        $booking->update(['status' => 'completed', 'actual_end_date' => now()]);
        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'available']);
        }
        if ($booking->item_id && $booking->item_type) {
            $item = $booking->item_type::find($booking->item_id);
            if ($item) $item->update(['status' => 'available']);
        }

        if ($booking->driver_id) {
            \App\Models\Driver::where('id', $booking->driver_id)->update(['status' => 'off_duty']);
        }

        $booking->user->notify(new \App\Notifications\BookingStatusChanged($booking, $oldStatus, 'completed'));

        return back()->with('success', 'Perjalanan selesai');
    }
}
