@extends('layouts.public')
@section('title', 'Booking ' . $item->name . ' - MariRent')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <a href="{{ route('public.item', [$type, $item->slug]) }}" class="text-sky-600 hover:text-sky-700 text-sm mb-6 inline-flex items-center transition">
        <i class="fas fa-arrow-left mr-1.5"></i> Kembali ke {{ $item->name }}
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
                        <i class="fas {{ $config['icon'] }} text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-navy-900">Form Booking — {{ $config['label'] }}</h1>
                        <p class="text-[12px] text-gray-400">Ambil di kantor kami &bull; wajib melampirkan KTP untuk verifikasi</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('bookings.store-item', $type) }}" enctype="multipart/form-data" id="booking-form">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $item->id }}">

                    <div class="space-y-5">
                        {{-- Rental Type --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-navy-700 mb-2">Tipe Sewa *</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2" x-data="{ type: '{{ old('rental_type', 'daily') }}' }">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="rental_type" value="hourly" x-model="type" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-xl p-3 text-center transition-all hover:border-sky-300">
                                        <i class="fas fa-clock text-gray-400 text-lg mb-1"></i>
                                        <p class="text-[12px] font-semibold text-navy-700">Per Jam</p>
                                        @if($item->hourly_price)
                                        <p class="text-[11px] text-gray-400">Rp {{ number_format($item->hourly_price, 0, ',', '.') }}</p>
                                        @endif
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="rental_type" value="daily" x-model="type" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-xl p-3 text-center transition-all hover:border-sky-300">
                                        <i class="fas fa-calendar-day text-gray-400 text-lg mb-1"></i>
                                        <p class="text-[12px] font-semibold text-navy-700">Per Hari</p>
                                        <p class="text-[11px] text-gray-400">Rp {{ number_format($item->daily_price, 0, ',', '.') }}</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="rental_type" value="weekly" x-model="type" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-xl p-3 text-center transition-all hover:border-sky-300">
                                        <i class="fas fa-calendar-week text-gray-400 text-lg mb-1"></i>
                                        <p class="text-[12px] font-semibold text-navy-700">Per Minggu</p>
                                        <p class="text-[11px] text-gray-400">Rp {{ number_format($item->weekly_price ?? $item->daily_price * 7, 0, ',', '.') }}</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="rental_type" value="monthly" x-model="type" class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-xl p-3 text-center transition-all hover:border-sky-300">
                                        <i class="fas fa-calendar-alt text-gray-400 text-lg mb-1"></i>
                                        <p class="text-[12px] font-semibold text-navy-700">Per Bulan</p>
                                        <p class="text-[11px] text-gray-400">Rp {{ number_format($item->monthly_price ?? $item->daily_price * 30, 0, ',', '.') }}</p>
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

                        {{-- Info pengambilan --}}
                        <div class="bg-emerald-50/60 border border-emerald-100 rounded-xl p-4">
                            <p class="text-[12px] font-semibold text-emerald-700 mb-1"><i class="fas fa-store mr-1"></i> Pengambilan & Pengembalian</p>
                            <ul class="text-[11.5px] text-gray-500 space-y-0.5 list-disc list-inside">
                                <li>Unit diambil & dikembalikan di kantor MariRent</li>
                                <li>Kondisi unit diperiksa bersama saat serah terima (foto kelengkapan dicatat)</li>
                            </ul>
                        </div>

                        {{-- KTP --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Foto KTP <span class="text-red-500">*</span></label>
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-sky-300 transition-colors">
                                <input type="file" name="ktp_photo" accept="image/*" id="ktp-input" required class="hidden" onchange="previewKTP(this)">
                                <div id="ktp-empty">
                                    <i class="fas fa-id-card text-gray-300 text-2xl mb-2"></i>
                                    <p class="text-[12px] text-gray-400 mb-2">Wajib: unggah foto KTP Anda untuk verifikasi</p>
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

                        {{-- Metode Pembayaran --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Pembayaran Awal <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-start gap-3 border-2 rounded-xl p-3.5 cursor-pointer transition border-gray-200 hover:border-sky-300 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/60">
                                    <input type="radio" name="payment_plan" value="full" {{ old('payment_plan', 'full') === 'full' ? 'checked' : '' }} @change="$dispatch('plan-changed')" class="mt-0.5 accent-sky-600">
                                    <span>
                                        <span class="block text-[13px] font-bold text-navy-800"><i class="fas fa-money-bill-wave text-emerald-500 mr-1 text-[11px]"></i> Bayar Penuh</span>
                                        <span class="block text-[11px] text-gray-400 mt-0.5">Lunasi 100% sekarang</span>
                                    </span>
                                </label>
                                <label class="flex items-start gap-3 border-2 rounded-xl p-3.5 cursor-pointer transition border-gray-200 hover:border-sky-300 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/60">
                                    <input type="radio" name="payment_plan" value="dp50" {{ old('payment_plan') === 'dp50' ? 'checked' : '' }} @change="$dispatch('plan-changed')" class="mt-0.5 accent-sky-600">
                                    <span>
                                        <span class="block text-[13px] font-bold text-navy-800"><i class="fas fa-hand-holding-dollar text-amber-500 mr-1 text-[11px]"></i> DP 50%</span>
                                        <span class="block text-[11px] text-gray-400 mt-0.5">Sisa dibayar saat pengambilan unit</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col sm:flex-row gap-3">
                        <button type="submit" class="btn-primary text-white px-8 py-3 rounded-xl font-semibold text-[14px] shadow-lg shadow-sky-500/25 transition flex items-center justify-center gap-2 flex-1">
                            <i class="fas fa-check-circle"></i> Konfirmasi Booking
                        </button>
                        <a href="{{ route('public.item', [$type, $item->slug]) }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-3 rounded-xl text-[13px] font-medium text-navy-700 transition text-center">
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
                    <div class="w-14 h-14 bg-sky-50 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas {{ $config['icon'] }} text-sky-400"></i>
                        @endif
                    </div>
                    <div>
                        <p class="font-bold text-navy-800 text-[14px]">{{ $item->name }}</p>
                        <p class="text-[11px] text-gray-400">{{ $item->brand }} {{ ($config['subtitle'])($item) }}</p>
                        <p class="text-[11px] text-sky-500 font-medium">{{ $config['label'] }}</p>
                    </div>
                </div>

                <div class="space-y-2.5 text-[13px]" x-data="bookingSummary()" @input.window="tick()" @change.window="tick()" @plan-changed.window="tick()">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Tipe Sewa</span>
                        <span class="font-medium text-navy-700" x-text="rentalTypeLabel()">Per Hari</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Harga Satuan</span>
                        <span class="font-medium text-navy-700" x-text="formatRupiah(unitPrice())">Rp {{ number_format($item->daily_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Durasi</span>
                        <span class="font-medium text-navy-700" x-text="durationText()">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Subtotal Sewa</span>
                        <span class="font-medium text-navy-700" x-text="formatRupiah(subtotal())">Rp 0</span>
                    </div>
                    <div class="border-t border-gray-100 pt-3 flex justify-between">
                        <span class="font-bold text-navy-800">Total</span>
                        <span class="font-bold text-sky-600 text-lg" x-text="formatRupiah(totalPrice())">Rp 0</span>
                    </div>
                    <div class="flex justify-between bg-emerald-50/60 -mx-2 px-2 py-1.5 rounded-lg">
                        <span class="font-semibold text-emerald-700">Bayar Sekarang</span>
                        <span class="font-bold text-emerald-600" x-text="formatRupiah(dueNow())">Rp 0</span>
                    </div>
                    <div class="flex justify-between" x-show="paymentPlan() === 'dp50'">
                        <span class="text-gray-400">Sisa Pelunasan</span>
                        <span class="font-medium text-navy-700" x-text="formatRupiah(remainingAfter())">Rp 0</span>
                    </div>
                </div>

                <div class="mt-5 space-y-2">
                    <div class="flex items-center text-[12px] text-gray-500">
                        <i class="fas fa-shield-alt text-sky-400 mr-2"></i> Unit diperiksa sebelum & sesudah sewa
                    </div>
                    <div class="flex items-center text-[12px] text-gray-500">
                        <i class="fas fa-box-open text-sky-400 mr-2"></i> Kelengkapan tercatat saat serah terima
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
    const hourlyPrice = {{ $item->hourly_price ?? 0 }};
    const dailyPrice = {{ $item->daily_price }};
    const weeklyPrice = {{ $item->weekly_price ?? $item->daily_price * 7 }};
    const monthlyPrice = {{ $item->monthly_price ?? $item->daily_price * 30 }};

    return {
        tickValue: 0,
        tick() { this.tickValue++; },
        get rentalType() {
            void this.tickValue;
            const el = document.querySelector('input[name="rental_type"]:checked');
            return el ? el.value : 'daily';
        },
        get startDate() {
            void this.tickValue;
            return document.querySelector('input[name="start_date"]')?.value;
        },
        get endDate() {
            void this.tickValue;
            return document.querySelector('input[name="end_date"]')?.value;
        },
        paymentPlan() {
            void this.tickValue;
            const el = document.querySelector('input[name="payment_plan"]:checked');
            return el ? el.value : 'full';
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
        totalPrice() {
            return this.subtotal();
        },
        dueNow() {
            return this.paymentPlan() === 'dp50' ? Math.round(this.totalPrice() * 0.5) : this.totalPrice();
        },
        remainingAfter() {
            return this.totalPrice() - this.dueNow();
        },
        formatRupiah(n) {
            return 'Rp ' + n.toLocaleString('id-ID');
        }
    };
}
</script>
@endsection
