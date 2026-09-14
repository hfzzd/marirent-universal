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

        if (in_array($role, ['driver', 'staff'], true)) {
            $driver = \App\Models\Driver::where('user_id', Auth::id())->first();
            if ($driver) {
                $query->whereHas('booking', fn($q) => $q->where('driver_id', $driver->id));
            } else {
                // Staff tanpa penugasan sopir: lingkup merchant-nya.
                $merchantId = Auth::user()->merchantIdForIsolation();
                $query->whereHas('originalVehicle', fn($q) => $q->where('owner_id', $merchantId ?? 0));
            }
        } elseif ($role === 'user') {
            $query->where('requested_by', Auth::id());
        } elseif ($role === 'inspector') {
            $merchantId = Auth::user()->merchantIdForIsolation();
            if ($merchantId) {
                $query->whereHas('originalVehicle', fn($q) => $q->where('owner_id', $merchantId));
            }
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

        if (in_array($role, ['driver', 'staff', 'user', 'owner', 'superadmin', 'inspector'])) {
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
        $bookings = $this->requestableBookings();
        $vehicles = $this->requestableVehicles(null);

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

    /**
     * Daftar booking yang dapat diajukan penggantian untuk role aktif.
     * Aturan 50% masa sewa dihapus: cukup booking berstatus ongoing (proses berjalan).
     */
    protected function requestableBookings(): \Illuminate\Support\Collection
    {
        $user = Auth::user();
        $role = $user->role;

        if ($role === 'superadmin') {
            return Booking::whereIn('status', ['confirmed', 'ongoing'])
                ->whereNotNull('vehicle_id')
                ->with(['vehicle.category', 'user'])
                ->get();
        }

        if ($user->isMerchantStaff()) {
            $query = Booking::whereIn('status', ['confirmed', 'ongoing'])
                ->whereNotNull('vehicle_id')
                ->with(['vehicle.category', 'user']);

            $ownerId = $this->companyOwnerId();
            if ($ownerId) {
                $query->whereHas('vehicle', fn($q) => $q->where('owner_id', $ownerId)
                    ->when($user->merchantCategoryId(), fn($categoryQuery, $categoryId) => $categoryQuery->where('category_id', $categoryId)));
            }

            return $query->get();
        }

        if (in_array($role, ['driver', 'staff'], true)) {
            $driver = \App\Models\Driver::where('user_id', $user->id)->first();
            if ($driver) {
                return Booking::where('driver_id', $driver->id)
                    ->where('status', 'ongoing')
                    ->whereNotNull('vehicle_id')
                    ->with(['vehicle.category', 'user'])
                    ->get();
            }

            $merchantId = $user->merchantIdForIsolation();
            return Booking::where('status', 'ongoing')
                ->whereNotNull('vehicle_id')
                ->whereHas('vehicle', fn($q) => $q->where('owner_id', $merchantId ?? 0))
                ->with(['vehicle.category', 'user'])
                ->get();
        }

        if ($role === 'inspector') {
            $merchantId = $user->merchantIdForIsolation();
            return Booking::where('status', 'ongoing')
                ->whereNotNull('vehicle_id')
                ->when($merchantId, fn($q) => $q->whereHas('vehicle', fn($vq) => $vq->where('owner_id', $merchantId)))
                ->with(['vehicle.category', 'user'])
                ->get();
        }

        // user (penyewa): hanya booking miliknya yang lepas kunci
        return Booking::where('user_id', $user->id)
            ->where('status', 'ongoing')
            ->where('with_driver', false)
            ->whereNotNull('vehicle_id')
            ->with(['vehicle.category', 'user'])
            ->get();
    }

    /**
     * Unit kendaraan pengganti yang dapat dipilih, dibatasi satu company (owner) dengan booking.
     */
    protected function requestableVehicles(?Booking $booking = null): \Illuminate\Support\Collection
    {
        $user = Auth::user();

        $query = Vehicle::where('status', 'available')
            ->where('is_active', true)
            ->with('category');

        if ($booking && $booking->vehicle_id) {
            $query->where('category_id', $booking->vehicle->category_id)
                ->where('id', '!=', $booking->vehicle_id)
                ->when(!$user->isSuperAdmin(), fn($q) => $q->where('owner_id', $booking->vehicle->owner_id));
        }

        if ($user->isMerchantStaff()) {
            $ownerId = $this->companyOwnerId();
            $query->when($ownerId, fn($q) => $q->where('owner_id', $ownerId));
        }

        return $query->get();
    }

    /**
     * Pengecekan otorisasi role terhadap booking untuk pengajuan penggantian.
     */
    protected function canRequestForBooking(Booking $booking): bool
    {
        $user = Auth::user();
        $role = $user->role;

        if ($role === 'superadmin') {
            return true;
        }

        if ($user->isMerchantStaff()) {
            $ownerId = $this->companyOwnerId();
            return $ownerId === null || (int) $booking->vehicle?->owner_id === $ownerId;
        }

        if (in_array($role, ['driver', 'staff'], true)) {
            $driver = \App\Models\Driver::where('user_id', $user->id)->first();
            if ($driver) {
                return (int) $booking->driver_id === (int) $driver->id;
            }
            $merchantId = $user->merchantIdForIsolation();
            return $merchantId !== null && (int) $booking->vehicle?->owner_id === $merchantId;
        }

        if ($role === 'inspector') {
            $merchantId = $user->merchantIdForIsolation();
            return $merchantId !== null && (int) $booking->vehicle?->owner_id === $merchantId;
        }

        if ($role === 'user') {
            return (int) $booking->user_id === (int) $user->id && !$booking->with_driver;
        }

        return false;
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
        if (in_array($user->role, ['driver', 'staff'], true)) {
            $driverId = \App\Models\Driver::where('user_id', $user->id)->value('id');
            if ($driverId) {
                if ((int) $replacement->booking?->driver_id !== (int) $driverId) {
                    abort(403);
                }
            } else {
                $merchantId = $user->merchantIdForIsolation();
                if (!$merchantId || (int) $replacement->originalVehicle?->owner_id !== $merchantId) {
                    abort(403);
                }
            }
        }
        if (in_array($user->role, ['owner', 'admin'])) {
            $this->guardCompanyReplacement($replacement);
        }
        if ($user->role === 'inspector') {
            $merchantId = $user->merchantIdForIsolation();
            if ($merchantId && (int) $replacement->originalVehicle?->owner_id !== $merchantId) {
                abort(403, 'Penggantian ini bukan milik company Anda');
            }
        }
        if (!in_array($user->role, ['superadmin', 'owner', 'admin', 'driver', 'staff', 'user', 'inspector'], true)) {
            abort(403);
        }

        return view('replacements.show', compact('replacement'));
    }

    public function create(Request $request)
    {
        $role = Auth::user()->role;

        if (!in_array($role, ['superadmin', 'owner', 'driver', 'staff', 'user', 'inspector'])) {
            abort(403, 'Anda tidak memiliki akses untuk membuat penggantian kendaraan');
        }

        $bookings = $this->requestableBookings();

        $preselectedBookingId = $request->query('booking_id') ?: old('booking_id');

        // Selalu sertakan booking yang sedang dibuka di detail booking, selama masih ongoing & milik role ini.
        if ($preselectedBookingId) {
            $target = Booking::with(['vehicle.category', 'user'])->find((int) $preselectedBookingId);
            if ($target && $target->vehicle_id && in_array($target->status, ['ongoing', 'confirmed']) && $this->canRequestForBooking($target)) {
                if (!$bookings->contains('id', $target->id)) {
                    $bookings = $bookings->push($target);
                }
            }
        }

        $preselectedBooking = $preselectedBookingId ? $bookings->firstWhere('id', (int) $preselectedBookingId) : null;

        $vehicles = $this->requestableVehicles($preselectedBooking);

        return view('replacements.create', compact('bookings', 'vehicles', 'preselectedBookingId'));
    }

    public function store(Request $request)
    {
        $role = Auth::user()->role;

        if (!in_array($role, ['superadmin', 'owner', 'driver', 'staff', 'user', 'inspector'])) {
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

        if (!$this->canRequestForBooking($booking)) {
            abort(403, 'Anda tidak memiliki akses untuk booking ini');
        }

        if (!in_array($role, ['superadmin', 'owner', 'admin']) && $booking->status !== 'ongoing') {
            return back()->with('error', 'Hanya booking dengan status ongoing yang dapat diajukan penggantian');
        }

        $replacementVehicle = Vehicle::findOrFail($validated['replacement_vehicle_id']);

        if (($replacementVehicle->status ?? '') !== 'available' || !($replacementVehicle->is_active ?? true)) {
            return back()->with('error', 'Kendaraan pengganti sedang tidak tersedia')->withInput();
        }

        if ($role !== 'superadmin' && (int) $replacementVehicle->owner_id !== (int) $booking->vehicle->owner_id) {
            return back()->with('error', 'Kendaraan pengganti harus dari company yang sama')->withInput();
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

        $replacement = VehicleReplacement::create([
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

        $this->notifyReplacementApprovers($replacement);

        return redirect()->route('replacements.index')->with('success', 'Permintaan penggantian kendaraan dibuat');
    }

    /**
     * Beri tahu superadmin serta owner/admin company terkait bahwa ada permintaan pending.
     */
    private function notifyReplacementApprovers(VehicleReplacement $replacement): void
    {
        $ownerId = $replacement->originalVehicle?->owner_id;

        $recipientIds = \App\Models\User::where('role', 'superadmin')->pluck('id');

        if ($ownerId) {
            $staffIds = \App\Models\User::whereIn('role', ['owner', 'admin'])
                ->where(fn($q) => $q->where('id', $ownerId)->orWhere('owner_id', $ownerId))
                ->pluck('id');
            $recipientIds = $recipientIds->merge($staffIds);
        }

        \App\Models\User::whereIn('id', $recipientIds->unique())
            ->get()
            ->each(fn($user) => $user->notify(new \App\Notifications\VehicleReplacementRequested($replacement)));
    }

    public function approve(VehicleReplacement $replacement)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403, 'Hanya superadmin, owner, atau admin yang dapat menyetujui');
        }
        $this->guardCompanyReplacement($replacement);

        if ($replacement->status === 'approved') {
            return back()->with('error', 'Penggantian sudah disetujui sebelumnya');
        }

        $booking = $replacement->booking;
        $replacementVehicle = Vehicle::find($replacement->replacement_vehicle_id);
        if ($replacementVehicle && (($replacementVehicle->status ?? '') !== 'available' || !($replacementVehicle->is_active ?? true))) {
            return back()->with('error', 'Kendaraan pengganti sudah tidak tersedia');
        }

        $replacement->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'swapped_at' => now(),
        ]);

        $originalVehicle = Vehicle::find($replacement->original_vehicle_id);

        if ($booking && $replacementVehicle) {
            $booking->update(['vehicle_id' => $replacement->replacement_vehicle_id]);
            $replacementVehicle->update(['status' => $booking->status === 'ongoing' ? 'rented' : 'reserved']);
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
            $this->syncReplacementInvoice($booking);
        }

        return back()->with('success', 'Penggantian kendaraan disetujui');
    }

    public function reject(VehicleReplacement $replacement)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403, 'Hanya superadmin, owner, atau admin yang dapat menolak');
        }
        $this->guardCompanyReplacement($replacement);

        $replacement->update(['status' => 'rejected', 'approved_by' => Auth::id()]);
        return back()->with('success', 'Permintaan ditolak');
    }

    public function updateStatus(Request $request, VehicleReplacement $replacement)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403, 'Hanya superadmin, owner, atau admin yang dapat mengubah status');
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

            if ($replacementVehicle && (($replacementVehicle->status ?? '') !== 'available' || !($replacementVehicle->is_active ?? true))) {
                return back()->with('error', 'Kendaraan pengganti sudah tidak tersedia');
            }

            if ($booking && $replacementVehicle) {
                $booking->update(['vehicle_id' => $replacement->replacement_vehicle_id]);
                $replacementVehicle->update(['status' => $booking->status === 'ongoing' ? 'rented' : 'reserved']);
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
                $this->syncReplacementInvoice($booking);
            }
        }

        return back()->with('success', 'Status penggantian berhasil diubah dari ' . ucfirst($oldStatus) . ' ke ' . ucfirst($newStatus));
    }

    private function syncReplacementInvoice(Booking $booking): void
    {
        $booking->refresh();
        $invoice = $booking->invoice ?? $booking->invoices()->first();
        if (!$invoice) {
            return;
        }
        $newTotal = (float) $booking->final_price;
        // Jika invoice multi-booking, hitung ulang dari semua booking terkait
        $invoice->loadMissing(['bookings', 'items']);
        if ($invoice->bookings->isNotEmpty()) {
            $ids = $invoice->bookings->pluck('id');
            if (!$ids->contains($booking->id)) {
                $ids->push($booking->id);
            }
            $newTotal = (float) Booking::whereIn('id', $ids)->sum('final_price');
        }
        $paidSoFar = (float) $invoice->paid_amount;
        $invoice->update([
            'subtotal' => $newTotal,
            'total_amount' => $newTotal,
            'due_amount' => max(0, $newTotal - $paidSoFar),
            'status' => $paidSoFar >= $newTotal && $newTotal > 0 ? 'paid' : ($paidSoFar > 0 ? 'partial' : 'sent'),
        ]);
        if ($invoice->items->count() === 1) {
            $invoice->items->first()->update(['unit_price' => $newTotal, 'total_price' => $newTotal]);
        } else {
            foreach ($invoice->items as $it) {
                if (str_contains($it->description ?? '', $booking->booking_code)) {
                    $it->update(['unit_price' => (float) $booking->final_price, 'total_price' => (float) $booking->final_price]);
                    break;
                }
            }
        }
    }
}
