<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DriverSalary;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryWebController extends Controller
{
    public function index(Request $request)
    {
        $query = DriverSalary::with(['driver.user', 'owner']);

        if (Auth::user()->role === 'driver') {
            $driver = Driver::where('user_id', Auth::id())->first();
            if ($driver) {
                $query->where('driver_id', $driver->id);
            }
        } elseif (Auth::user()->isMerchantStaff()) {
            $query->where('owner_id', Auth::user()->merchantId());
        }

        if ($request->period_month) {
            $query->where('period_month', $request->period_month);
        }

        $salaries = $query->latest()->paginate(15);

        return view('salaries.index', compact('salaries'));
    }

    public function create()
    {
        $ownerId = Auth::user()->merchantId();
        $drivers = Driver::when($ownerId, fn($q) => $q->where('owner_id', $ownerId))->where('is_active', true)->get();
        return view('salaries.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'period_month' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'base_salary' => 'nullable|numeric|min:0',
            'trip_bonus' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['owner_id'] = Auth::user()->merchantId() ?? Auth::id();
        $validated['base_salary'] = $validated['base_salary'] ?? 0;
        $validated['trip_bonus'] = $validated['trip_bonus'] ?? 0;
        $validated['overtime_pay'] = $validated['overtime_pay'] ?? 0;
        $validated['deductions'] = $validated['deductions'] ?? 0;
        $validated['total_salary'] = ($validated['base_salary'] + $validated['trip_bonus'] + $validated['overtime_pay']) - $validated['deductions'];

        DriverSalary::create($validated);

        return redirect()->route('salaries.index')->with('success', 'Gaji driver berhasil dicatat');
    }

    public function show(DriverSalary $salary)
    {
        $salary->load(['driver.user', 'owner', 'invoice']);
        return view('salaries.show', compact('salary'));
    }

    public function approve(DriverSalary $salary)
    {
        if (Auth::user()->isMerchantStaff() && (int)$salary->owner_id !== (int)Auth::user()->merchantId()) {
            abort(403);
        }
        $salary->update(['status' => 'approved']);
        return back()->with('success', 'Gaji berhasil disetujui');
    }

    public function pay(DriverSalary $salary)
    {
        if (Auth::user()->isMerchantStaff() && (int)$salary->owner_id !== (int)Auth::user()->merchantId()) {
            abort(403);
        }
        $salary->update(['status' => 'paid']);
        return back()->with('success', 'Gaji berhasil dibayar');
    }
}
