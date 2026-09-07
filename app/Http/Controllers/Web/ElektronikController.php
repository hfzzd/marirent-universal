<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use App\Models\Phone;
use App\Models\CampingEquipment;
use App\Models\Playstation;
use App\Models\Drone;
use App\Models\MusicalInstrument;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ElektronikController extends Controller
{
    private function getType($type)
    {
        return match ($type) {
            'kamera', 'hp', 'tenda', 'ps', 'drone', 'musik' => $type,
            default => null,
        };
    }

    private function getModel($type)
    {
        return match ($type) {
            'kamera' => new Camera(),
            'hp' => new Phone(),
            'tenda' => new CampingEquipment(),
            'ps' => new Playstation(),
            'drone' => new Drone(),
            'musik' => new MusicalInstrument(),
            default => null,
        };
    }

    private function getTableName($type)
    {
        return match ($type) {
            'kamera' => 'cameras',
            'hp' => 'phones',
            'tenda' => 'camping_equipments',
            'ps' => 'playstations',
            'drone' => 'drones',
            'musik' => 'musical_instruments',
            default => null,
        };
    }

    private function isOwner(): bool
    {
        return Auth::user()->isOwner() || Auth::user()->isAdmin();
    }

    private function getRoutePrefix(): string
    {
        return $this->isOwner() ? 'owner' : 'superadmin';
    }

    private function restrictedType(): ?string
    {
        $categoryId = Auth::user()->merchantCategoryId();

        if (!$categoryId) {
            return null;
        }

        $category = Category::find($categoryId);

        if (!$category) {
            return null;
        }

        $type = match ($category->slug) {
            'sewa-hp' => 'hp',
            'sewa-kamera' => 'kamera',
            'sewa-tenda' => 'tenda',
            'sewa-ps' => 'ps',
            'sewa-drone' => 'drone',
            'sewa-alat-musik' => 'musik',
            default => null,
        };

        return $type;
    }

    /**
     * Tipe elektronik yang boleh dimiliki akun owner/admin saat ini.
     * Superadmin & owner multi-kategori boleh akses semua tipe.
     */
    private function allowedTypes(): array
    {
        if (!$this->isOwner()) {
            return ['kamera', 'hp', 'tenda', 'ps', 'drone', 'musik'];
        }

        $restricted = $this->restrictedType();

        return $restricted ? [$restricted] : ['kamera', 'hp', 'tenda', 'ps', 'drone', 'musik'];
    }

    /**
     * Untuk halaman GET (browse/create/edit): arahkan owner yang kategori-nya
     * tidak sesuai ke tipe miliknya sendiri, agar tidak muncul error 403.
     */
    private function redirectIfNotAccessible(string $type)
    {
        $restricted = $this->restrictedType();

        if ($restricted && $type !== $restricted) {
            return redirect()->route($this->getRoutePrefix() . '.elektronik.type', $restricted);
        }

        return null;
    }

    /**
     * Untuk operasi tulis (create/update/delete): tetap tolak akses
     * jika tipe tidak sesuai dengan kategori akun owner/admin.
     */
    private function assertTypeAccessible(string $type): void
    {
        $restricted = $this->restrictedType();

        if ($restricted && $type !== $restricted) {
            abort(403);
        }
    }

    private function assertCategoryAccessible(int $categoryId): void
    {
        $allowedCategoryId = Auth::user()->merchantCategoryId();
        if ($allowedCategoryId && $allowedCategoryId !== $categoryId) {
            abort(403, 'Kategori inventaris tidak sesuai dengan akun Anda.');
        }
    }

    private function allowedCategories()
    {
        return Category::where('is_active', true)
            ->when(Auth::user()->merchantCategoryId(), fn($q, $categoryId) => $q->whereKey($categoryId))
            ->get();
    }

    private function staffCategoryId(): ?int
    {
        return $this->isOwner() ? Auth::user()->merchantCategoryId() : null;
    }

    private function staffOwnerId(): int
    {
        return Auth::user()->merchantId() ?? Auth::id();
    }

    public function index(Request $request, $type)
    {
        if (!$this->getType($type)) abort(404);

        if ($redirect = $this->redirectIfNotAccessible($type)) {
            return $redirect;
        }

        $model = $this->getModel($type);
        $query = $model->with(['category', 'owner']);

        if ($this->isOwner()) {
            $query->where('owner_id', $this->staffOwnerId());
        }

        if ($categoryId = $this->staffCategoryId()) {
            $query->where('category_id', $categoryId);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(15);

        $ownerFilter = $this->isOwner() ? fn($q) => $q->where('owner_id', $this->staffOwnerId()) : null;
        $categoryId = $this->staffCategoryId();
        $counts = [];

        foreach ($countTypes = ['kamera' => Camera::class, 'hp' => Phone::class, 'tenda' => CampingEquipment::class, 'ps' => Playstation::class, 'drone' => Drone::class, 'musik' => MusicalInstrument::class] as $key => $countModel) {
            $countQuery = $countModel::query();
            if ($ownerFilter) {
                $countQuery->where('owner_id', $this->staffOwnerId());
            }
            if ($categoryId) {
                $countQuery->where('category_id', $categoryId);
            }
            $counts[$key] = $countQuery->count();
        }

        $prefix = $this->getRoutePrefix();

        return view('superadmin.elektronik', compact('items', 'type', 'counts', 'prefix') + ['allowedTypes' => $this->allowedTypes()]);
    }

    public function create($type)
    {
        if (!$this->getType($type)) abort(404);

        if ($redirect = $this->redirectIfNotAccessible($type)) {
            return $redirect;
        }

        $categories = $this->allowedCategories();
        $prefix = $this->getRoutePrefix();

        return view('superadmin.elektronik-create', compact('type', 'categories', 'prefix'));
    }

    public function store(Request $request, $type)
    {
        if (!$this->getType($type)) abort(404);
        $this->assertTypeAccessible($type);

        $baseRules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'daily_price' => 'required|numeric|min:0',
            'weekly_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'hourly_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,maintenance',
            'condition' => 'required|in:excellent,good,fair,poor',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ];

        $extraRules = match ($type) {
            'kamera' => [
                'camera_model' => 'nullable|string|max:100',
                'sensor_size' => 'nullable|string|max:50',
                'lens_included' => 'nullable|string|max:255',
                'accessories' => 'nullable|string',
            ],
            'hp' => [
                'phone_model' => 'nullable|string|max:100',
                'storage_capacity' => 'nullable|string|max:20',
                'ram' => 'nullable|string|max:20',
                'color' => 'nullable|string|max:50',
            ],
            'tenda' => [
                'equipment_model' => 'nullable|string|max:100',
                'tent_type' => 'nullable|string|max:50',
                'capacity' => 'nullable|integer|min:1',
                'weight' => 'nullable|string|max:20',
                'material' => 'nullable|string|max:100',
            ],
            'ps' => [
                'console_model' => 'nullable|string|max:100',
                'storage_capacity' => 'nullable|string|max:20',
                'controllers_count' => 'nullable|integer|min:1',
                'color' => 'nullable|string|max:50',
                'accessories' => 'nullable|string',
            ],
            'drone' => [
                'drone_model' => 'nullable|string|max:100',
                'camera_resolution' => 'nullable|string|max:100',
                'flight_time' => 'nullable|string|max:50',
                'max_range' => 'nullable|string|max:50',
                'weight' => 'nullable|string|max:50',
                'accessories' => 'nullable|string',
            ],
            'musik' => [
                'instrument_type' => 'nullable|in:gitar,keyboard,drum,bass,ukulele,lainnya',
                'instrument_model' => 'nullable|string|max:100',
                'color' => 'nullable|string|max:50',
                'accessories' => 'nullable|string',
            ],
            default => [],
        };

        $validated = $request->validate(array_merge($baseRules, $extraRules));
        $this->assertCategoryAccessible((int) $validated['category_id']);
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        $validated['owner_id'] = $this->staffOwnerId();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('elektronik', 'public');
        }

        if (($type === 'kamera' || $type === 'ps' || $type === 'drone' || $type === 'musik') && isset($validated['accessories'])) {
            $validated['accessories'] = array_map('trim', explode(',', $validated['accessories']));
        }

        if ($type === 'tenda' && isset($validated['tent_type'])) {
            $validated['type'] = $validated['tent_type'];
            unset($validated['tent_type']);
        }

        $model = $this->getModel($type);
        $model->create($validated);

        $prefix = $this->getRoutePrefix();
        return redirect()->route($prefix . '.elektronik.type', $type)
            ->with('success', ucfirst($type) . ' berhasil ditambahkan');
    }

    public function edit($type, $id)
    {
        if (!$this->getType($type)) abort(404);

        if ($redirect = $this->redirectIfNotAccessible($type)) {
            return $redirect;
        }

        $model = $this->getModel($type);
        $item = $model->findOrFail($id);

        if ($this->isOwner() && $item->owner_id !== $this->staffOwnerId()) {
            abort(403);
        }
        $this->assertCategoryAccessible((int) $item->category_id);

        $categories = $this->allowedCategories();
        $prefix = $this->getRoutePrefix();

        return view('superadmin.elektronik-edit', compact('type', 'item', 'categories', 'prefix'));
    }

    public function update(Request $request, $type, $id)
    {
        if (!$this->getType($type)) abort(404);
        $this->assertTypeAccessible($type);

        $model = $this->getModel($type);
        $item = $model->findOrFail($id);

        if ($this->isOwner() && $item->owner_id !== $this->staffOwnerId()) {
            abort(403);
        }
        $this->assertCategoryAccessible((int) $item->category_id);

        $baseRules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'daily_price' => 'required|numeric|min:0',
            'weekly_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'hourly_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,rented,maintenance,reserved',
            'condition' => 'required|in:excellent,good,fair,poor',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ];

        $extraRules = match ($type) {
            'kamera' => [
                'camera_model' => 'nullable|string|max:100',
                'sensor_size' => 'nullable|string|max:50',
                'lens_included' => 'nullable|string|max:255',
                'accessories' => 'nullable|string',
            ],
            'hp' => [
                'phone_model' => 'nullable|string|max:100',
                'storage_capacity' => 'nullable|string|max:20',
                'ram' => 'nullable|string|max:20',
                'color' => 'nullable|string|max:50',
            ],
            'tenda' => [
                'equipment_model' => 'nullable|string|max:100',
                'tent_type' => 'nullable|string|max:50',
                'capacity' => 'nullable|integer|min:1',
                'weight' => 'nullable|string|max:20',
                'material' => 'nullable|string|max:100',
            ],
            'ps' => [
                'console_model' => 'nullable|string|max:100',
                'storage_capacity' => 'nullable|string|max:20',
                'controllers_count' => 'nullable|integer|min:1',
                'color' => 'nullable|string|max:50',
                'accessories' => 'nullable|string',
            ],
            'drone' => [
                'drone_model' => 'nullable|string|max:100',
                'camera_resolution' => 'nullable|string|max:100',
                'flight_time' => 'nullable|string|max:50',
                'max_range' => 'nullable|string|max:50',
                'weight' => 'nullable|string|max:50',
                'accessories' => 'nullable|string',
            ],
            'musik' => [
                'instrument_type' => 'nullable|in:gitar,keyboard,drum,bass,ukulele,lainnya',
                'instrument_model' => 'nullable|string|max:100',
                'color' => 'nullable|string|max:50',
                'accessories' => 'nullable|string',
            ],
            default => [],
        };

        $validated = $request->validate(array_merge($baseRules, $extraRules));
        $this->assertCategoryAccessible((int) $validated['category_id']);
        $validated['slug'] = $item->slug;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('elektronik', 'public');
        }

        if (($type === 'kamera' || $type === 'ps' || $type === 'drone' || $type === 'musik') && isset($validated['accessories'])) {
            $validated['accessories'] = array_map('trim', explode(',', $validated['accessories']));
        }

        if ($type === 'tenda' && isset($validated['tent_type'])) {
            $validated['type'] = $validated['tent_type'];
            unset($validated['tent_type']);
        }

        $item->update($validated);

        $prefix = $this->getRoutePrefix();
        return redirect()->route($prefix . '.elektronik.type', $type)
            ->with('success', ucfirst($type) . ' berhasil diperbarui');
    }

    public function destroy($type, $id)
    {
        if (!$this->getType($type)) abort(404);
        $this->assertTypeAccessible($type);

        $model = $this->getModel($type);
        $item = $model->findOrFail($id);

        if ($this->isOwner() && $item->owner_id !== $this->staffOwnerId()) {
            abort(403);
        }
        $this->assertCategoryAccessible((int) $item->category_id);

        $item->delete();

        $prefix = $this->getRoutePrefix();
        return redirect()->route($prefix . '.elektronik.type', $type)
            ->with('success', ucfirst($type) . ' berhasil dihapus');
    }
}
