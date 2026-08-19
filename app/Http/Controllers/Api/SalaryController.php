<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverSalary;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $query = DriverSalary::with(['driver.user', 'owner', 'invoice']);

        if ($request->driver_id) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->period_month) {
            $query->where('period_month', $request->period_month);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $salaries = $query->latest()->paginate(min($request->get('per_page', 15), 50));

        return response()->json(['success' => true, 'data' => $salaries]);
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

        $existing = DriverSalary::where('driver_id', $validated['driver_id'])
            ->where('period_month', $validated['period_month'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false, 'message' => 'Gaji untuk periode ini sudah ada',
            ], 422);
        }

        $validated['owner_id'] = $request->user()->id;
        $validated['base_salary'] = $validated['base_salary'] ?? 0;
        $validated['trip_bonus'] = $validated['trip_bonus'] ?? 0;
        $validated['overtime_pay'] = $validated['overtime_pay'] ?? 0;
        $validated['deductions'] = $validated['deductions'] ?? 0;
        $validated['total_salary'] = ($validated['base_salary'] + $validated['trip_bonus'] + $validated['overtime_pay']) - $validated['deductions'];

        $salary = DriverSalary::create($validated);

        return response()->json([
            'success' => true, 'message' => 'Gaji driver berhasil dicatat', 'data' => $salary->load('driver.user'),
        ], 201);
    }

    public function show(DriverSalary $salary)
    {
        $salary->load(['driver.user', 'owner', 'invoice']);
        return response()->json(['success' => true, 'data' => $salary]);
    }

    public function approve(DriverSalary $salary)
    {
        $salary->update(['status' => 'approved']);

        $driver = $salary->driver;
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber('driver_salary'),
            'booking_id' => $salary->driver->bookings()->latest()->first()?->id,
            'user_id' => $driver->user_id,
            'owner_id' => $salary->owner_id,
            'type' => 'driver_salary',
            'subtotal' => $salary->total_salary,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $salary->total_salary,
            'due_amount' => $salary->total_salary,
            'status' => 'sent',
            'due_date' => now()->addDays(7),
            'notes' => "Gaji driver {$driver->user->name} periode {$salary->period_month}",
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => "Gaji Pokok - {$salary->period_month}",
            'quantity' => 1,
            'unit_price' => $salary->base_salary,
            'total_price' => $salary->base_salary,
        ]);

        if ($salary->trip_bonus > 0) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Bonus Perjalanan',
                'quantity' => 1,
                'unit_price' => $salary->trip_bonus,
                'total_price' => $salary->trip_bonus,
            ]);
        }

        $salary->update(['invoice_id' => $invoice->id, 'status' => 'approved']);

        return response()->json([
            'success' => true, 'message' => 'Gaji disetujui dan invoice dibuat', 'data' => $salary->load('invoice'),
        ]);
    }

    public function pay(DriverSalary $salary)
    {
        if ($salary->status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Gaji harus disetujui dulu'], 422);
        }

        $salary->update(['status' => 'paid']);

        if ($salary->invoice) {
            $salary->invoice->update([
                'status' => 'paid',
                'paid_amount' => $salary->total_salary,
                'due_amount' => 0,
                'paid_at' => now(),
                'payment_method' => 'transfer',
            ]);
        }

        return response()->json([
            'success' => true, 'message' => 'Gaji berhasil dibayar', 'data' => $salary,
        ]);
    }
}
