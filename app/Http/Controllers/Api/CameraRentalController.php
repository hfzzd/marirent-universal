<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CameraRental;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CameraRentalController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'vehicle_id'      => 'required|exists:vehicles,id',
                'equipment_name'  => 'required|string|max:255',
                'equipment_type'  => 'nullable|string|max:100',
                'customer_name'   => 'required|string|max:255',
                'customer_email'  => 'required|email|max:255',
                'customer_phone'  => 'required|string|max:20',
                'rental_date'     => 'required|date',
                'return_date'     => 'required|date|after_or_equal:rental_date',
                'daily_rate'      => 'required|numeric|min:0',
                'quantity'        => 'required|integer|min:1',
                'deposit_amount'  => 'nullable|numeric|min:0',
                'insurance_fee'   => 'nullable|numeric|min:0',
                'delivery_address' => 'nullable|string|max:500',
                'notes'           => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'data'    => $validator->errors(),
                ], 422);
            }

            $days = max(1, \Carbon\Carbon::parse($request->rental_date)->diffInDays($request->return_date));
            $rentalCost = $request->daily_rate * $days * $request->quantity;
            $totalAmount = $rentalCost + ($request->deposit_amount ?? 0) + ($request->insurance_fee ?? 0);

            $cameraRentalCode = 'CAM-' . strtoupper(Str::random(8));

            DB::beginTransaction();

            $cameraRental = CameraRental::create([
                'camera_code'     => $cameraRentalCode,
                'vehicle_id'      => $request->vehicle_id,
                'equipment_name'  => $request->equipment_name,
                'equipment_type'  => $request->equipment_type,
                'customer_name'   => $request->customer_name,
                'customer_email'  => $request->customer_email,
                'customer_phone'  => $request->customer_phone,
                'rental_date'     => $request->rental_date,
                'return_date'     => $request->return_date,
                'days'            => $days,
                'daily_rate'      => $request->daily_rate,
                'quantity'        => $request->quantity,
                'rental_cost'     => $rentalCost,
                'deposit_amount'  => $request->deposit_amount ?? 0,
                'insurance_fee'   => $request->insurance_fee ?? 0,
                'total_amount'    => $totalAmount,
                'delivery_address' => $request->delivery_address,
                'status'          => 'pending',
                'created_by'      => $request->user()->id,
                'notes'           => $request->notes,
            ]);

            $invoice = Invoice::create([
                'camera_rental_id' => $cameraRental->id,
                'invoice_code'     => 'INV-CAM-' . strtoupper(Str::random(8)),
                'user_id'          => $request->user()->id,
                'total_amount'     => $totalAmount,
                'status'           => 'pending',
                'type'             => 'camera_rental',
                'due_date'         => $request->rental_date,
                'items'            => [
                    [
                        'description' => "Camera rental: {$request->equipment_name} ({$days} days x {$request->quantity})",
                        'amount'      => $rentalCost,
                        'quantity'    => 1,
                    ],
                    [
                        'description' => 'Deposit',
                        'amount'      => $request->deposit_amount ?? 0,
                        'quantity'    => 1,
                    ],
                    [
                        'description' => 'Insurance Fee',
                        'amount'      => $request->insurance_fee ?? 0,
                        'quantity'    => 1,
                    ],
                ],
            ]);

            DB::commit();

            $cameraRental->load('vehicle');

            return response()->json([
                'success' => true,
                'message' => 'Camera rental created successfully',
                'data'    => [
                    'camera_rental' => $cameraRental,
                    'invoice'       => $invoice,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create camera rental',
                'data'    => null,
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $cameraRental = CameraRental::with(['vehicle', 'creator', 'invoices'])->find($id);

            if (!$cameraRental) {
                return response()->json([
                    'success' => false,
                    'message' => 'Camera rental not found',
                    'data'    => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Camera rental retrieved successfully',
                'data'    => $cameraRental,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve camera rental',
                'data'    => null,
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $query = CameraRental::with(['vehicle']);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('customer_name', 'like', "%{$search}%")
                      ->orWhere('camera_code', 'like', "%{$search}%")
                      ->orWhere('equipment_name', 'like', "%{$search}%")
                      ->orWhere('customer_email', 'like', "%{$search}%")
                      ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('vehicle_id')) {
                $query->where('vehicle_id', $request->vehicle_id);
            }

            if ($request->filled('rental_date_from')) {
                $query->where('rental_date', '>=', $request->rental_date_from);
            }

            if ($request->filled('rental_date_to')) {
                $query->where('rental_date', '<=', $request->rental_date_to);
            }

            $cameraRentals = $query->latest()->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Camera rentals retrieved successfully',
                'data'    => $cameraRentals,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve camera rentals',
                'data'    => null,
            ], 500);
        }
    }
}
