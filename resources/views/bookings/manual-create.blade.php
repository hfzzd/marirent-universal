@extends('layouts.dashboard')
@section('page-title', 'Booking Manual (Walk-in)')

@section('content')
<div class="max-w-4xl">
    <a href="{{ route('bookings.index') }}" class="text-sky-600 text-sm mb-4 inline-flex items-center hover:text-sky-700 transition"><i class="fas fa-arrow-left mr-1.5"></i> Kembali ke Daftar Booking</a>

    <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5">
        <h2 class="text-lg font-extrabold text-navy-800 flex items-center gap-2"><i class="fas fa-user-pen text-sky-500"></i> Buat Booking Manual</h2>
        <p class="text-[12px] text-gray-400 mt-1">Catat penyewaan langsung dari pelanggan walk-in di kantor/lokasi. Booking otomatis berstatus <strong>Dikonfirmasi</strong>.</p>
    </div>

    <form method="POST" action="{{ route('bookings.manual-store') }}" x-data="manualBooking()" class="space-y-5">
        @csrf
        <input type="hidden" name="customer_mode" :value="mode">

        {{-- Data Pelanggan --}}
        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-user text-sky-500"></i> Data Pelanggan</h3>
            <div class="flex gap-2 mb-4">
                <button type="button" @click="mode = 'existing'" :class="mode === 'existing' ? 'bg-sky-500 text-white' : 'bg-gray-100 text-navy-600 hover:bg-gray-200'" class="px-4 py-2 rounded-xl text-[12px] font-bold transition"><i class="fas fa-address-book mr-1"></i> Akun Terdaftar</button>
                <button type="button" @click="mode = 'new'" :class="mode === 'new' ? 'bg-sky-500 text-white' : 'bg-gray-100 text-navy-600 hover:bg-gray-200'" class="px-4 py-2 rounded-xl text-[12px] font-bold transition"><i class="fas fa-user-plus mr-1"></i> Walk-in Baru</button>
            </div>

            <div x-show="mode === 'existing'">
                <label class="block text-[12px] font-semibold text-navy-700 mb-1">Pilih Pelanggan *</label>
                <select name="user_id" x-show="mode === 'existing'" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    <option value="">-- Pilih akun pelanggan --</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ old('user_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->phone ?? $c->email }})</option>
                    @endforeach
                </select>
            </div>

            <div x-show="mode === 'new'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="guest_name" value="{{ old('guest_name') }}" placeholder="Nama pelanggan" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">No. HP *</label>
                    <input type="text" name="guest_phone" value="{{ old('guest_phone') }}" placeholder="08xxxxxxxxxx" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <p class="sm:col-span-2 text-[11px] text-gray-400 bg-sky-50 rounded-lg px-3 py-2"><i class="fas fa-info-circle text-sky-400 mr-1"></i> Akun pelanggan dibuat otomatis oleh sistem dengan email sementara.</p>
            </div>
        </div>

        {{-- Pilih Item Sewa --}}
        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm" x-data="{ kind: '{{ old('item_kind', 'mobil') }}' }">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-box-open text-sky-500"></i> Pilih Item Sewa *</h3>
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 mb-4">
                @foreach(['mobil' => ['fa-car', 'Mobil'], 'motor' => ['fa-motorcycle', 'Motor'], 'kamera' => ['fa-camera', 'Kamera'], 'hp' => ['fa-mobile-alt', 'HP'], 'tenda' => ['fa-campground', 'Alat Camping'], 'ps' => ['fa-gamepad', 'PS'], 'drone' => ['fa-drone', 'Drone'], 'musik' => ['fa-guitar', 'Musik']] as $key => [$icon, $label])
                <button type="button" @click="kind = '{{ $key }}'; if (window.__filterManualItems) window.__filterManualItems('{{ $key }}');" :class="kind === '{{ $key }}' ? 'bg-sky-500 text-white shadow-lg shadow-sky-500/25' : 'bg-gray-50 text-navy-600 border border-gray-200 hover:border-sky-300'" class="flex flex-col items-center gap-1 px-2 py-3 rounded-xl text-[11px] font-bold transition">
                    <i class="fas {{ $icon }} text-base"></i> {{ $label }}
                </button>
                @endforeach
            </div>
            <input type="hidden" name="item_kind" :value="kind">

            <select name="item_id" id="item_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                <option value="">-- Pilih unit tersedia --</option>
                @foreach($vehicles->where('category.slug', 'mobil') as $v)
                <option value="{{ $v->id }}" data-kind="mobil">{{ $v->name }} - Rp {{ number_format($v->daily_price, 0, ',', '.') }}/hari {{ $v->license_plate ? '(' . $v->license_plate . ')' : '' }}</option>
                @endforeach
                @foreach($vehicles->where('category.slug', 'motor') as $v)
                <option value="{{ $v->id }}" data-kind="motor">{{ $v->name }} - Rp {{ number_format($v->daily_price, 0, ',', '.') }}/hari {{ $v->license_plate ? '(' . $v->license_plate . ')' : '' }}</option>
                @endforeach
                @foreach($cameras as $c)
                <option value="{{ $c->id }}" data-kind="kamera">{{ $c->name }} - Rp {{ number_format($c->daily_price, 0, ',', '.') }}/hari</option>
                @endforeach
                @foreach($phones as $p)
                <option value="{{ $p->id }}" data-kind="hp">{{ $p->name }} - Rp {{ number_format($p->daily_price, 0, ',', '.') }}/hari</option>
                @endforeach
                @foreach($campings as $t)
                <option value="{{ $t->id }}" data-kind="tenda">{{ $t->name }} - Rp {{ number_format($t->daily_price, 0, ',', '.') }}/hari</option>
                @endforeach
                @foreach($playstations as $p)
                <option value="{{ $p->id }}" data-kind="ps">{{ $p->name }} - Rp {{ number_format($p->daily_price, 0, ',', '.') }}/hari</option>
                @endforeach
                @foreach($drones as $d)
                <option value="{{ $d->id }}" data-kind="drone">{{ $d->name }} - Rp {{ number_format($d->daily_price, 0, ',', '.') }}/hari</option>
                @endforeach
                @foreach($instruments as $m)
                <option value="{{ $m->id }}" data-kind="musik">{{ $m->name }} - Rp {{ number_format($m->daily_price, 0, ',', '.') }}/hari</option>
                @endforeach
            </select>
        </div>

        {{-- Detail Sewa --}}
        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-calendar-check text-sky-500"></i> Detail Sewa</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Tipe Sewa *</label>
                    <select name="rental_type" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @foreach(['daily' => 'Harian', 'hourly' => 'Per Jam', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'] as $val => $label)
                        <option value="{{ $val }}" {{ old('rental_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Driver (opsional, kendaraan)</label>
                    <select name="driver_id" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <option value="">Tanpa driver / lepas kunci</option>
                        @foreach($drivers as $d)
                        <option value="{{ $d->id }}" {{ old('driver_id') == $d->id ? 'selected' : '' }}>{{ $d->user?->name ?? 'Driver #' . $d->id }} ({{ ucfirst(str_replace('_', ' ', $d->status)) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Tanggal & Jam Mulai *</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date', now()->format('Y-m-d\TH:i')) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Tanggal & Jam Selesai *</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date', now()->addDay()->format('Y-m-d\TH:i')) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Lokasi Ambil</label>
                    <input type="text" name="pickup_location" value="{{ old('pickup_location') }}" placeholder="Contoh: Kantor pusat" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Lokasi Kembali</label>
                    <input type="text" name="dropoff_location" value="{{ old('dropoff_location') }}" placeholder="Contoh: Kantor pusat" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
            </div>
        </div>

        {{-- Pembayaran --}}
        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-wallet text-sky-500"></i> Pembayaran</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Status Pembayaran Awal</label>
                    <select name="payment_status" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <option value="unpaid" {{ old('payment_status') == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="partial" {{ old('payment_status') == 'partial' ? 'selected' : '' }}>DP / Sebagian</option>
                        <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Lunas Langsung</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Jatuh Tempo Pembayaran</label>
                    <input type="date" name="payment_due_date" value="{{ old('payment_due_date', now()->addDays(2)->toDateString()) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Diskon (Rp)</label>
                    <input type="number" name="discount" value="{{ old('discount', 0) }}" min="0" step="1000" placeholder="0" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Metode Pembayaran</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-start gap-2.5 cursor-pointer border rounded-xl p-3 transition {{ old('payment_plan', 'full') !== 'dp50' ? 'border-sky-300 bg-sky-50/60' : 'border-gray-200' }}">
                            <input type="radio" name="payment_plan" value="full" {{ old('payment_plan') !== 'dp50' ? 'checked' : '' }} class="mt-0.5 accent-sky-600">
                            <span class="flex-1">
                                <span class="block text-[13px] font-bold text-navy-800">Bayar Penuh</span>
                                <span class="block text-[11px] text-gray-400">Lunasi seluruh biaya.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-2.5 cursor-pointer border rounded-xl p-3 transition {{ old('payment_plan') === 'dp50' ? 'border-amber-300 bg-amber-50/60' : 'border-gray-200' }}">
                            <input type="radio" name="payment_plan" value="dp50" {{ old('payment_plan') === 'dp50' ? 'checked' : '' }} class="mt-0.5 accent-amber-500">
                            <span class="flex-1">
                                <span class="block text-[13px] font-bold text-navy-800"><i class="fas fa-hand-holding-dollar text-amber-500 mr-1 text-[11px]"></i> DP 50%</span>
                                <span class="block text-[11px] text-gray-400">DP 50% dihitung otomatis dari total tagihan.</span>
                            </span>
                        </label>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-2">Jika memilih DP 50%, payment pertama minimal 50% dari total tagihan.</p>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan tambahan transaksi..." class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pb-2">
            <button type="submit" class="btn-primary text-white px-8 py-3 rounded-xl text-sm font-bold shadow-lg shadow-sky-500/25 transition"><i class="fas fa-save mr-2"></i> Simpan Booking Manual</button>
            <a href="{{ route('bookings.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-3 rounded-xl text-sm font-semibold text-navy-700 transition">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function manualBooking() {
    return {
        mode: '{{ old('customer_mode', 'existing') }}',
    };
}
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('item_id');
    if (!select) return;
    const filterItems = (kind) => {
        let first = true;
        select.querySelectorAll('option[data-kind]').forEach(opt => {
            const show = opt.dataset.kind === kind;
            opt.hidden = !show;
            opt.disabled = !show;
            if (show && first) { select.value = opt.value; first = false; }
        });
        if (first) select.value = '';
    };
    filterItems('{{ old('item_kind', 'mobil') }}');
    window.__filterManualItems = filterItems;
});
</script>
@endpush
