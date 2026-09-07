<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\VehicleReplacement;
use App\Models\Vehicle;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReplacementWebController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleReplacement::with(['booking', 'originalVehicle', 'replacementVehicle', 'requestedBy', 'approvedBy']);

        $role = Auth::user()->role;

        if ($role === 'driver') {
            $driver = \App\Models\Driver::where('user_id', Auth::id())->first();
            if ($driver) {
                $query->whereHas('booking', fn($q) => $q->where('driver_id', $driver->id));
            }
        } elseif ($role === 'user') {
            $query->where('requested_by', Auth::id());
        } elseif (in_array($role, ['owner', 'admin'])) {
            $ownerId = $role === 'owner' ? Auth::id() : Auth::user()->merchantId();
            $query->whereHas('originalVehicle', function ($q) use ($ownerId) {
                $q->where('owner_id', $ownerId)
                    ->when(Auth::user()->merchantCategoryId(), fn($categoryQuery, $categoryId) => $categoryQuery->where('category_id', $categoryId));
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $replacements = $query->latest()->paginate(15);

        $modalBookings = [];
        $modalVehicles = [];

        if (in_array($role, ['driver', 'user', 'owner', 'superadmin'])) {
            $formData = $this->getFormData();
            $modalBookings = $formData['bookings'];
            $modalVehicles = $formData['vehicles'];
        }

        return view('replacements.index', compact('replacements', 'modalBookings', 'modalVehicles'));
    }

    private function companyOwnerId(): ?int
    {
        $role = Auth::user()->role;
        if ($role === 'owner') {
            return (int) Auth::id();
        }
        if ($role === 'admin') {
            $merchantId = Auth::user()->merchantId();
            return $merchantId ? (int) $merchantId : null;
        }
        return null;
    }

    private function guardCompanyReplacement(VehicleReplacement $replacement): void
    {
        $ownerId = $this->companyOwnerId();
        if ($ownerId !== null && (int) $replacement->originalVehicle?->owner_id !== $ownerId) {
            abort(403, 'Penggantian ini bukan milik company Anda');
        }

        $categoryId = Auth::user()->merchantCategoryId();
        if ($categoryId && !$replacement->booking?->belongsToCategory($categoryId)) {
            abort(403, 'Penggantian ini bukan kategori Anda');
        }
    }

    protected function getFormData(): array
    {
        $role = Auth::user()->role;

        if (in_array($role, ['superadmin', 'owner'])) {
            $query = Booking::whereIn('status', ['confirmed', 'ongoing'])
                ->whereNotNull('vehicle_id')
                ->with(['vehicle.category', 'user']);

            $ownerId = $this->companyOwnerId();
            if ($ownerId) {
                $query->whereHas('vehicle', fn($q) => $q->where('owner_id', $ownerId)
                    ->when(Auth::user()->merchantCategoryId(), fn($categoryQuery, $categoryId) => $categoryQuery->where('category_id', $categoryId)));
            }

            $bookings = $query->get();

            $vehicleQuery = Vehicle::where('status', 'available')->where('is_active', true)->with('category');
            if ($ownerId) {
                $vehicleQuery->where('owner_id', $ownerId)
                    ->when(Auth::user()->merchantCategoryId(), fn($q, $categoryId) => $q->where('category_id', $categoryId));
            }
            $vehicles = $vehicleQuery->get();
        } elseif ($role === 'driver') {
            $driver = \App\Models\Driver::where('user_id', Auth::id())->first();
            $bookings = $driver
                ? Booking::where('driver_id', $driver->id)->where('status', 'ongoing')->whereNotNull('vehicle_id')->where('actual_start_date', '!=', null)->with(['vehicle.category', 'user'])->get()->filter(fn($b) => $this->isHalfPeriodElapsed($b))->values()
                : collect();
        } else {
            $bookings = Booking::where('user_id', Auth::id())->where('status', 'ongoing')->where('with_driver', false)->whereNotNull('vehicle_id')->where('actual_start_date', '!=', null)->with(['vehicle.category', 'user'])->get()->filter(fn($b) => $this->isHalfPeriodElapsed($b))->values();
        }

        if (!isset($vehicles)) {
            $vehicles = Vehicle::where('status', 'available')->where('is_active', true)->with('category')->get();
        }

        return [
            'bookings' => $bookings->map(fn($b) => [
                'id' => $b->id,
                'label' => $b->booking_code . ' - ' . ($b->vehicle?->name ?? '-') . ($b->status === 'ongoing' ? ' (sedang berjalan)' : ''),
            ]),
            'vehicles' => $vehicles->map(fn($v) => [
                'id' => $v->id,
                'label' => $v->name . ' (' . ($v->category?->name ?? '-') . ') - Rp ' . number_format($v->daily_price, 0, ',', '.') . '/hari',
            ]),
        ];
    }

    public function show(VehicleReplacement $replacement)
    {
        $replacement->load([
            'booking.user', 'booking.vehicle', 'booking.category',
            'originalVehicle', 'replacementVehicle',
            'requestedBy', 'approvedBy',
            'booking.tripReport', 'booking.driver.user',
        ]);

        $user = Auth::user();
        if ($user->role === 'user' && (int) $replacement->booking?->user_id !== (int) $user->id) {
            abort(403);
        }
        if ($user->role === 'driver') {
            $driverId = \App\Models\Driver::where('user_id', $user->id)->value('id');
            if (!$driverId || (int) $replacement->booking?->driver_id !== (int) $driverId) {
                abort(403);
            }
        }
        if (in_array($user->role, ['owner', 'admin'])) {
            $this->guardCompanyReplacement($replacement);
        }
        if (!in_array($user->role, ['superadmin', 'owner', 'admin', 'driver', 'user'], true)) {
            abort(403);
        }

        return view('replacements.show', compact('replacement'));
    }

    public function create(Request $request)
    {
        $role = Auth::user()->role;

        if (!in_array($role, ['superadmin', 'owner', 'driver', 'user'])) {
            abort(403, 'Anda tidak memiliki akses untuk membuat penggantian kendaraan');
        }

        if (in_array($role, ['superadmin', 'owner'])) {
            $query = Booking::whereIn('status', ['confirmed', 'ongoing'])
                ->whereNotNull('vehicle_id')
                ->with(['vehicle.category', 'user']);

            $ownerId = $this->companyOwnerId();
            if ($ownerId) {
                $query->whereHas('vehicle', fn($q) => $q->where('owner_id', $ownerId));
            }

            $bookings = $query->get();
        } elseif ($role === 'driver') {
            $driver = \App\Models\Driver::where('user_id', Auth::id())->first();
            if (!$driver) {
                abort(403, 'Profil driver tidak ditemukan');
            }
            $bookings = Booking::where('driver_id', $driver->id)
                ->where('status', 'ongoing')
                ->whereNotNull('vehicle_id')
                ->where('actual_start_date', '!=', null)
                ->with(['vehicle.category', 'user'])
                ->get()
                ->filter(function ($booking) {
                    return $this->isHalfPeriodElapsed($booking);
                })
                ->values();
        } else {
            $bookings = Booking::where('user_id', Auth::id())
                ->where('status', 'ongoing')
                ->where('with_driver', false)
                ->whereNotNull('vehicle_id')
                ->where('actual_start_date', '!=', null)
                ->with(['vehicle.category', 'user'])
                ->get()
                ->filter(function ($booking) {
                    return $this->isHalfPeriodElapsed($booking);
                })
                ->values();
        }

        $vehicles = Vehicle::where('status', 'available')
            ->where('is_active', true)
            ->with('category')
            ->when(($ownerId = $this->companyOwnerId()), fn($q) => $q->where('owner_id', $ownerId))
            ->get();

        return view('replacements.create', compact('bookings', 'vehicles'));
    }

    protected function isHalfPeriodElapsed(Booking $booking): bool
    {
        if (!$booking->actual_start_date || !$booking->end_date) {
            return false;
        }
        $totalDuration = $booking->actual_start_date->diffInSeconds($booking->end_date);
        $elapsed = $booking->actual_start_date->diffInSeconds(now());
        return $elapsed >= ($totalDuration / 2);
    }

    public function store(Request $request)
    {
        $role = Auth::user()->role;

        if (!in_array($role, ['superadmin', 'owner', 'driver', 'user'])) {
            abort(403, 'Anda tidak memiliki akses untuk membuat penggantian kendaraan');
        }

        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'replacement_vehicle_id' => 'required|exists:vehicles,id',
            'reason' => 'required|string|max:2000',
            'price_difference' => 'nullable|numeric',
            'mark_maintenance' => 'nullable|in:0,1',
            'initial_vehicle_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'final_vehicle_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        if (!$booking->vehicle_id) {
            return back()->with('error', 'Booking ini tidak memiliki kendaraan');
        }

        if (in_array($role, ['driver', 'user'])) {
            if ($booking->status !== 'ongoing') {
                return back()->with('error', 'Hanya booking dengan status ongoing yang dapat diajukan penggantian');
            }

            if (!$this->isHalfPeriodElapsed($booking)) {
                return back()->with('error', 'Penggantian kendaraan hanya dapat diajukan setelah setengah masa sewa berlalu');
            }

            if ($role === 'driver') {
                $driver = \App\Models\Driver::where('user_id', Auth::id())->first();
                if (!$driver || $booking->driver_id !== $driver->id) {
                    abort(403, 'Anda bukan driver untuk booking ini');
                }
            }

            if ($role === 'user') {
                if ($booking->user_id !== Auth::id() || $booking->with_driver) {
                    abort(403, 'Anda tidak memiliki akses untuk booking ini');
                }
            }
        } else {
            $ownerId = $this->companyOwnerId();
            if ($ownerId !== null && (int) $booking->vehicle?->owner_id !== $ownerId) {
                abort(403, 'Booking ini bukan milik company Anda');
            }
        }
$replacementVehicle = Vehicle::findOrFail($validated['replacement_vehicle_id']);

        $ownerId = $this->companyOwnerId();
        if (in_array($role, ['owner', 'admin']) && (int) $replacementVehicle->owner_id !== $ownerId) {
            abort(403, 'Kendaraan pengganti bukan milik company Anda');
        }

        if ($replacementVehicle->category_id !== $booking->vehicle->category_id) {
            return back()->with('error', 'Kendaraan pengganti harus dalam kategori yang sama');
        }

        if ($validated['replacement_vehicle_id'] == $booking->vehicle_id) {
            return back()->with('error', 'Kendaraan pengganti tidak boleh sama');
        }

        $initialPhoto = null;
        if ($request->hasFile('initial_vehicle_photo')) {
            $initialPhoto = $request->file('initial_vehicle_photo')->store('vehicle-replacements', 'public');
        }
        $finalPhoto = null;
        if ($request->hasFile('final_vehicle_photo')) {
            $finalPhoto = $request->file('final_vehicle_photo')->store('vehicle-replacements', 'public');
        }

        VehicleReplacement::create([
            'booking_id' => $booking->id,
            'original_vehicle_id' => $booking->vehicle_id,
            'replacement_vehicle_id' => $replacementVehicle->id,
            'requested_by' => Auth::id(),
            'status' => 'pending',
            'reason' => $validated['reason'],
            'price_difference' => $validated['price_difference'] ?? 0,
            'mark_maintenance' => ($validated['mark_maintenance'] ?? '1') === '1',
            'initial_vehicle_photo' => $initialPhoto,
            'final_vehicle_photo' => $finalPhoto,
        ]);

        return redirect()->route('replacements.index')->with('success', 'Permintaan penggantian kendaraan dibuat');
    }

    public function approve(VehicleReplacement $replacement)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403, 'Hanya superadmin, owner, dan admin yang dapat menyetujui');
        }
        $this->guardCompanyReplacement($replacement);

        $replacement->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'swapped_at' => now(),
        ]);

        $booking = $replacement->booking;
        $originalVehicle = Vehicle::find($replacement->original_vehicle_id);
        $replacementVehicle = Vehicle::find($replacement->replacement_vehicle_id);

        if ($booking && $replacementVehicle) {
            $booking->update(['vehicle_id' => $replacement->replacement_vehicle_id]);
            $replacementVehicle->update(['status' => 'reserved']);
        }

        if ($originalVehicle) {
            $originalVehicle->update([
                'status' => $replacement->mark_maintenance ? 'maintenance' : 'available',
            ]);
        }

        $priceDiff = (float) $replacement->price_difference;
        if ($priceDiff != 0 && $booking) {
            $booking->update([
                'total_price' => $booking->total_price + $priceDiff,
                'final_price' => $booking->final_price + $priceDiff,
            ]);
        }

        return back()->with('success', 'Penggantian kendaraan disetujui');
    }

    public function reject(VehicleReplacement $replacement)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403, 'Admin hanya dapat menyetujui pengajuan penggantian');
        }
        $this->guardCompanyReplacement($replacement);

        $replacement->update(['status' => 'rejected', 'approved_by' => Auth::id()]);
        return back()->with('success', 'Permintaan ditolak');
    }

    public function updateStatus(Request $request, VehicleReplacement $replacement)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403, 'Admin hanya dapat menyetujui pengajuan penggantian');
        }
        $this->guardCompanyReplacement($replacement);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $oldStatus = $replacement->status;
        $newStatus = $validated['status'];

        $updateData = [
            'status' => $newStatus,
            'admin_notes' => $validated['admin_notes'] ?? $replacement->admin_notes,
        ];

        if ($newStatus === 'approved' && $oldStatus !== 'approved') {
            $updateData['approved_by'] = Auth::id();
            $updateData['swapped_at'] = now();
        } elseif ($newStatus !== 'approved') {
            $updateData['approved_by'] = Auth::id();
        }

        $replacement->update($updateData);

        if ($newStatus === 'approved' && $oldStatus !== 'approved') {
            $booking = $replacement->booking;
            $originalVehicle = Vehicle::find($replacement->original_vehicle_id);
            $replacementVehicle = Vehicle::find($replacement->replacement_vehicle_id);

            if ($booking && $replacementVehicle) {
                $booking->update(['vehicle_id' => $replacement->replacement_vehicle_id]);
                $replacementVehicle->update(['status' => 'reserved']);
            }

            if ($originalVehicle) {
                $originalVehicle->update([
                    'status' => $replacement->mark_maintenance ? 'maintenance' : 'available',
                ]);
            }

            $priceDiff = (float) $replacement->price_difference;
            if ($priceDiff != 0 && $booking) {
                $booking->update([
                    'total_price' => $booking->total_price + $priceDiff,
                    'final_price' => $booking->final_price + $priceDiff,
                ]);
            }
        }

        return back()->with('success', 'Status penggantian berhasil diubah dari ' . ucfirst($oldStatus) . ' ke ' . ucfirst($newStatus));
    }
}
