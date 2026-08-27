@extends('layouts.dashboard')
@section('page-title', 'Buat Laporan Perjalanan')
@section('content')
<div class="max-w-3xl">
    <a href="{{ route('reports.index') }}" class="text-sky-600 text-[13px] mb-4 inline-flex items-center hover:text-sky-700 transition font-medium">
        <i class="fas fa-arrow-left mr-1.5"></i> Kembali ke Daftar
    </a>

    {{-- Header --}}
    <div class="bg-gradient-to-r from-sky-500 to-blue-600 rounded-2xl p-6 mb-6 text-white relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full"></div>
        <div class="absolute -left-4 -bottom-4 w-24 h-24 bg-white/5 rounded-full"></div>
        <div class="relative flex items-center gap-3">
            <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <i class="fas fa-route text-lg"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold">Laporan Perjalanan</h2>
                <p class="text-sky-200 text-[12px]">Isi data perjalanan dan foto kondisi kendaraan</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <div class="space-y-5">
            {{-- Booking Selection --}}
            <div class="glass-card rounded-2xl p-6 border border-sky-100/50">
                <h3 class="text-[13px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-calendar-check text-sky-500"></i> Data Perjalanan
                </h3>
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Booking (Perjalanan Aktif) *</label>
                    <select name="booking_id" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300 cursor-pointer">
                        <option value="">Pilih Booking</option>
                        @foreach($bookings as $b)
                        <option value="{{ $b->id }}">{{ $b->booking_code }} - {{ $b->vehicle?->name ?? ($b->category?->name ?? '-') }} ({{ $b->user?->name }})</option>
                        @endforeach
                    </select>
                    @error('booking_id') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Odometer --}}
            <div class="glass-card rounded-2xl p-6 border border-sky-100/50">
                <h3 class="text-[13px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-tachometer-alt text-sky-500"></i> Odometer
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Awal (km)</label>
                        <input type="number" name="start_odometer" value="{{ old('start_odometer') }}" placeholder="0" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] font-bold text-navy-800 text-center focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Akhir (km)</label>
                        <input type="number" name="end_odometer" value="{{ old('end_odometer') }}" placeholder="0" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] font-bold text-navy-800 text-center focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300">
                    </div>
                </div>
            </div>

            {{-- Biaya Operasional --}}
            <div class="glass-card rounded-2xl p-6 border border-sky-100/50">
                <h3 class="text-[13px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-wallet text-sky-500"></i> Biaya Operasional
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        ['fuel_cost', 'BBM', 'fa-gas-pump'],
                        ['toll_cost', 'Tol', 'fa-road'],
                        ['parking_cost', 'Parkir', 'fa-parking'],
                        ['other_cost', 'Lainnya', 'fa-ellipsis-h'],
                    ] as [$field, $label, $icon])
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">
                            <i class="fas {{ $icon }} text-sky-400 mr-1"></i> {{ $label }}
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[12px]">Rp</span>
                            <input type="number" name="{{ $field }}" value="{{ old($field, 0) }}" min="0" class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-3 text-[13px] text-right font-medium text-navy-800 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Foto Kendaraan --}}
            <div class="glass-card rounded-2xl p-6 border border-sky-100/50">
                <h3 class="text-[13px] font-bold text-navy-800 mb-1 flex items-center gap-2">
                    <i class="fas fa-camera text-violet-500"></i> Foto Kondisi Kendaraan
                </h3>
                <p class="text-[11px] text-gray-400 mb-4">Dokumentasikan kondisi kendaraan dari 4 sisi</p>

                <div class="grid grid-cols-2 gap-4">
                    @foreach([
                        ['photo_front', 'Depan', 'fa-arrow-up', 'photo-front'],
                        ['photo_rear', 'Belakang', 'fa-arrow-down', 'photo-rear'],
                        ['photo_right', 'Kanan', 'fa-arrow-right', 'photo-right'],
                        ['photo_left', 'Kiri', 'fa-arrow-left', 'photo-left'],
                    ] as [$field, $label, $icon, $id])
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">
                            <i class="fas {{ $icon }} text-gray-400 mr-1"></i> {{ $label }}
                        </label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-sky-300 transition bg-gray-50/50">
                            <input type="file" name="{{ $field }}" accept="image/*" id="{{ $id }}" class="hidden" onchange="previewPhoto(this, '{{ $id }}-preview', '{{ $id }}-empty')">
                            <div id="{{ $id }}-empty">
                                <i class="fas fa-camera text-gray-300 text-xl mb-1"></i>
                                <p class="text-[10px] text-gray-400 mb-1.5">Foto sisi {{ strtolower($label) }}</p>
                                <label for="{{ $id }}" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-3 py-1 rounded-lg text-[11px] font-semibold cursor-pointer transition inline-block">
                                    <i class="fas fa-upload text-[9px]"></i> Pilih
                                </label>
                            </div>
                            <div id="{{ $id }}-preview" class="hidden">
                                <img id="{{ $id }}-preview-img" class="max-h-20 mx-auto rounded-lg border border-gray-200 shadow-sm mb-1">
                                <button type="button" onclick="removePhoto('{{ $id }}', '{{ $id }}-preview', '{{ $id }}-empty')" class="text-red-500 text-[10px] font-medium hover:underline">Hapus</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Catatan --}}
            <div class="glass-card rounded-2xl p-6 border border-sky-100/50">
                <h3 class="text-[13px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-sticky-note text-amber-500"></i> Catatan
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Catatan Perjalanan</label>
                        <textarea name="notes" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300 resize-none" placeholder="Catatan perjalanan...">{{ old('notes') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Masalah yang Ditemukan</label>
                        <textarea name="issues_reported" rows="2" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300 resize-none" placeholder="Jika ada masalah, jelaskan di sini...">{{ old('issues_reported') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" :disabled="loading" class="bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white px-8 py-3 rounded-xl text-[13px] font-bold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-paper-plane" x-show="!loading"></i>
                <i class="fas fa-spinner fa-spin" x-show="loading" x-cloak></i>
                <span x-text="loading ? 'Menyimpan...' : 'Simpan Laporan'"></span>
            </button>
            <a href="{{ route('reports.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-3 rounded-xl text-[13px] font-medium text-navy-700 transition">Batal</a>
        </div>
    </form>
</div>

<script>
function previewPhoto(input, previewId, emptyId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId + '-img').src = e.target.result;
            document.getElementById(previewId).classList.remove('hidden');
            document.getElementById(emptyId).classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function removePhoto(inputId, previewId, emptyId) {
    document.getElementById(inputId).value = '';
    document.getElementById(previewId).classList.add('hidden');
    document.getElementById(emptyId).classList.remove('hidden');
}
</script>
@endsection
