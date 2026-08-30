<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;
use App\Models\Category;
use App\Models\Booking;
use App\Models\Inspection;
use App\Models\TripReport;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\DriverSalary;
use App\Models\Review;
use App\Models\VehicleReplacement;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $fleetOwner = User::where('email', 'owner-mobil@marirent.com')->first()
            ?? User::where('role', 'owner')->first();
        $ownerMotor = User::where('email', 'owner-motor@marirent.com')->first() ?? $fleetOwner;
        $ownerHp = User::where('email', 'owner-sewa-hp@marirent.com')->first() ?? $fleetOwner;
        $ownerKamera = User::where('email', 'owner-sewa-kamera@marirent.com')->first() ?? $fleetOwner;
        $ownerTenda = User::where('email', 'owner-sewa-tenda@marirent.com')->first() ?? $fleetOwner;
        $customer = User::where('role', 'user')->first();
        $admin = User::where('role', 'superadmin')->first();

        // ── Extra Drivers ──────────────────────────────────────────
        $driverUsers = [
            User::create(['name' => 'Rudi Hartono', 'email' => 'driver2@marirent.com', 'password' => Hash::make('password'), 'role' => 'driver', 'phone' => '081222333444', 'is_active' => true]),
            User::create(['name' => 'Deni Kurniawan', 'email' => 'driver3@marirent.com', 'password' => Hash::make('password'), 'role' => 'driver', 'phone' => '081333444555', 'is_active' => true]),
            User::create(['name' => 'Fajar Nugroho', 'email' => 'driver4@marirent.com', 'password' => Hash::make('password'), 'role' => 'driver', 'phone' => '081444555666', 'is_active' => true]),
        ];

        $drivers = [];
        $driverData = [
            ['license_number' => 'SIM-A-22345', 'license_type' => 'A', 'daily_salary' => 150000, 'trip_salary' => 50000, 'status' => 'active'],
            ['license_number' => 'SIM-A-33456', 'license_type' => 'A', 'daily_salary' => 140000, 'trip_salary' => 45000, 'status' => 'on_trip'],
            ['license_number' => 'SIM-A-44567', 'license_type' => 'A', 'daily_salary' => 160000, 'trip_salary' => 55000, 'status' => 'off_duty'],
        ];
        foreach ($driverUsers as $i => $du) {
            $drivers[] = Driver::create([
                'user_id' => $du->id, 'owner_id' => $fleetOwner->id,
                'license_number' => $driverData[$i]['license_number'],
                'license_type' => $driverData[$i]['license_type'],
                'daily_salary' => $driverData[$i]['daily_salary'],
                'trip_salary' => $driverData[$i]['trip_salary'],
                'status' => $driverData[$i]['status'], 'is_active' => true,
            ]);
        }

        // Also get the first driver from DatabaseSeeder
        $firstDriver = Driver::first();
        $allDrivers = array_merge([$firstDriver], $drivers);

        $mobil = Category::where('slug', 'mobil')->first();
        $motor = Category::where('slug', 'motor')->first();
        $hpCat = Category::where('slug', 'sewa-hp')->first();
        $kameraCat = Category::where('slug', 'sewa-kamera')->first();
        $tendaCat = Category::where('slug', 'sewa-tenda')->first();

        // ── Extra Vehicles (more variety) ──────────────────────────
        Vehicle::create([
            'category_id' => $motor->id, 'owner_id' => $ownerMotor->id,
            'name' => 'Honda PCX 160', 'slug' => 'honda-pcx-160-2024',
            'brand' => 'Honda', 'model' => 'PCX 160', 'year' => 2024, 'color' => 'Putih',
            'license_plate' => 'B 3333 CCC', 'description' => 'Honda PCX 160, skutik premium kelas atas.',
            'daily_price' => 135000, 'weekly_price' => 850000, 'monthly_price' => 3000000, 'hourly_price' => 22000,
            'status' => 'available', 'condition' => 'excellent', 'seats' => 2,
            'transmission' => 'automatic', 'fuel_type' => 'gasoline', 'is_active' => true,
        ]);
        Vehicle::create([
            'category_id' => $mobil->id, 'owner_id' => $fleetOwner->id,
            'name' => 'Toyota Avanza Veloz', 'slug' => 'toyota-avanza-veloz-2023',
            'brand' => 'Toyota', 'model' => 'Avanza Veloz', 'year' => 2023, 'color' => 'Putih',
            'license_plate' => 'B 2222 BBB', 'description' => 'Toyota Avanza Veloz, MPV nyaman untuk keluarga.',
            'daily_price' => 350000, 'weekly_price' => 2200000, 'monthly_price' => 8000000, 'hourly_price' => 50000,
            'status' => 'available', 'condition' => 'good', 'seats' => 7,
            'transmission' => 'automatic', 'fuel_type' => 'gasoline', 'is_active' => true,
        ]);
        Vehicle::create([
            'category_id' => $mobil->id, 'owner_id' => $fleetOwner->id,
            'name' => 'Toyota Fortuner VRZ', 'slug' => 'toyota-fortuner-vrz-2023',
            'brand' => 'Toyota', 'model' => 'Fortuner VRZ', 'year' => 2023, 'color' => 'Hitam',
            'license_plate' => 'B 4444 DDD', 'description' => 'Toyota Fortuner VRZ, SUV premium untuk perjalanan mewah.',
            'daily_price' => 800000, 'weekly_price' => 5000000, 'monthly_price' => 18000000, 'hourly_price' => 100000,
            'with_driver_daily_price' => 300000,
            'status' => 'available', 'condition' => 'excellent', 'seats' => 7,
            'transmission' => 'automatic', 'fuel_type' => 'diesel', 'with_driver' => true, 'is_active' => true,
        ]);

        $vehicles = Vehicle::where('category_id', $mobil->id)->get();
        $motorVehicles = Vehicle::where('category_id', $motor->id)->get();
        $phones = Phone::all();
        $cameras = Camera::all();
        $camping = CampingEquipment::all();

        $allVehicles = Vehicle::all();

        // ── Bookings ──────────────────────────────────────────────
        $bookings = [];
        $bookingDefs = [
            // Completed bookings
            ['vehicle_idx' => 0, 'cat' => 'mobil', 'type' => 'daily', 'days' => 3, 'status' => 'completed', 'payment' => 'paid', 'with_driver' => true, 'driver_idx' => 0],
            ['vehicle_idx' => 1, 'cat' => 'mobil', 'type' => 'daily', 'days' => 2, 'status' => 'completed', 'payment' => 'paid', 'with_driver' => false, 'driver_idx' => null],
            ['vehicle_idx' => 0, 'cat' => 'mobil', 'type' => 'weekly', 'days' => 7, 'status' => 'completed', 'payment' => 'paid', 'with_driver' => true, 'driver_idx' => 1],
            // Ongoing bookings
            ['vehicle_idx' => 2, 'cat' => 'mobil', 'type' => 'daily', 'days' => 5, 'status' => 'ongoing', 'payment' => 'paid', 'with_driver' => true, 'driver_idx' => 2],
            ['vehicle_idx' => 0, 'cat' => 'motor', 'type' => 'daily', 'days' => 2, 'status' => 'ongoing', 'payment' => 'paid', 'with_driver' => false, 'driver_idx' => null],
            // Confirmed bookings
            ['vehicle_idx' => 0, 'cat' => 'mobil', 'type' => 'daily', 'days' => 4, 'status' => 'confirmed', 'payment' => 'partial', 'with_driver' => false, 'driver_idx' => null],
            // Pending bookings
            ['vehicle_idx' => 1, 'cat' => 'mobil', 'type' => 'daily', 'days' => 1, 'status' => 'pending', 'payment' => 'unpaid', 'with_driver' => true, 'driver_idx' => 0],
            // Cancelled
            ['vehicle_idx' => 2, 'cat' => 'mobil', 'type' => 'daily', 'days' => 3, 'status' => 'cancelled', 'payment' => 'refunded', 'with_driver' => false, 'driver_idx' => null],
        ];

        $locations = [
            ['pick' => 'Jl. Sudirman, Jakarta Pusat', 'drop' => 'Jl. Gatot Subroto, Jakarta Selatan'],
            ['pick' => 'Bandara Soekarno-Hatta', 'drop' => 'Hotel Mulia, Jakarta'],
            ['pick' => 'Jl. Thamrin, Jakarta Pusat', 'drop' => 'Sentul, Bogor'],
            ['pick' => 'Jl. Kemang Raya, Jakarta Selatan', 'drop' => 'Puncak, Bogor'],
            ['pick' => 'Stasiun Gambir', 'drop' => 'Jl. Asia Afrika, Bandung'],
            ['pick' => 'Mall Plaza Senayan', 'drop' => 'Kota Tua, Jakarta'],
            ['pick' => 'Jl. Rasuna Said, Jakarta', 'drop' => 'BSD City, Tangerang'],
            ['pick' => 'Jl. Pantai Indah Utara', 'drop' => 'Ancol, Jakarta Utara'],
        ];

        foreach ($bookingDefs as $i => $bd) {
            $start = now()->subDays(rand(1, 30));
            $end = (clone $start)->addDays($bd['days']);
            $cat = $bd['cat'] === 'motor' ? $motor : $mobil;

            // Get vehicle/price
            if ($bd['cat'] === 'motor') {
                $v = $motorVehicles[$bd['vehicle_idx'] % $motorVehicles->count()];
            } else {
                $v = $vehicles[$bd['vehicle_idx'] % $vehicles->count()];
            }

            $pricePerDay = (float) $v->daily_price;
            $base = $pricePerDay * $bd['days'];
            $driverP = $bd['with_driver'] ? ($v->with_driver_daily_price ?? 200000) * $bd['days'] : 0;
            $discount = ($i % 3 === 0) ? round($base * 0.05) : 0;
            $total = $base + $driverP;
            $final = $total - $discount;

            $booking = Booking::create([
                'booking_code' => Booking::generateBookingCode(),
                'user_id' => $customer->id,
                'vehicle_id' => $v->id,
                'driver_id' => $bd['driver_idx'] !== null ? $allDrivers[$bd['driver_idx']]?->id : null,
                'category_id' => $cat->id,
                'item_type' => 'vehicle',
                'item_id' => $v->id,
                'rental_type' => $bd['type'],
                'start_date' => $start,
                'end_date' => $end,
                'actual_start_date' => in_array($bd['status'], ['ongoing', 'completed']) ? $start : null,
                'actual_end_date' => $bd['status'] === 'completed' ? $end : null,
                'pickup_location' => $locations[$i]['pick'],
                'dropoff_location' => $locations[$i]['drop'],
                'with_driver' => $bd['with_driver'],
                'base_price' => $base,
                'driver_price' => $driverP,
                'total_price' => $total,
                'discount' => $discount,
                'final_price' => $final,
                'status' => $bd['status'],
                'payment_status' => $bd['payment'],
                'notes' => $i === 0 ? 'Tolong sediakan kursi bayi' : null,
                'cancellation_reason' => $bd['status'] === 'cancelled' ? 'Perubahan rencana perjalanan' : null,
            ]);
            $bookings[] = $booking;
        }

        // ── Bookings for Phones ────────────────────────────────────
        $phoneBookings = [];
        foreach ($phones as $pi => $phone) {
            $start = now()->subDays(rand(2, 20));
            $days = [2, 3, 1][$pi];
            $end = (clone $start)->addDays($days);
            $base = (float) $phone->daily_price * $days;

            $b = Booking::create([
                'booking_code' => Booking::generateBookingCode(),
                'user_id' => $customer->id,
                'vehicle_id' => null,
                'driver_id' => null,
                'category_id' => $hpCat->id,
                'item_type' => Phone::class,
                'item_id' => $phone->id,
                'rental_type' => 'daily',
                'start_date' => $start, 'end_date' => $end,
                'actual_start_date' => $start, 'actual_end_date' => $pi === 0 ? $end : null,
                'pickup_location' => 'MariRent Office, Jakarta',
                'dropoff_location' => 'MariRent Office, Jakarta',
                'with_driver' => false,
                'base_price' => $base, 'driver_price' => 0,
                'total_price' => $base, 'discount' => 0, 'final_price' => $base,
                'status' => $pi === 0 ? 'completed' : ($pi === 1 ? 'ongoing' : 'pending'),
                'payment_status' => $pi < 2 ? 'paid' : 'unpaid',
            ]);
            $phoneBookings[] = $b;
        }

        // ── Bookings for Cameras ───────────────────────────────────
        $cameraBookings = [];
        foreach ($cameras as $ci => $cam) {
            $start = now()->subDays(rand(1, 15));
            $days = [2, 1, 3][$ci];
            $end = (clone $start)->addDays($days);
            $base = (float) $cam->daily_price * $days;

            $b = Booking::create([
                'booking_code' => Booking::generateBookingCode(),
                'user_id' => $customer->id,
                'vehicle_id' => null,
                'driver_id' => null,
                'category_id' => $kameraCat->id,
                'item_type' => Camera::class,
                'item_id' => $cam->id,
                'rental_type' => 'daily',
                'start_date' => $start, 'end_date' => $end,
                'actual_start_date' => $start, 'actual_end_date' => $ci === 0 ? $end : null,
                'pickup_location' => 'MariRent Office, Jakarta',
                'dropoff_location' => 'MariRent Office, Jakarta',
                'with_driver' => false,
                'base_price' => $base, 'driver_price' => 0,
                'total_price' => $base, 'discount' => 0, 'final_price' => $base,
                'status' => $ci === 0 ? 'completed' : ($ci === 1 ? 'ongoing' : 'confirmed'),
                'payment_status' => $ci < 2 ? 'paid' : 'partial',
            ]);
            $cameraBookings[] = $b;
        }

        // ── Bookings for Camping ───────────────────────────────────
        $campingBookings = [];
        foreach ($camping as $ci => $cp) {
            $start = now()->subDays(rand(3, 20));
            $days = 3;
            $end = (clone $start)->addDays($days);
            $base = (float) $cp->daily_price * $days;

            $b = Booking::create([
                'booking_code' => Booking::generateBookingCode(),
                'user_id' => $customer->id,
                'vehicle_id' => null,
                'driver_id' => null,
                'category_id' => $tendaCat->id,
                'item_type' => CampingEquipment::class,
                'item_id' => $cp->id,
                'rental_type' => 'daily',
                'start_date' => $start, 'end_date' => $end,
                'actual_start_date' => $start, 'actual_end_date' => $ci === 0 ? $end : null,
                'pickup_location' => 'MariRent Office, Jakarta',
                'dropoff_location' => 'Gunung Gede, Jawa Barat',
                'with_driver' => false,
                'base_price' => $base, 'driver_price' => 0,
                'total_price' => $base, 'discount' => 0, 'final_price' => $base,
                'status' => $ci === 0 ? 'completed' : ($ci === 1 ? 'ongoing' : 'pending'),
                'payment_status' => $ci === 0 ? 'paid' : 'unpaid',
            ]);
            $campingBookings[] = $b;
        }

        // ── Inspections (for vehicle bookings only) ────────────────
        $completedVehicleBookings = array_filter($bookings, fn($b) => $b->status === 'completed' && $b->vehicle_id);
        foreach ($completedVehicleBookings as $b) {
            Inspection::create([
                'booking_id' => $b->id,
                'vehicle_id' => $b->vehicle_id,
                'inspector_id' => $admin->id,
                'type' => 'pre_rental',
                'exterior_condition' => 9, 'interior_condition' => 8,
                'engine_condition' => 9, 'tire_condition' => 8,
                'brake_condition' => 9, 'electrical_condition' => 9, 'overall_condition' => 9,
                'fuel_level' => 85.00, 'odometer_reading' => rand(10000, 50000),
                'damages' => [], 'photos' => [],
                'notes' => 'Kondisi kendaraan sangat baik sebelum disewakan',
                'recommendations' => 'Tidak ada masalah',
            ]);
            Inspection::create([
                'booking_id' => $b->id,
                'vehicle_id' => $b->vehicle_id,
                'inspector_id' => $admin->id,
                'type' => 'post_rental',
                'exterior_condition' => 8, 'interior_condition' => 8,
                'engine_condition' => 9, 'tire_condition' => 7,
                'brake_condition' => 9, 'electrical_condition' => 9, 'overall_condition' => 8,
                'fuel_level' => 45.00, 'odometer_reading' => rand(10000, 50000) + rand(200, 800),
                'damages' => [['type' => 'Baret kecil', 'location' => 'Pintu kanan', 'severity' => 'minor']],
                'photos' => [],
                'notes' => 'Terdapat baret kecil di pintu kanan, sisanya baik',
                'recommendations' => 'Perlu detailing untuk baret minor',
            ]);
        }

        // ── Trip Reports (for bookings with drivers) ──────────────
        $withDriverBookings = array_filter($bookings, fn($b) => $b->with_driver && in_array($b->status, ['ongoing', 'completed']));
        foreach ($withDriverBookings as $b) {
            $startOdo = rand(10000, 50000);
            $dist = rand(50, 300);
            TripReport::create([
                'booking_id' => $b->id,
                'driver_id' => $b->driver_id,
                'vehicle_id' => $b->vehicle_id,
                'start_odometer' => $startOdo,
                'end_odometer' => $startOdo + $dist,
                'total_distance' => $dist,
                'fuel_used' => round($dist / 12, 2),
                'fuel_cost' => round($dist / 12 * 10000),
                'toll_cost' => rand(30000, 150000),
                'parking_cost' => rand(10000, 50000),
                'other_cost' => rand(0, 30000),
                'total_operational_cost' => 0,
                'route_points' => json_encode([['lat' => -6.2, 'lng' => 106.8], ['lat' => -6.5, 'lng' => 107.0]]),
                'notes' => 'Perjalanan lancar tanpa kendala',
                'status' => 'completed',
            ]);
        }

        // ── Invoices ──────────────────────────────────────────────
        $allBookingsForInvoice = array_merge($bookings, $phoneBookings, $cameraBookings, $campingBookings);
        $invoices = [];
        foreach ($allBookingsForInvoice as $b) {
            if ($b->status === 'cancelled') continue;

            $subtotal = (float) $b->final_price;
            $tax = round($subtotal * 0.11);
            $total = $subtotal + $tax;
            $paid = $b->payment_status === 'paid' ? $total : ($b->payment_status === 'partial' ? round($total * 0.5) : 0);

            $inv = Invoice::create([
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad($b->id, 4, '0', STR_PAD_LEFT),
                'booking_id' => $b->id,
                'user_id' => $b->user_id,
                'owner_id' => $b->merchantOwnerId() ?? $fleetOwner->id,
                'type' => 'rental',
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'discount_amount' => 0,
                'total_amount' => $total,
                'paid_amount' => $paid,
                'due_amount' => $total - $paid,
                'status' => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'sent'),
                'payment_method' => $paid > 0 ? 'transfer' : null,
                'paid_at' => $paid > 0 ? now()->subDays(rand(0, 5)) : null,
                'due_date' => now()->addDays(7),
                'notes' => 'Invoice untuk booking ' . $b->booking_code,
            ]);

            InvoiceItem::create([
                'invoice_id' => $inv->id,
                'description' => 'Sewa ' . $b->rental_type . ' x ' . max(1, $b->start_date->diffInDays($b->end_date)),
                'quantity' => max(1, $b->start_date->diffInDays($b->end_date)),
                'unit_price' => (float) $b->base_price / max(1, $b->start_date->diffInDays($b->end_date)),
                'total_price' => (float) $b->base_price,
            ]);
            if ($b->driver_price > 0) {
                InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'description' => 'Biaya driver',
                    'quantity' => 1,
                    'unit_price' => (float) $b->driver_price,
                    'total_price' => (float) $b->driver_price,
                ]);
            }

            $invoices[] = $inv;
        }

        // ── Payments ──────────────────────────────────────────────
        foreach ($invoices as $inv) {
            if ($inv->paid_amount <= 0) continue;

            Payment::create([
                'payment_code' => 'PAY-' . now()->format('Ymd') . '-' . str_pad($inv->id, 4, '0', STR_PAD_LEFT),
                'invoice_id' => $inv->id,
                'user_id' => $inv->user_id,
                'amount' => (float) $inv->paid_amount,
                'method' => collect(['transfer', 'ewallet', 'cash'])->random(),
                'reference_number' => 'TRF-' . strtoupper(uniqid()),
                'status' => 'verified',
                'notes' => 'Pembayaran lunas',
                'paid_at' => $inv->paid_at ?? now(),
            ]);
        }

        // ── Driver Salaries ───────────────────────────────────────
        foreach ($allDrivers as $d) {
            $base = (float) $d->daily_salary * 26;
            $bonus = rand(200000, 800000);
            $deductions = rand(50000, 200000);
            $total = $base + $bonus - $deductions;

            DriverSalary::create([
                'driver_id' => $d->id,
                'owner_id' => $d->owner_id ?? $fleetOwner->id,
                'period_month' => now()->format('Y-m'),
                'base_salary' => $base,
                'trip_bonus' => $bonus,
                'overtime_pay' => rand(100000, 500000),
                'deductions' => $deductions,
                'total_salary' => $total,
                'status' => collect(['approved', 'paid', 'draft'])->random(),
                'notes' => 'Gaji bulanan ' . now()->format('F Y'),
            ]);
        }

        // ── Reviews (for completed vehicle bookings) ──────────────
        $reviewComments = [
            5 => 'Sangat puas! Kendaraan dalam kondisi prima, prosesnya cepat dan mudah.',
            4 => 'Bagus, kendaraan nyaman. Hanya sedikit keterlambatan saat pengambilan.',
            3 => 'Lumayan, kendaraan oke tapi agak kotor saat diterima.',
            5 => 'Driver sangat profesional dan ramah. Perjalanan nyaman sekali.',
            4 => 'Kondisi mobil baik, harga sesuai. Recommended!',
        ];

        foreach ($completedVehicleBookings as $i => $b) {
            $rating = [5, 4, 5][$i % 3];
            Review::create([
                'booking_id' => $b->id,
                'user_id' => $b->user_id,
                'item_type' => 'App\Models\Vehicle',
                'item_id' => $b->vehicle_id,
                'rating' => $rating,
                'comment' => $reviewComments[$rating] ?? 'Good service.',
                'is_visible' => true,
            ]);
        }

        // ── Vehicle Replacements ───────────────────────────────────
        $ogVehicles = Vehicle::where('category_id', $mobil->id)->get();
        if ($ogVehicles->count() >= 2 && count($bookings) >= 4) {
            // 1. User (customer) meminta penggantian
            VehicleReplacement::create([
                'booking_id' => $bookings[3]->id,
                'original_vehicle_id' => $ogVehicles[0]->id,
                'replacement_vehicle_id' => $ogVehicles[1]->id,
                'requested_by' => $customer->id,
                'approved_by' => $bookings[3]->merchantOwnerId() ?? $fleetOwner->id,
                'status' => 'approved',
                'reason' => 'Kendaraan awal mengalami masalah mesin ringan saat perjalanan',
                'admin_notes' => 'Disetujui, unit pengganti sudah disiapkan',
                'price_difference' => 0,
            ]);

            // 2. Driver meminta penggantian (booking dengan driver)
            if ($firstDriver && count($bookings) >= 1) {
                VehicleReplacement::create([
                    'booking_id' => $bookings[0]->id,
                    'original_vehicle_id' => $ogVehicles->skip(2)->first()?->id ?? $ogVehicles[0]->id,
                    'replacement_vehicle_id' => $ogVehicles->first()->id,
                    'requested_by' => $firstDriver->user_id,
                    'approved_by' => $bookings[0]->merchantOwnerId() ?? $fleetOwner->id,
                    'status' => 'approved',
                    'reason' => 'AC kendaraan tidak berfungsi dengan baik di tengah perjalanan',
                    'admin_notes' => 'Disetujui, unit diganti untuk kenyamanan pelanggan',
                    'price_difference' => 0,
                ]);
            }

            // 3. User lain mengajukan - pending (create second user if needed)
            if (count($bookings) >= 7) {
                $secondCustomer = User::where('role', 'user')->where('id', '!=', $customer->id)->first();
                if (!$secondCustomer) {
                    $secondCustomer = User::create([
                        'name' => 'Budi Santoso',
                        'email' => 'user2@marirent.com',
                        'password' => Hash::make('password'),
                        'role' => 'user',
                        'phone' => '081555666777',
                        'is_active' => true,
                    ]);
                }
                VehicleReplacement::create([
                    'booking_id' => $bookings[6]->id,
                    'original_vehicle_id' => $ogVehicles[1]->id,
                    'replacement_vehicle_id' => $ogVehicles->skip(3)->first()?->id ?? $ogVehicles[0]->id,
                    'requested_by' => $secondCustomer->id,
                    'status' => 'pending',
                    'reason' => 'Minta kendaraan yang lebih baru untuk perjalanan dinas',
                    'price_difference' => 50000,
                ]);
            }

            // 4. Driver lain mengajukan - ditolak
            if ($ogVehicles->count() >= 3 && count($bookings) >= 3 && count($driverUsers) >= 1) {
                VehicleReplacement::create([
                    'booking_id' => $bookings[2]->id,
                    'original_vehicle_id' => $ogVehicles[0]->id,
                    'replacement_vehicle_id' => $ogVehicles[2]->id,
                    'requested_by' => $driverUsers[0]->id,
                    'approved_by' => $bookings[2]->merchantOwnerId() ?? $fleetOwner->id,
                    'status' => 'rejected',
                    'reason' => 'Ban kendaraan aus dan perlu diganti',
                    'admin_notes' => 'Ditolak, kendaraan masih dalam kondisi layak jalan',
                    'price_difference' => 0,
                ]);
            }
        }

        echo "Demo seeder completed!\n";
        echo "  - " . User::count() . " users\n";
        echo "  - " . Driver::count() . " drivers\n";
        echo "  - " . Vehicle::count() . " vehicles\n";
        echo "  - " . Phone::count() . " phones\n";
        echo "  - " . Camera::count() . " cameras\n";
        echo "  - " . CampingEquipment::count() . " camping equipments\n";
        echo "  - " . Booking::count() . " bookings\n";
        echo "  - " . Invoice::count() . " invoices\n";
        echo "  - " . InvoiceItem::count() . " invoice items\n";
        echo "  - " . Payment::count() . " payments\n";
        echo "  - " . Inspection::count() . " inspections\n";
        echo "  - " . TripReport::count() . " trip reports\n";
        echo "  - " . DriverSalary::count() . " driver salaries\n";
        echo "  - " . Review::count() . " reviews\n";
        echo "  - " . VehicleReplacement::count() . " vehicle replacements\n";
    }
}
