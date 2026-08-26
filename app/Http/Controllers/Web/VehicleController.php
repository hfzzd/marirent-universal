<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Vehicle::with('category', 'owner');

        if ($user->isOwner()) {
            $query->where('owner_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('license_plate', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active') !== null && $request->is_active !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $vehicles = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        return view('vehicles.admin-index', compact('vehicles', 'categories'));
    }

    public function create()
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $categories = Category::where('is_active', true)->get();

        return view('vehicles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $request->validate([
            'category_id'   => 'required|exists:categories,id',
            'name'          => 'required|string|max:255',
            'brand'         => 'required|string|max:255',
            'model'         => 'required|string|max:255',
            'year'          => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'license_plate'  => 'required|string|max:20|unique:vehicles,license_plate',
            'color'         => 'nullable|string|max:100',
            'seats'      => 'required|integer|min:1',
            'transmission'  => 'required|in:automatic,manual',
            'fuel_type'     => 'nullable|string|max:50',
            'daily_price'    => 'required|numeric|min:0',
            'weekly_price'   => 'required|numeric|min:0',
            'monthly_price'  => 'required|numeric|min:0',
            'description'   => 'nullable|string|max:2000',
            'gallery'        => 'nullable|array',
            'gallery.*'      => 'string|max:500',
        ]);

        try {
            $vehicle = Vehicle::create([
                'category_id'   => $request->category_id,
                'name'          => $request->name,
                'slug'          => Str::slug($request->name . '-' . $request->license_plate),
                'brand'         => $request->brand,
                'model'         => $request->model,
                'year'          => $request->year,
                'license_plate'  => $request->license_plate,
                'color'         => $request->color,
                'seats'      => $request->seats,
                'transmission'  => $request->transmission,
                'fuel_type'     => $request->fuel_type,
                'daily_price'    => $request->daily_price,
                'weekly_price'   => $request->weekly_price,
                'monthly_price'  => $request->monthly_price,
                'description'   => $request->description,
                'gallery'        => $request->gallery,
                'status'        => 'available',
                'owner_id'      => $user->isOwner() ? $user->id : ($request->owner_id ?? null),
                'is_active'     => true,
            ]);

            return redirect()->route('vehicles.show', $vehicle->id)
                ->with('success', 'Kendaraan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan kendaraan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $vehicle = Vehicle::with('category', 'owner', 'rentals')->findOrFail($id);

        return view('vehicles.admin-show', compact('vehicle'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $vehicle = Vehicle::findOrFail($id);

        if ($user->isOwner() && $vehicle->owner_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke kendaraan ini.');
        }

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $categories = Category::where('is_active', true)->get();

        return view('vehicles.edit', compact('vehicle', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $vehicle = Vehicle::findOrFail($id);

        if ($user->isOwner() && $vehicle->owner_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke kendaraan ini.');
        }

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $request->validate([
            'category_id'   => 'required|exists:categories,id',
            'name'          => 'required|string|max:255',
            'brand'         => 'required|string|max:255',
            'model'         => 'required|string|max:255',
            'year'          => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'license_plate'  => 'required|string|max:20|unique:vehicles,license_plate,' . $vehicle->id,
            'color'         => 'nullable|string|max:100',
            'seats'      => 'required|integer|min:1',
            'transmission'  => 'required|in:automatic,manual',
            'fuel_type'     => 'nullable|string|max:50',
            'daily_price'    => 'required|numeric|min:0',
            'weekly_price'   => 'required|numeric|min:0',
            'monthly_price'  => 'required|numeric|min:0',
            'description'   => 'nullable|string|max:2000',
            'status'        => 'required|in:available,rented,maintenance,reserved',
            'is_active'     => 'boolean',
            'gallery'        => 'nullable|array',
            'gallery.*'      => 'string|max:500',
        ]);

        try {
            $vehicle->update([
                'category_id'   => $request->category_id,
                'name'          => $request->name,
                'slug'          => Str::slug($request->name . '-' . $request->license_plate),
                'brand'         => $request->brand,
                'model'         => $request->model,
                'year'          => $request->year,
                'license_plate'  => $request->license_plate,
                'color'         => $request->color,
                'seats'      => $request->seats,
                'transmission'  => $request->transmission,
                'fuel_type'     => $request->fuel_type,
                'daily_price'    => $request->daily_price,
                'weekly_price'   => $request->weekly_price,
                'monthly_price'  => $request->monthly_price,
                'description'   => $request->description,
                'gallery'        => $request->gallery,
                'status'        => $request->status,
                'is_active'     => $request->boolean('is_active', true),
            ]);

            return redirect()->route('vehicles.show', $vehicle->id)
                ->with('success', 'Kendaraan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui kendaraan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $vehicle = Vehicle::findOrFail($id);

        if ($user->isOwner() && $vehicle->owner_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke kendaraan ini.');
        }

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $hasActiveRentals = $vehicle->rentals()
            ->whereIn('status', ['pending', 'confirmed', 'ongoing'])
            ->exists();

        if ($hasActiveRentals) {
            return back()->with('error', 'Kendaraan memiliki sewa aktif dan tidak dapat dihapus.');
        }

        try {
            $vehicle->update(['is_active' => false, 'status' => 'maintenance']);

            return redirect()->route('vehicles.index')
                ->with('success', 'Kendaraan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus kendaraan: ' . $e->getMessage());
        }
    }
}
