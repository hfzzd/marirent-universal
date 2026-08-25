<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Maintenance::with(['vehicle', 'creator']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        if ($request->search) {
            $search = "%{$request->search}%";
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('maintenance_code', 'like', $search)
                  ->orWhereHas('vehicle', fn($vq) => $vq->where('name', 'like', $search));
            });
        }

        $maintenances = $query->latest('scheduled_date')->paginate(15);
        $vehicles = Vehicle::where('is_active', true)->get();

        return view('superadmin.maintenance', compact('maintenances', 'vehicles'));
    }

    public function store(Request $request)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'vehicle_id' => 'required|exists:vehicles,id',
            'scheduled_date' => 'required|date',
            'type' => 'required|in:routine,repair,inspection,emergency',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_cost' => 'nullable|numeric|min:0',
            'technician' => 'nullable|string|max:200',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['maintenance_code'] = Maintenance::generateMaintenanceCode();
        $validated['created_by'] = Auth::id();
        $validated['status'] = 'scheduled';

        Maintenance::create($validated);

        return back()->with('success', 'Jadwal maintenance berhasil dibuat.');
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'actual_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        $updateData = $validated;
        if ($validated['status'] === 'completed') {
            $updateData['completed_date'] = now();
        }

        $maintenance->update($updateData);

        if ($validated['status'] === 'completed' && $maintenance->vehicle) {
            $maintenance->vehicle->update(['status' => 'available', 'condition' => 'excellent']);
        }

        return back()->with('success', 'Status maintenance berhasil diperbarui.');
    }

    public function destroy(Maintenance $maintenance)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403);
        }

        $maintenance->delete();
        return back()->with('success', 'Jadwal maintenance berhasil dihapus.');
    }
}
