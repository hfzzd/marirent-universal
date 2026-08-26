<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ItemReplacement;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;
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
            default => null,
        };
    }

    public function index(Request $request)
    {
        $query = ItemReplacement::with(['booking', 'originalItem', 'replacementItem', 'requestedBy', 'approvedBy']);

        $role = Auth::user()->role;

        if ($role === 'user') {
            $query->whereHas('booking', fn($q) => $q->where('user_id', Auth::id()));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $replacements = $query->latest()->paginate(15);

        return view('item-replacements.index', compact('replacements'));
    }

    public function create(Request $request)
    {
        if (Auth::user()->role !== 'user') {
            abort(403, 'Hanya user yang dapat mengajukan penggantian unit elektronik');
        }

        $type = $request->type ?? 'hp';
        if (!in_array($type, ['hp', 'camera', 'tenda'])) {
            abort(404);
        }

        $bookings = Booking::whereIn('status', ['confirmed', 'ongoing'])
            ->where('user_id', Auth::id())
            ->where('item_type', '!=', null)
            ->with(['user', 'category'])
            ->get();

        $items = $this->getAvailableItems($type);

        return view('item-replacements.create', compact('type', 'bookings', 'items'));
    }

    private function getAvailableItems(string $type)
    {
        $modelClass = match ($type) {
            'hp' => Phone::class,
            'camera' => Camera::class,
            'tenda' => CampingEquipment::class,
        };

        return $modelClass::where('status', 'available')
            ->where('is_active', true)
            ->with('category')
            ->get();
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'user') {
            abort(403, 'Hanya user yang dapat mengajukan penggantian unit elektronik');
        }

        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'item_type' => 'required|in:hp,camera,tenda',
            'original_item_id' => 'required|integer',
            'replacement_item_id' => 'required|integer',
            'reason' => 'required|string|max:2000',
            'initial_item_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'final_item_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        $modelClass = match ($validated['item_type']) {
            'hp' => Phone::class,
            'camera' => Camera::class,
            'tenda' => CampingEquipment::class,
        };

        $originalItem = $modelClass::findOrFail($validated['original_item_id']);
        $replacementItem = $modelClass::findOrFail($validated['replacement_item_id']);

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
            'initial_item_photo' => $initialPhoto,
            'final_item_photo' => $finalPhoto,
        ]);

        return redirect()->route('item-replacements.index')->with('success', 'Permintaan penggantian unit dibuat');
    }

    public function approve(ItemReplacement $replacement)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403, 'Hanya superadmin dan owner yang dapat menyetujui');
        }

        $replacement->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        $modelClass = match ($replacement->item_type) {
            'hp' => Phone::class,
            'camera' => Camera::class,
            'tenda' => CampingEquipment::class,
        };

        $originalItem = $modelClass::find($replacement->original_item_id);
        if ($originalItem) {
            $originalItem->update(['status' => 'available']);
        }

        $replacementItem = $modelClass::find($replacement->replacement_item_id);
        if ($replacementItem) {
            $replacementItem->update(['status' => 'reserved']);
        }

        $booking = $replacement->booking;
        $booking->update([
            'item_type' => $replacement->item_type === 'hp' ? \App\Models\Phone::class : ($replacement->item_type === 'camera' ? \App\Models\Camera::class : \App\Models\CampingEquipment::class),
            'item_id' => $replacement->replacement_item_id,
            'final_price' => $booking->final_price + $replacement->price_difference,
        ]);

        return back()->with('success', 'Penggantian unit disetujui');
    }

    public function reject(ItemReplacement $replacement)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403, 'Hanya superadmin dan owner yang dapat menolak');
        }

        $replacement->update(['status' => 'rejected', 'approved_by' => Auth::id()]);
        return back()->with('success', 'Permintaan ditolak');
    }
}
