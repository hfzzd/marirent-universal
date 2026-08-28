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
        return Auth::user()->role === 'owner';
    }

    private function getRoutePrefix(): string
    {
        return $this->isOwner() ? 'owner' : 'superadmin';
    }

    public function index(Request $request, $type)
    {
        if (!$this->getType($type)) abort(404);

        $model = $this->getModel($type);
        $query = $model->with(['category', 'owner']);

        if ($this->isOwner()) {
            $query->where('owner_id', Auth::id());
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

        $ownerFilter = $this->isOwner() ? fn($q) => $q->where('owner_id', Auth::id()) : null;
        $counts = [
            'kamera' => $ownerFilter ? Camera::where('owner_id', Auth::id())->count() : Camera::count(),
            'hp' => $ownerFilter ? Phone::where('owner_id', Auth::id())->count() : Phone::count(),
            'tenda' => $ownerFilter ? CampingEquipment::where('owner_id', Auth::id())->count() : CampingEquipment::count(),
            'ps' => $ownerFilter ? Playstation::where('owner_id', Auth::id())->count() : Playstation::count(),
            'drone' => $ownerFilter ? Drone::where('owner_id', Auth::id())->count() : Drone::count(),
            'musik' => $ownerFilter ? MusicalInstrument::where('owner_id', Auth::id())->count() : MusicalInstrument::count(),
        ];

        $prefix = $this->getRoutePrefix();

        return view('superadmin.elektronik', compact('items', 'type', 'counts', 'prefix'));
    }

    public function create($type)
    {
        if (!$this->getType($type)) abort(404);
        $categories = Category::where('is_active', true)->get();
        $prefix = $this->getRoutePrefix();

        return view('superadmin.elektronik-create', compact('type', 'categories', 'prefix'));
    }

    public function store(Request $request, $type)
    {
        if (!$this->getType($type)) abort(404);

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
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        $validated['owner_id'] = Auth::id();

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

        $model = $this->getModel($type);
        $item = $model->findOrFail($id);

        if ($this->isOwner() && $item->owner_id !== Auth::id()) {
            abort(403);
        }

        $categories = Category::where('is_active', true)->get();
        $prefix = $this->getRoutePrefix();

        return view('superadmin.elektronik-edit', compact('type', 'item', 'categories', 'prefix'));
    }

    public function update(Request $request, $type, $id)
    {
        if (!$this->getType($type)) abort(404);

        $model = $this->getModel($type);
        $item = $model->findOrFail($id);

        if ($this->isOwner() && $item->owner_id !== Auth::id()) {
            abort(403);
        }

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

        $model = $this->getModel($type);
        $item = $model->findOrFail($id);

        if ($this->isOwner() && $item->owner_id !== Auth::id()) {
            abort(403);
        }

        $item->delete();

        $prefix = $this->getRoutePrefix();
        return redirect()->route($prefix . '.elektronik.type', $type)
            ->with('success', ucfirst($type) . ' berhasil dihapus');
    }
}
