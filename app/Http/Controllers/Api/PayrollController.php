<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = Payroll::with(['driver']);

            if ($user->role === 'driver') {
                $query->where('driver_id', $user->id);
            }

            if ($request->filled('driver_id')) {
                $query->where('driver_id', $request->driver_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('period_start')) {
                $query->where('period_start', '>=', $request->period_start);
            }

            if ($request->filled('period_end')) {
                $query->where('period_end', '<=', $request->period_end);
            }

            $payrolls = $query->latest()->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Payrolls retrieved successfully',
                'data'    => $payrolls,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payrolls',
                'data'    => null,
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $payroll = Payroll::with(['driver', 'approvedBy', 'rentals'])->find($id);

            if (!$payroll) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll not found',
                    'data'    => null,
                ], 404);
            }

            $user = auth()->user();
            if ($user->role === 'driver' && $payroll->driver_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                    'data'    => null,
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payroll retrieved successfully',
                'data'    => $payroll,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payroll',
                'data'    => null,
            ], 500);
        }
    }

    public function generate(Request $request)
    {
        try {
            $user = $request->user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can generate payrolls',
                    'data'    => null,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'driver_id'    => 'required|exists:users,id',
                'period_start' => 'required|date',
                'period_end'   => 'required|date|after_or_equal:period_start',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'data'    => $validator->errors(),
                ], 422);
            }

            $driver = User::where('role', 'driver')->find($request->driver_id);
            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found',
                    'data'    => null,
                ], 404);
            }

            $existing = Payroll::where('driver_id', $request->driver_id)
                ->where('period_start', $request->period_start)
                ->where('period_end', $request->period_end)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll already exists for this driver and period',
                    'data'    => $existing,
                ], 422);
            }

            $completedRentals = $driver->rentals()
                ->where('status', 'completed')
                ->where('completed_at', '>=', $request->period_start)
                ->where('completed_at', '<=', $request->period_end . ' 23:59:59')
                ->get();

            $totalTrips = $completedRentals->count();
            $totalEarnings = $completedRentals->sum('driver_fee');
            $bonus = 0;
            $deductions = 0;

            $netPay = $totalEarnings + $bonus - $deductions;

            $payroll = Payroll::create([
                'driver_id'     => $request->driver_id,
                'period_start'  => $request->period_start,
                'period_end'    => $request->period_end,
                'total_trips'   => $totalTrips,
                'total_earnings' => $totalEarnings,
                'bonus'         => $bonus,
                'deductions'    => $deductions,
                'net_pay'       => $netPay,
                'status'        => 'pending',
                'rental_ids'    => $completedRentals->pluck('id')->toArray(),
            ]);

            $payroll->load('driver');

            return response()->json([
                'success' => true,
                'message' => 'Payroll generated successfully',
                'data'    => $payroll,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate payroll',
                'data'    => null,
            ], 500);
        }
    }

    public function approve($id)
    {
        try {
            $user = auth()->user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can approve payrolls',
                    'data'    => null,
                ], 403);
            }

            $payroll = Payroll::find($id);

            if (!$payroll) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll not found',
                    'data'    => null,
                ], 404);
            }

            if ($payroll->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll cannot be approved in current status',
                    'data'    => null,
                ], 422);
            }

            $payroll->update([
                'status'        => 'approved',
                'approved_by'   => $user->id,
                'approved_at'   => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payroll approved successfully',
                'data'    => $payroll->fresh('driver'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve payroll',
                'data'    => null,
            ], 500);
        }
    }

    public function markPaid($id)
    {
        try {
            $user = auth()->user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can mark payrolls as paid',
                    'data'    => null,
                ], 403);
            }

            $payroll = Payroll::find($id);

            if (!$payroll) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll not found',
                    'data'    => null,
                ], 404);
            }

            if ($payroll->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll must be approved before marking as paid',
                    'data'    => null,
                ], 422);
            }

            $payroll->update([
                'status'    => 'paid',
                'paid_at'   => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payroll marked as paid successfully',
                'data'    => $payroll->fresh('driver'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark payroll as paid',
                'data'    => null,
            ], 500);
        }
    }
}
