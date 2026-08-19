<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Category;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with(['category', 'owner', 'reviews'])
            ->where('is_active', true);

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

        if ($request->status) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'available');
        }

        if ($request->with_driver !== null) {
            $query->where('with_driver', $request->boolean('with_driver'));
        }

        if ($request->transmission) {
            $query->where('transmission', $request->transmission);
        }

        $sortField = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortField, $sortDir);

        $perPage = min($request->get('per_page', 12), 50);
        $vehicles = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $vehicles,
        ]);
    }

    public function show(string $slug)
    {
        $vehicle = Vehicle::with(['category', 'owner', 'reviews.user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $vehicle,
        ]);
    }

    public function categories()
    {
        $categories = Category::where('is_active', true)
            ->withCount(['vehicles' => fn($q) => $q->where('status', 'available')->where('is_active', true)])
            ->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function publicList(Request $request)
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
                  ->orWhere('brand', 'like', "%{$request->search}%");
            });
        }

        $perPage = min($request->get('per_page', 12), 50);
        $vehicles = $query->paginate($perPage);

        return response()->json(['success' => true, 'data' => $vehicles]);
    }
}
