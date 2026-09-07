@extends('layouts.dashboard')
@section('page-title', 'Input Gaji Driver & Karyawan')
@section('content')
<div class="max-w-3xl">
    <a href="{{ route('salaries.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ route('salaries.store') }}" x-data="salaryForm()">
            @csrf
            <div class="space-y-4">
                @if($isSuperadmin)
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Company *</label>
                    <select name="company_id" required onchange="window.location.href='{{ route('salaries.create') }}?company_id=' + this.value" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">-- Pilih company --</option>
                        @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ $selectedCompanyId == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->city ?? '-' }})</option>
                        @endforeach
                    </select>
                    @error('company_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Driver / Karyawan *</label>
                    <select name="driver_id" required x-model="driverId" @change="loadDriver()" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">Pilih Driver / Karyawan</option>
                        @foreach($drivers as $d)
                        <option value="{{ $d->id }}" data-name="{{ $d->user?->name }}" data-position="{{ $d->position ?? 'Driver' }}"
                            data-salary="{{ (int) $d->daily_salary }}" data-trip="{{ (int) $d->trip_salary }}"
                            data-phone="{{ $d->user?->phone }}">{{ $d->user?->name }}
                            <span class="text-gray-400">({{ $d->position ?? 'Driver' }} - Rp {{ number_format($d->daily_salary,0,',','.') }}/hari)</span>
                        </option>
                        @endforeach
                    </select>
                    @error('driver_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div x-show="driverId" x-transition class="bg-gradient-to-br from-sky-50 to-sky-100/40 rounded-xl p-4 border border-sky-100 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-navy-800 text-sm" x-text="driverName">-</p>
                        <p class="text-[12px] text-navy-500 mt-0.5"><span class="badge badge-blue" x-text="driverPosition"></span><span x-text="driverPhone ? '  •  ' + driverPhone : ''" class="text-gray-400"></span></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[11px] text-gray-400 uppercase tracking-wide">Gaji / Hari</p>
                        <p class="font-bold text-sky-600 text-sm" x-text="'Rp ' + formatRp(driverDaily)">-</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Periode (Bulan) *</label>
                        <input type="month" name="period_month" value="{{ old('period_month', date('Y-m')) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Gaji Pokok (Rp)</label>
                        <input type="number" name="base_salary" x-model.number="baseSalary" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Bonus Perjalanan (Rp)</label>
                        <input type="number" name="trip_bonus" x-model.number="tripBonus" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Lembur (Rp)</label>
                        <input type="number" name="overtime_pay" x-model.number="overtimePay" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Potongan (Rp)</label>
                        <input type="number" name="deductions" x-model.number="deductions" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-[12px] font-semibold text-gray-400 uppercase tracking-wide">Total Take Home Pay</span>
                        <span class="text-xl font-extrabold text-sky-600" x-text="'Rp ' + formatRp(total)">Rp 0</span>
                    </div>
                    <div class="text-right text-[11px] text-gray-400 mt-0.5">
                        <span x-text="'Pokok ' + formatRp(baseSalary)">-</span> • <span x-text="'Bonus ' + formatRp(tripBonus)">-</span> • <span x-text="'Lembur ' + formatRp(overtimePay)">-</span> • <span x-text="'Potongan ' + formatRp(deductions)">-</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500"></textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">Simpan</button>
                <a href="{{ route('salaries.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function salaryForm() {
    const opts = Array.from(document.querySelectorAll('[name="driver_id"] option')).filter(o => o.value);
    const map = {};
    opts.forEach(o => {
        map[o.value] = {
            name: o.dataset.name,
            position: o.dataset.position,
            salary: Number(o.dataset.salary) || 0,
            trip: Number(o.dataset.trip) || 0,
            phone: o.dataset.phone || ''
        };
    });

    return {
        driverId: '',
        driverName: '',
        driverPosition: '',
        driverPhone: '',
        driverDaily: 0,
        baseSalary: 0,
        tripBonus: 0,
        overtimePay: 0,
        deductions: 0,
        loadDriver() {
            const d = map[this.driverId];
            if (!d) return;
            this.driverName = d.name;
            this.driverPosition = d.position;
            this.driverPhone = d.phone;
            this.driverDaily = d.salary;
            this.baseSalary = d.salary;
            this.tripBonus = d.trip;
        },
        get total() {
            return (Number(this.baseSalary) || 0) + (Number(this.tripBonus) || 0) + (Number(this.overtimePay) || 0) - (Number(this.deductions) || 0);
        },
        formatRp(v) {
            return Number(v || 0).toLocaleString('id-ID');
        },
    };
}
</script>
@endsection