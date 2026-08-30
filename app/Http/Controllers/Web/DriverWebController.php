<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::with(['user', 'owner']);

        if (Auth::user()->isMerchantStaff()) {
            $query->where('owner_id', Auth::user()->merchantId());
        }

        if ($request->search) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        $drivers = $query->latest()->paginate(15);

        return view('drivers.index', compact('drivers'));
    }

    public function create()
    {
        return view('drivers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'license_type' => 'nullable|string|max:20',
            'daily_salary' => 'required|numeric|min:0',
            'trip_salary' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['owner_id'] = Auth::user()->merchantId() ?? Auth::id();

        $driver = Driver::create($validated);
        $driver->user->update(['role' => 'driver']);

        return redirect()->route('drivers.index')->with('success', 'Driver berhasil ditambahkan');
    }

    public function edit(Driver $driver)
    {
        return view('drivers.edit', compact('driver'));
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'license_type' => 'nullable|string|max:20',
            'daily_salary' => 'required|numeric|min:0',
            'trip_salary' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:active,inactive,on_trip,off_duty',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $driver->update($validated);

        return redirect()->route('drivers.index')->with('success', 'Driver berhasil diperbarui');
    }

    public function destroy(Driver $driver)
    {
        $driver->update(['is_active' => false, 'status' => 'inactive']);
        return redirect()->route('drivers.index')->with('success', 'Driver berhasil dinonaktifkan');
    }
}
