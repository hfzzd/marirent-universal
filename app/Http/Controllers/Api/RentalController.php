<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\Vehicle;
use App\Services\VehicleSwapService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RentalController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = Rental::with(['vehicle', 'user', 'driver']);

            if ($user->role === 'user') {
                $query->where('user_id', $user->id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('vehicle_id')) {
                $query->where('vehicle_id', $request->vehicle_id);
            }

            if ($request->filled('start_date')) {
                $query->where('start_date', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->where('end_date', '<=', $request->end_date);
            }

            $rentals = $query->latest()->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Rentals retrieved successfully',
                'data' => $rentals,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve rentals',
                'data' => null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'vehicle_id' => 'required|exists:vehicles,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'pickup_location' => 'nullable|string|max:255',
                'dropoff_location' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
                'driver_id' => 'nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'data' => $validator->errors(),
                ], 422);
            }

            $vehicle = Vehicle::findOrFail($request->vehicle_id);

            if ($vehicle->status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'Vehicle is not available',
                    'data' => null,
                ], 422);
            }

            $hasConflict = $vehicle->rentals()
                ->whereNotIn('status', ['cancelled'])
                ->where(function ($query) use ($request) {
                    $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                        ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                        ->orWhere(function ($q) use ($request) {
                            $q->where('start_date', '<=', $request->start_date)
                                ->where('end_date', '>=', $request->end_date);
                        });
                })
                ->exists();

            if ($hasConflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vehicle is already rented for the selected dates',
                    'data' => null,
                ], 422);
            }

            $days = max(1, Carbon::parse($request->start_date)->diffInDays($request->end_date));
            $totalAmount = (float) $vehicle->daily_price * $days;

            $rental = Rental::create([
                'user_id' => $request->user()->id,
                'vehicle_id' => $request->vehicle_id,
                'driver_id' => $request->driver_id,
                'category_type' => $vehicle->category->name ?? null,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'pickup_location' => $request->pickup_location,
                'dropoff_location' => $request->dropoff_location,
                'with_driver' => false,
                'status' => 'pending',
                'daily_rate' => $vehicle->daily_price,
                'total_days' => $days,
                'subtotal' => $totalAmount,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
            ]);

            $rental->load(['vehicle', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Rental created successfully',
                'data' => $rental,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'message' => 'Rental created successfully',
                'data' => [
                    'rental' => $rental,
                    'invoice' => $invoice,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create rental',
                'data' => null,
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $rental = Rental::with(['vehicle', 'user', 'driver', 'vehicleReplacements'])->find($id);

            if (! $rental) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rental not found',
                    'data' => null,
                ], 404);
            }

            $user = auth()->user();
            if ($user->role === 'user' && $rental->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                    'data' => null,
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rental retrieved successfully',
                'data' => $rental,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve rental',
                'data' => null,
            ], 500);
        }
    }

    public function cancel($id, Request $request)
    {
        try {
            $rental = Rental::find($id);

            if (! $rental) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rental not found',
                    'data' => null,
                ], 404);
            }

            $user = $request->user();
            if ($user->role === 'user' && $rental->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                    'data' => null,
                ], 403);
            }

            if (! in_array($rental->status, ['pending', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rental cannot be cancelled in current status',
                    'data' => null,
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'cancellation_reason' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'data' => $validator->errors(),
                ], 422);
            }

            $rental->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason,
            ]);

            $rental->invoices()->where('status', 'pending')->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Rental cancelled successfully',
                'data' => $rental->fresh(['vehicle', 'user']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel rental',
                'data' => null,
            ], 500);
        }
    }

    public function confirm($id)
    {
        try {
            $user = auth()->user();
            if (! in_array($user->role, ['superadmin', 'owner'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can confirm rentals',
                    'data' => null,
                ], 403);
            }

            $rental = Rental::find($id);

            if (! $rental) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rental not found',
                    'data' => null,
                ], 404);
            }

            if ($rental->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Rental cannot be confirmed in current status',
                    'data' => null,
                ], 422);
            }

            $rental->update(['status' => 'confirmed']);

            return response()->json([
                'success' => true,
                'message' => 'Rental confirmed successfully',
                'data' => $rental->fresh(['vehicle', 'user', 'driver']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm rental',
                'data' => null,
            ], 500);
        }
    }

    public function complete($id)
    {
        try {
            $user = auth()->user();
            if (! in_array($user->role, ['superadmin', 'owner'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can complete rentals',
                    'data' => null,
                ], 403);
            }

            $rental = Rental::find($id);

            if (! $rental) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rental not found',
                    'data' => null,
                ], 404);
            }

            if (! in_array($rental->status, ['confirmed', 'ongoing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rental cannot be completed in current status',
                    'data' => null,
                ], 422);
            }

            $rental->update([
                'status' => 'completed',
                'actual_return' => now(),
            ]);

            $vehicle = Vehicle::find($rental->vehicle_id);
            if ($vehicle && ! in_array($vehicle->status, ['maintenance'])) {
                $vehicle->update(['status' => 'available']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rental completed successfully',
                'data' => $rental->fresh(['vehicle', 'user', 'driver']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete rental',
                'data' => null,
            ], 500);
        }
    }

    public function replaceVehicle(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if (! in_array($user->role, ['superadmin', 'owner'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can replace rental vehicles',
                    'data' => null,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'replacement_vehicle_id' => 'required|exists:vehicles,id',
                'reason' => 'nullable|string|max:500',
                'price_difference' => 'nullable|numeric',
                'mark_maintenance' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'data' => $validator->errors(),
                ], 422);
            }

            $rental = Rental::find($id);

            if (! $rental) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rental not found',
                    'data' => null,
                ], 404);
            }

            $replacementVehicle = Vehicle::find($request->replacement_vehicle_id);

            $replacement = app(VehicleSwapService::class)->swapForRental(
                rental: $rental,
                replacementVehicle: $replacementVehicle,
                actor: $user,
                reason: (string) $request->input('reason', ''),
                priceDifference: $request->filled('price_difference') ? (float) $request->price_difference : null,
                markMaintenance: $request->boolean('mark_maintenance', true),
            );

            return response()->json([
                'success' => true,
                'message' => "Kendaraan diganti ke {$replacementVehicle->name}",
                'data' => [
                    'replacement' => $replacement->load(['originalVehicle', 'replacementVehicle']),
                    'rental' => $rental->fresh(['vehicle', 'user', 'driver']),
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => implode(' ', collect($e->errors())->flatten()->all()),
                'data' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to replace rental vehicle',
                'data' => null,
            ], 500);
        }
    }
}
