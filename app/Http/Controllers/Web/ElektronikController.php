<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use App\Models\Phone;
use App\Models\CampingEquipment;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ElektronikController extends Controller
{
    private function getType($type)
    {
        return match ($type) {
            'kamera' => 'kamera',
            'hp' => 'hp',
            'tenda' => 'tenda',
            default => null,
        };
    }

    private function getModel($type)
    {
        return match ($type) {
            'kamera' => new Camera(),
            'hp' => new Phone(),
            'tenda' => new CampingEquipment(),
            default => null,
        };
    }

    private function getTableName($type)
    {
        return match ($type) {
            'kamera' => 'cameras',
            'hp' => 'phones',
            'tenda' => 'camping_equipments',
            default => null,
        };
    }

    public function index(Request $request, $type)
    {
        if (!$this->getType($type)) abort(404);

        $model = $this->getModel($type);
        $query = $model->with(['category', 'owner']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(15);
        $counts = [
            'kamera' => Camera::count(),
            'hp' => Phone::count(),
            'tenda' => CampingEquipment::count(),
        ];

        return view('superadmin.elektronik', compact('items', 'type', 'counts'));
    }

    public function create($type)
    {
        if (!$this->getType($type)) abort(404);
        $categories = Category::where('is_active', true)->get();

        return view('superadmin.elektronik-create', compact('type', 'categories'));
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
            default => [],
        };

        $validated = $request->validate(array_merge($baseRules, $extraRules));
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        $validated['owner_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('elektronik', 'public');
        }

        if ($type === 'kamera' && isset($validated['accessories'])) {
            $validated['accessories'] = array_map('trim', explode(',', $validated['accessories']));
        }

        if ($type === 'tenda' && isset($validated['tent_type'])) {
            $validated['type'] = $validated['tent_type'];
            unset($validated['tent_type']);
        }

        $model = $this->getModel($type);
        $model->create($validated);

        return redirect()->route('superadmin.elektronik.type', $type)
            ->with('success', ucfirst($type) . ' berhasil ditambahkan');
    }

    public function edit($type, $id)
    {
        if (!$this->getType($type)) abort(404);

        $model = $this->getModel($type);
        $item = $model->findOrFail($id);
        $categories = Category::where('is_active', true)->get();

        return view('superadmin.elektronik-edit', compact('type', 'item', 'categories'));
    }

    public function update(Request $request, $type, $id)
    {
        if (!$this->getType($type)) abort(404);

        $model = $this->getModel($type);
        $item = $model->findOrFail($id);

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
            default => [],
        };

        $validated = $request->validate(array_merge($baseRules, $extraRules));
        $validated['slug'] = $item->slug;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('elektronik', 'public');
        }

        if ($type === 'kamera' && isset($validated['accessories'])) {
            $validated['accessories'] = array_map('trim', explode(',', $validated['accessories']));
        }

        if ($type === 'tenda' && isset($validated['tent_type'])) {
            $validated['type'] = $validated['tent_type'];
            unset($validated['tent_type']);
        }

        $item->update($validated);

        return redirect()->route('superadmin.elektronik.type', $type)
            ->with('success', ucfirst($type) . ' berhasil diperbarui');
    }

    public function destroy($type, $id)
    {
        if (!$this->getType($type)) abort(404);

        $model = $this->getModel($type);
        $model->findOrFail($id)->delete();

        return redirect()->route('superadmin.elektronik.type', $type)
            ->with('success', ucfirst($type) . ' berhasil dihapus');
    }
}
