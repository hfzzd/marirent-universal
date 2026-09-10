<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ItemReplacement;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;
use App\Models\Playstation;
use App\Models\Drone;
use App\Models\MusicalInstrument;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemReplacementWebController extends Controller
{
    private function getModel(string $type)
    {
        return match ($type) {
            'hp' => new Phone(),
            'camera' => new Camera(),
            'tenda' => new CampingEquipment(),
            'ps' => new Playstation(),
            'drone' => new Drone(),
            'musik' => new MusicalInstrument(),
            default => null,
        };
    }

    public function index(Request $request)
    {
        $query = ItemReplacement::with(['booking', 'originalItem', 'replacementItem', 'requestedBy', 'approvedBy']);

        $role = Auth::user()->role;

        if ($role === 'user') {
            $query->whereHas('booking', fn($q) => $q->where('user_id', Auth::id()));
        } elseif (in_array($role, ['owner', 'admin'])) {
            $ownerId = $this->itemMerchantOwnerId();
            $query->whereHas('booking', fn($q) => $q->forMerchantCategory($ownerId, Auth::user()->merchantCategoryId()));
        } elseif ($role !== 'superadmin') {
            $query->whereRaw('1 = 0');
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('booking', fn($b) => $b->where('booking_code', 'like', "%{$search}%"))
                    ->orWhereHas('originalItem', fn($i) => $i->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('replacementItem', fn($i) => $i->where('name', 'like', "%{$search}%"));
            });
        }

        $replacements = $query->latest()->paginate(15);

        return view('item-replacements.index', compact('replacements'));
    }

    public function show(ItemReplacement $replacement)
    {
        $replacement->load([
            'booking.user', 'booking.category', 'booking.vehicle',
            'requestedBy', 'approvedBy', 'returnedBy',
            'originalItem', 'replacementItem',
        ]);
        $this->guardReplacementView($replacement);

        return view('item-replacements.show', compact('replacement'));
    }

    public function create(Request $request)
    {
        $role = Auth::user()->role;
        if (!in_array($role, ['superadmin', 'owner', 'user'])) {
            abort(403, 'Anda tidak berhak mengajukan penggantian unit');
        }

        $type = $request->type ?? 'hp';
        if (!in_array($type, ['hp', 'camera', 'tenda', 'ps', 'drone', 'musik'])) {
            abort(404);
        }

        $bookings = Booking::whereIn('status', ['confirmed', 'ongoing'])
            ->where('item_type', '!=', null)
            ->with(['user', 'category']);

        if ($role === 'user') {
            $bookings->where('user_id', Auth::id());
        } else {
            $ownerId = $this->itemMerchantOwnerId();
            if ($ownerId) {
                $bookings->forMerchantCategory($ownerId, Auth::user()->merchantCategoryId());
            }
        }

        $bookings = $bookings->get();

        $items = $this->getAvailableItems($type, $role);

        return view('item-replacements.create', compact('type', 'bookings', 'items'));
    }

    private function itemMerchantOwnerId(): ?int
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

    private function guardCompanyItemReplacement(ItemReplacement $replacement): void
    {
        $ownerId = $this->itemMerchantOwnerId();
        if ($ownerId && !Booking::where('id', $replacement->booking_id)
            ->forMerchantCategory($ownerId, Auth::user()->merchantCategoryId())
            ->exists()) {
            abort(403, 'Penggantian unit ini bukan milik company Anda');
        }
    }

    private function guardReplacementView(ItemReplacement $replacement): void
    {
        $user = Auth::user();

        if ($user->role === 'user') {
            abort_unless((int) $replacement->booking?->user_id === (int) $user->id, 403);
            return;
        }

        if (in_array($user->role, ['owner', 'admin'])) {
            $this->guardCompanyItemReplacement($replacement);
            return;
        }

        abort_unless($user->isSuperAdmin(), 403);
    }

    private function getAvailableItems(string $type, ?string $role = null)
    {
        $modelClass = match ($type) {
            'hp' => Phone::class,
            'camera' => Camera::class,
            'tenda' => CampingEquipment::class,
            'ps' => Playstation::class,
            'drone' => Drone::class,
            'musik' => MusicalInstrument::class,
        };

        $query = $modelClass::where('status', 'available')
            ->where('is_active', true)
            ->with('category');

        $ownerId = $this->itemMerchantOwnerId();
        if ($role !== 'user' && $ownerId) {
            $query->where('owner_id', $ownerId);
        }
        if ($categoryId = Auth::user()->merchantCategoryId()) {
            $query->where('category_id', $categoryId);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $role = Auth::user()->role;
        if (!in_array($role, ['superadmin', 'owner', 'user'])) {
            abort(403, 'Anda tidak berhak mengajukan penggantian unit');
        }

        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'item_type' => 'required|in:hp,camera,tenda,ps,drone,musik',
            'original_item_id' => 'required|integer',
            'replacement_item_id' => 'required|integer',
            'reason' => 'required|string|max:2000',
            'initial_item_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'final_item_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'mark_maintenance' => 'nullable|in:0,1',
            'damage_notes' => 'nullable|string|max:2000',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        if ($role === 'user') {
            if ($booking->user_id !== Auth::id()) {
                abort(403);
            }
        } else {
            $ownerId = $this->itemMerchantOwnerId();
            if ($ownerId && !Booking::where('id', $booking->id)->ownedByMerchant($ownerId)->exists()) {
                abort(403, 'Booking ini bukan milik company Anda');
            }
        }

        $modelClass = match ($validated['item_type']) {
            'hp' => Phone::class,
            'camera' => Camera::class,
            'tenda' => CampingEquipment::class,
            'ps' => Playstation::class,
            'drone' => Drone::class,
            'musik' => MusicalInstrument::class,
        };

        if ($booking->item_type !== $modelClass || (int) $booking->item_id !== (int) $validated['original_item_id']) {
            return back()->with('error', 'Unit asal tidak sesuai dengan unit pada booking')->withInput();
        }

        $originalItem = $modelClass::findOrFail($validated['original_item_id']);
        $replacementItem = $modelClass::findOrFail($validated['replacement_item_id']);

        if ((int) $originalItem->id === (int) $replacementItem->id) {
            return back()->with('error', 'Unit pengganti harus berbeda dari unit asal')->withInput();
        }
        if ($replacementItem->status !== 'available' || !$replacementItem->is_active) {
            return back()->with('error', 'Unit pengganti tidak tersedia')->withInput();
        }

        $ownerId = $this->itemMerchantOwnerId();
        if (in_array($role, ['owner', 'admin']) && $ownerId) {
            if ((int) $originalItem->owner_id !== $ownerId || (int) $replacementItem->owner_id !== $ownerId) {
                abort(403, 'Unit tidak termasuk milik company Anda');
            }
        }

        $priceDiff = 0;
        if ($replacementItem->daily_price > $originalItem->daily_price) {
            $days = max(1, $booking->start_date->diffInDays($booking->end_date));
            $priceDiff = ($replacementItem->daily_price - $originalItem->daily_price) * $days;
        }

        $initialPhoto = $request->file('initial_item_photo')->store('item-replacements', 'public');
        $finalPhoto = null;
        if ($request->hasFile('final_item_photo')) {
            $finalPhoto = $request->file('final_item_photo')->store('item-replacements', 'public');
        }

        ItemReplacement::create([
            'booking_id' => $booking->id,
            'item_type' => $validated['item_type'],
            'original_item_id' => $validated['original_item_id'],
            'replacement_item_id' => $validated['replacement_item_id'],
            'requested_by' => Auth::id(),
            'status' => 'pending',
            'reason' => $validated['reason'],
            'price_difference' => $priceDiff,
            'mark_maintenance' => $request->boolean('mark_maintenance'),
            'damage_notes' => $validated['damage_notes'] ?? null,
            'initial_item_photo' => $initialPhoto,
            'final_item_photo' => $finalPhoto,
        ]);

        return redirect()->route('item-replacements.index')->with('success', 'Permintaan penggantian unit dibuat');
    }

    public function approve(ItemReplacement $replacement)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403, 'Hanya superadmin, owner, atau admin yang dapat menyetujui');
        }
        $this->guardCompanyItemReplacement($replacement);

        if ($replacement->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses');
        }

        $replacement->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        $modelClass = match ($replacement->item_type) {
            'hp' => Phone::class,
            'camera' => Camera::class,
            'tenda' => CampingEquipment::class,
            'ps' => Playstation::class,
            'drone' => Drone::class,
            'musik' => MusicalInstrument::class,
        };

        $originalItem = $modelClass::find($replacement->original_item_id);
        if ($originalItem) {
            $originalItem->update([
                'status' => $replacement->mark_maintenance ? 'maintenance' : 'available',
            ]);
        }

        $replacementItem = $modelClass::find($replacement->replacement_item_id);
        if ($replacementItem) {
            $replacementItem->update(['status' => 'reserved']);
        }

        $booking = $replacement->booking;
        $booking->update([
            'item_type' => $modelClass,
            'item_id' => $replacement->replacement_item_id,
            'final_price' => $booking->final_price + $replacement->price_difference,
        ]);

        return back()->with('success', 'Penggantian unit disetujui');
    }

    public function reject(ItemReplacement $replacement)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403, 'Hanya superadmin, owner, atau admin yang dapat menolak');
        }
        $this->guardCompanyItemReplacement($replacement);

        $replacement->update(['status' => 'rejected', 'approved_by' => Auth::id()]);
        return back()->with('success', 'Permintaan ditolak');
    }

    public function returnItem(Request $request, ItemReplacement $replacement)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses untuk memproses pengembalian');
        }
        $this->guardCompanyItemReplacement($replacement);

        if ($replacement->status !== 'approved' || $replacement->is_returned) {
            return back()->with('error', 'Unit ini tidak dapat diproses untuk pengembalian');
        }

        $validated = $request->validate([
            'condition_notes' => 'required|string|max:2000',
            'condition_rating' => 'required|integer|min:1|max:10',
            'return_is_damaged' => 'nullable|in:0,1',
            'return_damage_notes' => 'nullable|string|max:2000',
        ]);

        $replacement->update([
            'return_notes' => $validated['condition_notes'],
            'return_condition' => $validated['condition_rating'],
            'is_returned' => true,
            'returned_at' => now(),
            'returned_by' => Auth::id(),
            'return_is_damaged' => $validated['return_is_damaged'] ?? false,
            'return_damage_notes' => $validated['return_damage_notes'] ?? null,
        ]);

        $modelClass = match ($replacement->item_type) {
            'hp' => Phone::class,
            'camera' => Camera::class,
            'tenda' => CampingEquipment::class,
            'ps' => Playstation::class,
            'drone' => Drone::class,
            'musik' => MusicalInstrument::class,
        };
        if ($replacementItem = $modelClass::find($replacement->replacement_item_id)) {
            $replacementItem->update([
                'status' => $replacement->return_is_damaged ? 'maintenance' : 'available',
            ]);
        }

        return back()->with('success', 'Pengembalian unit berhasil dicatat');
    }
}
