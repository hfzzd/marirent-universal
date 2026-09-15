<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginIdentifierTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(?string $phone = null, string $email = null): User
    {
        return User::create([
            'name' => 'Login ' . uniqid(),
            'email' => $email ?? (uniqid() . '@example.test'),
            'password' => Hash::make('password'),
            'phone' => $phone,
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    public function test_web_login_via_phone(): void
    {
        $user = $this->makeUser('081234567201');

        $this->post('/login', [
            'login_identifier' => '081234567201',
            'password' => 'password',
        ])->assertRedirect();

        $this->assertTrue(Auth::check());
        $this->assertSame($user->id, Auth::id());
    }

    public function test_web_login_via_phone_with_formatting(): void
    {
        $user = $this->makeUser('081234567202');

        $this->post('/login', [
            'login_identifier' => '+62 812-3456-7202',
            'password' => 'password',
        ])->assertRedirect();

        $this->assertSame($user->id, Auth::id());
    }

    public function test_web_login_via_email(): void
    {
        $email = uniqid() . '@example.test';
        $user = $this->makeUser(null, $email);

        $this->post('/login', [
            'login_identifier' => strtoupper($email),
            'password' => 'password',
        ])->assertRedirect();

        $this->assertSame($user->id, Auth::id());
    }

    public function test_web_login_wrong_password_shows_error(): void
    {
        $this->makeUser('081234567203');

        $this->from('/login')->post('/login', [
            'login_identifier' => '081234567203',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('login_identifier');
    }

    public function test_web_login_fallback_legacy_email_field(): void
    {
        $email = uniqid() . '@example.test';
        $user = $this->makeUser(null, $email);

        $this->post('/login', [
            'email' => $email,
            'password' => 'password',
        ])->assertRedirect();

        $this->assertSame($user->id, Auth::id());
    }

    public function test_api_login_via_phone(): void
    {
        $user = $this->makeUser('081234567204');

        $this->postJson('/api/login', [
            'login_identifier' => '081234567204',
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['success', 'data' => ['user', 'token']])
            ->assertJsonPath('data.user.phone', '6281234567204');

        $this->assertSame($user->email, \App\Models\User::find($user->id)->email);
    }

    public function test_api_login_via_email(): void
    {
        $email = uniqid() . '@example.test';
        $this->makeUser(null, $email);

        $this->postJson('/api/login', [
            'login_identifier' => $email,
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['data' => ['token']]);
    }

    public function test_api_login_via_phone_field(): void
    {
        $this->makeUser('081234567205');

        $this->postJson('/api/login', [
            'phone' => '081234567205',
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['data' => ['token']]);
    }

    public function test_api_login_fallback_legacy_email_field(): void
    {
        $email = uniqid() . '@example.test';
        $this->makeUser(null, $email);

        $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['data' => ['token']]);
    }

    public function test_api_login_wrong_phone_returns_422(): void
    {
        $this->makeUser('081234567206');

        $this->postJson('/api/login', [
            'login_identifier' => '081234567206',
            'password' => 'wrong-password',
        ])->assertStatus(422)->assertJsonValidationErrors('phone');
    }
}