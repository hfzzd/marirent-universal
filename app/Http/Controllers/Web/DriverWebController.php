<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DriverWebController extends Controller
{
    private function authorizeManage(): void
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403, 'Hanya superadmin dan owner yang dapat mengelola driver/karyawan.');
        }
    }

    private function guardDriver(Driver $driver): void
    {
        if (Auth::user()->isOwner() && (int) $driver->owner_id !== (int) Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke driver ini.');
        }
    }

    private function currentCompanyList(): \Illuminate\Support\Collection
    {
        if (Auth::user()->isSuperAdmin()) {
            return Company::where('is_active', true)->orderBy('name')->get();
        }
        return collect([$this->resolveOwnCompany()])->filter();
    }

    private function resolveOwnCompany(): ?Company
    {
        return Company::where('user_id', Auth::id())->first();
    }

    public function index(Request $request)
    {
        $this->authorizeManage();

        $query = Driver::with(['user', 'company', 'owner']);

        if (Auth::user()->isOwner()) {
            $query->where('owner_id', Auth::id());
        } elseif ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->search) {
            $search = "%{$request->search}%";
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', $search)->orWhere('email', 'like', $search)->orWhere('phone', 'like', $search))
                  ->orWhere('license_number', 'like', $search);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $drivers = $query->latest()->paginate(15)->withQueryString();
        $companies = Auth::user()->isSuperAdmin() ? Company::where('is_active', true)->orderBy('name')->get() : collect();

        return view('drivers.index', compact('drivers', 'companies'));
    }

    public function create()
    {
        $this->authorizeManage();

        $companies = $this->currentCompanyList();
        $isSuperadmin = Auth::user()->isSuperAdmin();

        return view('drivers.create', compact('companies', 'isSuperadmin'));
    }

    public function store(Request $request)
    {
        $this->authorizeManage();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'position' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:50|unique:drivers,license_number',
            'license_type' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'daily_salary' => 'required|numeric|min:0',
            'trip_salary' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $company = $this->resolveCompanyFromRequest($request);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'role' => 'driver',
            'owner_id' => $company?->user_id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Driver::create([
            'user_id' => $user->id,
            'owner_id' => $company?->user_id ?? Auth::id(),
            'company_id' => $company?->id,
            'position' => $validated['position'] ?? null,
            'license_number' => $validated['license_number'] ?? null,
            'license_type' => $validated['license_type'] ?? null,
            'license_expiry' => $validated['license_expiry'] ?? null,
            'daily_salary' => $validated['daily_salary'],
            'trip_salary' => $validated['trip_salary'] ?? 0,
            'status' => 'off_duty',
            'is_active' => true,
            'notes' => $validated['notes'] ?? null,
        ]);

        $companyName = $company?->name ?? 'tanpa company';
        return redirect()->route('drivers.index')->with('success', "Akun {$user->name} berhasil ditambahkan ke {$companyName}.");
    }

    public function show(Driver $driver)
    {
        $this->authorizeManage();
        $this->guardDriver($driver);

        $driver->load(['user', 'company', 'owner', 'salaries']);

        return view('drivers.show', compact('driver'));
    }

    public function edit(Driver $driver)
    {
        $this->authorizeManage();
        $this->guardDriver($driver);

        $driver->load(['user', 'company']);
        $companies = $this->currentCompanyList();
        $isSuperadmin = Auth::user()->isSuperAdmin();

        return view('drivers.edit', compact('driver', 'companies', 'isSuperadmin'));
    }

    public function update(Request $request, Driver $driver)
    {
        $this->authorizeManage();
        $this->guardDriver($driver);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8|confirmed',
            'position' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:50|unique:drivers,license_number,' . $driver->id,
            'license_type' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'daily_salary' => 'required|numeric|min:0',
            'trip_salary' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:active,inactive,on_trip,off_duty,on_duty',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $company = null;
        if (Auth::user()->isSuperAdmin()) {
            $request->validate([
                'company_id' => 'required|exists:companies,id',
            ]);
            $company = Company::findOrFail($request->company_id);
        } else {
            $company = $this->resolveOwnCompany() ?? $driver->company;
        }

        $userData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ];
        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }
        $driver->user->update($userData);

        $driver->user->update([
            'owner_id' => $company?->user_id ?? $driver->user->owner_id,
        ]);

        $driver->update([
            'owner_id' => $company?->user_id ?? $driver->owner_id,
            'company_id' => $company?->id ?? $driver->company_id,
            'position' => $validated['position'] ?? null,
            'license_number' => $validated['license_number'] ?? null,
            'license_type' => $validated['license_type'] ?? null,
            'license_expiry' => $validated['license_expiry'] ?? null,
            'daily_salary' => $validated['daily_salary'],
            'trip_salary' => $validated['trip_salary'] ?? 0,
            'status' => $validated['status'] ?? $driver->status,
            'is_active' => $request->boolean('is_active', $driver->is_active),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('drivers.index')->with('success', "Data {$driver->user->name} berhasil diperbarui.");
    }

    public function destroy(Driver $driver)
    {
        $this->authorizeManage();
        $this->guardDriver($driver);

        $name = $driver->user?->name ?? ('#' . $driver->id);
        $driver->delete();
        $driver->user?->delete();

        return redirect()->route('drivers.index')->with('success', "Akun {$name} berhasil dihapus.");
    }

    private function resolveCompanyFromRequest(Request $request): ?Company
    {
        if (Auth::user()->isSuperAdmin()) {
            $request->validate([
                'company_id' => 'required|exists:companies,id',
            ]);
            return Company::findOrFail($request->company_id);
        }

        return $this->resolveOwnCompany();
    }
}