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

class InvoiceCategoryTest extends TestCase
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

    private function makeBooking(User $customer, Vehicle $vehicle, Category $cat): Booking
    {
        return Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'category_id' => $cat->id,
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
    }

    public function test_store_rejects_mixed_categories(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();
        $motor = Category::where('slug', 'motor')->firstOrFail();
        $owner = $this->makeOwner('mix_owner@example.test');
        $customer = $this->makeCustomer('mix_cust@example.test');
        $super = User::where('role', 'superadmin')->firstOrFail();

        $car = $this->makeVehicle($owner, $mobil, 'MIXCAR');
        $bike = $this->makeVehicle($owner, $motor, 'MIXBIKE');
        $b1 = $this->makeBooking($customer, $car, $mobil);
        $b2 = $this->makeBooking($customer, $bike, $motor);

        $this->actingAs($super)->post('/invoices', [
            'user_id' => $customer->id,
            'booking_ids' => [$b1->id, $b2->id],
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('invoices', ['booking_id' => $b1->id, 'category_id' => $motor->id]);
    }

    public function test_store_accepts_same_category_sets_category_id(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();
        $owner = $this->makeOwner('same_owner@example.test');
        $customer = $this->makeCustomer('same_cust@example.test');
        $super = User::where('role', 'superadmin')->firstOrFail();

        $car1 = $this->makeVehicle($owner, $mobil, 'SAME1');
        $car2 = $this->makeVehicle($owner, $mobil, 'SAME2');
        $b1 = $this->makeBooking($customer, $car1, $mobil);
        $b2 = $this->makeBooking($customer, $car2, $mobil);

        $response = $this->actingAs($super)->post('/invoices', [
            'user_id' => $customer->id,
            'booking_ids' => [$b1->id, $b2->id],
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ]);
        $response->assertRedirect();

        $invoice = Invoice::where('user_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($invoice);
        $this->assertSame((int) $mobil->id, (int) $invoice->category_id);
        $this->assertEquals(800000, (float) $invoice->total_amount);
        $this->assertSame(2, $invoice->items()->count());
    }

    public function test_auto_merge_same_category_same_user(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();
        $owner = $this->makeOwner('auto_owner@example.test');
        $customer = $this->makeCustomer('auto_cust@example.test');

        $car1 = $this->makeVehicle($owner, $mobil, 'AUTO1');
        $car2 = $this->makeVehicle($owner, $mobil, 'AUTO2');

        // Booking pertama via HTTP (buat invoice baru)
        $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $car1->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(3)->format('Y-m-d H:i'),
            'with_driver' => '0',
            'payment_plan' => 'full',
        ])->assertRedirect();

        $b1 = Booking::where('user_id', $customer->id)->latest('id')->first();
        $inv1 = $b1->invoice ?? $b1->invoices()->first();
        $this->assertNotNull($inv1);
        $countBefore = Invoice::where('user_id', $customer->id)->count();

        // Booking kedua kategori sama harus menempel ke invoice yang sama
        $car2->update(['status' => 'available']);
        $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $car2->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(4)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(6)->format('Y-m-d H:i'),
            'with_driver' => '0',
            'payment_plan' => 'full',
        ])->assertRedirect();

        $countAfter = Invoice::where('user_id', $customer->id)->count();
        $this->assertSame($countBefore, $countAfter, 'Booking 1 kategori yang sama harus gabung 1 invoice, bukan terpisah');

        $b2 = Booking::where('user_id', $customer->id)->latest('id')->first();
        $inv2 = $b2->invoices()->first() ?? $b2->invoice;
        $this->assertNotNull($inv2);
        $this->assertSame((int) $inv1->id, (int) $inv2->id);
    }

    public function test_auto_merge_rejected_for_different_category(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();
        $motor = Category::where('slug', 'motor')->firstOrFail();
        $owner = $this->makeOwner('diff_owner@example.test');
        $customer = $this->makeCustomer('diff_cust@example.test');

        $car = $this->makeVehicle($owner, $mobil, 'DIFFCAR');
        $bike = $this->makeVehicle($owner, $motor, 'DIFFBIKE');

        $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $car->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(3)->format('Y-m-d H:i'),
            'with_driver' => '0',
        ])->assertRedirect();

        $countBefore = Invoice::where('user_id', $customer->id)->count();

        $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $bike->id,
            'rental_type' => 'daily',
            'start_date' => now()->addDays(1)->format('Y-m-d H:i'),
            'end_date' => now()->addDays(3)->format('Y-m-d H:i'),
            'with_driver' => '0',
        ])->assertRedirect();

        $countAfter = Invoice::where('user_id', $customer->id)->count();
        $this->assertSame($countBefore + 1, $countAfter, 'Beda kategori harus tetap terpisah (mobil vs motor)');
    }

    public function test_validate_mergeable_detects_mixed(): void
    {
        $mobil = Category::where('slug', 'mobil')->firstOrFail();
        $motor = Category::where('slug', 'motor')->firstOrFail();
        $owner = $this->makeOwner('val_owner@example.test');
        $customer = $this->makeCustomer('val_cust@example.test');

        $car = $this->makeVehicle($owner, $mobil, 'VALCAR');
        $bike = $this->makeVehicle($owner, $motor, 'VALBIKE');
        $b1 = $this->makeBooking($customer, $car, $mobil);
        $b2 = $this->makeBooking($customer, $bike, $motor);

        $svc = app(InvoiceService::class);
        $check = $svc->validateMergeable([$b1, $b2]);
        $this->assertFalse($check['ok']);
        $this->assertStringContainsString('1 kategori', $check['message']);

        $b3 = $this->makeBooking($customer, $car, $mobil);
        $ok = $svc->validateMergeable([$b1, $b3]);
        $this->assertTrue($ok['ok']);
        $this->assertSame((int) $mobil->id, (int) $ok['category_id']);
    }
}
