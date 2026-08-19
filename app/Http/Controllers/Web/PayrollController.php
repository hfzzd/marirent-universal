<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            if ($user->isDriver()) {
                $driver = Driver::where('user_id', $user->id)->first();
                if ($driver) {
                    $payrolls = Payroll::with('driver.user')
                        ->where('driver_id', $driver->id)
                        ->latest('period_end')
                        ->paginate(15);
                } else {
                    $payrolls = collect()->paginate(15);
                }
            } else {
                abort(403, 'Anda tidak memiliki akses.');
            }
        } else {
            $query = Payroll::with('driver.user');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('driver_id')) {
                $query->where('driver_id', $request->driver_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('driver.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $payrolls = $query->latest('period_end')->paginate(15)->withQueryString();
        }

        $drivers = Driver::with('user')->where('status', 'active')->get();

        return view('payrolls.index', compact('payrolls', 'drivers'));
    }

    public function create()
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $drivers = Driver::with('user')->where('status', 'active')->get();

        return view('payrolls.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $request->validate([
            'driver_id'     => 'required|exists:drivers,id',
            'period_start'  => 'required|date',
            'period_end'    => 'required|date|after_or_equal:period_start',
            'base_salary'   => 'required|numeric|min:0',
            'commission'    => 'nullable|numeric|min:0',
            'bonus'         => 'nullable|numeric|min:0',
            'deduction'     => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string|max:1000',
        ]);

        try {
            $driver = Driver::findOrFail($request->driver_id);

            $totalTrips = $driver->rentals()
                ->where('status', 'completed')
                ->whereBetween('end_date', [$request->period_start, $request->period_end])
                ->count();

            $commission = $request->commission ?? 0;
            $bonus = $request->bonus ?? 0;
            $deduction = $request->deduction ?? 0;
            $totalAmount = $request->base_salary + $commission + $bonus - $deduction;

            $payroll = Payroll::create([
                'driver_id'     => $driver->id,
                'period_start'  => $request->period_start,
                'period_end'    => $request->period_end,
                'base_salary'   => $request->base_salary,
                'commission'    => $commission,
                'bonus'         => $bonus,
                'deduction'     => $deduction,
                'total_trips'   => $totalTrips,
                'total_amount'  => $totalAmount,
                'status'        => 'draft',
                'notes'         => $request->notes,
            ]);

            return redirect()->route('payrolls.show', $payroll->id)
                ->with('success', 'Payroll berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat payroll: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $payroll = Payroll::with('driver.user')->findOrFail($id);

        return view('payrolls.show', compact('payroll'));
    }

    public function approve($id)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $payroll = Payroll::findOrFail($id);

        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Hanya payroll dengan status draft yang dapat disetujui.');
        }

        try {
            $payroll->update(['status' => 'approved']);

            return redirect()->route('payrolls.show', $payroll->id)
                ->with('success', 'Payroll berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui payroll: ' . $e->getMessage());
        }
    }

    public function markPaid($id)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $payroll = Payroll::findOrFail($id);

        if ($payroll->status !== 'approved') {
            return back()->with('error', 'Hanya payroll dengan status approved yang dapat ditandai sebagai dibayar.');
        }

        try {
            $payroll->update([
                'status'    => 'paid',
                'paid_date' => now()->toDateString(),
            ]);

            return redirect()->route('payrolls.show', $payroll->id)
                ->with('success', 'Payroll berhasil ditandai sebagai dibayar.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status payroll: ' . $e->getMessage());
        }
    }
}
