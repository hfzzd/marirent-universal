<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class MerchantAdminTest extends TestCase
{
    public function test_owner_can_register_admin_and_delete(): void
    {
        $owner = User::where('email', 'owner@marirent.com')->firstOrFail();
        $this->actingAs($owner);

        $page = $this->get(route('merchant.profile'));
        $page->assertOk();
        $page->assertSee('Pendaftaran Admin Toko');

        $uniq = 'admintest-' . uniqid() . '@marirent.com';

        $resp = $this->post(route('merchant.admin.store'), [
            'name' => 'Admin Uji',
            'email' => $uniq,
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '08123',
        ]);
        $resp->assertRedirect();
        $resp->assertSessionHas('admin_success');

        $admin = User::where('email', $uniq)->first();
        $this->assertNotNull($admin);
        $this->assertSame('admin', $admin->role);
        $this->assertSame($owner->id, $admin->owner_id);

        $destroy = $this->delete(route('merchant.admin.destroy', $admin));
        $destroy->assertRedirect();
        $this->assertNotNull(User::withTrashed()->find($admin->id)->deleted_at);

        $admin->forceDelete();
    }

    public function test_owner_cannot_register_duplicate_email(): void
    {
        $owner = User::where('email', 'owner@marirent.com')->firstOrFail();
        $this->actingAs($owner);

        $dup = User::where('role', 'admin')->firstOrFail();

        $resp = $this->from(route('merchant.profile'))->post(route('merchant.admin.store'), [
            'name' => 'Dup',
            'email' => $dup->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // ValidationException dilempar (email sudah dipakai) sehingga admin baru TIDAK dibuat.
        $this->assertInstanceOf(
            \Illuminate\Validation\ValidationException::class,
            $resp->exception
        );
        $this->assertFalse(User::where('role', 'admin')->where('owner_id', $owner->id)->where('name', 'Dup')->exists());
    }
}
