<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Rental;
use App\Models\Vehicle;
use App\Models\VehicleReplacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleReplacementController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = VehicleReplacement::with('originalRental', 'replacementVehicle', 'driver.user', 'requester', 'approver');

        if ($user->isDriver()) {
            $driver = Driver::where('user_id', $user->id)->first();
            if ($driver) {
                $query->where('driver_id', $driver->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->isOwner()) {
            $query->where(function ($q) use ($user) {
                $q->where('requested_by', $user->id)
                    ->orWhereHas('originalRental.vehicle', function ($q2) use ($user) {
                        $q2->where('owner_id', $user->id);
                    });
            });
        } elseif ($user->isUser()) {
            $query->where('requested_by', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('originalRental', function ($q2) use ($search) {
                    $q2->where('rental_code', 'like', "%{$search}%");
                })->orWhereHas('replacementVehicle', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('plate_number', 'like', "%{$search}%");
                });
            });
        }

        $replacements = $query->latest()->paginate(15)->withQueryString();

        return view('vehicle-replacements.index', compact('replacements'));
    }

    public function create($rentalId)
    {
        $rental = Rental::with('vehicle')->findOrFail($rentalId);
        $user = Auth::user();

        if ($user->isUser() && $rental->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke sewa ini.');
        }

        $replacementVehicles = Vehicle::with('category')
            ->where('is_active', true)
            ->where('status', 'available')
            ->where('id', '!=', $rental->vehicle_id)
            ->get();

        $drivers = Driver::where('is_available', true)
            ->where('status', 'active')
            ->with('user')
            ->get();

        return view('vehicle-replacements.create', compact('rental', 'replacementVehicles', 'drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'original_rental_id'      => 'required|exists:rentals,id',
            'replacement_vehicle_id'  => 'required|exists:vehicles,id',
            'driver_id'               => 'nullable|exists:drivers,id',
            'reason'                  => 'required|string|max:1000',
            'replacement_start'       => 'required|date',
            'replacement_end'         => 'nullable|date|after_or_equal:replacement_start',
            'cost_difference'         => 'nullable|numeric|min:0',
            'notes'                   => 'nullable|string|max:1000',
        ]);

        try {
            $originalRental = Rental::findOrFail($request->original_rental_id);
            $replacementVehicle = Vehicle::findOrFail($request->replacement_vehicle_id);

            if ($replacementVehicle->id === $originalRental->vehicle_id) {
                return back()->withInput()->with('error', 'Kendaraan pengganti harus berbeda dari kendaraan asli.');
            }

            $replacement = VehicleReplacement::create([
                'original_rental_id'      => $originalRental->id,
                'replacement_vehicle_id'  => $replacementVehicle->id,
                'driver_id'               => $request->driver_id,
                'reason'                  => $request->reason,
                'requested_by'            => Auth::id(),
                'replacement_start'       => $request->replacement_start,
                'replacement_end'         => $request->replacement_end,
                'cost_difference'         => $request->cost_difference ?? 0,
                'notes'                   => $request->notes,
                'status'                  => 'pending',
            ]);

            return redirect()->route('vehicle-replacements.show', $replacement->id)
                ->with('success', 'Permintaan penggantian kendaraan berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat permintaan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $replacement = VehicleReplacement::with(
            'originalRental',
            'originalRental.vehicle',
            'originalRental.user',
            'replacementVehicle',
            'driver.user',
            'requester',
            'approver'
        )->findOrFail($id);

        return view('vehicle-replacements.show', compact('replacement'));
    }

    public function approve($id)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $replacement = VehicleReplacement::findOrFail($id);

        if ($replacement->status !== 'pending') {
            return back()->with('error', 'Hanya permintaan dengan status pending yang dapat disetujui.');
        }

        try {
            $replacement->update([
                'status'       => 'approved',
                'approved_by'  => Auth::id(),
            ]);

            return redirect()->route('vehicle-replacements.show', $replacement->id)
                ->with('success', 'Permintaan penggantian kendaraan berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui permintaan: ' . $e->getMessage());
        }
    }

    public function complete($id)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $replacement = VehicleReplacement::findOrFail($id);

        if ($replacement->status !== 'approved') {
            return back()->with('error', 'Hanya permintaan dengan status approved yang dapat diselesaikan.');
        }

        try {
            $replacement->update([
                'status'          => 'completed',
                'replacement_end' => now(),
            ]);

            $replacementVehicle = Vehicle::find($replacement->replacement_vehicle_id);
            if ($replacementVehicle) {
                $replacementVehicle->update(['status' => 'rented']);
            }

            return redirect()->route('vehicle-replacements.show', $replacement->id)
                ->with('success', 'Penggantian kendaraan berhasil diselesaikan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyelesaikan penggantian: ' . $e->getMessage());
        }
    }

    public function reject($id, Request $request)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isOwner()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $replacement = VehicleReplacement::findOrFail($id);

        if ($replacement->status !== 'pending') {
            return back()->with('error', 'Hanya permintaan dengan status pending yang dapat ditolak.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $replacement->update([
                'status'       => 'rejected',
                'approved_by'  => Auth::id(),
                'notes'        => $request->notes ?? 'Permintaan ditolak.',
            ]);

            return redirect()->route('vehicle-replacements.show', $replacement->id)
                ->with('success', 'Permintaan penggantian kendaraan berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak permintaan: ' . $e->getMessage());
        }
    }
}
