@extends('layouts.dashboard')
@section('page-title', 'Ajukan Penggantian Unit Elektronik')
@section('content')
<div class="max-w-2xl">
    <a href="{{ route('item-replacements.index') }}" class="text-sky-600 text-[13px] mb-4 inline-flex items-center hover:text-sky-700 transition font-medium">
        <i class="fas fa-arrow-left mr-1.5"></i> Kembali ke Daftar
    </a>

    {{-- HEADER --}}
    <div class="bg-gradient-to-r from-sky-500 to-blue-600 rounded-2xl p-6 mb-6 text-white relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full"></div>
        <div class="absolute -left-4 -bottom-4 w-24 h-24 bg-white/5 rounded-full"></div>
        <div class="relative flex items-center gap-3">
            <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <i class="fas fa-swap-horizontal text-lg"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold">Ajukan Penggantian Unit Elektronik</h2>
                <p class="text-sky-200 text-[12px]">Isi form di bawah untuk mengajukan penggantian unit</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('item-replacements.store') }}" enctype="multipart/form-data" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <div class="glass-card rounded-2xl p-6 mb-5 border border-sky-100/50">
            <h3 class="text-[13px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                <i class="fas fa-box text-sky-500"></i> Tipe Unit
            </h3>
            <div class="grid grid-cols-3 gap-2">
                @foreach(['hp' => ['HP', 'fa-mobile-alt text-blue-500', 'text-blue-500'], 'camera' => ['Kamera', 'fa-camera text-violet-500', 'text-violet-500'], 'tenda' => ['Alat Camping', 'fa-campground text-emerald-500', 'text-emerald-500'], 'ps' => ['Playstation', 'fa-gamepad text-indigo-500', 'text-indigo-500'], 'drone' => ['Drone', 'fa-drone text-cyan-500', 'text-cyan-500'], 'musik' => ['Musik', 'fa-guitar text-rose-500', 'text-rose-500']] as $val => [$label, $icon, $iconColor])
                <a href="{{ route('item-replacements.create', ['type' => $val]) }}" class="block border-2 {{ $type === $val ? 'border-sky-500 bg-sky-50 shadow-sm' : 'border-gray-200' }} rounded-xl p-3 text-center transition-all hover:border-sky-300">
                    <i class="fas {{ $icon }} text-lg mb-1"></i>
                    <p class="text-xs font-semibold text-navy-700">{{ $label }}</p>
                </a>
                @endforeach
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6 mb-5 border border-sky-100/50">
            <h3 class="text-[13px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                <i class="fas fa-calendar-check text-amber-500"></i> Pilih Booking
            </h3>
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Booking *</label>
                <select name="booking_id" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300 cursor-pointer">
                    <option value="">Pilih Booking</option>
                    @foreach($bookings as $b)
                    <option value="{{ $b->id }}">{{ $b->booking_code }} - {{ $b->item_type ? class_basename($b->item_type) : '-' }} @if($b->status === 'ongoing')(sedang berjalan)@endif</option>
                    @endforeach
                </select>
                @error('booking_id') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6 mb-5 border border-sky-100/50">
            <h3 class="text-[13px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                <i class="fas fa-right-left text-emerald-500"></i> Pilih Unit
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Unit Saat Ini *</label>
                    <select name="original_item_id" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300 cursor-pointer">
                        <option value="">Pilih Unit</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->brand }}) - Rp {{ number_format($item->daily_price,0,',','.') }}/hari</option>
                        @endforeach
                    </select>
                    @error('original_item_id') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Unit Pengganti *</label>
                    <select name="replacement_item_id" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300 cursor-pointer">
                        <option value="">Pilih Unit Pengganti</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->brand }}) - Rp {{ number_format($item->daily_price,0,',','.') }}/hari</option>
                        @endforeach
                    </select>
                    @error('replacement_item_id') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6 mb-5 border border-sky-100/50">
            <h3 class="text-[13px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                <i class="fas fa-comment-dots text-amber-500"></i> Alasan Penggantian
            </h3>
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Alasan *</label>
                <textarea name="reason" rows="3" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300 resize-none" placeholder="Jelaskan alasan penggantian unit...">{{ old('reason') }}</textarea>
                @error('reason') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Foto Bukti --}}
        <div class="glass-card rounded-2xl p-6 mb-6 border border-sky-100/50">
            <h3 class="text-[13px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                <i class="fas fa-camera text-violet-500"></i> Bukti Foto Unit
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Foto Awal Unit *</label>
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-sky-300 transition bg-gray-50/50">
                        <input type="file" name="initial_item_photo" accept="image/*" id="initial-photo" required class="hidden" onchange="previewPhoto(this, 'initial-preview', 'initial-empty')">
                        <div id="initial-empty">
                            <i class="fas fa-camera text-gray-300 text-xl mb-1"></i>
                            <p class="text-[11px] text-gray-400 mb-1.5">Foto kondisi awal unit</p>
                            <label for="initial-photo" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-3 py-1.5 rounded-lg text-[11px] font-semibold cursor-pointer transition inline-block">
                                <i class="fas fa-upload text-[9px]"></i> Pilih Foto
                            </label>
                        </div>
                        <div id="initial-preview" class="hidden">
                            <img id="initial-preview-img" class="max-h-28 mx-auto rounded-lg border border-gray-200 shadow-sm mb-1">
                            <button type="button" onclick="removePhoto('initial-photo', 'initial-preview', 'initial-empty')" class="text-red-500 text-[10px] font-medium hover:underline">Hapus</button>
                        </div>
                    </div>
                    @error('initial_item_photo') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Foto Akhir Unit</label>
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-sky-300 transition bg-gray-50/50">
                        <input type="file" name="final_item_photo" accept="image/*" id="final-photo" class="hidden" onchange="previewPhoto(this, 'final-preview', 'final-empty')">
                        <div id="final-empty">
                            <i class="fas fa-camera text-gray-300 text-xl mb-1"></i>
                            <p class="text-[11px] text-gray-400 mb-1.5">Foto kondisi akhir unit</p>
                            <label for="final-photo" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-3 py-1.5 rounded-lg text-[11px] font-semibold cursor-pointer transition inline-block">
                                <i class="fas fa-upload text-[9px]"></i> Pilih Foto
                            </label>
                        </div>
                        <div id="final-preview" class="hidden">
                            <img id="final-preview-img" class="max-h-28 mx-auto rounded-lg border border-gray-200 shadow-sm mb-1">
                            <button type="button" onclick="removePhoto('final-photo', 'final-preview', 'final-empty')" class="text-red-500 text-[10px] font-medium hover:underline">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" :disabled="loading" class="bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white px-8 py-3 rounded-xl text-[13px] font-bold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-paper-plane" x-show="!loading"></i>
                <i class="fas fa-spinner fa-spin" x-show="loading" x-cloak></i>
                <span x-text="loading ? 'Mengirim...' : 'Ajukan Penggantian'"></span>
            </button>
            <a href="{{ route('item-replacements.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-3 rounded-xl text-[13px] font-medium text-navy-700 transition">Batal</a>
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
