<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\DriverSalary;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryWebController extends Controller
{
    private function scopeOwnerIds(): array
    {
        $user = Auth::user();
        if ($user->isSuperAdmin()) {
            return [];
        }
        $ownerId = $user->isOwner() ? (int) Auth::id() : $user->merchantId();
        return $ownerId ? [(int) $ownerId] : [0];
    }

    public function index(Request $request)
    {
        $query = DriverSalary::with(['driver.user', 'driver.company', 'owner']);

        $allowedOwners = $this->scopeOwnerIds();
        if (!empty($allowedOwners)) {
            $query->whereIn('owner_id', $allowedOwners);
        }

        if (Auth::user()->role === 'driver') {
            $driver = Driver::where('user_id', Auth::id())->first();
            if ($driver) {
                $query->where('driver_id', $driver->id);
            }
        }

        if ($request->filled('company_id') && Auth::user()->isSuperAdmin()) {
            $ownerId = Company::where('id', $request->company_id)->value('user_id');
            $query->where('owner_id', $ownerId);
        }

        if ($request->period_month) {
            $query->where('period_month', $request->period_month);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = "%{$request->search}%";
            $query->whereHas('driver.user', fn($q) => $q->where('name', 'like', $search));
        }

        $salaries = $query->latest()->paginate(15)->withQueryString();

        $companies = Auth::user()->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('salaries.index', compact('salaries', 'companies'));
    }

    public function create(Request $request)
    {
        $isSuperadmin = Auth::user()->isSuperAdmin();
        $companies = collect();

        $ownerFilter = null;
        if ($isSuperadmin) {
            $companies = Company::where('is_active', true)->orderBy('name')->get();
            $request->validate(['company_id' => 'required|exists:companies,id']);
            $ownerFilter = Company::where('id', $request->company_id)->value('user_id');
        } else {
            $ownerFilter = Auth::user()->isOwner() ? Auth::id() : Auth::user()->merchantId();
        }

        $drivers = Driver::with('user', 'company')
            ->where('owner_id', $ownerFilter)
            ->where('is_active', true)
            ->orderByDesc('position')
            ->get();

        $selectedCompanyId = $request->company_id;

        return view('salaries.create', compact('drivers', 'companies', 'isSuperadmin', 'selectedCompanyId'));
    }

    public function store(Request $request)
    {
        $isSuperadmin = Auth::user()->isSuperAdmin();

        $rules = [
            'driver_id' => 'required|exists:drivers,id',
            'period_month' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'base_salary' => 'nullable|numeric|min:0',
            'trip_bonus' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ];
        if ($isSuperadmin) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        $validated = $request->validate($rules);

        $driver = Driver::with('user', 'company')->findOrFail($validated['driver_id']);

        if ($isSuperadmin) {
            $ownerId = Company::where('id', $validated['company_id'])->value('user_id');
            if (!(int) $ownerId || (int) $driver->owner_id !== (int) $ownerId) {
                abort(403, 'Driver tidak terdaftar pada company tersebut.');
            }
        } else {
            $ownerId = Auth::user()->isOwner() ? Auth::id() : Auth::user()->merchantId();
            if (!(int) $ownerId || (int) $driver->owner_id !== (int) $ownerId) {
                abort(403, 'Driver/karyawan bukan bagian dari company Anda.');
            }
        }

        $baseSalary = $validated['base_salary'] ?? $driver->daily_salary ?? 0;
        $tripBonus = $validated['trip_bonus'] ?? 0;
        $overtimePay = $validated['overtime_pay'] ?? 0;
        $deductions = $validated['deductions'] ?? 0;

        DriverSalary::create([
            'driver_id' => $driver->id,
            'owner_id' => $ownerId,
            'period_month' => $validated['period_month'],
            'base_salary' => $baseSalary,
            'trip_bonus' => $tripBonus,
            'overtime_pay' => $overtimePay,
            'deductions' => $deductions,
            'total_salary' => ($baseSalary + $tripBonus + $overtimePay) - $deductions,
            'status' => 'draft',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('salaries.index')->with('success', 'Gaji driver/karyawan berhasil dicatat');
    }

    public function show(DriverSalary $salary)
    {
        $this->guardSalary($salary);
        $salary->load(['driver.user', 'driver.company', 'owner', 'invoice']);
        return view('salaries.show', compact('salary'));
    }

    public function approve(DriverSalary $salary)
    {
        $this->guardSalary($salary);
        $salary->update(['status' => 'approved']);
        return back()->with('success', 'Gaji berhasil disetujui');
    }

    public function pay(DriverSalary $salary)
    {
        $this->guardSalary($salary);
        $salary->update(['status' => 'paid']);
        return back()->with('success', 'Gaji berhasil dibayar');
    }

    private function guardSalary(DriverSalary $salary): void
    {
        $allowedOwners = $this->scopeOwnerIds();
        if (!empty($allowedOwners) && !in_array((int) $salary->owner_id, $allowedOwners, true)) {
            abort(403, 'Anda tidak memiliki akses ke data gaji ini.');
        }
    }
}
