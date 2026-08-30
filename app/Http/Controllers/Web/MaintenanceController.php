<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Maintenance::with(['vehicle', 'creator']);
        $user = Auth::user();

        if ($user->isMerchantStaff()) {
            $merchantId = $user->merchantId();
            $categoryId = $user->merchantCategoryId();
            $query->whereHas('vehicle', fn($vq) => $vq->where('owner_id', $merchantId)->when($categoryId, fn($q) => $q->where('category_id', $categoryId)));
        }

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

        $merchantId = $user->isMerchantStaff() ? $user->merchantId() : null;
        if ($merchantId !== null) {
            $vehicles = $vehicles
                ->filter(fn($v) => (int) $v->owner_id === $merchantId)
                ->filter(fn($v) => !$user->merchantCategoryId() || (int) $v->category_id === $user->merchantCategoryId())
                ->values();
        }

        return view('superadmin.maintenance', compact('maintenances', 'vehicles'));
    }

    private function staffCanAccessVehicle(?Vehicle $vehicle): bool
    {
        if (!$vehicle) {
            return false;
        }

        $user = Auth::user();

        if ($user->isMerchantStaff() && (int) $vehicle->owner_id !== $user->merchantId()) {
            return false;
        }

        if ($user->isMerchantStaff() && $user->merchantCategoryId() && (int) $vehicle->category_id !== $user->merchantCategoryId()) {
            return false;
        }

        return true;
    }

    public function store(Request $request)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin', 'inspector'])) {
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

        $vehicle = Vehicle::find($validated['vehicle_id']);
        $user = Auth::user();

        if ($user->isMerchantStaff() && !$this->staffCanAccessVehicle($vehicle)) {
            abort(403);
        }

        $validated['maintenance_code'] = Maintenance::generateMaintenanceCode();
        $validated['created_by'] = $user->id;
        $validated['status'] = 'scheduled';

        $maintenance = Maintenance::create($validated);

        $this->notifyMerchant($vehicle, $validated['title'], 'maintenance_baru');

        return back()->with('success', 'Jadwal maintenance berhasil dibuat.');
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin', 'inspector'])) {
            abort(403);
        }

        $user = Auth::user();
        if ($user->isMerchantStaff() && !$this->staffCanAccessVehicle($maintenance->vehicle)) {
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

        $vehicle = $maintenance->vehicle;
        if ($validated['status'] === 'completed' && $vehicle) {
            $vehicle->update(['status' => 'available', 'condition' => 'excellent']);
        }

        $this->notifyMerchant($vehicle, $maintenance->title, 'maintenance_' . $validated['status']);

        return back()->with('success', 'Status maintenance berhasil diperbarui.');
    }

    public function destroy(Maintenance $maintenance)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403);
        }

        if (Auth::user()->isMerchantStaff() && !$this->staffCanAccessVehicle($maintenance->vehicle)) {
            abort(403);
        }

        $maintenance->delete();
        return back()->with('success', 'Jadwal maintenance berhasil dihapus.');
    }

    private function notifyMerchant(?Vehicle $vehicle, string $title, string $type): void
    {
        if (!$vehicle || !$vehicle->owner_id) {
            return;
        }

        $owner = User::find($vehicle->owner_id);
        if (!$owner) {
            return;
        }

        $recipients = User::where('role', 'admin')->where('owner_id', $vehicle->owner_id)->get();
        if ($owner->is_active) {
            $recipients->push($owner);
        }

        $driverUserIds = \App\Models\Driver::where('owner_id', $vehicle->owner_id)->pluck('user_id');
        $drivers = User::whereIn('id', $driverUserIds)->get();
        $recipients = $recipients->merge($drivers)->unique('id');

        foreach ($recipients as $recipient) {
            $recipient->notify(new \App\Notifications\MaintenanceNotification($vehicle, $title, $type));
        }
    }
}