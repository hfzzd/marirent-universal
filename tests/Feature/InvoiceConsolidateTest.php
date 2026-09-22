<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InvoiceConsolidateTest extends TestCase
{
    use DatabaseTransactions;

    private function makeOwner(string $email): User
    {
        return User::create([
            'name' => 'Owner ' . $email,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);
    }

    private function makeCustomer(string $email): User
    {
        return User::create([
            'name' => 'Cust ' . $email,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    private function makeVehicle(User $owner, Category $cat, string $name): Vehicle
    {
        return Vehicle::create([
            'category_id' => $cat->id,
            'owner_id' => $owner->id,
            'name' => $name . '-' . uniqid(),
            'slug' => strtolower($name) . '-' . uniqid(),
            'brand' => 'Test',
            'model' => 'M',
            'license_plate' => 'T' . strtoupper(substr(uniqid(), -5)),
            'daily_price' => 200000,
            'weekly_price' => 1200000,
            'monthly_price' => 5000000,
            'hourly_price' => 25000,
            'with_driver' => false,
            'status' => 'available',
            'condition' => 'excellent',
            'is_active' => true,
        ]);
    }

    public function test_create_lists_bookings_with_solo_invoices(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();
        $owner = $this->makeOwner('list_owner@example.test');
        $customer = $this->makeCustomer('list_cust@example.test');
        $super = User::where('role', 'superadmin')->firstOrFail();

        $car1 = $this->makeVehicle($owner, $mobil, 'LIST1');
        $car2 = $this->makeVehicle($owner, $mobil, 'LIST2');

        // Buat 2 booking via HTTP -> masing-masing dapat invoice solo otomatis
        $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $car1->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(3)->format('Y-m-d H:i'),
            'with_driver' => '0',
        ])->assertRedirect();
        $car2->update(['status' => 'available']);
        // Booking kedua beda tanggal agar tidak auto-merge? auto-merge akan gabung,
        // jadi paksa pisah dengan membuat invoice via Booking::create langsung
        $b2 = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $car2->id,
            'category_id' => $mobil->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(7),
            'base_price' => 400000,
            'total_price' => 400000,
            'final_price' => 400000,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_plan' => 'full',
        ]);
        // Buatkan invoice solo manual untuk b2 (simulasi alur lama)
        $svc = app(InvoiceService::class);
        $b1 = Booking::where('user_id', $customer->id)->where('vehicle_id', $car1->id)->firstOrFail();
        // b1 sudah punya invoice dari auto-merge logic; pastikan b2 juga punya solo
        Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber('rental'),
            'booking_id' => $b2->id,
            'user_id' => $customer->id,
            'owner_id' => $owner->id,
            'category_id' => $mobil->id,
            'type' => 'rental',
            'subtotal' => 400000,
            'total_amount' => 400000,
            'paid_amount' => 0,
            'due_amount' => 400000,
            'status' => 'sent',
            'due_date' => now()->addDays(7),
        ]);

        // Halaman create harus menampilkan booking (tidak kosong)
        $response = $this->actingAs($super)->get('/invoices/create')->assertOk();
        // Set user filter via query? halaman pakai Alpine, tapi data harus ada di HTML
        $response->assertSee($b1->booking_code);
        $response->assertSee($b2->booking_code);
    }

    public function test_store_consolidates_solo_invoices_into_one_category(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();
        $owner = $this->makeOwner('cons_owner@example.test');
        $customer = $this->makeCustomer('cons_cust@example.test');
        $super = User::where('role', 'superadmin')->firstOrFail();

        $car1 = $this->makeVehicle($owner, $mobil, 'CONS1');
        $car2 = $this->makeVehicle($owner, $mobil, 'CONS2');

        $b1 = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $car1->id,
            'category_id' => $mobil->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'base_price' => 400000,
            'total_price' => 400000,
            'final_price' => 400000,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_plan' => 'full',
        ]);
        $b2 = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $car2->id,
            'category_id' => $mobil->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(4),
            'end_date' => now()->addDays(6),
            'base_price' => 400000,
            'total_price' => 400000,
            'final_price' => 400000,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_plan' => 'full',
        ]);

        $inv1 = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber('rental'),
            'booking_id' => $b1->id,
            'user_id' => $customer->id,
            'owner_id' => $owner->id,
            'category_id' => $mobil->id,
            'type' => 'rental',
            'subtotal' => 400000,
            'total_amount' => 400000,
            'paid_amount' => 0,
            'due_amount' => 400000,
            'status' => 'sent',
            'due_date' => now()->addDays(7),
        ]);
        $inv2 = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber('rental'),
            'booking_id' => $b2->id,
            'user_id' => $customer->id,
            'owner_id' => $owner->id,
            'category_id' => $mobil->id,
            'type' => 'rental',
            'subtotal' => 400000,
            'total_amount' => 400000,
            'paid_amount' => 0,
            'due_amount' => 400000,
            'status' => 'sent',
            'due_date' => now()->addDays(7),
        ]);

        $this->actingAs($super)->post('/invoices', [
            'user_id' => $customer->id,
            'booking_ids' => [$b1->id, $b2->id],
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ])->assertRedirect();

        $combined = Invoice::where('user_id', $customer->id)->latest('id')->firstOrFail();
        $this->assertSame((int) $mobil->id, (int) $combined->category_id);
        $this->assertEquals(800000, (float) $combined->total_amount);
        $this->assertSame(2, $combined->items()->count());

        // Invoice solo lama terhapus (soft delete)
        $this->assertSoftDeleted('invoices', ['id' => $inv1->id]);
        $this->assertSoftDeleted('invoices', ['id' => $inv2->id]);
    }

    public function test_store_rejects_booking_with_payment(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();
        $owner = $this->makeOwner('paylock_owner@example.test');
        $customer = $this->makeCustomer('paylock_cust@example.test');
        $super = User::where('role', 'superadmin')->firstOrFail();

        $car1 = $this->makeVehicle($owner, $mobil, 'LOCK1');
        $car2 = $this->makeVehicle($owner, $mobil, 'LOCK2');

        $b1 = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $car1->id,
            'category_id' => $mobil->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'base_price' => 400000,
            'total_price' => 400000,
            'final_price' => 400000,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_plan' => 'full',
        ]);
        $b2 = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $car2->id,
            'category_id' => $mobil->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(4),
            'end_date' => now()->addDays(6),
            'base_price' => 400000,
            'total_price' => 400000,
            'final_price' => 400000,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_plan' => 'full',
        ]);

        $inv1 = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber('rental'),
            'booking_id' => $b1->id,
            'user_id' => $customer->id,
            'owner_id' => $owner->id,
            'category_id' => $mobil->id,
            'type' => 'rental',
            'subtotal' => 400000,
            'total_amount' => 400000,
            'paid_amount' => 0,
            'due_amount' => 400000,
            'status' => 'sent',
            'due_date' => now()->addDays(7),
        ]);
        // Simulasi ada pembayaran pending di inv1
        \App\Models\Payment::create([
            'payment_code' => \App\Models\Payment::generatePaymentCode(),
            'invoice_id' => $inv1->id,
            'user_id' => $customer->id,
            'amount' => 100000,
            'method' => 'transfer',
            'status' => 'pending',
            'paid_at' => now(),
        ]);
        Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber('rental'),
            'booking_id' => $b2->id,
            'user_id' => $customer->id,
            'owner_id' => $owner->id,
            'category_id' => $mobil->id,
            'type' => 'rental',
            'subtotal' => 400000,
            'total_amount' => 400000,
            'paid_amount' => 0,
            'due_amount' => 400000,
            'status' => 'sent',
            'due_date' => now()->addDays(7),
        ]);

        $this->actingAs($super)->post('/invoices', [
            'user_id' => $customer->id,
            'booking_ids' => [$b1->id, $b2->id],
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ])->assertSessionHas('error');
    }
}
