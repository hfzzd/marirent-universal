<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleReplacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VehicleReplacementController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rental_id'        => 'required|exists:rentals,id',
                'original_vehicle_id' => 'required|exists:vehicles,id',
                'replacement_vehicle_id' => 'required|exists:vehicles,id|different:original_vehicle_id',
                'reason'           => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'data'    => $validator->errors(),
                ], 422);
            }

            $replacementVehicle = Vehicle::find($request->replacement_vehicle_id);
            if ($replacementVehicle->status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'Replacement vehicle is not available',
                    'data'    => null,
                ], 422);
            }

            $existingPending = VehicleReplacement::where('rental_id', $request->rental_id)
                ->where('status', 'pending')
                ->exists();

            if ($existingPending) {
                return response()->json([
                    'success' => false,
                    'message' => 'A pending replacement request already exists for this rental',
                    'data'    => null,
                ], 422);
            }

            $replacement = VehicleReplacement::create([
                'replacement_code'       => 'RPL-' . strtoupper(Str::random(8)),
                'rental_id'              => $request->rental_id,
                'original_vehicle_id'    => $request->original_vehicle_id,
                'replacement_vehicle_id' => $request->replacement_vehicle_id,
                'requested_by'           => $request->user()->id,
                'reason'                 => $request->reason,
                'status'                 => 'pending',
            ]);

            $replacement->load(['rental', 'originalVehicle', 'replacementVehicle', 'requester']);

            return response()->json([
                'success' => true,
                'message' => 'Vehicle replacement request submitted successfully',
                'data'    => $replacement,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit replacement request',
                'data'    => null,
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = VehicleReplacement::with(['rental', 'originalVehicle', 'replacementVehicle', 'requester', 'approver']);

            if ($user->role === 'user') {
                $query->where('requested_by', $user->id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('rental_id')) {
                $query->where('rental_id', $request->rental_id);
            }

            $replacements = $query->latest()->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Replacement requests retrieved successfully',
                'data'    => $replacements,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve replacement requests',
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
                    'message' => 'Only admins can approve replacement requests',
                    'data'    => null,
                ], 403);
            }

            $replacement = VehicleReplacement::find($id);

            if (!$replacement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Replacement request not found',
                    'data'    => null,
                ], 404);
            }

            if ($replacement->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Replacement request cannot be approved in current status',
                    'data'    => null,
                ], 422);
            }

            $replacementVehicle = Vehicle::find($replacement->replacement_vehicle_id);
            if (!$replacementVehicle || $replacementVehicle->status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'Replacement vehicle is no longer available',
                    'data'    => null,
                ], 422);
            }

            DB::beginTransaction();

            $replacement->update([
                'status'     => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $originalVehicle = Vehicle::find($replacement->original_vehicle_id);
            if ($originalVehicle) {
                $originalVehicle->update(['status' => 'maintenance']);
            }

            $replacementVehicle->update(['status' => 'rented']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Replacement request approved successfully',
                'data'    => $replacement->fresh(['rental', 'originalVehicle', 'replacementVehicle', 'requester', 'approver']),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve replacement request',
                'data'    => null,
            ], 500);
        }
    }

    public function complete($id)
    {
        try {
            $user = auth()->user();
            if (!in_array($user->role, ['admin', 'driver'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins or drivers can complete replacements',
                    'data'    => null,
                ], 403);
            }

            $replacement = VehicleReplacement::find($id);

            if (!$replacement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Replacement request not found',
                    'data'    => null,
                ], 404);
            }

            if ($replacement->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Replacement request must be approved before completion',
                    'data'    => null,
                ], 422);
            }

            $replacement->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Replacement request completed successfully',
                'data'    => $replacement->fresh(['rental', 'originalVehicle', 'replacementVehicle', 'requester', 'approver']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete replacement request',
                'data'    => null,
            ], 500);
        }
    }

    public function reject($id, Request $request)
    {
        try {
            $user = auth()->user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can reject replacement requests',
                    'data'    => null,
                ], 403);
            }

            $replacement = VehicleReplacement::find($id);

            if (!$replacement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Replacement request not found',
                    'data'    => null,
                ], 404);
            }

            if ($replacement->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Replacement request cannot be rejected in current status',
                    'data'    => null,
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'data'    => $validator->errors(),
                ], 422);
            }

            $replacement->update([
                'status'           => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'approved_by'      => $user->id,
                'approved_at'      => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Replacement request rejected successfully',
                'data'    => $replacement->fresh(['rental', 'originalVehicle', 'replacementVehicle', 'requester', 'approver']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject replacement request',
                'data'    => null,
            ], 500);
        }
    }
}
