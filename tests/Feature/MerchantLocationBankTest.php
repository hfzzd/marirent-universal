<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Merchant;
use App\Models\User;
use Database\Seeders\MerchantLocationBankSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MerchantLocationBankTest extends TestCase
{
    use DatabaseTransactions;

    public function test_seeder_fills_coords_and_bank_for_merchants_missing_them(): void
    {
        $owner = User::create([
            'name' => 'LocBank Owner',
            'email' => uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $merchant = Merchant::create([
            'user_id' => $owner->id,
            'slug' => 'locbank-' . uniqid(),
            'name' => 'LocBank Store ' . uniqid(),
            'city' => 'Bandung',
            'address' => 'Jl. Test No. 1, Bandung',
            'is_active' => true,
            'status' => 'active',
        ]);
        Company::ensureForOwner($owner);

        $this->assertNull($merchant->latitude);
        $this->assertNull($merchant->bank_name);

        (new MerchantLocationBankSeeder())->run();

        $merchant->refresh();
        $company = Company::where('user_id', $owner->id)->firstOrFail();

        $this->assertNotNull($merchant->latitude);
        $this->assertNotNull($merchant->longitude);
        $this->assertTrue($merchant->hasCoordinates());
        // Fallback kota Bandung + offset kecil
        $this->assertEqualsWithDelta(-6.9175, (float) $merchant->latitude, 0.01);
        $this->assertEqualsWithDelta(107.6091, (float) $merchant->longitude, 0.01);
        $this->assertNotEmpty($merchant->bank_name);
        $this->assertNotEmpty($merchant->bank_account_number);
        $this->assertNotEmpty($merchant->bank_account_holder);
        // Companies ikut sinkron
        $this->assertEquals((float) $merchant->latitude, (float) $company->latitude);
        $this->assertEquals($merchant->bank_account_number, $company->bank_account_number);
        // URL peta valid
        $this->assertStringStartsWith('https://www.google.com/maps?q=', $merchant->mapsEmbedUrl());
    }

    public function test_coordinates_formatted_with_cardinal_directions(): void
    {
        $jakarta = new Merchant(['latitude' => -6.1954000, 'longitude' => 106.8230000]);
        $this->assertEquals('LS', $jakarta->latitudeDirection());
        $this->assertEquals('BT', $jakarta->longitudeDirection());
        $this->assertEquals('6.19540° LS, 106.82300° BT', $jakarta->formattedCoordinates());
        $this->assertEquals('-6.19540, 106.82300', $jakarta->coordinatesDecimal());

        $medan = new Merchant(['latitude' => 3.5952000, 'longitude' => 98.6722000]);
        $this->assertEquals('LU', $medan->latitudeDirection());
        $this->assertEquals('3.59520° LU, 98.67220° BT', $medan->formattedCoordinates());

        $empty = new Merchant();
        $this->assertNull($empty->formattedCoordinates());
        $this->assertNull($empty->coordinatesDecimal());
    }

    public function test_seeder_does_not_overwrite_existing_custom_data(): void
    {
        $owner = User::create([
            'name' => 'Custom Owner',
            'email' => uniqid() . '@example.test',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);
        Merchant::create([
            'user_id' => $owner->id,
            'slug' => 'custom-' . uniqid(),
            'name' => 'Custom Store ' . uniqid(),
            'city' => 'Jakarta',
            'latitude' => -6.1000000,
            'longitude' => 106.9000000,
            'bank_name' => 'BCA',
            'bank_account_number' => '1112223334',
            'bank_account_holder' => 'Custom Owner',
            'is_active' => true,
            'status' => 'active',
        ]);
        Company::ensureForOwner($owner);

        (new MerchantLocationBankSeeder())->run();

        $merchant = Merchant::where('user_id', $owner->id)->firstOrFail();
        $this->assertEquals(-6.1, (float) $merchant->latitude);
        $this->assertEquals(106.9, (float) $merchant->longitude);
        $this->assertEquals('1112223334', $merchant->bank_account_number);
    }
}
