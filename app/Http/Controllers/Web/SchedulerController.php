<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\SchedulerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Kalender scheduler operasional per kategori merchant.
 *
 * - Superadmin : seluruh booking + maintenance, bisa filter per kategori.
 * - Owner/admin: hanya data merchant-nya; terkunci ke kategori merchant
 *   bila merchant punya kategori (motor khusus motor, mobil khusus mobil, dst).
 */
class SchedulerController extends Controller
{
    public function index(Request $request, SchedulerService $scheduler)
    {
        $user = Auth::user();
        $isPlatform = $user->isSuperAdmin();

        $ownerId = $isPlatform ? null : $user->merchantId();
        $merchantCategoryId = $isPlatform ? null : $user->merchantCategoryId();
        $locked = !$isPlatform && $merchantCategoryId !== null;

        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $requestedSlug = $request->input('category', 'all');
        if ($locked) {
            $activeCategory = $user->merchantCategory?->slug ?? 'all';
            $categoryId = $merchantCategoryId;
        } else {
            $activeCategory = $requestedSlug ?: 'all';
            $categoryId = $scheduler->resolveCategoryId($activeCategory);
        }

        $eventsUrl = route('scheduler.events', $activeCategory !== 'all' ? ['category' => $activeCategory] : []);

        $scopeNote = $locked
            ? 'Menampilkan khusus kategori ' . ($user->merchantCategory?->name ?? '-') . ' milik toko Anda.'
            : ($isPlatform
                ? 'Menampilkan seluruh booking & maintenance. Gunakan filter kategori bila perlu.'
                : 'Menampilkan seluruh booking & maintenance milik toko Anda.');

        return view('superadmin.scheduler', compact(
            'eventsUrl', 'categories', 'activeCategory', 'locked', 'scopeNote'
        ));
    }

    public function events(Request $request, SchedulerService $scheduler)
    {
        $user = Auth::user();
        $isPlatform = $user->isSuperAdmin();

        $ownerId = $isPlatform ? null : $user->merchantId();
        $merchantCategoryId = $isPlatform ? null : $user->merchantCategoryId();

        // Merchant berkategori terkunci ke kategorinya sendiri.
        if (!$isPlatform && $merchantCategoryId !== null) {
            $categoryId = $merchantCategoryId;
        } else {
            $categoryId = $scheduler->resolveCategoryId($request->input('category'));
            // Merchant tanpa kategori boleh filter; tanpa filter = semua kategorinya.
            if (!$isPlatform && $categoryId === null) {
                $categoryId = null;
            }
        }

        $start = $request->input('start') ? \Carbon\Carbon::parse($request->input('start')) : now()->startOfMonth();
        $end = $request->input('end') ? \Carbon\Carbon::parse($request->input('end')) : now()->endOfMonth();

        return response()->json($scheduler->events($start, $end, $ownerId, $categoryId));
    }
}
