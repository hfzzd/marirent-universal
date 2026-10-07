@extends(auth()->user()->role === 'user' ? 'layouts.user' : 'layouts.dashboard')
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
    @if(in_array(auth()->user()->role, ['superadmin','owner','admin','user']))
    <div class="flex items-center gap-2 flex-wrap">
        @if(in_array(auth()->user()->role, ['admin', 'owner'], true))
        <button type="button" x-data @click="$dispatch('open-item-modal')" class="bg-gradient-to-r from-sky-500 to-sky-600 text-white px-4 py-2 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-1.5">
            <i class="fas fa-bolt text-[10px]"></i> Penggantian Cepat
        </button>
        @endif
        <a href="{{ route('item-replacements.create') }}" class="bg-white border border-gray-200 hover:border-sky-300 hover:text-sky-600 text-gray-500 px-4 py-2 rounded-xl text-[12px] font-semibold transition flex items-center gap-1.5">
            <i class="fas fa-plus text-[10px]"></i> Form Lengkap
        </a>
    </div>
    @endif
</div>

{{-- MODAL PENGGANTIAN CEPAT (khusus akun admin & owner) --}}
@if(in_array(auth()->user()->role, ['admin', 'owner'], true))
@php
    $typeLabels = ['hp' => 'HP', 'camera' => 'Kamera', 'tenda' => 'Alat Camping', 'ps' => 'Playstation', 'drone' => 'Drone', 'musik' => 'Alat Musik'];
    $typeToClass = ['Phone' => 'hp', 'Camera' => 'camera', 'CampingEquipment' => 'tenda', 'Playstation' => 'ps', 'Drone' => 'drone', 'MusicalInstrument' => 'musik'];
@endphp
<div x-data="itemQuickForm()" @open-item-modal.window="open = true" x-cloak>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50" @click="open = false"></div>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95 translate-y-4" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 rounded-t-2xl p-5 relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-swap-horizontal text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Penggantian Unit Cepat</h3>
                            <p class="text-sky-200 text-[11px]">Ajukan tanpa buka halaman baru</p>
                        </div>
                    </div>
                    <button @click="open = false" class="text-white/70 hover:text-white transition"><i class="fas fa-times text-lg"></i></button>
                </div>
            </div>
            <form method="POST" action="{{ route('item-replacements.store') }}" enctype="multipart/form-data" @submit="loading = true" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Tipe Unit *</label>
                    <x-searchable-select name="item_type" placeholder="Pilih tipe unit" x-model="type" @change="onTypeChange()" required>
                        @foreach($typeLabels as $val => $label)
                        <option value="{{ $val }}" {{ ($modalType ?? 'hp') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-searchable-select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Booking *</label>
                    <x-searchable-select name="booking_id" placeholder="Pilih Booking" x-model="bookingId" required>
                        <option value="">Pilih Booking</option>
                        @foreach($modalBookings as $b)
                        <option value="{{ $b->id }}" data-type="{{ $typeToClass[class_basename($b->item_type ?? '')] ?? '' }}">{{ $b->booking_code }} - {{ $b->item_type ? class_basename($b->item_type) : '-' }}</option>
                        @endforeach
                    </x-searchable-select>
                    <p x-show="filteredBookings === 0" class="text-amber-600 text-[12px] mt-2"><i class="fas fa-info-circle mr-1"></i> Tidak ada booking ongoing untuk tipe ini.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Unit Saat Ini *</label>
                        <x-searchable-select name="original_item_id" placeholder="Pilih Unit" required x-html="itemOptions"></x-searchable-select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Unit Pengganti *</label>
                        <x-searchable-select name="replacement_item_id" placeholder="Pilih Unit" required x-html="itemOptions"></x-searchable-select>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Alasan *</label>
                    <textarea name="reason" rows="2" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50 resize-none" placeholder="Jelaskan alasan penggantian unit..."></textarea>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Foto Awal Unit *</label>
                    <input type="file" name="initial_item_photo" accept="image/*" required class="w-full text-[12px] text-navy-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-[12px] file:font-semibold file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100 file:transition border-2 border-dashed border-gray-200 rounded-xl p-3 bg-gray-50/50">
                </div>
                <div class="flex gap-3 pt-2 border-t border-gray-100">
                    <button type="submit" :disabled="loading" class="bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white px-6 py-2.5 rounded-xl text-[13px] font-bold shadow-lg shadow-sky-500/25 transition flex items-center gap-2 disabled:opacity-50">
                        <i class="fas fa-paper-plane" x-show="!loading"></i>
                        <i class="fas fa-spinner fa-spin" x-show="loading" x-cloak></i>
                        <span x-text="loading ? 'Mengirim...' : 'Ajukan Penggantian'"></span>
                    </button>
                    <button type="button" @click="open = false" class="bg-gray-100 hover:bg-gray-200 px-5 py-2.5 rounded-xl text-[13px] font-medium text-navy-700 transition">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function itemQuickForm() {
    const itemsByType = @json($modalItemsByType ?? []);
    return {
        open: false,
        loading: false,
        type: '{{ $modalType ?? 'hp' }}',
        bookingId: '',
        itemOptions: '',
        filteredBookings: 1,
        init() {
            this.onTypeChange();
            this.$watch('type', () => this.onTypeChange());
        },
        onTypeChange() {
            const list = itemsByType[this.type] || [];
            if (!list.length) {
                this.itemOptions = '<option value="">Tidak ada unit tersedia</option>';
            } else {
                this.itemOptions = '<option value="">Pilih Unit</option>' + list.map(i =>
                    '<option value="' + i.id + '">' + i.label.replace(/</g, '&lt;') + '</option>'
                ).join('');
            }
            // Filter booking sesuai tipe
            const sel = this.$el.querySelector('select[name="booking_id"]');
            let visible = 0;
            sel.querySelectorAll('option[data-type]').forEach(o => {
                const show = !o.dataset.type || o.dataset.type === this.type;
                o.hidden = !show;
                if (show) visible++;
            });
            this.filteredBookings = visible;
            if (this.bookingId) {
                const cur = sel.querySelector('option[value="' + this.bookingId + '"]');
                if (!cur || cur.hidden) this.bookingId = '';
            }
        }
    };
}
</script>
@endpush
@endif

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
            <x-searchable-select name="status" placeholder="Semua" size="sm" wrapClass="min-w-[140px]">
                <option value="">Semua</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </x-searchable-select>
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
                        @elseif(in_array($r->item_type, ['camera', 'kamera']))
                            <span class="bg-violet-100 text-violet-700" style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;display:inline-block;"><i class="fas fa-camera mr-1"></i>Kamera</span>
                        @elseif($r->item_type === 'tenda')
                            <span class="bg-emerald-100 text-emerald-700" style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;display:inline-block;"><i class="fas fa-campground mr-1"></i>Alat Camping</span>
                        @elseif($r->item_type === 'ps')
                            <span class="bg-indigo-100 text-indigo-700" style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;display:inline-block;"><i class="fas fa-gamepad mr-1"></i>Playstation</span>
                        @elseif($r->item_type === 'drone')
                            <span class="bg-cyan-100 text-cyan-700" style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;display:inline-block;"><i class="fas fa-drone mr-1"></i>Drone</span>
                        @elseif($r->item_type === 'musik')
                            <span class="bg-rose-100 text-rose-700" style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;display:inline-block;"><i class="fas fa-guitar mr-1"></i>Alat Musik</span>
                        @else
                            <span class="bg-emerald-100 text-emerald-700" style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;display:inline-block;"><i class="fas fa-campground mr-1"></i>Alat Camping</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-navy-600">{{ $r->originalItem->name ?? '-' }}</td>
                    <td class="py-3 px-5 text-navy-600">{{ $r->replacementItem->name ?? '-' }}</td>
                    <td class="py-3 px-5 font-medium {{ $r->price_difference > 0 ? 'text-red-600' : ($r->price_difference < 0 ? 'text-green-600' : 'text-navy-500') }}">
                        {{ $r->price_difference > 0 ? '+' : '' }} Rp {{ number_format($r->price_difference,0,',','.') }}
                    </td>
                    <td class="py-3 px-5 text-center"><span class="{{ $r->status == 'approved' ? 'badge-green' : ($r->status == 'rejected' ? 'badge-red' : 'badge-blue') }} capitalize" style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;display:inline-block;">{{ $r->status }}</span></td>
                     <td class="py-3 px-5">
                         @if($r->status == 'pending' && in_array(auth()->user()->role, ['superadmin','owner','admin']))
                         <div class="flex items-center gap-2">
                         <form method="POST" action="{{ route('item-replacements.approve', $r) }}" class="inline">@csrf
                             <button class="text-emerald-600 text-[11px] font-semibold hover:text-emerald-700"><i class="fas fa-check mr-1"></i>Setuju</button>
                         </form>
                         <form method="POST" action="{{ route('item-replacements.reject', $r) }}" class="inline" onsubmit="return confirm('Tolak permintaan ini?')">@csrf
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
                         <a href="{{ route('item-replacements.show', $r) }}" class="text-sky-600 text-[11px] font-semibold hover:text-sky-700"><i class="fas fa-eye mr-1"></i>Detail</a>
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
                     <input type="checkbox" name="return_is_damaged" value="1" class="rounded border-gray-300 text-amber-500 focus:ring-amber-300" onchange="document.getElementById('damageNotesField').classList.toggle('hidden', !this.checked)">
                    <span class="text-sm text-gray-700">Unit mengalami kerusakan</span>
                </label>
            </div>
            <div id="damageNotesField" class="mb-4 hidden">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Detail Kerusakan</label>
                 <textarea name="return_damage_notes" rows="2" maxlength="2000" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-300" placeholder="Jelaskan kerusakan yang ditemukan..."></textarea>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeReturnModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-lg"><i class="fas fa-check mr-1"></i>Submit</button>
            </div>
        </form>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
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
