<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VehicleWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::whereHas('category', fn($q) => $q->where('slug', 'mobil'))
            ->with(['category', 'owner']);

        if (Auth::user()->isMerchantStaff()) {
            $query->where('owner_id', Auth::user()->merchantId());
        }

        $categoryId = Auth::user()->merchantCategoryId();
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('license_plate', 'like', "%{$request->search}%")
                  ->orWhere('brand', 'like', "%{$request->search}%")
                  ->orWhere('model', 'like', "%{$request->search}%");
            });
        }

        $vehicles = $query->latest()->paginate(15);

        return view('vehicles.index', compact('vehicles'));
    }

    public function motor(Request $request)
    {
        $query = Vehicle::whereHas('category', fn($q) => $q->where('slug', 'motor'))
            ->with(['category', 'owner']);

        if (Auth::user()->isMerchantStaff()) {
            $query->where('owner_id', Auth::user()->merchantId());
        }

        $categoryId = Auth::user()->merchantCategoryId();
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('license_plate', 'like', "%{$request->search}%")
                  ->orWhere('brand', 'like', "%{$request->search}%")
                  ->orWhere('model', 'like', "%{$request->search}%");
            });
        }

        $vehicles = $query->latest()->paginate(15);

        return view('vehicles.motor', compact('vehicles'));
    }

    public function create(Request $request)
    {
        if (($categoryId = Auth::user()->merchantCategoryId())
            && !Category::whereKey($categoryId)->whereIn('slug', ['mobil', 'motor'])->exists()) {
            abort(403, 'Akun ini tidak memiliki kategori kendaraan.');
        }

        $categories = Category::where('is_active', true)
            ->whereIn('slug', ['mobil', 'motor'])
            ->when(Auth::user()->merchantCategoryId(), fn($q, $categoryId) => $q->whereKey($categoryId))
            ->get();
        $selectedType = $request->query('type', 'mobil');
        $defaultCategory = $categories->firstWhere('slug', $selectedType) ?? $categories->first();

        return view('vehicles.create', compact('categories', 'selectedType', 'defaultCategory'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'license_plate' => 'required|string|max:20|unique:vehicles',
            'description' => 'nullable|string',
            'daily_price' => 'required|numeric|min:0',
            'weekly_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'hourly_price' => 'nullable|numeric|min:0',
            'with_driver_daily_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,maintenance',
            'condition' => 'nullable|in:excellent,good,fair,poor',
            'seats' => 'nullable|integer|min:1',
            'transmission' => 'nullable|in:automatic,manual',
            'fuel_type' => 'nullable|in:gasoline,diesel,electric,hybrid',
            'with_driver' => 'boolean',
            'is_active' => 'boolean',
        ]);
        $this->assertCategoryAccessible((int) $validated['category_id']);
        $this->assertVehicleCategory((int) $validated['category_id']);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        $validated['owner_id'] = Auth::user()->merchantId() ?? Auth::id();
        $validated['condition'] = $validated['condition'] ?? 'good';

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('vehicles', 'public');
        }

        $vehicle = Vehicle::create($validated);
        $category = Category::find($validated['category_id']);

        if ($category && $category->slug === 'motor') {
            return redirect()->route('motors.index')->with('success', 'Motor berhasil ditambahkan');
        }

        return redirect()->route('vehicles.index')->with('success', 'Mobil berhasil ditambahkan');
    }

    public function edit(Vehicle $vehicle)
    {
        $this->guardVehicle($vehicle);
        $categories = Category::where('is_active', true)
            ->whereIn('slug', ['mobil', 'motor'])
            ->when(Auth::user()->merchantCategoryId(), fn($q, $categoryId) => $q->whereKey($categoryId))
            ->get();
        return view('vehicles.edit', compact('vehicle', 'categories'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer',
            'color' => 'nullable|string|max:50',
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate,' . $vehicle->id,
            'description' => 'nullable|string',
            'daily_price' => 'required|numeric|min:0',
            'weekly_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'hourly_price' => 'nullable|numeric|min:0',
            'with_driver_daily_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,rented,maintenance,reserved',
            'condition' => 'required|in:excellent,good,fair,poor',
            'seats' => 'nullable|integer|min:1',
            'transmission' => 'nullable|in:automatic,manual',
            'fuel_type' => 'nullable|in:gasoline,diesel,electric,hybrid',
            'with_driver' => 'boolean',
            'is_active' => 'boolean',
        ]);
        $this->guardVehicle($vehicle);
        $this->assertCategoryAccessible((int) $validated['category_id']);
        $this->assertVehicleCategory((int) $validated['category_id']);

        $validated['slug'] = $vehicle->slug;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('vehicles', 'public');
        }

        $vehicle->update($validated);
        $category = Category::find($validated['category_id']);

        if ($category && $category->slug === 'motor') {
            return redirect()->route('motors.index')->with('success', 'Data motor berhasil diperbarui');
        }

        return redirect()->route('vehicles.index')->with('success', 'Data mobil berhasil diperbarui');
    }

    public function destroy(Vehicle $vehicle)
    {
        $this->guardVehicle($vehicle);
        $isMotor = $vehicle->category && $vehicle->category->slug === 'motor';
        $vehicle->delete();

        if ($isMotor) {
            return redirect()->route('motors.index')->with('success', 'Motor berhasil dihapus');
        }

        return redirect()->route('vehicles.index')->with('success', 'Mobil berhasil dihapus');
    }

    private function assertCategoryAccessible(int $categoryId): void
    {
        $allowedCategoryId = Auth::user()->merchantCategoryId();
        if ($allowedCategoryId && $allowedCategoryId !== $categoryId) {
            abort(403, 'Kategori inventaris tidak sesuai dengan akun Anda.');
        }
    }

    private function assertVehicleCategory(int $categoryId): void
    {
        abort_unless(Category::whereKey($categoryId)->whereIn('slug', ['mobil', 'motor'])->exists(), 403, 'Kategori bukan kendaraan.');
    }

    private function guardVehicle(Vehicle $vehicle): void
    {
        $user = Auth::user();
        if ($user->isMerchantStaff() && (int) $vehicle->owner_id !== (int) $user->merchantId()) {
            abort(403, 'Unit ini bukan milik company Anda.');
        }
        $this->assertCategoryAccessible((int) $vehicle->category_id);
    }
}
