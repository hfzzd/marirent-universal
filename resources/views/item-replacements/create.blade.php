@extends('layouts.dashboard')
@section('page-title', 'Ajukan Penggantian Unit Elektronik')
@section('content')
<div class="max-w-2xl">
    <a href="{{ route('item-replacements.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ route('item-replacements.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Tipe Unit *</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['hp' => 'HP', 'camera' => 'Kamera', 'tenda' => 'Tenda'] as $val => $label)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="item_type" value="{{ $val }}" class="peer sr-only" {{ $val === $type ? 'checked' : '' }}>
                            <a href="{{ route('item-replacements.create', ['type' => $val]) }}" class="block border-2 {{ $type === $val ? 'border-sky-500 bg-sky-50' : 'border-gray-200' }} rounded-xl p-3 text-center transition-all hover:border-sky-300">
                                <i class="fas {{ $val === 'hp' ? 'fa-mobile-alt text-blue-500' : ($val === 'camera' ? 'fa-camera text-violet-500' : 'fa-campground text-emerald-500') }} text-lg mb-1"></i>
                                <p class="text-xs font-semibold text-navy-700">{{ $label }}</p>
                            </a>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Booking *</label>
                    <select name="booking_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">Pilih Booking</option>
                        @foreach($bookings as $b)
                        <option value="{{ $b->id }}">{{ $b->booking_code }} - {{ $b->item_type ? class_basename($b->item_type) : '-' }} @if($b->status === 'ongoing')(sedang berjalan)@endif</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Unit Saat Ini *</label>
                    <select name="original_item_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">Pilih Unit</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->brand }}) - Rp {{ number_format($item->daily_price,0,',','.') }}/hari</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Unit Pengganti *</label>
                    <select name="replacement_item_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">Pilih Unit Pengganti</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->brand }}) - Rp {{ number_format($item->daily_price,0,',','.') }}/hari</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Alasan Penggantian *</label>
                    <textarea name="reason" rows="3" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500" placeholder="Jelaskan alasan penggantian unit...">{{ old('reason') }}</textarea>
                </div>

                {{-- Foto Bukti --}}
                <div class="border-t border-gray-100 pt-4">
                    <p class="text-sm font-semibold text-navy-800 mb-3"><i class="fas fa-camera text-sky-500 mr-1.5"></i> Bukti Foto Unit</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-navy-700 mb-1.5">Foto Awal Unit *</label>
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-3 text-center hover:border-sky-300 transition-colors">
                                <input type="file" name="initial_item_photo" accept="image/*" id="initial-photo" required class="hidden" onchange="previewPhoto(this, 'initial-preview', 'initial-empty')">
                                <div id="initial-empty">
                                    <i class="fas fa-camera text-gray-300 text-xl mb-1"></i>
                                    <p class="text-[11px] text-gray-400 mb-1.5">Foto kondisi awal unit</p>
                                    <label for="initial-photo" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-3 py-1 rounded-lg text-[11px] font-semibold cursor-pointer transition">
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
                            <label class="block text-xs font-medium text-navy-700 mb-1.5">Foto Akhir Unit</label>
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-3 text-center hover:border-sky-300 transition-colors">
                                <input type="file" name="final_item_photo" accept="image/*" id="final-photo" class="hidden" onchange="previewPhoto(this, 'final-preview', 'final-empty')">
                                <div id="final-empty">
                                    <i class="fas fa-camera text-gray-300 text-xl mb-1"></i>
                                    <p class="text-[11px] text-gray-400 mb-1.5">Foto kondisi akhir unit</p>
                                    <label for="final-photo" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-3 py-1 rounded-lg text-[11px] font-semibold cursor-pointer transition">
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
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">Ajukan</button>
                <a href="{{ route('item-replacements.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
            </div>
        </form>
    </div>
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
