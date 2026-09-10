<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $query = Driver::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_available') !== null && $request->is_available !== '') {
            $query->where('is_available', $request->boolean('is_available'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('license_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $drivers = $query->latest()->paginate(15)->withQueryString();

        return view('drivers.index', compact('drivers'));
    }

    public function create()
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('drivers.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|max:255|unique:users',
            'password'          => 'required|string|min:8|confirmed',
            'phone'             => 'required|string|max:20',
            'address'           => 'nullable|string|max:500',
            'license_number'    => 'required|string|max:50|unique:drivers,license_number',
            'license_type'      => 'required|string|max:50',
            'license_expiry'    => 'required|date|after:today',
            'daily_salary'      => 'required|numeric|min:0',
        ]);

        try {
            $userData = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'phone'      => $request->phone,
                'address'    => $request->address,
                'role'       => User::roleForPosition($request->input('position'), $user->category_id),
                'is_active'  => true,
            ]);

            $driver = Driver::create([
                'user_id'            => $userData->id,
                'license_number'     => $request->license_number,
                'license_type'       => $request->license_type,
                'license_expiry'     => $request->license_expiry,
                'daily_salary'       => $request->daily_salary,
                'is_active'          => true,
                'status'             => 'active',
            ]);

            return redirect()->route('drivers.show', $driver->id)
                ->with('success', 'Driver berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan driver: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            if ($user->isDriver()) {
                $driver = Driver::where('user_id', $user->id)->first();
                if (!$driver || $driver->id != $id) {
                    abort(403, 'Anda tidak memiliki akses.');
                }
            } else {
                abort(403, 'Anda tidak memiliki akses.');
            }
        }

        $driver = Driver::with('user', 'rentals', 'tripReports', 'payrolls')->findOrFail($id);

        $completedTrips = $driver->rentals()->where('status', 'completed')->count();
        $activeTrips = $driver->rentals()->whereIn('status', ['confirmed', 'ongoing'])->count();
        $totalEarnings = $driver->payrolls()->where('status', 'paid')->sum('total_amount');
        $pendingPayrolls = $driver->payrolls()->where('status', 'draft')->count();

        return view('drivers.show', compact(
            'driver',
            'completedTrips',
            'activeTrips',
            'totalEarnings',
            'pendingPayrolls'
        ));
    }

    public function edit($id)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $driver = Driver::with('user')->findOrFail($id);

        return view('drivers.edit', compact('driver'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $driver = Driver::findOrFail($id);

        $request->validate([
            'name'              => 'sometimes|string|max:255',
            'phone'             => 'sometimes|string|max:20',
            'address'           => 'nullable|string|max:500',
            'license_number'    => 'sometimes|string|max:50|unique:drivers,license_number,' . $driver->id,
            'license_type'      => 'sometimes|string|max:50',
            'license_expiry'    => 'sometimes|date',
            'daily_salary'      => 'sometimes|numeric|min:0',
            'is_active'         => 'boolean',
            'status'            => 'sometimes|in:active,inactive,suspended',
        ]);

        try {
            $driver->user->update([
                'name'     => $request->input('name', $driver->user->name),
                'phone'    => $request->input('phone', $driver->user->phone),
                'address'  => $request->input('address', $driver->user->address),
            ]);

            $driver->update([
                'license_number'  => $request->input('license_number', $driver->license_number),
                'license_type'    => $request->input('license_type', $driver->license_type),
                'license_expiry'  => $request->input('license_expiry', $driver->license_expiry),
                'daily_salary'    => $request->input('daily_salary', $driver->daily_salary),
                'is_active'       => $request->boolean('is_active', $driver->is_active),
                'status'          => $request->input('status', $driver->status),
            ]);

            return redirect()->route('drivers.show', $driver->id)
                ->with('success', 'Data driver berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui data driver: ' . $e->getMessage());
        }
    }

    public function payrollHistory($id)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            if ($user->isDriver()) {
                $driver = Driver::where('user_id', $user->id)->first();
                if (!$driver || $driver->id != $id) {
                    abort(403, 'Anda tidak memiliki akses.');
                }
            } else {
                abort(403, 'Anda tidak memiliki akses.');
            }
        }

        $driver = Driver::with('user')->findOrFail($id);

        $payrolls = $driver->payrolls()
            ->latest('period_end')
            ->paginate(15);

        return view('drivers.payroll-history', compact('driver', 'payrolls'));
    }
}
