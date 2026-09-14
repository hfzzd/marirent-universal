<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HpRental;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HpRentalController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'vehicle_id'     => 'required|exists:vehicles,id',
                'customer_name'  => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_address' => 'nullable|string|max:500',
                'ic_number'      => 'required|string|max:50',
                'license_number' => 'required|string|max:50',
                'hire_period_months' => 'required|integer|min:1|max:120',
                'monthly_payment' => 'required|numeric|min:0',
                'down_payment'   => 'nullable|numeric|min:0',
                'interest_rate'  => 'nullable|numeric|min:0|max:100',
                'start_date'     => 'required|date',
                'notes'          => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'data'    => $validator->errors(),
                ], 422);
            }

            $totalCost = ($request->monthly_payment * $request->hire_period_months);
            if ($request->down_payment) {
                $totalCost += $request->down_payment;
            }

            $hpRentalCode = 'HP-' . strtoupper(Str::random(8));

            DB::beginTransaction();

            $hpRental = HpRental::create([
                'hp_code'           => $hpRentalCode,
                'vehicle_id'        => $request->vehicle_id,
                'customer_name'     => $request->customer_name,
                'customer_email'    => $request->customer_email,
                'customer_phone'    => $request->customer_phone,
                'customer_address'  => $request->customer_address,
                'ic_number'         => $request->ic_number,
                'license_number'    => $request->license_number,
                'hire_period_months' => $request->hire_period_months,
                'monthly_payment'   => $request->monthly_payment,
                'down_payment'      => $request->down_payment ?? 0,
                'interest_rate'     => $request->interest_rate ?? 0,
                'total_cost'        => $totalCost,
                'start_date'        => $request->start_date,
                'end_date'          => \Carbon\Carbon::parse($request->start_date)->addMonths($request->hire_period_months),
                'status'            => 'pending',
                'created_by'        => $request->user()->id,
                'notes'             => $request->notes,
            ]);

            $invoice = Invoice::create([
                'hp_rental_id'  => $hpRental->id,
                'invoice_code'  => 'INV-HP-' . strtoupper(Str::random(8)),
                'user_id'       => $request->user()->id,
                'total_amount'  => ($request->down_payment ?? 0) + $request->monthly_payment,
                'status'        => 'pending',
                'type'          => 'hp_rental',
                'due_date'      => $request->start_date,
                'items'         => [
                    [
                        'description' => 'Down Payment',
                        'amount'      => $request->down_payment ?? 0,
                        'quantity'    => 1,
                    ],
                    [
                        'description' => 'First Month Payment',
                        'amount'      => $request->monthly_payment,
                        'quantity'    => 1,
                    ],
                ],
            ]);

            DB::commit();

            $hpRental->load('vehicle');

            return response()->json([
                'success' => true,
                'message' => 'HP rental created successfully',
                'data'    => [
                    'hp_rental' => $hpRental,
                    'invoice'   => $invoice,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create HP rental',
                'data'    => null,
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $hpRental = HpRental::with(['vehicle', 'creator', 'invoices'])->find($id);

            if (!$hpRental) {
                return response()->json([
                    'success' => false,
                    'message' => 'HP rental not found',
                    'data'    => null,
                ], 404);
            }

            $totalPaid = $hpRental->invoices()
                ->where('status', 'paid')
                ->sum('total_amount');

            $remainingBalance = $hpRental->total_cost - $totalPaid;

            $monthsCompleted = \Carbon\Carbon::parse($hpRental->start_date)
                ->diffInMonths(now());

            $data = $hpRental->toArray();
            $data['total_paid'] = $totalPaid;
            $data['remaining_balance'] = max(0, $remainingBalance);
            $data['months_completed'] = min($monthsCompleted, $hpRental->hire_period_months);
            $data['months_remaining'] = max(0, $hpRental->hire_period_months - $monthsCompleted);

            return response()->json([
                'success' => true,
                'message' => 'HP rental retrieved successfully',
                'data'    => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve HP rental',
                'data'    => null,
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $query = HpRental::with(['vehicle']);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('customer_name', 'like', "%{$search}%")
                      ->orWhere('hp_code', 'like', "%{$search}%")
                      ->orWhere('customer_email', 'like', "%{$search}%")
                      ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('vehicle_id')) {
                $query->where('vehicle_id', $request->vehicle_id);
            }

            $hpRentals = $query->latest()->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'HP rentals retrieved successfully',
                'data'    => $hpRentals,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve HP rentals',
                'data'    => null,
            ], 500);
        }
    }
}
