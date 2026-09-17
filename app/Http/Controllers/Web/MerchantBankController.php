<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Rekening bank toko untuk transaksi.
 * Ditampilkan ke penyewa pada halaman invoice sebagai rekening tujuan
 * pembayaran transfer, dan dikelola di sini oleh owner/admin.
 */
class MerchantBankController extends Controller
{
    private function merchant(): Merchant
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['owner', 'admin', 'superadmin'], true), 403);

        $ownerId = $user->role === 'owner' ? $user->id : $user->merchantId();

        // Superadmin tanpa merchant: tidak ada rekening untuk dikelola di sini.
        if ($user->isSuperAdmin() && !$ownerId) {
            abort(404, 'Halaman ini khusus akun merchant (owner/admin).');
        }

        abort_unless($ownerId, 403, 'Akun Anda tidak terhubung ke merchant mana pun.');

        $merchant = Merchant::firstOrCreate(
            ['user_id' => $ownerId],
            [
                'slug' => $this->uniqueSlug(User::find($ownerId)?->name ?? ('toko-' . $ownerId)),
                'name' => User::find($ownerId)?->name ?? ('Toko ' . $ownerId),
                'commission_rate' => 10,
                'is_active' => true,
                'status' => 'active',
            ]
        );
        Company::ensureForOwner(User::find($ownerId));

        return $merchant;
    }

    public function show()
    {
        $merchant = $this->merchant()->fresh();

        return view('merchant.bank', compact('merchant'));
    }

    public function update(Request $request)
    {
        $merchant = $this->merchant();

        $validated = $request->validate([
            'bank_name' => 'nullable|string|max:80',
            'bank_account_number' => 'nullable|string|max:40',
            'bank_account_holder' => 'nullable|string|max:120',
        ]);

        $merchant->update($validated);

        $owner = \App\Models\User::find($merchant->user_id);
        if ($owner) {
            Company::ensureForOwner($owner)->update($validated);
        }

        return back()->with('bank_success', 'Rekening bank toko berhasil disimpan dan tampil pada invoice transaksi.');
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name) ?: Str::random(6);
        $base = $slug;
        $i = 1;
        while (Merchant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . ($i++);
        }
        return $slug;
    }
}
