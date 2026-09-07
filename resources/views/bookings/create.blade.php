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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="bookingApp()">
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
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="rental_type" value="hourly" x-model="rentalType" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-xl p-3 text-center transition-all hover:border-sky-300">
                                        <i class="fas fa-clock text-gray-400 peer-checked:text-sky-500 text-lg mb-1"></i>
                                        <p class="text-[12px] font-semibold text-navy-700">Per Jam</p>
                                        @if($vehicle->hourly_price)
                                        <p class="text-[11px] text-gray-400">Rp {{ number_format($vehicle->hourly_price, 0, ',', '.') }}</p>
                                        @endif
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="rental_type" value="daily" x-model="rentalType" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-xl p-3 text-center transition-all hover:border-sky-300">
                                        <i class="fas fa-calendar-day text-gray-400 peer-checked:text-sky-500 text-lg mb-1"></i>
                                        <p class="text-[12px] font-semibold text-navy-700">Per Hari</p>
                                        <p class="text-[11px] text-gray-400">Rp {{ number_format($vehicle->daily_price, 0, ',', '.') }}</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="rental_type" value="weekly" x-model="rentalType" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-xl p-3 text-center transition-all hover:border-sky-300">
                                        <i class="fas fa-calendar-week text-gray-400 peer-checked:text-sky-500 text-lg mb-1"></i>
                                        <p class="text-[12px] font-semibold text-navy-700">Per Minggu</p>
                                        <p class="text-[11px] text-gray-400">Rp {{ number_format($vehicle->weekly_price ?? $vehicle->daily_price * 7, 0, ',', '.') }}</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="rental_type" value="monthly" x-model="rentalType" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-xl p-3 text-center transition-all hover:border-sky-300">
                                        <i class="fas fa-calendar-alt text-gray-400 peer-checked:text-sky-500 text-lg mb-1"></i>
                                        <p class="text-[12px] font-semibold text-navy-700">Per Bulan</p>
                                        <p class="text-[11px] text-gray-400">Rp {{ number_format($vehicle->monthly_price ?? $vehicle->daily_price * 30, 0, ',', '.') }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Dates & Duration Form Controls --}}
                        <template x-if="rentalType === 'hourly' || rentalType === 'daily'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Tanggal & Jam Mulai *</label>
                                    <input type="datetime-local" name="start_date" x-model="startDate" required
                                        min="{{ now()->addHours(1)->format('Y-m-d\TH:i') }}"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                                </div>
                                <div>
                                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Tanggal & Jam Selesai *</label>
                                    <input type="datetime-local" name="end_date" x-model="endDate" required
                                        min="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                                </div>
                            </div>
                        </template>

                        <template x-if="rentalType === 'weekly'">
                            <div class="space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Tanggal Mulai Sewa *</label>
                                        <input type="datetime-local" name="start_date" x-model="startDate" required
                                            min="{{ now()->addHours(1)->format('Y-m-d\TH:i') }}"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                                    </div>
                                    <div>
                                        <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Durasi Sewa (Berapa Minggu?) *</label>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="durationWeeks = Math.max(1, durationWeeks - 1)" class="w-10 h-10 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 flex items-center justify-center font-bold text-gray-600 transition">
                                                <i class="fas fa-minus text-xs"></i>
                                            </button>
                                            <input type="number" name="duration_weeks" x-model.number="durationWeeks" min="1" max="52" required
                                                class="flex-1 text-center font-bold text-navy-900 border border-gray-200 rounded-xl py-2.5 text-[14px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                                            <button type="button" @click="durationWeeks++" class="w-10 h-10 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 flex items-center justify-center font-bold text-gray-600 transition">
                                                <i class="fas fa-plus text-xs"></i>
                                            </button>
                                            <span class="text-sm font-semibold text-navy-700">Minggu</span>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="end_date" :value="calculatedEndDate">
                                <div class="bg-sky-50/70 border border-sky-200/80 rounded-xl p-3.5 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-sky-500 text-white flex items-center justify-center flex-shrink-0 text-xs">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="text-[12px]">
                                        <span class="text-gray-500">Estimasi Tanggal & Jam Pengembalian:</span>
                                        <p class="font-bold text-navy-900" x-text="calculatedEndDateFormatted"></p>
                                        <p class="text-[11px] text-sky-600 mt-0.5">Total durasi: <span x-text="durationWeeks"></span> minggu (<span x-text="durationWeeks * 7"></span> hari)</p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="rentalType === 'monthly'">
                            <div class="space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Tanggal Mulai Sewa *</label>
                                        <input type="datetime-local" name="start_date" x-model="startDate" required
                                            min="{{ now()->addHours(1)->format('Y-m-d\TH:i') }}"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                                    </div>
                                    <div>
                                        <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Durasi Sewa (Berapa Bulan?) *</label>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="durationMonths = Math.max(1, durationMonths - 1)" class="w-10 h-10 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 flex items-center justify-center font-bold text-gray-600 transition">
                                                <i class="fas fa-minus text-xs"></i>
                                            </button>
                                            <input type="number" name="duration_months" x-model.number="durationMonths" min="1" max="36" required
                                                class="flex-1 text-center font-bold text-navy-900 border border-gray-200 rounded-xl py-2.5 text-[14px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                                            <button type="button" @click="durationMonths++" class="w-10 h-10 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 flex items-center justify-center font-bold text-gray-600 transition">
                                                <i class="fas fa-plus text-xs"></i>
                                            </button>
                                            <span class="text-sm font-semibold text-navy-700">Bulan</span>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="end_date" :value="calculatedEndDate">
                                <div class="bg-sky-50/70 border border-sky-200/80 rounded-xl p-3.5 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-sky-500 text-white flex items-center justify-center flex-shrink-0 text-xs">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="text-[12px]">
                                        <span class="text-gray-500">Estimasi Tanggal & Jam Pengembalian:</span>
                                        <p class="font-bold text-navy-900" x-text="calculatedEndDateFormatted"></p>
                                        <p class="text-[11px] text-sky-600 mt-0.5">Total durasi: <span x-text="durationMonths"></span> bulan</p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Tipe Penggunaan: Lepas Kunci / Sama Driver --}}
                        @if($vehicle->with_driver)
                        <div>
                            <label class="block text-[12px] font-semibold text-navy-700 mb-2">Tipe Penggunaan *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="with_driver" value="0" x-model="withDriver" class="peer sr-only">
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
                                    <input type="radio" name="with_driver" value="1" x-model="withDriver" class="peer sr-only">
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

                <div class="space-y-2.5 text-[13px]">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Tipe Sewa</span>
                        <span class="font-medium text-navy-700" x-text="rentalTypeLabel">Per Hari</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Harga Satuan</span>
                        <span class="font-medium text-navy-700" x-text="formatRupiah(unitPrice)">Rp {{ number_format($vehicle->daily_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Durasi</span>
                        <span class="font-medium text-navy-700" x-text="durationText">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Subtotal Sewa</span>
                        <span class="font-medium text-navy-700" x-text="formatRupiah(subtotal)">Rp 0</span>
                    </div>
                    <template x-if="withDriverActive">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Biaya Driver</span>
                            <span class="font-medium text-navy-700" x-text="formatRupiah(driverCost)">Rp 0</span>
                        </div>
                    </template>
                    <div class="border-t border-gray-100 pt-3 flex justify-between">
                        <span class="font-bold text-navy-800">Total</span>
                        <span class="font-bold text-sky-600 text-lg" x-text="formatRupiah(totalPrice)">Rp 0</span>
                    </div>
                    <div class="border-t border-gray-100 pt-3 space-y-2.5">
                        <p class="font-bold text-navy-800 text-[12px] uppercase tracking-wider text-gray-500">Metode Pembayaran</p>
                        <label class="flex items-start gap-2.5 cursor-pointer border rounded-xl p-3 transition" :class="paymentPlan === 'full' ? 'border-sky-300 bg-sky-50/60' : 'border-gray-200'">
                            <input type="radio" name="payment_plan" value="full" x-model="paymentPlan" class="mt-0.5 accent-sky-600">
                            <span class="flex-1">
                                <span class="block text-[13px] font-bold text-navy-800">Bayar Penuh</span>
                                <span class="block text-[11px] text-gray-400">Lunasi seluruh biaya di awal penyewaan.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-2.5 cursor-pointer border rounded-xl p-3 transition" :class="paymentPlan === 'dp50' ? 'border-amber-300 bg-amber-50/60' : 'border-gray-200'">
                            <input type="radio" name="payment_plan" value="dp50" x-model="paymentPlan" class="mt-0.5 accent-amber-500">
                            <span class="flex-1">
                                <span class="block text-[13px] font-bold text-navy-800"><i class="fas fa-hand-holding-dollar text-amber-500 mr-1 text-[11px]"></i> DP 50%</span>
                                <span class="block text-[11px] text-gray-400">Bayar 50% di awal, sisanya saat penyewaan selesai.</span>
                            </span>
                        </label>
                        <template x-if="paymentPlan === 'dp50'">
                            <div class="flex justify-between bg-amber-50 border border-amber-100 rounded-xl px-3.5 py-2.5">
                                <span class="text-[12px] font-semibold text-amber-700">DP yang harus dibayar</span>
                                <span class="text-[13px] font-bold text-amber-700" x-text="formatRupiah(dpAmount)">Rp 0</span>
                            </div>
                        </template>
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
function bookingApp() {
    const hourlyPrice = {{ $vehicle->hourly_price ?? 0 }};
    const dailyPrice = {{ $vehicle->daily_price }};
    const weeklyPrice = {{ $vehicle->weekly_price ?? $vehicle->daily_price * 7 }};
    const monthlyPrice = {{ $vehicle->monthly_price ?? $vehicle->daily_price * 30 }};
    const driverDaily = {{ $vehicle->with_driver_daily_price ?? 0 }};

    return {
        rentalType: '{{ old('rental_type', 'daily') }}',
        startDate: '{{ old('start_date') }}',
        endDate: '{{ old('end_date') }}',
        durationWeeks: {{ max(1, (int)old('duration_weeks', 1)) }},
        durationMonths: {{ max(1, (int)old('duration_months', 1)) }},
        withDriver: '{{ old('with_driver', '0') }}',
        paymentPlan: '{{ old('payment_plan', 'full') }}',

        get rentalTypeLabel() {
            return { hourly: 'Per Jam', daily: 'Per Hari', weekly: 'Per Minggu', monthly: 'Per Bulan' }[this.rentalType] || 'Per Hari';
        },
        get unitPrice() {
            return { hourly: hourlyPrice, daily: dailyPrice, weekly: weeklyPrice, monthly: monthlyPrice }[this.rentalType] || dailyPrice;
        },
        get calculatedEndDate() {
            if (!this.startDate) return '';
            const d = new Date(this.startDate);
            if (isNaN(d.getTime())) return '';
            if (this.rentalType === 'weekly') {
                const w = Math.max(1, parseInt(this.durationWeeks || 1));
                d.setDate(d.getDate() + (w * 7));
            } else if (this.rentalType === 'monthly') {
                const m = Math.max(1, parseInt(this.durationMonths || 1));
                d.setMonth(d.getMonth() + m);
            }
            const pad = (n) => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        },
        get calculatedEndDateFormatted() {
            if (!this.startDate) return 'Pilih tanggal mulai terlebih dahulu';
            const d = new Date(this.startDate);
            if (isNaN(d.getTime())) return '-';
            if (this.rentalType === 'weekly') {
                const w = Math.max(1, parseInt(this.durationWeeks || 1));
                d.setDate(d.getDate() + (w * 7));
            } else if (this.rentalType === 'monthly') {
                const m = Math.max(1, parseInt(this.durationMonths || 1));
                d.setMonth(d.getMonth() + m);
            }
            return d.toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' });
        },
        get durationValue() {
            if (this.rentalType === 'weekly') {
                return Math.max(1, parseInt(this.durationWeeks || 1));
            }
            if (this.rentalType === 'monthly') {
                return Math.max(1, parseInt(this.durationMonths || 1));
            }
            if (!this.startDate || !this.endDate) return 0;
            const start = new Date(this.startDate);
            const end = new Date(this.endDate);
            const diff = end - start;
            if (diff <= 0) return 0;
            if (this.rentalType === 'hourly') return Math.max(1, Math.ceil(diff / (1000 * 60 * 60)));
            return Math.max(1, Math.ceil(diff / (1000 * 60 * 60 * 24)));
        },
        get durationText() {
            const v = this.durationValue;
            if (v === 0) return '-';
            if (this.rentalType === 'weekly') {
                return `${v} minggu (${v * 7} hari)`;
            }
            if (this.rentalType === 'monthly') {
                return `${v} bulan`;
            }
            const unit = { hourly: 'jam', daily: 'hari' }[this.rentalType] || 'hari';
            return v + ' ' + unit;
        },
        get subtotal() {
            return this.unitPrice * this.durationValue;
        },
        get withDriverActive() {
            return this.withDriver === '1' && driverDaily > 0;
        },
        get driverDays() {
            if (this.rentalType === 'weekly') {
                return this.durationValue * 7;
            }
            if (this.rentalType === 'monthly') {
                return this.durationValue * 30;
            }
            return this.durationValue;
        },
        get driverCost() {
            return this.withDriverActive ? (driverDaily * this.driverDays) : 0;
        },
        get totalPrice() {
            return this.subtotal + this.driverCost;
        },
        get dpAmount() {
            return this.paymentPlan === 'dp50' ? Math.round(this.totalPrice * 0.5) : this.totalPrice;
        },
        formatRupiah(n) {
            return 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
        }
    };
}
</script>
@endsection
