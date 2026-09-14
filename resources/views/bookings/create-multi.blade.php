@extends(auth()->user()->role === 'user' ? 'layouts.user' : 'layouts.dashboard')
@section('page-title', 'Booking Multi-Item (1 Invoice)')
@section('content')
<div class="max-w-4xl">
    <a href="{{ route('bookings.index') }}" class="text-sky-600 text-sm mb-4 inline-flex items-center hover:text-sky-700 transition"><i class="fas fa-arrow-left mr-1.5"></i> Kembali ke Daftar Booking</a>

    <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5">
        <h2 class="text-lg font-extrabold text-navy-800 flex items-center gap-2"><i class="fas fa-boxes text-sky-500"></i> Booking Multi-Item</h2>
        <p class="text-[12px] text-gray-400 mt-1">Pilih beberapa unit (HP, Kamera, Alat, dll) dalam 1 booking. Sistem membuat <strong>1 invoice</strong> untuk semua item.</p>
    </div>

    <form method="POST" action="{{ route('bookings.store-multi') }}" enctype="multipart/form-data" x-data="multiBooking()" class="space-y-5">
        @csrf

        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-4">Jadwal Sewa</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Tipe Sewa *</label>
                    <select name="rental_type" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="daily" {{ old('rental_type')=='daily'?'selected':'' }}>Harian</option>
                        <option value="hourly" {{ old('rental_type')=='hourly'?'selected':'' }}>Per Jam</option>
                        <option value="weekly" {{ old('rental_type')=='weekly'?'selected':'' }}>Mingguan</option>
                        <option value="monthly" {{ old('rental_type')=='monthly'?'selected':'' }}>Bulanan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Mulai *</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date') }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Selesai *</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date') }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Rencana Pembayaran *</label>
                    <select name="payment_plan" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="full">Bayar Penuh</option>
                        <option value="dp50">DP 50%</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Foto KTP *</label>
                    <input type="file" name="ktp_photo" required accept="image/*" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-[12px] font-semibold text-navy-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm" placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[13px] font-extrabold text-navy-800">Pilih Item (<span x-text="selected.length"></span> dipilih)</h3>
                <div class="flex gap-2">
                    <template x-for="cat in categories" :key="cat.key">
                        <button type="button" @click="activeCat = cat.key" :class="activeCat === cat.key ? 'bg-sky-500 text-white' : 'bg-gray-100 text-navy-600'" class="px-3 py-1.5 rounded-lg text-[11px] font-bold transition" x-text="cat.label"></button>
                    </template>
                </div>
            </div>

            @php
                $groups = [
                    'hp' => ['label' => 'HP', 'items' => $phones ?? collect()],
                    'kamera' => ['label' => 'Kamera', 'items' => $cameras ?? collect()],
                    'tenda' => ['label' => 'Alat Camping', 'items' => $equipments ?? collect()],
                    'ps' => ['label' => 'Playstation', 'items' => $playstations ?? collect()],
                    'drone' => ['label' => 'Drone', 'items' => $drones ?? collect()],
                    'musik' => ['label' => 'Alat Musik', 'items' => $instruments ?? collect()],
                ];
            @endphp

            <template x-for="(catItems, catKey) in allItems" :key="catKey">
                <div x-show="activeCat === catKey" class="space-y-3">
                    <template x-for="it in catItems" :key="catKey + '-' + it.id">
                        <div class="border rounded-xl p-3 flex flex-col gap-2" :class="isSelected(catKey, it.id) ? 'border-sky-400 bg-sky-50/50' : 'border-gray-200'">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" :checked="isSelected(catKey, it.id)" @change="toggleItem(catKey, it)" class="rounded border-gray-300 text-sky-600 w-4 h-4">
                                <div class="flex-1">
                                    <p class="text-[13px] font-bold text-navy-800" x-text="it.name"></p>
                                    <p class="text-[11px] text-gray-400" x-text="'Rp ' + Number(it.daily_price).toLocaleString('id-ID') + '/hari'"></p>
                                </div>
                                <span x-show="isSelected(catKey, it.id)" class="text-sky-600 text-xs font-bold">Dipilih</span>
                            </label>
                            <div x-show="isSelected(catKey, it.id)" class="grid grid-cols-2 gap-2 pl-7">
                                <select :name="'items[' + selIndex(catKey, it.id) + '][urgency]'" class="border border-gray-300 rounded-lg px-2 py-1.5 text-[12px]">
                                    <option value="normal">Normal</option>
                                    <option value="urgent">Urgent (+10%)</option>
                                    <option value="very_urgent">Sangat Urgent (+20%)</option>
                                </select>
                                <select :name="'items[' + selIndex(catKey, it.id) + '][with_insurance]'" class="border border-gray-300 rounded-lg px-2 py-1.5 text-[12px]">
                                    <option value="0">Tanpa Asuransi</option>
                                    <option value="1">Dengan Asuransi</option>
                                </select>
                                <input type="hidden" :name="'items[' + selIndex(catKey, it.id) + '][type]'" :value="catKey">
                                <input type="hidden" :name="'items[' + selIndex(catKey, it.id) + '][id]'" :value="it.id">
                            </div>
                        </div>
                    </template>
                    <p x-show="catItems.length === 0" class="text-[12px] text-gray-400 text-center py-4">Tidak ada unit tersedia di kategori ini.</p>
                </div>
            </template>

            <p x-show="selected.length === 0" class="text-[12px] text-red-500 mt-3">Pilih minimal 1 item untuk melanjutkan.</p>
        </div>

        <button type="submit" :disabled="selected.length === 0" :class="selected.length === 0 ? 'opacity-50 cursor-not-allowed' : ''" class="btn-primary text-white w-full py-3.5 rounded-xl font-bold text-[14px] shadow-lg shadow-sky-500/25">
            <i class="fas fa-check mr-2"></i> Buat Booking (<span x-text="selected.length"></span> item, 1 Invoice)
        </button>
    </form>
</div>

<script>
function multiBooking() {
    return {
        activeCat: 'hp',
        categories: [
            { key: 'hp', label: 'HP' },
            { key: 'kamera', label: 'Kamera' },
            { key: 'tenda', label: 'Camping' },
            { key: 'ps', label: 'PS' },
            { key: 'drone', label: 'Drone' },
            { key: 'musik', label: 'Musik' },
        ],
        allItems: {
            hp: @js(($phones ?? collect())->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'daily_price' => (float) $p->daily_price])->values()),
            kamera: @js(($cameras ?? collect())->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'daily_price' => (float) $p->daily_price])->values()),
            tenda: @js(($equipments ?? collect())->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'daily_price' => (float) $p->daily_price])->values()),
            ps: @js(($playstations ?? collect())->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'daily_price' => (float) $p->daily_price])->values()),
            drone: @js(($drones ?? collect())->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'daily_price' => (float) $p->daily_price])->values()),
            musik: @js(($instruments ?? collect())->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'daily_price' => (float) $p->daily_price])->values()),
        },
        selected: [],
        toggleItem(catKey, it) {
            const idx = this.selected.findIndex(s => s.type === catKey && String(s.id) === String(it.id));
            if (idx >= 0) this.selected.splice(idx, 1);
            else this.selected.push({ type: catKey, id: it.id });
        },
        isSelected(catKey, id) {
            return this.selected.some(s => s.type === catKey && String(s.id) === String(id));
        },
        selIndex(catKey, id) {
            return this.selected.findIndex(s => s.type === catKey && String(s.id) === String(id));
        }
    }
}
</script>
@endsection
