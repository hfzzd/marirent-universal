<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tests\TestCase;

class TempBrandProbeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_probe_admin_brand_page(): void
    {
        $tenda = Category::where('slug', 'sewa-tenda')->firstOrFail();
        $owner = User::create([
            'name' => 'Probe Owner', 'email' => 'probe_o@example.test',
            'password' => Hash::make('password'), 'role' => 'owner', 'is_active' => true,
        ]);
        $admin = User::create([
            'name' => 'Probe Admin', 'email' => 'probe_a@example.test',
            'password' => Hash::make('password'), 'role' => 'admin',
            'owner_id' => $owner->id, 'category_id' => $tenda->id, 'is_active' => true,
        ]);
        $super = User::where('role', 'superadmin')->firstOrFail();

        $resp = $this->actingAs($admin)->get('/admin/brand-catalog');
        $content = $resp->getContent();
        echo "ADMIN status: " . $resp->getStatusCode() . "\n";
        echo "ADMIN content length: " . strlen($content) . "\n";
        file_put_contents(sys_get_temp_dir() . '/admin_brand_probe.html', $content);
        $matches = [];
        preg_match_all('/>\s*([A-Z][^<]{1,40})\s*</', $content, $matches);
        $brands = array_values(array_unique(array_filter($matches[1], fn($m) => str_word_count(trim($m)) > 0)));
        echo "=== ADMIN page unique visible words ===\n" . implode(' | ', array_slice($brands, 0, 60)) . "\n\n";
        echo "ADMIN contains Eiger: " . (str_contains($content, 'Eiger') ? 'YES' : 'NO') . "\n";
        echo "ADMIN contains RED: " . (str_contains($content, 'RED') ? 'YES' : 'NO') . "\n";
        echo "ADMIN contains Apple: " . (str_contains($content, 'Apple') ? 'YES' : 'NO') . "\n";
        echo "ADMIN contains toyota.png: " . (str_contains($content, 'toyota.png') ? 'YES' : 'NO') . "\n";
        echo "ADMIN contains alphard.jpg: " . (str_contains($content, 'alphard.jpg') ? 'YES' : 'NO') . "\n";
        echo "ADMIN contains 'Tambah Foto': " . (str_contains($content, 'Tambah Foto') ? 'YES' : 'NO') . "\n";
        echo "ADMIN contains 'Belum ada brand': " . (str_contains($content, 'Belum ada brand') ? 'YES' : 'NO') . "\n";

        $sc = $this->actingAs($super)->get('/admin/brand-catalog')->getContent();
        echo "SUPER contains Eiger: " . (str_contains($sc, 'Eiger') ? 'YES' : 'NO') . "\n";
        $this->assertTrue(true);
    }
}
