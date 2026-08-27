@extends('layouts.dashboard')
@section('page-title', 'Penggantian Unit Elektronik')
@section('content')
{{-- HEADER & ACTION --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-5 gap-3">
    <div>
        <h2 class="text-lg font-extrabold text-navy-800 flex items-center gap-2">
            <i class="fas fa-swap-horizontal text-sky-500"></i> Penggantian Unit Elektronik
        </h2>
        <p class="text-xs text-gray-400 mt-0.5">Kelola permintaan penggantian unit HP, kamera, dan tenda.</p>
    </div>
    <a href="{{ route('item-replacements.create') }}" class="btn-primary text-white px-4 py-2 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-1.5">
        <i class="fas fa-plus text-[10px]"></i> Ajukan Penggantian
    </a>
</div>

{{-- FILTERS --}}
<div class="glass-card rounded-2xl p-4 mb-5 border border-sky-100/50 shadow-sm">
    <form action="{{ route('item-replacements.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
        <div class="flex-1 w-full">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cari</label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-[11px]"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode booking, nama unit..."
                    class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50">
            </div>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Status</label>
            <select name="status" class="border border-gray-200 rounded-xl px-3 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50 min-w-[140px]">
                <option value="">Semua</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2.5 rounded-xl text-[12px] font-semibold shadow-sm">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('item-replacements.index') }}" class="bg-red-50 hover:bg-red-100 text-red-500 px-3 py-2.5 rounded-xl text-[12px] font-medium transition border border-red-100">
                <i class="fas fa-times text-[10px]"></i> Reset
            </a>
            @endif
        </div>
    </form>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead><tr class="bg-sky-50/50 border-b border-sky-100/50">
                <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Booking</th>
                <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Tipe</th>
                <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Unit Asal</th>
                <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Unit Pengganti</th>
                <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Selisih Harga</th>
                <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($replacements as $r)
                <tr class="border-b hover:bg-sky-50/30">
                    <td class="py-3 px-5 font-mono font-medium text-sky-600 text-[12px]">{{ $r->booking->booking_code }}</td>
                    <td class="py-3 px-5">
                        @if($r->item_type === 'hp')
                            <span class="bg-blue-100 text-blue-700" style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;display:inline-block;"><i class="fas fa-mobile-alt mr-1"></i>HP</span>
                        @elseif($r->item_type === 'camera')
                            <span class="bg-violet-100 text-violet-700" style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;display:inline-block;"><i class="fas fa-camera mr-1"></i>Kamera</span>
                        @else
                            <span class="bg-emerald-100 text-emerald-700" style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;display:inline-block;"><i class="fas fa-campground mr-1"></i>Tenda</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-navy-600">{{ $r->originalItem->name ?? '-' }}</td>
                    <td class="py-3 px-5 text-navy-600">{{ $r->replacementItem->name ?? '-' }}</td>
                    <td class="py-3 px-5 font-medium {{ $r->price_difference > 0 ? 'text-red-600' : ($r->price_difference < 0 ? 'text-green-600' : 'text-navy-500') }}">
                        {{ $r->price_difference > 0 ? '+' : '' }} Rp {{ number_format($r->price_difference,0,',','.') }}
                    </td>
                    <td class="py-3 px-5 text-center"><span class="{{ $r->status == 'approved' ? 'badge-green' : ($r->status == 'rejected' ? 'badge-red' : 'badge-blue') }} capitalize" style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;display:inline-block;">{{ $r->status }}</span></td>
                    <td class="py-3 px-5">
                        @if($r->status == 'pending' && in_array(auth()->user()->role, ['superadmin','owner']))
                        <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('item-replacements.approve', $r) }}" class="inline">@csrf
                            <button class="text-emerald-600 text-[11px] font-semibold hover:text-emerald-700"><i class="fas fa-check mr-1"></i>Setuju</button>
                        </form>
                        <form method="POST" action="{{ route('item-replacements.reject', $r) }}" class="inline">@csrf
                            <button class="text-red-500 text-[11px] font-semibold hover:text-red-600"><i class="fas fa-times mr-1"></i>Tolak</button>
                        </form>
                        </div>
                        @endif
                        @if($r->status == 'approved' && !$r->is_returned && in_array(auth()->user()->role, ['superadmin','owner']))
                        <button onclick="openReturnModal({{ $r->id }}, '{{ addslashes($r->booking->booking_code ?? '-') }}', '{{ addslashes($r->replacementItem->name ?? '-') }}')" class="text-amber-600 text-[11px] font-semibold"><i class="fas fa-undo-alt mr-1"></i>Kembalikan</button>
                        @endif
                        @if($r->is_returned)
                        <span class="text-emerald-600 text-[11px] font-semibold"><i class="fas fa-check-circle mr-1"></i>Dikembalikan</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-14 text-center text-navy-400">Belum ada permintaan penggantian unit</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $replacements->withQueryString()->links() }}</div>
</div>

{{-- Return Modal --}}
<div id="returnModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-navy-800"><i class="fas fa-undo-alt mr-2 text-amber-500"></i>Pengembalian Unit</h3>
            <button onclick="closeReturnModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div class="text-xs text-gray-400 mb-4">
            <span id="returnBookingCode"></span> &middot; <span id="returnItemName"></span>
        </div>
        <form id="returnForm" method="POST" action="">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Kondisi Unit (1-10) <span class="text-navy-500 font-bold" id="ratingValue">5</span></label>
                <input type="range" name="condition_rating" id="conditionRating" min="1" max="10" value="5" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-sky-500" oninput="document.getElementById('ratingValue').textContent = this.value">
                <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                    <span>Sangat Buruk</span>
                    <span>Sangat Baik</span>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan Kondisi <span class="text-red-500">*</span></label>
                <textarea name="condition_notes" rows="3" required maxlength="2000" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 focus:border-sky-300" placeholder="Deskripsikan kondisi unit saat dikembalikan..."></textarea>
            </div>
            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_damaged" value="1" class="rounded border-gray-300 text-amber-500 focus:ring-amber-300" onchange="document.getElementById('damageNotesField').classList.toggle('hidden', !this.checked)">
                    <span class="text-sm text-gray-700">Unit mengalami kerusakan</span>
                </label>
            </div>
            <div id="damageNotesField" class="mb-4 hidden">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Detail Kerusakan</label>
                <textarea name="damage_notes" rows="2" maxlength="2000" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-300" placeholder="Jelaskan kerusakan yang ditemukan..."></textarea>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeReturnModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-lg"><i class="fas fa-check mr-1"></i>Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReturnModal(id, bookingCode, itemName) {
    document.getElementById('returnForm').action = '{{ url("item-replacements") }}/' + id + '/return';
    document.getElementById('returnBookingCode').textContent = 'Booking: ' + bookingCode;
    document.getElementById('returnItemName').textContent = 'Unit: ' + itemName;
    document.getElementById('conditionRating').value = 5;
    document.getElementById('ratingValue').textContent = '5';
    document.getElementById('damageNotesField').classList.add('hidden');
    var modal = document.getElementById('returnModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeReturnModal() {
    var modal = document.getElementById('returnModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
