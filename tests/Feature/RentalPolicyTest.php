<?php

namespace Tests\Feature;

use App\Models\Rental;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RentalPolicyTest extends TestCase
{
    use DatabaseTransactions;

    private User $customerUser;
    private User $ownerMerchantA;
    private User $ownerMerchantB;
    private Vehicle $vehicleMerchantA;
    private Vehicle $vehicleMerchantB;
    private Rental $rentalMerchantA;
    private Rental $rentalMerchantB;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure category exists
        $category = \App\Models\Category::firstOrCreate(
            ['id' => 1],
            ['name' => 'Mobil', 'slug' => 'mobil', 'is_active' => true]
        );

        $this->customerUser = User::create([
            'name' => 'Customer User',
            'email' => 'customer@test.local',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->ownerMerchantA = User::create([
            'name' => 'Owner Merchant A',
            'email' => 'owner-a@test.local',
            'password' => Hash::make('password'),
            'phone' => '081234567891',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->ownerMerchantB = User::create([
            'name' => 'Owner Merchant B',
            'email' => 'owner-b@test.local',
            'password' => Hash::make('password'),
            'phone' => '081234567892',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->vehicleMerchantA = Vehicle::create([
            'name' => 'Vehicle A',
            'slug' => 'vehicle-a-'.uniqid(),
            'license_plate' => 'B1234XYZ',
            'owner_id' => $this->ownerMerchantA->id,
            'category_id' => $category->id,
            'status' => 'available',
            'is_active' => true,
            'daily_price' => 100000,
        ]);

        $this->vehicleMerchantB = Vehicle::create([
            'name' => 'Vehicle B',
            'slug' => 'vehicle-b-'.uniqid(),
            'license_plate' => 'B5678ABC',
            'owner_id' => $this->ownerMerchantB->id,
            'category_id' => $category->id,
            'status' => 'available',
            'is_active' => true,
            'daily_price' => 100000,
        ]);

        $this->rentalMerchantA = Rental::create([
            'user_id' => $this->customerUser->id,
            'vehicle_id' => $this->vehicleMerchantA->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'pickup_location' => 'Location A',
            'dropoff_location' => 'Location B',
            'status' => 'pending',
            'daily_rate' => 100000,
            'total_days' => 2,
            'subtotal' => 200000,
            'total_amount' => 200000,
        ]);

        $this->rentalMerchantB = Rental::create([
            'user_id' => $this->customerUser->id,
            'vehicle_id' => $this->vehicleMerchantB->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'pickup_location' => 'Location A',
            'dropoff_location' => 'Location B',
            'status' => 'pending',
            'daily_rate' => 100000,
            'total_days' => 2,
            'subtotal' => 200000,
            'total_amount' => 200000,
        ]);
    }

    public function test_customer_can_view_own_rental()
    {
        $response = $this->actingAs($this->customerUser)
            ->get(route('rentals.show', $this->rentalMerchantA->id));

        $response->assertStatus(200);
    }

    public function test_customer_cannot_view_rental_of_different_customer()
    {
        $otherCustomer = User::create([
            'name' => 'Other Customer',
            'email' => 'other@test.local',
            'password' => Hash::make('password'),
            'phone' => '081234567893',
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($otherCustomer)
            ->get(route('rentals.show', $this->rentalMerchantA->id));

        $response->assertStatus(403);
    }

    public function test_owner_can_view_rental_of_own_merchant()
    {
        $response = $this->actingAs($this->ownerMerchantA)
            ->get(route('rentals.show', $this->rentalMerchantA->id));

        $response->assertStatus(200);
    }

    public function test_owner_cannot_view_rental_of_different_merchant()
    {
        $response = $this->actingAs($this->ownerMerchantA)
            ->get(route('rentals.show', $this->rentalMerchantB->id));

        $response->assertStatus(403);
    }

    public function test_superadmin_can_view_any_rental()
    {
        $superadmin = User::create([
            'name' => 'Superadmin',
            'email' => 'superadmin@test.local',
            'password' => Hash::make('password'),
            'phone' => '081234567894',
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superadmin)
            ->get(route('rentals.show', $this->rentalMerchantA->id));

        $response->assertStatus(200);
    }

    public function test_owner_cannot_update_rental_of_different_merchant()
    {
        $response = $this->actingAs($this->ownerMerchantA)
            ->put(route('rentals.update', $this->rentalMerchantB->id), [
                'vehicle_id' => $this->vehicleMerchantA->id,
                'start_date' => now()->addDay(),
                'end_date' => now()->addDays(3),
                'pickup_location' => 'Test',
                'dropoff_location' => 'Test',
            ]);

        $response->assertStatus(403);
    }

    public function test_owner_cannot_cancel_rental_of_different_merchant()
    {
        $response = $this->actingAs($this->ownerMerchantA)
            ->post(route('rentals.cancel', $this->rentalMerchantB->id), [
                'cancelled_reason' => 'Test reason',
            ]);

        $response->assertStatus(403);
    }
}
