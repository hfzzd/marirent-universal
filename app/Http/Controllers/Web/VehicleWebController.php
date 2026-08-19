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
        $query = Vehicle::with(['category', 'owner']);

        if (Auth::user()->role === 'owner') {
            $query->where('owner_id', Auth::id());
        }

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        $vehicles = $query->latest()->paginate(15);

        return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('vehicles.create', compact('categories'));
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
            'seats' => 'nullable|integer|min:1',
            'transmission' => 'nullable|in:automatic,manual',
            'fuel_type' => 'nullable|in:gasoline,diesel,electric,hybrid',
            'with_driver' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        $validated['owner_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('vehicles', 'public');
        }

        Vehicle::create($validated);

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil ditambahkan');
    }

    public function edit(Vehicle $vehicle)
    {
        $categories = Category::where('is_active', true)->get();
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

        $validated['slug'] = $vehicle->slug;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('vehicles', 'public');
        }

        $vehicle->update($validated);

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil diperbarui');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil dihapus');
    }
}
