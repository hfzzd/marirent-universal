<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->withCount(['vehicles' => function ($query) {
                $query->where('status', 'available')->where('is_active', true);
            }])
            ->get();

        $featuredVehicles = Vehicle::with('category')
            ->where('is_active', true)
            ->where('status', 'available')
            ->latest()
            ->limit(8)
            ->get();

        return view('home', compact('categories', 'featuredVehicles'));
    }

    public function vehicles(Request $request)
    {
        $query = Vehicle::with('category')
            ->where('is_active', true)
            ->where('status', 'available');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('plate_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('min_price')) {
            $query->where('daily_rate', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('daily_rate', '<=', $request->max_price);
        }

        if ($request->filled('transmission')) {
            $query->where('transmission', $request->transmission);
        }

        if ($request->filled('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
        }

        $vehicles = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        return view('vehicles.index', compact('vehicles', 'categories'));
    }

    public function vehicleDetail($id)
    {
        $vehicle = Vehicle::with('category', 'owner')
            ->where('is_active', true)
            ->findOrFail($id);

        $relatedVehicles = Vehicle::with('category')
            ->where('is_active', true)
            ->where('status', 'available')
            ->where('category_id', $vehicle->category_id)
            ->where('id', '!=', $vehicle->id)
            ->limit(4)
            ->get();

        return view('vehicles.show', compact('vehicle', 'relatedVehicles'));
    }
}
