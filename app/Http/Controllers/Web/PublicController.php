<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)
            ->withCount(['vehicles' => fn($q) => $q->where('status', 'available')->where('is_active', true)])
            ->get();

        $query = Vehicle::with(['category', 'reviews'])
            ->where('is_active', true)
            ->where('status', 'available');

        if ($request->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('brand', 'like', "%{$request->search}%")
                  ->orWhere('model', 'like', "%{$request->search}%");
            });
        }

        if ($request->min_price) {
            $query->where('daily_price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('daily_price', '<=', $request->max_price);
        }

        if ($request->with_driver !== null) {
            $query->where('with_driver', $request->boolean('with_driver'));
        }

        $vehicles = $query->latest()->paginate(12)->withQueryString();

        $featuredVehicles = Vehicle::with(['category', 'reviews'])
            ->where('is_active', true)
            ->where('status', 'available')
            ->inRandomOrder()
            ->limit(6)
            ->get();

        return view('public.index', compact('categories', 'vehicles', 'featuredVehicles'));
    }

    public function about()
    {
        return view('pages.about');
    }

    public function products(Request $request)
    {
        $query = Vehicle::with(['category', 'reviews'])
            ->where('is_active', true)
            ->where('status', 'available');

        if ($request->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('brand', 'like', "%{$request->search}%")
                  ->orWhere('model', 'like', "%{$request->search}%");
            });
        }

        if ($request->max_price) {
            $query->where('daily_price', '<=', $request->max_price);
        }

        $products = $query->latest()->paginate(12)->withQueryString();

        return view('pages.products', compact('products'));
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function show(string $slug)
    {
        $vehicle = Vehicle::with(['category', 'owner', 'reviews.user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $relatedVehicles = Vehicle::where('category_id', $vehicle->category_id)
            ->where('id', '!=', $vehicle->id)
            ->where('is_active', true)
            ->where('status', 'available')
            ->limit(4)
            ->get();

        return view('public.show', compact('vehicle', 'relatedVehicles'));
    }
}
