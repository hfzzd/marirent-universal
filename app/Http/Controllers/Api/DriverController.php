<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::with(['user', 'owner'])
            ->where('is_active', true);

        if ($request->owner_id) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->available) {
            $query->where('status', 'off_duty');
        }

        $drivers = $query->paginate(min($request->get('per_page', 15), 50));

        return response()->json(['success' => true, 'data' => $drivers]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->isOwner() && !$request->user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'license_type' => 'nullable|string|max:20',
            'daily_salary' => 'required|numeric|min:0',
            'trip_salary' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['owner_id'] = $request->user()->id;

        $driver = Driver::create($validated);
        $driver->user->update(['role' => 'driver']);

        return response()->json([
            'success' => true, 'message' => 'Driver berhasil ditambahkan', 'data' => $driver->load('user'),
        ], 201);
    }

    public function show(Driver $driver)
    {
        $driver->load(['user', 'owner', 'bookings' => fn($q) => $q->latest()->limit(10)]);

        return response()->json(['success' => true, 'data' => $driver]);
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'license_number' => 'sometimes|string|max:50',
            'license_expiry' => 'sometimes|nullable|date',
            'license_type' => 'sometimes|nullable|string|max:20',
            'daily_salary' => 'sometimes|numeric|min:0',
            'trip_salary' => 'sometimes|nullable|numeric|min:0',
            'status' => 'sometimes|in:active,inactive,on_trip,off_duty',
            'is_active' => 'sometimes|boolean',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        $driver->update($validated);

        return response()->json([
            'success' => true, 'message' => 'Driver berhasil diperbarui', 'data' => $driver,
        ]);
    }

    public function destroy(Driver $driver)
    {
        $driver->update(['is_active' => false, 'status' => 'inactive']);

        return response()->json(['success' => true, 'message' => 'Driver berhasil dinonaktifkan']);
    }
}
