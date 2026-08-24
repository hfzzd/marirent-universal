<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Rental;
use App\Models\User;
use App\Models\Vehicle;
use Tests\TestCase;

class VehicleSwapTest extends TestCase
{
    /**
     * Jalankan terhadap database lokal (tanpa refresh agar data demo tetap utuh).
     */
    public function test_quick_swap_web_endpoint_swaps_vehicle_and_records_history(): void
    {
        $admin = User::where('role', 'superadmin')->firstOrFail();
        $booking = Booking::whereIn('status', ['confirmed', 'ongoing'])->with('vehicle')->firstOrFail();
        $target = $this->ensureAvailableVehicle($booking->vehicle->category_id, [$booking->vehicle_id]);

        $this->actingAs($admin);

        $response = $this->post(route('bookings.replace-vehicle', $booking), [
            'replacement_vehicle_id' => $target->id,
            'reason' => 'Uji HTTP quick swap',
            'price_difference' => 0,
            'mark_maintenance' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $booking->refresh();
        $this->assertSame($target->id, $booking->vehicle_id);
        $latest = $booking->replacements()->with(['originalVehicle', 'replacementVehicle'])->latest('swapped_at')->first();
        $this->assertNotNull($latest);
        $this->assertSame('Uji HTTP quick swap', $latest->reason);
        $this->assertSame($target->id, $latest->replacement_vehicle_id);
        $this->assertSame('maintenance', Vehicle::find($latest->original_vehicle_id)->status);

        // Halaman detail booking merender riwayat penggantian
        $show = $this->get(route('bookings.show', $booking));
        $show->assertOk();
        $show->assertSee('Riwayat Penggantian Kendaraan');
    }

    public function test_api_replace_vehicle_for_rental(): void
    {
        $admin = User::where('role', 'superadmin')->firstOrFail();

        $rentalVehicle = Vehicle::where('id', '!=', Booking::max('vehicle_id'))->firstOrFail();
        $rentalVehicle->update(['status' => 'rented']);

        $rental = Rental::create([
            'user_id' => User::where('role', 'user')->firstOrFail()->id,
            'vehicle_id' => $rentalVehicle->id,
            'start_date' => now()->subHour(),
            'end_date' => now()->addDay(),
            'status' => 'ongoing',
            'total_days' => 1,
            'total_amount' => 350000,
        ]);

        $target = $this->ensureAvailableVehicle($rentalVehicle->category_id, [$rentalVehicle->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/rentals/{$rental->id}/replace-vehicle", [
                'replacement_vehicle_id' => $target->id,
                'reason' => 'Uji API swap rental',
                'price_difference' => 50000,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $rental->refresh();
        $this->assertSame($target->id, $rental->vehicle_id);
        $this->assertEquals(400000.0, (float) $rental->total_amount);
        $this->assertSame('maintenance', Vehicle::find($rental->vehicleReplacements->first()->original_vehicle_id)->status);
    }

    public function test_swap_to_different_category_is_rejected(): void
    {
        $admin = User::where('role', 'superadmin')->firstOrFail();
        $booking = Booking::whereIn('status', ['confirmed', 'ongoing'])->with('vehicle')->firstOrFail();
        $vehicleIdBefore = $booking->vehicle_id;

        $otherCategory = Category::where('id', '!=', $booking->vehicle->category_id)
            ->orderByRaw('FIELD(slug, ?, ?) DESC', ['motor', 'mobil'])
            ->firstOrFail();
        $crossTarget = $this->ensureAvailableVehicle($otherCategory->id, [$booking->vehicle_id]);
        $this->assertNotSame((int) $booking->vehicle->category_id, (int) $crossTarget->category_id);

        $response = $this->actingAs($admin)
            ->post(route('bookings.replace-vehicle', $booking), [
                'replacement_vehicle_id' => $crossTarget->id,
                'reason' => 'Uji lintas kategori',
            ]);

        $response->assertRedirect();
        $this->assertStringContainsString(
            'kategori yang sama',
            (string) $response->getSession()->get('error')
        );

        // Tidak ada perubahan pada booking maupun unit
        $booking->refresh();
        $this->assertSame($vehicleIdBefore, $booking->vehicle_id);
        $this->assertSame('available', $crossTarget->fresh()->status);
    }

    public function test_api_replace_vehicle_cross_category_returns_422(): void
    {
        $admin = User::where('role', 'superadmin')->firstOrFail();

        $rentalVehicle = Vehicle::where('id', '!=', Booking::max('vehicle_id'))->firstOrFail();
        $rentalVehicle->update(['status' => 'rented']);

        $rental = Rental::create([
            'user_id' => User::where('role', 'user')->firstOrFail()->id,
            'vehicle_id' => $rentalVehicle->id,
            'start_date' => now()->subHour(),
            'end_date' => now()->addDay(),
            'status' => 'ongoing',
            'total_days' => 1,
            'total_amount' => 350000,
        ]);

        $otherCategory = Category::where('id', '!=', $rentalVehicle->category_id)->firstOrFail();
        $crossTarget = $this->ensureAvailableVehicle($otherCategory->id, [$rentalVehicle->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/rentals/{$rental->id}/replace-vehicle", [
                'replacement_vehicle_id' => $crossTarget->id,
                'reason' => 'Uji lintas kategori API',
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('kategori yang sama', $response->json('message'));

        $rental->refresh();
        $this->assertSame($rentalVehicle->id, $rental->vehicle_id);
        $this->assertSame('available', $crossTarget->fresh()->status);
    }

    public function test_api_notifications_listed_for_user(): void
    {
        $user = User::where('role', 'user')->firstOrFail();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/notifications');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure(['data' => ['unread_count', 'items']]);
    }

    private function ensureAvailableVehicle(int $categoryId, array $excludeIds = []): Vehicle
    {
        return Vehicle::where('status', 'available')
            ->where('is_active', true)
            ->where('category_id', $categoryId)
            ->whereNotIn('id', $excludeIds)
            ->first()
            ?: tap(
                Vehicle::where('category_id', $categoryId)
                    ->whereNotIn('id', $excludeIds)
                    ->firstOrFail(),
                fn ($v) => $v->update(['status' => 'available'])
            );
    }
}
