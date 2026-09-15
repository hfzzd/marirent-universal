<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Merchant;
use App\Models\User;
use App\Services\GeocodeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MerchantLocationTest extends TestCase
{
    use DatabaseTransactions;

    private function makeOwner(int $seed = 0): User
    {
        return User::create([
            'name' => 'Owner ' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);
    }

    private function makeMerchant(User $owner, array $overrides = []): Merchant
    {
        return Merchant::create(array_merge([
            'user_id' => $owner->id,
            'slug' => 'toko-' . uniqid(),
            'name' => 'Toko ' . uniqid(),
            'is_active' => true,
            'status' => 'active',
        ], $overrides));
    }

    public function test_owner_can_set_location_coordinates(): void
    {
        $owner = $this->makeOwner();
        $this->actingAs($owner);

        $this->put('/owner/store', [
            'name' => 'Toko Lokasi',
            'address' => 'Jl. Sudirman No. 1',
            'city' => 'Jakarta',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
        ])->assertRedirect();

        $merchant = Merchant::where('user_id', $owner->id)->firstOrFail();
        $company = Company::where('user_id', $owner->id)->firstOrFail();

        $this->assertEquals(-6.2, (float) $merchant->latitude);
        $this->assertEquals(106.8166667, (float) $merchant->longitude);
        $this->assertEquals((float) $merchant->latitude, (float) $company->latitude);
        $this->assertEquals((float) $merchant->longitude, (float) $company->longitude);
    }

    public function test_update_without_pin_geocodes_when_address_changed(): void
    {
        $owner = $this->makeOwner();
        $this->actingAs($owner);

        $this->mock(GeocodeService::class)
            ->shouldReceive('geocode')
            ->once()
            ->with('Jl. Merdeka, Bandung')
            ->andReturn(['latitude' => -6.9147, 'longitude' => 107.6098]);

        $this->put('/owner/store', [
            'name' => 'Toko Geocode',
            'address' => 'Jl. Merdeka',
            'city' => 'Bandung',
        ])->assertRedirect();

        $merchant = Merchant::where('user_id', $owner->id)->firstOrFail();
        $this->assertEquals(-6.9147, (float) $merchant->latitude);
        $this->assertEquals(107.6098, (float) $merchant->longitude);
    }

    public function test_update_keeps_existing_coords_when_address_unchanged(): void
    {
        $owner = $this->makeOwner();
        $merchant = $this->makeMerchant($owner, [
            'address' => 'Jl. Lama No. 9',
            'city' => 'Yogyakarta',
            'latitude' => -7.7956,
            'longitude' => 110.3695,
        ]);

        $this->actingAs($owner);

        $this->mock(GeocodeService::class)->shouldNotReceive('geocode');

        $this->put('/owner/store', [
            'name' => 'Toko Lama',
            'address' => 'Jl. Lama No. 9',
            'city' => 'Yogyakarta',
        ])->assertRedirect();

        $merchant->refresh();
        $this->assertEquals(-7.7956, (float) $merchant->latitude);
        $this->assertEquals(110.3695, (float) $merchant->longitude);
    }

    public function test_validation_rejects_out_of_range_latitude(): void
    {
        $owner = $this->makeOwner();
        $this->actingAs($owner);

        $this->put('/owner/store', [
            'name' => 'Toko Invalid',
            'latitude' => 100.0,
            'longitude' => 106.8,
        ])->assertSessionHasErrors('latitude');
    }

    public function test_validation_requires_longitude_when_latitude_given(): void
    {
        $owner = $this->makeOwner();
        $this->actingAs($owner);

        $this->put('/owner/store', [
            'name' => 'Toko Invalid',
            'latitude' => -6.2,
        ])->assertSessionHasErrors('longitude');
    }

    public function test_public_store_page_shows_google_map_when_coordinates_present(): void
    {
        $owner = $this->makeOwner();
        $merchant = $this->makeMerchant($owner, [
            'address' => 'Jl. Malioboro',
            'city' => 'Yogyakarta',
            'latitude' => -7.7956,
            'longitude' => 110.3695,
        ]);

        $this->get('/store/' . $merchant->slug)
            ->assertOk()
            ->assertSee('Lokasi Toko')
            ->assertSee('google.com/maps')
            ->assertSee('output=embed');
    }

    public function test_public_store_page_falls_back_to_address_query(): void
    {
        $owner = $this->makeOwner();
        $merchant = $this->makeMerchant($owner, [
            'address' => 'Jl. Merdeka No. 7',
            'city' => 'Jakarta',
        ]);

        $this->get('/store/' . $merchant->slug)
            ->assertOk()
            ->assertSee('Lokasi Toko')
            ->assertSee('google.com/maps')
            ->assertSee(urlencode('Jl. Merdeka No. 7, Jakarta'));
    }

    public function test_public_store_page_hides_map_when_no_location_data(): void
    {
        $owner = $this->makeOwner();
        $merchant = $this->makeMerchant($owner);

        $this->get('/store/' . $merchant->slug)
            ->assertOk()
            ->assertDontSee('Lokasi Toko')
            ->assertDontSee('google.com/maps');
    }

    public function test_stores_directory_lists_only_active_merchants(): void
    {
        $ownerActive = $this->makeOwner();
        $ownerInactive = $this->makeOwner();

        $active = $this->makeMerchant($ownerActive, [
            'name' => 'Toko Aktif',
            'city' => 'Semarang',
            'latitude' => -6.9667,
            'longitude' => 110.4167,
        ]);
        $this->makeMerchant($ownerInactive, [
            'name' => 'Toko Nonaktif',
            'city' => 'Surabaya',
            'is_active' => false,
        ]);

        $this->get('/stores')
            ->assertOk()
            ->assertSee($active->name)
            ->assertDontSee('Toko Nonaktif')
            ->assertSee('google.com/maps');
    }

    public function test_geocode_command_backfills_missing_coordinates(): void
    {
        $owner = $this->makeOwner();
        $missing = $this->makeMerchant($owner, [
            'name' => 'Toko Backfill',
            'address' => 'Jl. Backfill No. 1',
            'city' => 'Jakarta',
        ]);
        $ownerWithCoords = $this->makeOwner();
        $alreadySet = $this->makeMerchant($ownerWithCoords, [
            'name' => 'Toko Sudah Ada',
            'address' => 'Jl. Lain',
            'city' => 'Solo',
            'latitude' => -7.5667,
            'longitude' => 110.8286,
        ]);

        $this->mock(GeocodeService::class)
            ->shouldReceive('geocode')
            ->andReturnUsing(fn (string $q) => $q === 'Jl. Backfill No. 1, Jakarta'
                ? ['latitude' => -6.2088, 'longitude' => 106.8456]
                : null);

        $this->artisan('merchant:geocode', ['--delay' => 0])->assertSuccessful();

        $this->assertEquals(-6.2088, (float) $missing->refresh()->latitude);
        $this->assertEquals(106.8456, (float) $missing->refresh()->longitude);
        $this->assertEquals(-7.5667, (float) $alreadySet->refresh()->latitude);
        $this->assertEquals(110.8286, (float) $alreadySet->refresh()->longitude);
    }
}