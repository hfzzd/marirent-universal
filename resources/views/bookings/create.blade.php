@extends('layouts.public')
@section('title', 'Booking ' . $vehicle->name . ' - MariRent')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <a href="{{ route('public.vehicle', $vehicle->slug) }}" class="text-sky-600 hover:text-sky-700 text-sm mb-6 inline-flex items-center transition">
        <i class="fas fa-arrow-left mr-1.5"></i> Kembali ke {{ $vehicle->name }}
    </a>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-5 text-[13px] flex items-center gap-2">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-5 text-[13px]">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- FORM --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-11 h-11 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/20">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-navy-900">Form Booking</h1>
                        <p class="text-[12px] text-gray-400">Lengkapi data berikut untuk menyewa kendaraan</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('bookings.store') }}" enctype="multipart/form-data" id="booking-form">
                    @csrf
                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

                    <div class="space-y-5">
                        {{-- Rental Type --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-navy-700 mb-2">Tipe Sewa *</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2" x-data="{ type: '{{ old('rental_type', 'daily') }}' }">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="rental_type" value="hourly" x-model="type" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-xl p-3 text-center transition-all hover:border-sky-300">
                                        <i class="fas fa-clock text-gray-400 peer-checked:text-sky-500 text-lg mb-1"></i>
                                        <p class="text-[12px] font-semibold text-navy-700">Per Jam</p>
                                        @if($vehicle->hourly_price)
                                        <p class="text-[11px] text-gray-400">Rp {{ number_format($vehicle->hourly_price, 0, ',', '.') }}</p>
                                        @endif
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="rental_type" value="daily" x-model="type" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-xl p-3 text-center transition-all hover:border-sky-300">
                                        <i class="fas fa-calendar-day text-gray-400 peer-checked:text-sky-500 text-lg mb-1"></i>
                                        <p class="text-[12px] font-semibold text-navy-700">Per Hari</p>
                                        <p class="text-[11px] text-gray-400">Rp {{ number_format($vehicle->daily_price, 0, ',', '.') }}</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="rental_type" value="weekly" x-model="type" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-xl p-3 text-center transition-all hover:border-sky-300">
                                        <i class="fas fa-calendar-week text-gray-400 peer-checked:text-sky-500 text-lg mb-1"></i>
                                        <p class="text-[12px] font-semibold text-navy-700">Per Minggu</p>
                                        <p class="text-[11px] text-gray-400">Rp {{ number_format($vehicle->weekly_price ?? $vehicle->daily_price * 7, 0, ',', '.') }}</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="rental_type" value="monthly" x-model="type" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-xl p-3 text-center transition-all hover:border-sky-300">
                                        <i class="fas fa-calendar-alt text-gray-400 peer-checked:text-sky-500 text-lg mb-1"></i>
                                        <p class="text-[12px] font-semibold text-navy-700">Per Bulan</p>
                                        <p class="text-[11px] text-gray-400">Rp {{ number_format($vehicle->monthly_price ?? $vehicle->daily_price * 30, 0, ',', '.') }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Dates --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Tanggal Mulai *</label>
                                <input type="datetime-local" name="start_date" value="{{ old('start_date') }}" required
                                    min="{{ now()->addHours(1)->format('Y-m-d\TH:i') }}"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Tanggal Selesai *</label>
                                <input type="datetime-local" name="end_date" value="{{ old('end_date') }}" required
                                    min="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                            </div>
                        </div>

                        {{-- Tipe Penggunaan: Lepas Kunci / Sama Driver --}}
                        @if($vehicle->with_driver)
                        <div>
                            <label class="block text-[12px] font-semibold text-navy-700 mb-2">Tipe Penggunaan *</label>
                            <div class="grid grid-cols-2 gap-3" x-data="{ mode: '{{ old('with_driver') ? 'driver' : 'lepas_kunci' }}' }">
                                <label class="cursor-pointer">
                                    <input type="radio" name="with_driver" value="0" x-model="mode" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-2xl p-5 transition-all hover:border-sky-300 group text-center">
                                        <div class="w-12 h-12 rounded-xl bg-blue-50 peer-checked:bg-sky-100 flex items-center justify-center mb-3 mx-auto transition">
                                            <i class="fas fa-key text-blue-500 text-xl"></i>
                                        </div>
                                        <p class="font-bold text-navy-800 text-[14px]">Lepas Kunci</p>
                                        <p class="text-[11px] text-gray-400 mt-1">Kendarai sendiri kendaraan pilihan Anda</p>
                                        <p class="text-[13px] font-bold text-sky-600 mt-2">Rp {{ number_format($vehicle->daily_price, 0, ',', '.') }}/hari</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="with_driver" value="1" x-model="mode" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-2xl p-5 transition-all hover:border-sky-300 group text-center">
                                        <div class="w-12 h-12 rounded-xl bg-emerald-50 peer-checked:bg-sky-100 flex items-center justify-center mb-3 mx-auto transition">
                                            <i class="fas fa-user-tie text-emerald-500 text-xl"></i>
                                        </div>
                                        <p class="font-bold text-navy-800 text-[14px]">Sama Driver</p>
                                        <p class="text-[11px] text-gray-400 mt-1">Driver profesional siap mengantar Anda</p>
                                        <p class="text-[13px] font-bold text-sky-600 mt-2">+ Rp {{ number_format($vehicle->with_driver_daily_price ?? 0, 0, ',', '.') }}/hari</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @endif

                        {{-- Locations --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Lokasi Jemput</label>
                                <input type="text" name="pickup_location" value="{{ old('pickup_location') }}" placeholder="Alamat penjemputan"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Lokasi Pengembalian</label>
                                <input type="text" name="dropoff_location" value="{{ old('dropoff_location') }}" placeholder="Alamat pengembalian"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                            </div>
                        </div>

                        {{-- KTP --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Foto KTP</label>
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-sky-300 transition-colors">
                                <input type="file" name="ktp_photo" accept="image/*" id="ktp-input" class="hidden" onchange="previewKTP(this)">
                                <div id="ktp-empty">
                                    <i class="fas fa-id-card text-gray-300 text-2xl mb-2"></i>
                                    <p class="text-[12px] text-gray-400 mb-2">Unggah foto KTP Anda (opsional, bisa diisi nanti)</p>
                                    <label for="ktp-input" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-4 py-1.5 rounded-lg text-[12px] font-semibold cursor-pointer transition inline-flex items-center gap-1">
                                        <i class="fas fa-upload text-[10px]"></i> Pilih Foto
                                    </label>
                                </div>
                                <div id="ktp-preview" class="hidden">
                                    <img id="ktp-preview-img" class="max-h-40 mx-auto rounded-lg border border-gray-200 shadow-sm mb-2">
                                    <button type="button" onclick="cancelKTP()" class="text-red-500 text-[11px] font-medium hover:underline">Hapus</button>
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Catatan</label>
                            <textarea name="notes" rows="3" placeholder="Catatan tambahan untuk booking ini..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col sm:flex-row gap-3">
                        <button type="submit" class="btn-primary text-white px-8 py-3 rounded-xl font-semibold text-[14px] shadow-lg shadow-sky-500/25 transition flex items-center justify-center gap-2 flex-1">
                            <i class="fas fa-check-circle"></i> Konfirmasi Booking
                        </button>
                        <a href="{{ route('public.vehicle', $vehicle->slug) }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-3 rounded-xl text-[13px] font-medium text-navy-700 transition text-center">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- SUMMARY SIDEBAR --}}
        <div>
            <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-24">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-receipt text-sky-500 mr-2"></i>Ringkasan Booking</h3>

                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                    <div class="w-14 h-14 {{ $vehicle->category->slug == 'mobil' ? 'bg-sky-50' : ($vehicle->category->slug == 'motor' ? 'bg-amber-50' : 'bg-violet-50') }} rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden">
                        @if($vehicle->image)
                            <img src="{{ asset('storage/' . $vehicle->image) }}" alt="{{ $vehicle->name }}" class="w-full h-full object-cover">
                        @else
                            @if($vehicle->category->slug == 'mobil') <i class="fas fa-car text-sky-400"></i>
                            @elseif($vehicle->category->slug == 'motor') <i class="fas fa-motorcycle text-amber-400"></i>
                            @else <i class="fas fa-camera text-violet-400"></i>
                            @endif
                        @endif
                    </div>
                    <div>
                        <p class="font-bold text-navy-800 text-[14px]">{{ $vehicle->name }}</p>
                        <p class="text-[11px] text-gray-400">{{ $vehicle->brand }} {{ $vehicle->model }}</p>
                        <p class="text-[11px] text-sky-500 font-medium">{{ $vehicle->category->name }}</p>
                    </div>
                </div>

                <div class="space-y-2.5 text-[13px]" x-data="bookingSummary()">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Tipe Sewa</span>
                        <span class="font-medium text-navy-700" x-text="rentalTypeLabel()">Per Hari</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Harga Satuan</span>
                        <span class="font-medium text-navy-700" x-text="formatRupiah(unitPrice())">Rp {{ number_format($vehicle->daily_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Durasi</span>
                        <span class="font-medium text-navy-700" x-text="durationText()">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Subtotal Sewa</span>
                        <span class="font-medium text-navy-700" x-text="formatRupiah(subtotal())">Rp 0</span>
                    </div>
                    <template x-if="withDriver()">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Biaya Driver</span>
                            <span class="font-medium text-navy-700" x-text="formatRupiah(driverCost())">Rp 0</span>
                        </div>
                    </template>
                    <div class="border-t border-gray-100 pt-3 flex justify-between">
                        <span class="font-bold text-navy-800">Total</span>
                        <span class="font-bold text-sky-600 text-lg" x-text="formatRupiah(totalPrice())">Rp 0</span>
                    </div>
                </div>

                <div class="mt-5 space-y-2">
                    <div class="flex items-center text-[12px] text-gray-500">
                        <i class="fas fa-shield-alt text-sky-400 mr-2"></i> Asuransi kendaraan termasuk
                    </div>
                    <div class="flex items-center text-[12px] text-gray-500">
                        <i class="fas fa-headset text-sky-400 mr-2"></i> Dukungan 24/7
                    </div>
                    <div class="flex items-center text-[12px] text-gray-500">
                        <i class="fas fa-times-circle text-sky-400 mr-2"></i> Gratis pembatalan 24 jam
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewKTP(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('ktp-preview-img').src = e.target.result;
            document.getElementById('ktp-preview').classList.remove('hidden');
            document.getElementById('ktp-empty').classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function cancelKTP() {
    document.getElementById('ktp-input').value = '';
    document.getElementById('ktp-preview').classList.add('hidden');
    document.getElementById('ktp-empty').classList.remove('hidden');
}
</script>

<script>
function bookingSummary() {
    const hourlyPrice = {{ $vehicle->hourly_price ?? 0 }};
    const dailyPrice = {{ $vehicle->daily_price }};
    const weeklyPrice = {{ $vehicle->weekly_price ?? $vehicle->daily_price * 7 }};
    const monthlyPrice = {{ $vehicle->monthly_price ?? $vehicle->daily_price * 30 }};
    const driverDaily = {{ $vehicle->with_driver_daily_price ?? 0 }};

    return {
        get rentalType() {
            const el = document.querySelector('input[name="rental_type"]:checked');
            return el ? el.value : 'daily';
        },
        get startDate() {
            return document.querySelector('input[name="start_date"]')?.value;
        },
        get endDate() {
            return document.querySelector('input[name="end_date"]')?.value;
        },
        get withDriverChecked() {
            const el = document.querySelector('input[name="with_driver"]:checked');
            return el ? el.value === '1' : false;
        },
        rentalTypeLabel() {
            return { hourly: 'Per Jam', daily: 'Per Hari', weekly: 'Per Minggu', monthly: 'Per Bulan' }[this.rentalType] || 'Per Hari';
        },
        unitPrice() {
            return { hourly: hourlyPrice, daily: dailyPrice, weekly: weeklyPrice, monthly: monthlyPrice }[this.rentalType] || dailyPrice;
        },
        durationValue() {
            if (!this.startDate || !this.endDate) return 0;
            const start = new Date(this.startDate);
            const end = new Date(this.endDate);
            const diff = end - start;
            if (diff <= 0) return 0;
            if (this.rentalType === 'hourly') return Math.max(1, Math.ceil(diff / (1000 * 60 * 60)));
            return Math.max(1, Math.ceil(diff / (1000 * 60 * 60 * 24)));
        },
        durationText() {
            const v = this.durationValue();
            if (v === 0) return '-';
            const unit = { hourly: 'jam', daily: 'hari', weekly: 'minggu', monthly: 'bulan' }[this.rentalType] || 'hari';
            return v + ' ' + unit;
        },
        subtotal() {
            return this.unitPrice() * this.durationValue();
        },
        withDriver() {
            return this.withDriverChecked && driverDaily > 0;
        },
        driverCost() {
            return driverDaily * this.durationValue();
        },
        totalPrice() {
            return this.subtotal() + (this.withDriver() ? this.driverCost() : 0);
        },
        formatRupiah(n) {
            return 'Rp ' + n.toLocaleString('id-ID');
        }
    };
}
</script>
@endsection
