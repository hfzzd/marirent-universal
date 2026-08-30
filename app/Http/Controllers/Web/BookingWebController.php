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
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['vehicle', 'driver.user', 'category', 'user', 'bookingItems']);
        $user = Auth::user();

        if ($user->role === 'user') {
            $query->where('user_id', Auth::id());
        } elseif ($user->role === 'driver') {
            $driver = \App\Models\Driver::where('user_id', Auth::id())->first();
            if ($driver) {
                $query->where('driver_id', $driver->id);
            }
        } elseif ($user->isMerchantStaff()) {
            $query->forMerchantCategory($user->merchantId(), $user->merchantCategoryId());
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(15);

        return view('bookings.index', compact('bookings'));
    }

    private function bookingBelongsToMerchant(Booking $booking, int $merchantId): bool
    {
        if ($booking->vehicle) {
            return (int) $booking->vehicle->owner_id === $merchantId;
        }
        if ($booking->item_id && $booking->item_type) {
            $item = $booking->item;
            return $item && (int) $item->owner_id === $merchantId;
        }

        foreach ($booking->childBookings as $child) {
            if ($this->bookingBelongsToMerchant($child, $merchantId)) {
                return true;
            }
        }

        return false;
    }

    private function bookingAccessibleByStaff(Booking $booking, User $user): bool
    {
        if (!$user->isMerchantStaff()) {
            return false;
        }

        return $this->bookingBelongsToMerchant($booking, $user->merchantId())
            && $booking->belongsToCategory($user->merchantCategoryId());
    }

    private function notifyBookingCreated(Booking $booking): void
    {
        $recipients = collect();

        $recipients->push(User::where('role', 'superadmin')->where('is_active', true)->get());

        $staff = User::whereIn('role', ['admin', 'owner'])->where('is_active', true)->get();
        foreach ($staff as $staffUser) {
            if ($staffUser->merchantId() !== $booking->merchantOwnerId()) {
                continue;
            }
            if (!$booking->belongsToCategory($staffUser->merchantCategoryId())) {
                continue;
            }
            $recipients->push($staffUser);
        }

        $recipients->flatten()->each(
            fn($user) => $user->notify(new \App\Notifications\BookingCreated($booking))
        );
    }

    private function staffProductScope(): array
    {
        $user = Auth::user();
        if (!$user->isMerchantStaff()) {
            return [null, null];
        }

        return [$user->merchantId(), $user->merchantCategoryId()];
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
            'payment_plan' => 'nullable|in:full,dp50',
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
            'payment_plan' => $validated['payment_plan'] ?? 'full',
            'notes' => $validated['notes'] ?? null,
            'ktp_photo' => $ktpPath,
        ]);

        $booking->setDpAmountFromPlan();

        $vehicle->update(['status' => 'reserved']);

        $this->createBookingInvoice($booking);

        // Notify superadmin dan staf merchant (sesuai kategori) atas booking baru
        $this->notifyBookingCreated($booking);

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
            'payment_plan' => $validated['payment_plan'],
            'accessories' => $selectedAccessories ?: null,
            'urgency' => $validated['urgency'],
            'with_insurance' => $validated['with_insurance'] === '1',
            'notes' => $validated['notes'] ?? null,
            'ktp_photo' => $ktpPath,
        ]);

        $booking->setDpAmountFromPlan();

        $item->update(['status' => 'reserved']);

        $this->createBookingInvoice($booking);

        // Notify superadmin dan staf merchant (sesuai kategori) atas booking item baru
        $this->notifyBookingCreated($booking);

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

    private function createBookingInvoice(Booking $booking, array $relatedBookings = []): void
    {
        $related = $relatedBookings !== [] ? $relatedBookings : [$booking];
        $subtotal = collect($related)->sum(fn($b) => (float) $b->final_price);

        $owner = null;
        if ($booking->vehicle && $booking->vehicle->owner_id) {
            $owner = User::find($booking->vehicle->owner_id);
        }
        if (!$owner) {
            foreach ($related as $b) {
                if ($b->item_id && $b->item_type) {
                    $item = $b->item;
                    if ($item && $item->owner_id) {
                        $owner = User::find($item->owner_id);
                        break;
                    }
                }
            }
        }
        if (!$owner) {
            $owner = User::where('role', 'superadmin')->orderBy('id')->first();
        }

        $paidAmount = $booking->payment_status === 'paid' ? $subtotal : 0;
        $status = $booking->payment_status === 'paid' ? 'paid' : 'sent';

        $isDp50 = collect($related)->contains(fn($b) => $b->payment_plan === 'dp50');
        $totalDp = collect($related)->sum(fn($b) => (float) ($b->getDpAmount() ?? 0));

        $invoiceNotes = [];
        if ($booking->notes) {
            $invoiceNotes[] = $booking->notes;
        }
        if ($isDp50) {
            $invoiceNotes[] = 'Sistem pembayaran DP 50%: DP minimal Rp ' . number_format($totalDp, 0, ',', '.') . ', pelunasan sisanya sebelum/ sesuai jadwal sewa.';
        }

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber('rental'),
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'owner_id' => $owner?->id,
            'category_id' => $booking->category_id,
            'type' => 'rental',
            'subtotal' => $subtotal,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $subtotal,
            'paid_amount' => $paidAmount,
            'due_amount' => max(0, $subtotal - $paidAmount),
            'status' => $status,
            'paid_at' => $booking->payment_status === 'paid' ? now() : null,
            'due_date' => $booking->payment_due_date
                ?? ($booking->start_date ? \Carbon\Carbon::parse($booking->start_date) : now()->addDays(7)),
            'notes' => $invoiceNotes ? implode(' | ', $invoiceNotes) : null,
        ]);

        foreach ($related as $b) {
            $unit = $b->vehicle?->name ?? $b->item?->name ?? ($b->category->name ?? '-');
            $days = $b->start_date && $b->end_date ? max(1, $b->start_date->diffInDays($b->end_date)) : 1;
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => "Sewa {$unit} ({$b->booking_code}) - {$days} hari",
                'quantity' => 1,
                'unit_price' => (float) $b->final_price,
                'total_price' => (float) $b->final_price,
            ]);
        }

        $ids = collect($related)->pluck('id');
        if (!$ids->contains($booking->id)) {
            $ids->push($booking->id);
        }
        $invoice->bookings()->attach($ids);
    }

    public function createMulti()
    {
        [$merchantId, $categoryId] = $this->staffProductScope();

        $phones = Phone::where('status', 'available')->where('is_active', true)
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->with('category')->get();
        $cameras = Camera::where('status', 'available')->where('is_active', true)
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->with('category')->get();
        $equipments = CampingEquipment::where('status', 'available')->where('is_active', true)
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->with('category')->get();
        $playstations = Playstation::where('status', 'available')->where('is_active', true)
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->with('category')->get();
        $drones = Drone::where('status', 'available')->where('is_active', true)
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->with('category')->get();
        $instruments = MusicalInstrument::where('status', 'available')->where('is_active', true)
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->with('category')->get();

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

        [$merchantId, $categoryId] = $this->staffProductScope();
        foreach ($validated['items'] as $itemData) {
            $item = $this->getItemModel($itemData['type'])::findOrFail($itemData['id']);
            if ($merchantId !== null && (int) $item->owner_id !== $merchantId) {
                return back()->with('error', 'Salah satu item tidak termasuk merchant Anda')->withInput();
            }
            if ($categoryId !== null && (int) $item->category_id !== $categoryId) {
                return back()->with('error', 'Salah satu item tidak sesuai kategori Anda')->withInput();
            }
        }

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
            'payment_plan' => $validated['payment_plan'],
            'notes' => $validated['notes'] ?? null,
            'ktp_photo' => $ktpPath,
        ]);

        $totalBookingPrice = 0;
        $childBookings = [];

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

            $child = Booking::create([
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
                'payment_plan' => $validated['payment_plan'],
                'accessories' => $selectedAccessories ?: null,
                'urgency' => $itemData['urgency'],
                'with_insurance' => $itemData['with_insurance'] === '1',
                'notes' => $validated['notes'] ?? null,
                'ktp_photo' => $ktpPath,
                'parent_booking_id' => $booking->id,
            ]);

            $item->update(['status' => 'reserved']);
            $child->setDpAmountFromPlan();
            $childBookings[] = $child;
        }

        $booking->update([
            'total_price' => $totalBookingPrice,
            'final_price' => $totalBookingPrice,
        ]);

        $booking->setDpAmountFromPlan();

        $this->createBookingInvoice($booking, $childBookings);

        $this->notifyBookingCreated($booking);

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking multi-item berhasil dibuat! Kode: ' . $booking->booking_code);
    }

    public function manualCreate()
    {
        [$merchantId, $categoryId] = $this->staffProductScope();

        $customers = \App\Models\User::where('role', 'user')->orderBy('name')->get();
        $vehicles = Vehicle::with('category')->where('status', 'available')
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->get();
        $drivers = Driver::with('user')
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->get();
        $cameras = Camera::where('status', 'available')
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->get();
        $phones = Phone::where('status', 'available')
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->get();
        $campings = CampingEquipment::where('status', 'available')
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->get();
        $playstations = Playstation::where('status', 'available')
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->get();
        $drones = Drone::where('status', 'available')
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->get();
        $instruments = MusicalInstrument::where('status', 'available')
            ->when($merchantId, fn($q) => $q->where('owner_id', $merchantId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->get();

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
            'payment_plan' => 'nullable|in:full,dp50',
            'payment_due_date' => 'nullable|date',
            'discount' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();

        if ($validated['customer_mode'] === 'new') {
            $guestEmail = 'walkin_' . strtolower(uniqid()) . '@marirent.local';
            $guestUser = \App\Models\User::create([
                'name' => $validated['guest_name'],
                'email' => $guestEmail,
                'phone' => $validated['guest_phone'],
                'password' => bcrypt('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
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

        [$merchantId, $categoryId] = $this->staffProductScope();
        if ($merchantId !== null && (int) $item->owner_id !== $merchantId) {
            return back()->with('error', 'Item tidak termasuk merchant Anda')->withInput();
        }
        if ($categoryId !== null && (int) $item->category_id !== $categoryId) {
            return back()->with('error', 'Item tidak sesuai kategori Anda')->withInput();
        }

        $basePrice = $item->getPriceForType($validated['rental_type']);
        $totalPrice = $basePrice * $duration;

        $driverPrice = 0;
        $driverId = null;
        if (($validated['driver_id'] ?? null) && in_array($itemKind, ['mobil', 'motor'])) {
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
            'payment_plan' => $validated['payment_plan'] ?? 'full',
            'payment_due_date' => $validated['payment_due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($validated['payment_status'] === 'paid' && ($validated['payment_plan'] ?? 'full') === 'dp50') {
            $booking->update([
                'payment_plan' => 'full',
                'dp_amount' => null,
            ]);
        }

        $booking->setDpAmountFromPlan();

        $item->update(['status' => 'reserved']);

        $this->createBookingInvoice($booking);

        if ($driverId) {
            Driver::where('id', $driverId)->update(['status' => 'on_duty']);
        }

        // Notify the customer about their new booking
        if (isset($guestUser)) {
            $guestUser->notify(new \App\Notifications\BookingCreated($booking));
        } else {
            $booking->user->notify(new \App\Notifications\BookingCreated($booking));
        }

        // Notify superadmin dan staf merchant (sesuai kategori)
        $this->notifyBookingCreated($booking);

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking manual berhasil dibuat! Kode: ' . $booking->booking_code);
    }

    public function show(Booking $booking)
    {
        $user = Auth::user();

        if ($user->role === 'user' && (int) $booking->user_id !== (int) $user->id) {
            abort(403);
        }

        if ($user->isMerchantStaff() && !$this->bookingAccessibleByStaff($booking, $user)) {
            abort(403);
        }

        $booking->load(['vehicle.category', 'driver.user', 'category', 'user', 'invoice', 'tripReport', 'inspection', 'payments', 'bookingItems', 'childBookings']);

        $replacements = $booking->replacements()->with(['originalVehicle', 'replacementVehicle', 'requestedBy'])->get();

        $isMerchantStaff = Auth::user()->isSuperAdmin() || Auth::user()->isMerchantStaff();

        $swappableVehicles = collect();
        if ($booking->vehicle && in_array($booking->status, ['confirmed', 'ongoing']) && $isMerchantStaff) {
            $swappableVehicles = Vehicle::where('status', 'available')
                ->where('is_active', true)
                ->where('category_id', $booking->vehicle->category_id)
                ->where('id', '!=', $booking->vehicle_id)
                ->get();
        }

        return view('bookings.show', compact('booking', 'replacements', 'swappableVehicles', 'isMerchantStaff'));
    }

    public function reschedule(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isMerchantStaff()) {
            abort(403);
        }

        if ($user->isMerchantStaff() && !$this->bookingAccessibleByStaff($booking, $user)) {
            abort(403);
        }

        if (!in_array($booking->status, ['pending', 'confirmed', 'ongoing'])) {
            return back()->with('error', 'Booking tidak bisa diubah jadwalnya pada status saat ini');
        }

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $oldStart = $booking->start_date;
        $oldEnd = $booking->end_date;

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);

        if ($booking->status === 'ongoing') {
            $startDate = $booking->actual_start_date ?? $booking->start_date;
        }

        $prices = $this->recalculateBookingPrices($booking, $startDate, $endDate);
        if ($prices === null) {
            return back()->with('error', 'Unit sewa tidak ditemukan untuk booking ini');
        }

        $booking->update([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'base_price' => $prices['base'],
            'driver_price' => $prices['driver'],
            'insurance_fee' => $prices['insurance'],
            'total_price' => $prices['total'],
            'final_price' => $prices['final'],
        ]);

        $booking->setDpAmountFromPlan();

        // Sinkronkan invoice yang terhubung
        $invoice = $booking->invoice;
        if (!$invoice && $booking->invoices()->exists()) {
            $invoice = $booking->invoices()->first();
        }

        if ($invoice) {
            $days = max(1, $startDate->diffInDays($endDate));
            $newTotal = (float) $booking->final_price;

            $oldTotal = (float) $invoice->total_amount;
            $paidSoFar = (float) $invoice->paid_amount;

            $invoice->update([
                'subtotal' => $newTotal,
                'total_amount' => $newTotal,
                'discount_amount' => $oldTotal - $newTotal > 0 ? ($oldTotal - $newTotal) + (float) $invoice->discount_amount : $invoice->discount_amount,
                'due_amount' => max(0, $newTotal - $paidSoFar),
                'status' => $paidSoFar >= $newTotal && $newTotal > 0 ? 'paid' : ($paidSoFar > 0 ? 'partial' : 'sent'),
                'notes' => trim(($invoice->notes ?? '') . ' | Jadwal diubah: ' . $oldStart->format('d M Y H:i') . ' -> ' . ($booking->status === 'ongoing' ? $booking->actual_start_date?->format('d M Y H:i') : $startDate->format('d M Y H:i')) . ' s/d ' . $endDate->format('d M Y H:i')),
            ]);

            foreach ($invoice->items as $item) {
                $item->update([
                    'unit_price' => (float) $booking->final_price,
                    'total_price' => (float) $booking->final_price,
                ]);
            }

            $bookingPaymentStatus = match($invoice->status) {
                'paid' => 'paid',
                'partial' => 'partial',
                default => 'unpaid',
            };
            $booking->update(['payment_status' => $bookingPaymentStatus]);
        }

        $booking->user->notify(new \App\Notifications\BookingStatusChanged($booking, $booking->status, $booking->status));

        return back()->with('success', 'Jadwal booking berhasil diubah dari ' . $oldEnd->format('d M Y') . ' menjadi ' . $endDate->format('d M Y') . '. Total tagihan diperbarui.');
    }

    private function recalculateBookingPrices(Booking $booking, \Carbon\Carbon $start, \Carbon\Carbon $end): ?array
    {
        $rentalType = $booking->rental_type;
        $duration = $rentalType === 'hourly'
            ? max(1, $start->diffInHours($end))
            : max(1, $start->diffInDays($end));

        $unit = $booking->item_id && $booking->item_type ? $booking->item : ($booking->vehicle ?? null);
        if (!$unit) {
            return null;
        }

        $basePrice = $unit->getPriceForType($rentalType);
        $rentalRate = $rentalType === 'hourly' ? ($unit->hourly_price ?? 0) : $basePrice;
        $subtotal = round($rentalRate * $duration);

        $driverPrice = 0;
        if ($booking->with_driver && $booking->vehicle && $booking->vehicle->with_driver) {
            $driverDays = max(1, $start->diffInDays($end));
            $driverPrice = round(($booking->vehicle->with_driver_daily_price ?? 0) * $driverDays);
        } else {
            $driverPrice = (float) ($booking->driver_price ?? 0);
        }

        $insuranceFee = 0;
        if ($booking->with_insurance && $unit->daily_price) {
            $insuranceRate = round($unit->daily_price * 0.05);
            $insuranceFee = round($insuranceRate * $duration);
        }

        $accessoriesCost = 0;
        $typeKey = $this->getTypeKey($booking->item_type);
        $config = $this->getItemConfig($typeKey);
        if ($booking->item_id && $booking->accessories) {
            foreach ($booking->accessories as $accName) {
                foreach ($config['accessories'] ?? [] as $acc) {
                    if ($acc['name'] === $accName) {
                        $accessoriesCost += round($acc['price'] * $duration);
                        break;
                    }
                }
            }
        }

        $urgencyMultiplier = match($booking->urgency) {
            'urgent' => 0.10,
            'very_urgent' => 0.20,
            default => 0,
        };
        $urgencyFee = round($subtotal * $urgencyMultiplier);

        $depositAmount = (float) ($booking->deposit_amount ?? 0);
        $discount = (float) ($booking->discount ?? 0);

        if ($booking->item_id) {
            $total = $subtotal + $insuranceFee + $accessoriesCost + $urgencyFee + $depositAmount;
        } else {
            $total = $subtotal + $driverPrice;
        }

        $final = max(0, $total - $discount);

        return [
            'base' => $subtotal,
            'driver' => $driverPrice,
            'insurance' => $insuranceFee,
            'total' => $total,
            'final' => $final,
        ];
    }

    private function getTypeKey(?string $class): string
    {
        return match($class) {
            Phone::class => 'hp',
            Camera::class => 'kamera',
            CampingEquipment::class => 'tenda',
            Playstation::class => 'ps',
            Drone::class => 'drone',
            MusicalInstrument::class => 'musik',
            default => 'hp',
        };
    }

    public function replaceVehicle(Request $request, Booking $booking)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403);
        }

        if (Auth::user()->isMerchantStaff() && !$this->bookingAccessibleByStaff($booking, Auth::user())) {
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
