<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CampingEquipment;
use App\Models\Category;
use App\Models\Driver;
use App\Models\Drone;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\Maintenance;
use App\Models\Payment;
use App\Models\Phone;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OverhaulTest extends TestCase
{
    use DatabaseTransactions;

    private function makeOwner(string $email, ?int $categoryId = null): User
    {
        return User::create([
            'name' => 'Owner ' . $email,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'owner',
            'category_id' => $categoryId,
            'is_active' => true,
        ]);
    }

    private function makeAdmin(string $email, User $owner, ?int $categoryId = null): User
    {
        return User::create([
            'name' => 'Admin ' . $email,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'admin',
            'owner_id' => $owner->id,
            'category_id' => $categoryId,
            'is_active' => true,
        ]);
    }

    private function makeCamping(User $owner, Category $category): CampingEquipment
    {
        return CampingEquipment::create([
            'category_id' => $category->id,
            'owner_id' => $owner->id,
            'name' => 'TENDA-TEST-' . uniqid(),
            'slug' => 'tenda-test-' . uniqid(),
            'brand' => 'Test',
            'equipment_model' => 'Test Tent',
            'type' => 'Tenda Dome',
            'capacity' => 4,
            'weight' => '3.5 kg',
            'material' => 'Polyester',
            'daily_price' => 100000,
            'weekly_price' => 600000,
            'monthly_price' => 2000000,
            'hourly_price' => 15000,
            'status' => 'available',
            'condition' => 'excellent',
            'is_active' => true,
        ]);
    }

    private function makeDrone(User $owner, Category $category): Drone
    {
        return Drone::create([
            'category_id' => $category->id,
            'owner_id' => $owner->id,
            'name' => 'DRONE-TEST-' . uniqid(),
            'slug' => 'drone-test-' . uniqid(),
            'brand' => 'DJI',
            'drone_model' => 'Mini 4 Pro',
            'camera_resolution' => '4K',
            'flight_time' => '34 menit',
            'max_range' => '20 km',
            'weight' => '249 g',
            'daily_price' => 250000,
            'weekly_price' => 1500000,
            'monthly_price' => 5000000,
            'hourly_price' => 35000,
            'status' => 'available',
            'condition' => 'excellent',
            'is_active' => true,
        ]);
    }

    private function makePhone(User $owner, Category $category): Phone
    {
        return Phone::create([
            'category_id' => $category->id,
            'owner_id' => $owner->id,
            'name' => 'PHONE-TEST-' . uniqid(),
            'slug' => 'phone-test-' . uniqid(),
            'brand' => 'Apple',
            'phone_model' => 'Test Phone',
            'storage_capacity' => '128GB',
            'ram' => '8GB',
            'color' => 'Black',
            'daily_price' => 100000,
            'weekly_price' => 600000,
            'monthly_price' => 2000000,
            'hourly_price' => 15000,
            'status' => 'available',
            'condition' => 'excellent',
            'is_active' => true,
        ]);
    }

    private function makeItemBooking(User $customer, $item, Category $category): Booking
    {
        return Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $customer->id,
            'category_id' => $category->id,
            'item_type' => get_class($item),
            'item_id' => $item->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(3)->format('Y-m-d H:i'),
            'base_price' => 500000,
            'total_price' => 500000,
            'final_price' => 500000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_plan' => 'full',
        ]);
    }

    private function makeRoleUser(string $email, string $role): User
    {
        return User::create([
            'name' => ucfirst($role) . ' ' . $email,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function makeVehicle(User $owner, string $name, Category $category): Vehicle
    {
        return Vehicle::create([
            'category_id' => $category->id,
            'owner_id' => $owner->id,
            'name' => $name,
            'slug' => str($name)->slug() . '-' . uniqid(),
            'brand' => 'Test',
            'model' => 'Test Model',
            'license_plate' => 'T' . strtoupper(substr(uniqid(), -5)),
            'daily_price' => 200000,
            'weekly_price' => 1200000,
            'monthly_price' => 5000000,
            'hourly_price' => 25000,
            'with_driver_daily_price' => 150000,
            'with_driver' => false,
            'status' => 'available',
            'condition' => 'excellent',
            'is_active' => true,
        ]);
    }

    public function test_dp50_vehicle_booking_stores_half_as_dp(): void
    {
        $customer = User::where('role', 'user')->firstOrFail();
        $vehicle = Vehicle::where('status', 'available')->where('is_active', true)->firstOrFail();

        $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $vehicle->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(3)->format('Y-m-d H:i'),
            'with_driver' => '0',
            'payment_plan' => 'dp50',
        ])->assertRedirect();

        $booking = Booking::where('user_id', $customer->id)->latest('id')->first();
        $this->assertSame('dp50', $booking->payment_plan);
        $this->assertEquals(round($booking->final_price * 0.5), $booking->getDpAmount());

        $invoice = $booking->invoice;
        $this->assertNotNull($invoice);
        $this->assertStringContainsString('DP 50%', $invoice->notes ?? '');
    }

    public function test_dp50_item_booking_via_phone_category(): void
    {
        Storage::fake('public');
        $customer = User::where('role', 'user')->firstOrFail();
        $phone = Phone::where('status', 'available')->where('is_active', true)->firstOrFail();

        $this->actingAs($customer)->post('/bookings/store-item/hp', [
            'item_id' => $phone->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(2)->format('Y-m-d H:i'),
            'urgency' => 'normal',
            'with_insurance' => '1',
            'payment_plan' => 'dp50',
            'accessories' => [],
            'ktp_photo' => UploadedFile::fake()->image('ktp.jpg', 400, 300),
        ])->assertSessionHasNoErrors();

        $booking = Booking::where('user_id', $customer->id)->whereNotNull('item_id')->latest('id')->first();
        $this->assertSame('dp50', $booking->payment_plan);
        $this->assertEquals(round($booking->final_price * 0.5), $booking->getDpAmount());
    }

    public function test_manual_store_dp50_partial_stores_dp_and_paid_forces_full(): void
    {
        $admin = User::where('role', 'superadmin')->firstOrFail();
        $customer = User::where('role', 'user')->firstOrFail();
        $vehicle = Vehicle::where('status', 'available')->where('is_active', true)->firstOrFail();

        $this->actingAs($admin)->post('/bookings/manual-store', [
            'customer_mode' => 'existing',
            'user_id' => $customer->id,
            'item_kind' => $vehicle->category->slug === 'motor' ? 'motor' : 'mobil',
            'item_id' => $vehicle->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(3)->format('Y-m-d H:i'),
            'payment_status' => 'partial',
            'payment_plan' => 'dp50',
        ])->assertRedirect();

        $booking = Booking::where('user_id', $customer->id)->latest('id')->first();
        $this->assertSame('dp50', $booking->payment_plan);
        $this->assertEquals(round($booking->final_price * 0.5), $booking->getDpAmount());

        $this->actingAs($admin)->post('/bookings/manual-store', [
            'customer_mode' => 'existing',
            'user_id' => $customer->id,
            'item_kind' => $vehicle->category->slug === 'motor' ? 'motor' : 'mobil',
            'item_id' => $vehicle->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(5)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(7)->format('Y-m-d H:i'),
            'payment_status' => 'paid',
            'payment_plan' => 'dp50',
        ])->assertRedirect();

        $paidBooking = Booking::where('user_id', $customer->id)->where('payment_status', 'paid')->latest('id')->first();
        $this->assertSame('full', $paidBooking->payment_plan);
        $this->assertNull($paidBooking->dp_amount);
    }

    public function test_pay_blocks_first_payment_below_dp50(): void
    {
        $customer = User::where('role', 'user')->firstOrFail();
        $vehicle = Vehicle::where('status', 'available')->where('is_active', true)->firstOrFail();

        $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $vehicle->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(3)->format('Y-m-d H:i'),
            'with_driver' => '0',
            'payment_plan' => 'dp50',
        ]);

        $booking = Booking::where('user_id', $customer->id)->latest('id')->first();
        $invoice = $booking->invoice;
        $dp = $booking->getDpAmount();
        $before = Payment::count();

        $this->actingAs($customer)->post('/invoices/' . $invoice->id . '/pay', [
            'amount' => (float) $invoice->total_amount * 0.4,
            'method' => 'cash',
        ])->assertRedirect();

        $this->assertEquals($before, Payment::count());
    }

    public function test_reschedule_shortens_rental_and_updates_invoice(): void
    {
        $admin = User::where('role', 'superadmin')->firstOrFail();
        $customer = User::where('role', 'user')->firstOrFail();
        $vehicle = Vehicle::where('status', 'available')->where('is_active', true)->firstOrFail();

        $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $vehicle->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(8)->format('Y-m-d H:i'),
            'with_driver' => '0',
        ]);
        $booking = Booking::where('user_id', $customer->id)->latest('id')->first();
        $oldPrice = (float) $booking->final_price;

        $start = $booking->start_date->format('Y-m-d H:i');
        $this->actingAs($admin)->post('/bookings/' . $booking->id . '/reschedule', [
            'start_date' => $start,
            'end_date' => $booking->start_date->addDays(5)->format('Y-m-d H:i'),
        ])->assertRedirect();

        $booking->refresh();
        $newPrice = (float) $booking->final_price;
        $this->assertLessThan($oldPrice, $newPrice);

        $invoice = $booking->invoice;
        $this->assertNotNull($invoice);
        $this->assertEquals($newPrice, (float) $invoice->total_amount);
    }

    public function test_admin_only_sees_own_merchant_assets(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();

        $ownerA = $this->makeOwner('scope_a@example.test');
        $ownerB = $this->makeOwner('scope_b@example.test');
        $adminA = $this->makeAdmin('admin_scope_a@example.test', $ownerA);
        $adminB = $this->makeAdmin('admin_scope_b@example.test', $ownerB);

        $carA = $this->makeVehicle($ownerA, 'SCOPE-CAR-AAA', $mobil);
        $carB = $this->makeVehicle($ownerB, 'SCOPE-CAR-BBB', $mobil);

        $this->actingAs($adminA)->get('/vehicles')->assertSee('SCOPE-CAR-AAA')->assertDontSee('SCOPE-CAR-BBB');
        $this->actingAs($adminB)->get('/vehicles')->assertSee('SCOPE-CAR-BBB')->assertDontSee('SCOPE-CAR-AAA');

        $super = User::where('role', 'superadmin')->firstOrFail();
        $this->actingAs($super)->get('/vehicles')->assertSee('SCOPE-CAR-AAA')->assertSee('SCOPE-CAR-BBB');
    }

    public function test_inspector_creates_maintenance_and_notifies_merchant_admin_and_drivers(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();
        $owner = $this->makeOwner('maint_owner@example.test');
        $admin = $this->makeAdmin('maint_admin@example.test', $owner);
        $inspector = $this->makeRoleUser('maint_inspector@example.test', 'inspector');
        $driverUser = $this->makeRoleUser('maint_driver@example.test', 'driver');
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'owner_id' => $owner->id,
            'license_number' => 'SIM-TEST-1',
            'daily_salary' => 150000,
            'status' => 'off_duty',
            'is_active' => true,
        ]);
        $vehicle = $this->makeVehicle($owner, 'MAINT-CAR-1', $mobil);

        $before = DB::table('notifications')->count();

        $this->actingAs($inspector)->post('/maintenances', [
            'title' => 'Ganti oli MAINT-CAR-1',
            'vehicle_id' => $vehicle->id,
            'scheduled_date' => now()->addDays(2)->format('Y-m-d'),
            'type' => 'routine',
            'priority' => 'medium',
            'estimated_cost' => 350000,
        ])->assertRedirect();

        $maintenance = Maintenance::where('title', 'Ganti oli MAINT-CAR-1')->first();
        $this->assertNotNull($maintenance);
        $this->assertSame('scheduled', $maintenance->status);

        $after = DB::table('notifications')
            ->whereIn('notifiable_id', [$admin->id, $driverUser->id, $owner->id])
            ->count();
        $this->assertGreaterThanOrEqual(3, $after);
    }

    public function test_driver_report_to_inspector_handoff_flow(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();
        $owner = $this->makeOwner('insp_owner@example.test');
        $admin = $this->makeAdmin('insp_admin@example.test', $owner);
        $inspector = $this->makeRoleUser('insp_operator@example.test', 'inspector');
        $inspector->update(['owner_id' => $owner->id]);
        $driverUser = $this->makeRoleUser('insp_driver@example.test', 'driver');
        $customer = $this->makeRoleUser('insp_customer@example.test', 'user');
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'owner_id' => $owner->id,
            'license_number' => 'SIM-INSP-1',
            'daily_salary' => 150000,
            'status' => 'off_duty',
            'is_active' => true,
        ]);
        $vehicle = $this->makeVehicle($owner, 'INSP-CAR-1', $mobil);

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'category_id' => $mobil->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(3)->format('Y-m-d H:i'),
            'base_price' => 200000,
            'total_price' => 200000,
            'final_price' => 200000,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_plan' => 'full',
            'with_driver' => true,
        ]);

        $this->actingAs($driverUser)->post('/inspections', [
            'booking_id' => $booking->id,
            'type' => 'post_rental',
            'scope' => 'kendaraan',
            'inspection_item_id' => $vehicle->id,
            'overall_condition' => 6,
            'exterior_condition' => 6,
            'tire_condition' => 5,
            'damage_items' => ['Ban belakang bocor'],
            'notes' => 'Ditemukan kerusakan saat pengembalian unit.',
        ])->assertRedirect();

        $inspection = Inspection::where('booking_id', $booking->id)->first();
        $this->assertNotNull($inspection);
        $this->assertSame('reported', $inspection->status);
        $this->assertEquals($driverUser->id, $inspection->reported_by);
        $this->assertContains('Ban belakang bocor', $inspection->damage_items ?? []);

        $this->actingAs($inspector)->post('/inspections/' . $inspection->id . '/start')->assertRedirect();
        $inspection->refresh();
        $this->assertSame('processing', $inspection->status);
        $this->assertEquals($inspector->id, $inspection->assigned_to);

        $this->actingAs($inspector)->post('/inspections/' . $inspection->id . '/complete', [
            'resolution_notes' => 'Ban diganti dengan sparepart baru, unit layak sewa kembali.',
        ])->assertRedirect();
        $inspection->refresh();
        $this->assertSame('completed', $inspection->status);
        $this->assertStringContainsString('Ban diganti', $inspection->resolution_notes);
    }

    public function test_admin_scoped_to_category_sees_only_own_category_pages(): void
    {
        $tenda = Category::where('slug', 'sewa-tenda')->firstOrFail();
        $drone = Category::where('slug', 'sewa-drone')->firstOrFail();

        $owner = $this->makeOwner('cat_owner@example.test');
        $adminTenda = $this->makeAdmin('cat_admin_tenda@example.test', $owner, $tenda->id);
        $adminDrone = $this->makeAdmin('cat_admin_drone@example.test', $owner, $drone->id);

        $tendaItem = $this->makeCamping($owner, $tenda);
        $droneItem = $this->makeDrone($owner, $drone);
        $customer = $this->makeRoleUser('cat_cust@example.test', 'user');

        $tendaBooking = $this->makeItemBooking($customer, $tendaItem, $tenda);
        $droneBooking = $this->makeItemBooking($customer, $droneItem, $drone);

        $this->actingAs($adminTenda)->get('/bookings')
            ->assertSee($tendaBooking->booking_code)
            ->assertDontSee($droneBooking->booking_code);
        $this->actingAs($adminDrone)->get('/bookings')
            ->assertSee($droneBooking->booking_code)
            ->assertDontSee($tendaBooking->booking_code);

        $this->actingAs($adminTenda)->get('/owner/elektronik/tenda')->assertSee($tendaItem->name);
        $this->actingAs($adminTenda)->get('/owner/elektronik/drone')
            ->assertRedirect(route('owner.elektronik.type', 'tenda'));

        $this->actingAs($adminDrone)->get('/owner/elektronik/drone')->assertSee($droneItem->name);
        $this->actingAs($adminDrone)->get('/owner/elektronik/tenda')
            ->assertRedirect(route('owner.elektronik.type', 'drone'));
    }

    public function test_new_booking_notifies_superadmin_owner_and_matching_category_admin_only(): void
    {
        Storage::fake('public');

        $tenda = Category::where('slug', 'sewa-tenda')->firstOrFail();
        $drone = Category::where('slug', 'sewa-drone')->firstOrFail();

        $owner = $this->makeOwner('notif_owner@example.test');
        $adminTenda = $this->makeAdmin('notif_admin_tenda@example.test', $owner, $tenda->id);
        $adminDrone = $this->makeAdmin('notif_admin_drone@example.test', $owner, $drone->id);

        $tendaItem = $this->makeCamping($owner, $tenda);
        $customer = $this->makeRoleUser('notif_cust@example.test', 'user');
        $superadmin = User::where('role', 'superadmin')->firstOrFail();

        $this->actingAs($customer)->post('/bookings/store-item/tenda', [
            'item_id' => $tendaItem->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(2)->format('Y-m-d H:i'),
            'urgency' => 'normal',
            'with_insurance' => '1',
            'payment_plan' => 'full',
            'accessories' => [],
            'ktp_photo' => UploadedFile::fake()->image('ktp.jpg', 400, 300),
        ])->assertRedirect();

        $booking = Booking::where('user_id', $customer->id)->where('item_id', $tendaItem->id)->first();
        $this->assertNotNull($booking);
        $this->assertNotNull($booking->invoice);

        $titles = DB::table('notifications')
            ->where('type', \App\Notifications\BookingCreated::class)
            ->where('data', 'like', '%' . $booking->booking_code . '%')
            ->get();

        $notifiedIds = $titles->pluck('notifiable_id');
        $this->assertContains($superadmin->id, $notifiedIds);
        $this->assertContains($owner->id, $notifiedIds);
        $this->assertContains($adminTenda->id, $notifiedIds);
        $this->assertNotContains($adminDrone->id, $notifiedIds);
    }

    public function test_category_scoped_admin_cannot_view_other_category_booking_detail(): void
    {
        $tenda = Category::where('slug', 'sewa-tenda')->firstOrFail();
        $drone = Category::where('slug', 'sewa-drone')->firstOrFail();

        $owner = $this->makeOwner('scope_owner@example.test');
        $adminTenda = $this->makeAdmin('scope_admin_tenda@example.test', $owner, $tenda->id);

        $droneItem = $this->makeDrone($owner, $drone);
        $customer = $this->makeRoleUser('scope_cust@example.test', 'user');
        $droneBooking = $this->makeItemBooking($customer, $droneItem, $drone);

        $this->actingAs($adminTenda)->get('/bookings/' . $droneBooking->id)->assertForbidden();
    }

    public function test_category_scoped_staff_sees_only_own_items_on_manual_create(): void
    {
        $tenda = Category::where('slug', 'sewa-tenda')->firstOrFail();
        $hp = Category::where('slug', 'sewa-hp')->firstOrFail();

        $owner = $this->makeOwner('manual_owner@example.test');
        $adminTenda = $this->makeAdmin('manual_admin_tenda@example.test', $owner, $tenda->id);

        $tendaItem = $this->makeCamping($owner, $tenda);
        $hpItem = $this->makePhone($owner, $hp);

        $this->actingAs($adminTenda)->get('/bookings/manual-create')
            ->assertSee($tendaItem->name)
            ->assertDontSee($hpItem->name);

        $super = User::where('role', 'superadmin')->firstOrFail();
        $this->actingAs($super)->get('/bookings/manual-create')
            ->assertSee($tendaItem->name)
            ->assertSee($hpItem->name);
    }

    public function test_store_multi_rejects_item_outside_staff_category(): void
    {
        Storage::fake('public');

        $tenda = Category::where('slug', 'sewa-tenda')->firstOrFail();
        $hp = Category::where('slug', 'sewa-hp')->firstOrFail();

        $owner = $this->makeOwner('multi_owner@example.test');
        $adminTenda = $this->makeAdmin('multi_admin_tenda@example.test', $owner, $tenda->id);

        $hpItem = $this->makePhone($owner, $hp);
        $customer = $this->makeRoleUser('multi_cust@example.test', 'user');
        $this->makeItemBooking($customer, $hpItem, $hp);

        $before = Booking::count();

        $this->actingAs($adminTenda)->post('/bookings/store-multi', [
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(2)->format('Y-m-d H:i'),
            'payment_plan' => 'full',
            'items' => [
                ['type' => 'hp', 'id' => $hpItem->id, 'with_insurance' => '0', 'urgency' => 'normal', 'accessories' => []],
            ],
            'ktp_photo' => UploadedFile::fake()->image('ktp.jpg', 400, 300),
        ])->assertSessionHas('error');

        $this->assertEquals($before, Booking::count());
    }

    public function test_category_dashboard_and_bookings_scoped_for_owner(): void
    {
        $tenda = Category::where('slug', 'sewa-tenda')->firstOrFail();
        $hp = Category::where('slug', 'sewa-hp')->firstOrFail();

        $ownerTenda = $this->makeOwner('own_tenda@example.test', $tenda->id);
        $ownerHp = $this->makeOwner('own_hp@example.test', $hp->id);

        $tendaItem = $this->makeCamping($ownerTenda, $tenda);
        $hpItem = $this->makePhone($ownerHp, $hp);
        $customer = $this->makeRoleUser('own_cust@example.test', 'user');
        $tendaBooking = $this->makeItemBooking($customer, $tendaItem, $tenda);
        $hpBooking = $this->makeItemBooking($customer, $hpItem, $hp);

        $this->actingAs($ownerTenda)->get('/dashboard')->assertOk();
        $this->actingAs($ownerHp)->get('/dashboard')->assertOk();

        $this->actingAs($ownerTenda)->get('/bookings')
            ->assertSee($tendaBooking->booking_code)
            ->assertDontSee($hpBooking->booking_code);
        $this->actingAs($ownerHp)->get('/bookings')
            ->assertSee($hpBooking->booking_code)
            ->assertDontSee($tendaBooking->booking_code);
    }

    public function test_brand_catalog_shows_all_brands_for_superadmin_and_scopes_by_category(): void
    {
        $tenda = Category::where('slug', 'sewa-tenda')->firstOrFail();

        $owner = $this->makeOwner('catalog_owner@example.test');
        $adminTenda = $this->makeAdmin('catalog_admin_tenda@example.test', $owner, $tenda->id);
        $this->makeCamping($owner, $tenda);

        $super = User::where('role', 'superadmin')->firstOrFail();
        $this->actingAs($super)->get('/admin/brand-catalog')
            ->assertOk()
            ->assertSee('RED')
            ->assertSee('Eiger')
            ->assertSee('Gambar/Mobil/Brand/toyota.png')
            ->assertSee('Gambar/Mobil/Mobil/alphard.jpg');

        $this->actingAs($adminTenda)->get('/admin/brand-catalog')
            ->assertOk()
            ->assertSee('Eiger')
            ->assertDontSee('RED')
            ->assertDontSee('Apple')
            ->assertDontSee('Gambar/Mobil/Brand/toyota.png')
            ->assertDontSee('Gambar/Mobil/Mobil/alphard.jpg');
    }

    public function test_verified_manual_payment_updates_invoice_amounts_and_booking_status(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();
        $owner = $this->makeOwner('pay_owner@example.test');
        $customer = $this->makeRoleUser('pay_cust@example.test', 'user');
        $vehicle = $this->makeVehicle($owner, 'PAY-CAR-1', $mobil);
        $super = User::where('role', 'superadmin')->firstOrFail();

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'category_id' => $mobil->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(3)->format('Y-m-d H:i'),
            'base_price' => 600000,
            'total_price' => 600000,
            'final_price' => 600000,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_plan' => 'full',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber('rental'),
            'booking_id' => $booking->id,
            'user_id' => $customer->id,
            'owner_id' => $owner->id,
            'category_id' => $mobil->id,
            'type' => 'rental',
            'subtotal' => 600000,
            'total_amount' => 600000,
            'paid_amount' => 0,
            'due_amount' => 600000,
            'status' => 'sent',
            'due_date' => now()->addDays(7),
        ]);

        $this->actingAs($customer)->post('/invoices/' . $invoice->id . '/pay', [
            'amount' => 600000,
            'method' => 'transfer',
            'reference_number' => 'REF-MANUAL-1',
        ])->assertRedirect();

        $payment = Payment::where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame('pending', $payment->status);

        $invoice->refresh();
        $this->assertEquals(0, (float) $invoice->paid_amount);
        $this->assertSame('sent', $invoice->status);
        $this->assertSame('unpaid', $booking->fresh()->payment_status);

        $this->actingAs($super)->post('/invoices/payments/' . $payment->id . '/verify')->assertRedirect();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertEquals(600000, (float) $invoice->paid_amount);
        $this->assertEquals(0, (float) $invoice->due_amount);
        $this->assertSame('paid', $booking->fresh()->payment_status);
        $this->assertSame('verified', $payment->fresh()->status);
    }

    public function test_admin_can_assign_driver_to_existing_booking_and_update_invoice(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();
        $owner = $this->makeOwner('assign_owner@example.test');
        $customer = $this->makeRoleUser('assign_cust@example.test', 'user');
        $driverUser = $this->makeRoleUser('assign_driver@example.test', 'driver');
        $vehicle = $this->makeVehicle($owner, 'ASSIGN-CAR-1', $mobil);
        $vehicle->update(['with_driver' => true, 'with_driver_daily_price' => 150000]);
        $super = User::where('role', 'superadmin')->firstOrFail();

        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'owner_id' => $owner->id,
            'license_number' => 'SIM-ASSIGN-1',
            'daily_salary' => 150000,
            'status' => 'off_duty',
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'category_id' => $mobil->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(3)->format('Y-m-d H:i'),
            'base_price' => 200000,
            'total_price' => 200000,
            'final_price' => 200000,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_plan' => 'full',
        ]);

        $this->actingAs($super)->post('/bookings/' . $booking->id . '/assign-driver', [
            'driver_id' => $driver->id,
        ])->assertRedirect();

        $booking->refresh();
        $this->assertSame($driver->id, $booking->driver_id);
        $this->assertTrue($booking->with_driver);
        $this->assertEquals(300000, (float) $booking->driver_price);
        $this->assertEquals(500000, (float) $booking->total_price);
        $this->assertEquals(500000, (float) $booking->final_price);
        $this->assertSame('on_duty', $driver->fresh()->status);

        $this->actingAs($super)->post('/bookings/' . $booking->id . '/remove-driver')->assertRedirect();
        $booking->refresh();
        $this->assertNull($booking->driver_id);
        $this->assertFalse($booking->with_driver);
        $this->assertEquals(0, (float) $booking->driver_price);
        $this->assertEquals(200000, (float) $booking->final_price);
        $this->assertSame('off_duty', $driver->fresh()->status);
    }

    public function test_invoice_can_be_resent_multiple_times_until_paid(): void
    {
        $owner = $this->makeOwner('resend_owner@example.test');
        $customer = $this->makeRoleUser('resend_cust@example.test', 'user');
        $super = User::where('role', 'superadmin')->firstOrFail();

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber('rental'),
            'user_id' => $customer->id,
            'owner_id' => $owner->id,
            'type' => 'rental',
            'subtotal' => 500000,
            'total_amount' => 500000,
            'paid_amount' => 0,
            'due_amount' => 500000,
            'status' => 'sent',
            'due_date' => now()->addDays(7),
        ]);

        $this->actingAs($super)->post('/invoices/' . $invoice->id . '/send')->assertRedirect();
        $this->assertSame('sent', $invoice->fresh()->status);

        $this->actingAs($super)->post('/invoices/' . $invoice->id . '/send')->assertRedirect();
        $this->assertSame('sent', $invoice->fresh()->status);

        $invoice->update(['status' => 'paid', 'paid_amount' => 500000, 'due_amount' => 0, 'paid_at' => now()]);
        $this->actingAs($super)->post('/invoices/' . $invoice->id . '/send')->assertRedirect();
        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_booking_detail_renders_with_driver_card_and_assign_panel(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();
        $owner = $this->makeOwner('detail_owner@example.test');
        $otherOwner = $this->makeOwner('detail_other_owner@example.test');
        $customer = $this->makeRoleUser('detail_cust@example.test', 'user');
        $driverUser = $this->makeRoleUser('detail_driver@example.test', 'driver');
        $vehicle = $this->makeVehicle($owner, 'DETAIL-CAR-1', $mobil);
        $super = User::where('role', 'superadmin')->firstOrFail();

        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'owner_id' => $otherOwner->id,
            'license_number' => 'SIM-DETAIL-1',
            'daily_salary' => 150000,
            'status' => 'off_duty',
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'category_id' => $mobil->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(3)->format('Y-m-d H:i'),
            'base_price' => 200000,
            'total_price' => 200000,
            'final_price' => 200000,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_plan' => 'full',
        ]);

        $this->actingAs($super)->get('/bookings/' . $booking->id)
            ->assertOk()
            ->assertSee('Lepas Kunci')
            ->assertSee('Kelola Driver')
            ->assertSee('Tugaskan')
            ->assertSee($driverUser->name)
            ->assertSee('Tersedia');
    }

    private function makeCompany(User $owner, string $name = 'PT Test Karyawan'): \App\Models\Company
    {
        return \App\Models\Company::create([
            'user_id' => $owner->id,
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . uniqid(),
            'city' => 'Jakarta',
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    private function makeOwnedDriver(User $owner, \App\Models\Company $company, string $email, string $position = 'Driver'): Driver
    {
        $user = $this->makeRoleUser($email, 'driver');
        return Driver::create([
            'user_id' => $user->id,
            'owner_id' => $owner->id,
            'company_id' => $company->id,
            'position' => $position,
            'license_number' => 'SIM-' . strtoupper(substr(uniqid(), -6)),
            'daily_salary' => 150000,
            'status' => 'off_duty',
            'is_active' => true,
        ]);
    }

    public function test_superadmin_and_owner_manage_drivers_but_admin_denied(): void
    {
        $tenda = Category::where('slug', 'sewa-tenda')->firstOrFail();
        $owner = $this->makeOwner('drv_owner@example.test');
        $company = $this->makeCompany($owner);
        $driver = $this->makeOwnedDriver($owner, $company, 'drv_own@example.test');
        $admin = $this->makeAdmin('drv_admin@example.test', $owner, $tenda->id);
        $super = User::where('role', 'superadmin')->firstOrFail();

        $this->actingAs($super)->get('/drivers')->assertOk()->assertSee($company->name);
        $this->actingAs($super)->get('/drivers/' . $driver->id)->assertOk()->assertSee($company->name);

        $this->actingAs($owner)->get('/drivers')->assertOk()->assertSee($driver->user->name);

        $this->actingAs($admin)->get('/drivers')->assertForbidden();
        $this->actingAs($admin)->post('/drivers')->assertForbidden();
        $this->actingAs($admin)->delete('/drivers/' . $driver->id)->assertForbidden();
    }

    public function test_superadmin_can_create_driver_and_karyawan_account_for_company(): void
    {
        $owner = $this->makeOwner('drv_create_owner@example.test');
        $company = $this->makeCompany($owner, 'PT Driver Baru');
        $super = User::where('role', 'superadmin')->firstOrFail();

        $this->actingAs($super)->post('/drivers', [
            'company_id' => $company->id,
            'name' => 'Slamet Supir',
            'email' => 'slamet@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '081234000111',
            'position' => 'Driver',
            'license_number' => 'SIM-CREATE-1',
            'license_type' => 'A',
            'license_expiry' => now()->addYears(1)->format('Y-m-d'),
            'daily_salary' => 150000,
            'trip_salary' => 50000,
        ])->assertRedirect();

        $driver = Driver::whereHas('user', fn($q) => $q->where('email', 'slamet@example.test'))->first();
        $this->assertNotNull($driver);
        $this->assertSame($company->id, $driver->company_id);
        $this->assertSame($owner->id, $driver->owner_id);
        $this->assertSame('driver', $driver->user->role);
        $this->assertSame('SIM-CREATE-1', $driver->license_number);

        $this->actingAs($super)->post('/drivers', [
            'company_id' => $company->id,
            'name' => 'Siti Admin',
            'email' => 'siti@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'position' => 'Karyawan',
            'daily_salary' => 100000,
            'trip_salary' => 0,
        ])->assertRedirect();

        $karyawan = Driver::whereHas('user', fn($q) => $q->where('email', 'siti@example.test'))->first();
        $this->assertNotNull($karyawan);
        $this->assertSame('Karyawan', $karyawan->position);
        $this->assertNull($karyawan->license_number);

        $this->actingAs($super)->get('/drivers')->assertSee('Slamet Supir')->assertSee('Siti Admin');

        $this->actingAs($super)->put('/drivers/' . $driver->id, [
            'company_id' => $company->id,
            'name' => 'Slamet Supir Edan',
            'position' => 'Driver Senior',
            'license_number' => 'SIM-CREATE-1',
            'license_type' => 'A',
            'daily_salary' => 200000,
            'status' => 'off_duty',
            'is_active' => 1,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect();

        $this->assertSame('Slamet Supir Edan', $driver->fresh()->user->name);
        $this->assertSame('Driver Senior', $driver->fresh()->position);
        $this->assertEquals(200000, (float) $driver->fresh()->daily_salary);

        $this->actingAs($super)->delete('/drivers/' . $karyawan->id)->assertRedirect();
        $this->assertNull(Driver::find($karyawan->id));
        $this->assertNull(User::find($karyawan->user_id));
    }

    public function test_owner_only_manages_own_drivers(): void
    {
        $owner = $this->makeOwner('drv_own_owner@example.test');
        $other = $this->makeOwner('drv_other_owner@example.test');
        $ownCompany = $this->makeCompany($owner, 'PT Own Aja');
        $otherCompany = $this->makeCompany($other, 'PT Lain');
        $ownDriver = $this->makeOwnedDriver($owner, $ownCompany, 'drv_mine@example.test');
        $otherDriver = $this->makeOwnedDriver($other, $otherCompany, 'drv_theirs@example.test');

        $this->actingAs($owner)->get('/drivers/')
            ->assertOk()
            ->assertSee('drv_mine@example.test')
            ->assertDontSee('drv_theirs@example.test');

        $this->actingAs($owner)->get('/drivers/' . $ownDriver->id)->assertOk();

        $otherShow = $this->actingAs($owner)->get('/drivers/' . $otherDriver->id);
        $this->assertContains($otherShow->getStatusCode(), [403, 404]);

        $otherUpdate = $this->actingAs($owner)->put('/drivers/' . $otherDriver->id, [
            'name' => 'Hacked',
            'password' => '',
            'password_confirmation' => '',
            'license_number' => 'X',
            'daily_salary' => 1,
            'status' => 'off_duty',
            'is_active' => 1,
        ]);
        $this->assertContains($otherUpdate->getStatusCode(), [403, 404]);

        $otherDelete = $this->actingAs($owner)->delete('/drivers/' . $otherDriver->id);
        $this->assertContains($otherDelete->getStatusCode(), [403, 404]);
    }
}