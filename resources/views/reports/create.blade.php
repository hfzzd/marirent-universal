@extends('layouts.dashboard')
@section('page-title', 'Buat Laporan Perjalanan')
@section('content')
<div class="max-w-3xl">
    <a href="{{ route('reports.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Header --}}
        <div class="bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-2xl p-6 mb-6 relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
            <h2 class="text-xl font-bold relative z-10"><i class="fas fa-route mr-2"></i>Laporan Perjalanan</h2>
            <p class="text-sky-100 text-sm mt-1 relative z-10">Isi data perjalanan dan foto kondisi kendaraan</p>
        </div>

        <div class="space-y-6">
            {{-- Booking Selection --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center"><i class="fas fa-calendar-check text-sky-500 text-sm"></i></div>
                    Data Perjalanan
                </h3>
                <div>
                    <label class="block text-xs font-medium text-navy-600 mb-1.5">Booking (Perjalanan Aktif) *</label>
                    <select name="booking_id" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-400 transition bg-gray-50/50">
                        <option value="">Pilih Booking</option>
                        @foreach($bookings as $b)
                        <option value="{{ $b->id }}">{{ $b->booking_code }} - {{ $b->vehicle?->name ?? ($b->category?->name ?? '-') }} ({{ $b->user?->name }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Odometer --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center"><i class="fas fa-tachometer-alt text-sky-500 text-sm"></i></div>
                    Odometer
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="border border-gray-100 rounded-xl p-3">
                        <label class="block text-xs font-medium text-navy-600 mb-1.5">Awal (km)</label>
                        <input type="number" name="start_odometer" value="{{ old('start_odometer') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 transition text-center font-bold text-navy-800" placeholder="0">
                    </div>
                    <div class="border border-gray-100 rounded-xl p-3">
                        <label class="block text-xs font-medium text-navy-600 mb-1.5">Akhir (km)</label>
                        <input type="number" name="end_odometer" value="{{ old('end_odometer') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 transition text-center font-bold text-navy-800" placeholder="0">
                    </div>
                </div>
            </div>

            {{-- Biaya Operasional --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center"><i class="fas fa-wallet text-sky-500 text-sm"></i></div>
                    Biaya Operasional
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        ['fuel_cost', 'BBM', 'fa-gas-pump'],
                        ['toll_cost', 'Tol', 'fa-road'],
                        ['parking_cost', 'Parkir', 'fa-parking'],
                        ['other_cost', 'Lainnya', 'fa-ellipsis-h'],
                    ] as [$field, $label, $icon])
                    <div class="border border-gray-100 rounded-xl p-3 hover:border-sky-200 transition">
                        <label class="text-xs font-semibold text-navy-700 flex items-center gap-1.5 mb-2">
                            <i class="fas {{ $icon }} text-gray-400"></i> {{ $label }}
                        </label>
                        <div class="relative">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                            <input type="number" name="{{ $field }}" value="{{ old($field, 0) }}" min="0" class="w-full border border-gray-200 rounded-lg pl-9 pr-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 transition text-right font-medium text-navy-800">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Foto Kendaraan --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-navy-800 mb-2 flex items-center gap-2">
                    <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center"><i class="fas fa-camera text-sky-500 text-sm"></i></div>
                    Foto Kondisi Kendaraan
                </h3>
                <p class="text-xs text-gray-400 mb-4">Dokumentasikan kondisi kendaraan dari 4 sisi</p>

                <div class="grid grid-cols-2 gap-4">
                    @foreach([
                        ['photo_front', 'Depan', 'fa-arrow-up', 'photo-front'],
                        ['photo_rear', 'Belakang', 'fa-arrow-down', 'photo-rear'],
                        ['photo_right', 'Kanan', 'fa-arrow-right', 'photo-right'],
                        ['photo_left', 'Kiri', 'fa-arrow-left', 'photo-left'],
                    ] as [$field, $label, $icon, $id])
                    <div>
                        <label class="block text-xs font-semibold text-navy-700 mb-1.5">
                            <i class="fas {{ $icon }} text-gray-400 mr-1"></i> {{ $label }}
                        </label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-3 text-center hover:border-sky-300 transition-colors">
                            <input type="file" name="{{ $field }}" accept="image/*" id="{{ $id }}" class="hidden" onchange="previewPhoto(this, '{{ $id }}-preview', '{{ $id }}-empty')">
                            <div id="{{ $id }}-empty">
                                <i class="fas fa-camera text-gray-300 text-xl mb-1"></i>
                                <p class="text-[10px] text-gray-400 mb-1">Foto sisi {{ strtolower($label) }}</p>
                                <label for="{{ $id }}" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-2.5 py-1 rounded-lg text-[10px] font-semibold cursor-pointer transition inline-block">
                                    <i class="fas fa-upload text-[8px]"></i> Pilih
                                </label>
                            </div>
                            <div id="{{ $id }}-preview" class="hidden">
                                <img id="{{ $id }}-preview-img" class="max-h-20 mx-auto rounded-lg border border-gray-200 shadow-sm mb-1">
                                <button type="button" onclick="removePhoto('{{ $id }}', '{{ $id }}-preview', '{{ $id }}-empty')" class="text-red-500 text-[9px] font-medium hover:underline">Hapus</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Catatan --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center"><i class="fas fa-sticky-note text-sky-500 text-sm"></i></div>
                    Catatan
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-navy-600 mb-1.5">Catatan Perjalanan</label>
                        <textarea name="notes" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-400 transition bg-gray-50/50" placeholder="Catatan perjalanan...">{{ old('notes') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-navy-600 mb-1.5">Masalah yang Ditemukan</label>
                        <textarea name="issues_reported" rows="2" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-400 transition bg-gray-50/50" placeholder="Jika ada masalah, jelaskan di sini...">{{ old('issues_reported') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold"><i class="fas fa-save mr-2"></i>Simpan Laporan</button>
            <a href="{{ route('reports.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
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
