@extends('layouts.dashboard')
@section('page-title', 'Ajukan Penggantian Kendaraan')
@section('content')
<div class="max-w-2xl">
    <a href="{{ route('replacements.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ route('replacements.store') }}" enctype="multipart/form-data" x-data="replacementForm()">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Booking *</label>
                    <select name="booking_id" x-model="selectedBookingId" @change="updateHandoverType()" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">Pilih Booking</option>
                        @foreach($bookings as $b)
                        <option value="{{ $b->id }}" data-with-driver="{{ $b->with_driver ? '1' : '0' }}">{{ $b->booking_code }} - {{ $b->vehicle->name ?? ($b->category->name ?? '-') }} @if($b->status === 'ongoing')(sedang berjalan)@endif</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Kendaraan Pengganti *</label>
                    <select name="replacement_vehicle_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">Pilih Kendaraan</option>
                        @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->category->name }}) - Rp {{ number_format($v->daily_price,0,',','.') }}/hari</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Alasan Penggantian *</label>
                    <textarea name="reason" rows="3" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500" placeholder="Jelaskan alasan penggantian kendaraan...">{{ old('reason') }}</textarea>
                </div>

                {{-- Tipe Serah Terima - HIDDEN for drivers, shown for users --}}
                @if(auth()->user()->role === 'user')
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-2">Tipe Serah Terima</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="handover_type" value="lepas_kunci" x-model="handoverType" class="peer sr-only" :disabled="!canChooseType">
                            <div class="border-2 border-gray-200 rounded-xl p-4 text-center transition-all peer-checked:border-sky-500 peer-checked:bg-sky-50 hover:border-gray-300" :class="!canChooseType ? 'opacity-50 cursor-not-allowed' : ''">
                                <div class="w-10 h-10 mx-auto mb-2 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center peer-checked:bg-sky-100 peer-checked:text-sky-600">
                                    <i class="fas fa-key text-lg"></i>
                                </div>
                                <div class="font-semibold text-sm text-navy-800">Lepas Kunci</div>
                                <div class="text-xs text-navy-500 mt-1">Tanpa supir</div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="handover_type" value="with_driver" x-model="handoverType" class="peer sr-only" :disabled="!canChooseType">
                            <div class="border-2 border-gray-200 rounded-xl p-4 text-center transition-all peer-checked:border-sky-500 peer-checked:bg-sky-50 hover:border-gray-300" :class="!canChooseType ? 'opacity-50 cursor-not-allowed' : ''">
                                <div class="w-10 h-10 mx-auto mb-2 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-user-tie text-lg"></i>
                                </div>
                                <div class="font-semibold text-sm text-navy-800">Dengan Supir</div>
                                <div class="text-xs text-navy-500 mt-1">Disertai supir</div>
                            </div>
                        </label>
                    </div>
                    <p x-show="!canChooseType" class="text-xs text-gray-400 mt-2 italic">Tipe serah terima mengikuti booking asal</p>
                    @error('handover_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @else
                <input type="hidden" name="handover_type" value="lepas_kunci">
                @endif

                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Catatan Serah Terima</label>
                    <textarea name="handover_notes" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500" placeholder="Kondisi kendaraan, hal khusus, dll...">{{ old('handover_notes') }}</textarea>
                </div>

                {{-- Foto Bukti --}}
                <div class="border-t border-gray-100 pt-4">
                    <p class="text-sm font-semibold text-navy-800 mb-3"><i class="fas fa-camera text-sky-500 mr-1.5"></i> Bukti Foto Kendaraan</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-navy-700 mb-1.5">Foto Awal Kendaraan *</label>
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-3 text-center hover:border-sky-300 transition-colors">
                                <input type="file" name="initial_vehicle_photo" accept="image/*" id="initial-photo" required class="hidden" onchange="previewPhoto(this, 'initial-preview', 'initial-empty')">
                                <div id="initial-empty">
                                    <i class="fas fa-camera text-gray-300 text-xl mb-1"></i>
                                    <p class="text-[11px] text-gray-400 mb-1.5">Foto kondisi awal</p>
                                    <label for="initial-photo" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-3 py-1 rounded-lg text-[11px] font-semibold cursor-pointer transition">
                                        <i class="fas fa-upload text-[9px]"></i> Pilih Foto
                                    </label>
                                </div>
                                <div id="initial-preview" class="hidden">
                                    <img id="initial-preview-img" class="max-h-28 mx-auto rounded-lg border border-gray-200 shadow-sm mb-1">
                                    <button type="button" onclick="removePhoto('initial-photo', 'initial-preview', 'initial-empty')" class="text-red-500 text-[10px] font-medium hover:underline">Hapus</button>
                                </div>
                            </div>
                            @error('initial_vehicle_photo') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-navy-700 mb-1.5">Foto Akhir Kendaraan</label>
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-3 text-center hover:border-sky-300 transition-colors">
                                <input type="file" name="final_vehicle_photo" accept="image/*" id="final-photo" class="hidden" onchange="previewPhoto(this, 'final-preview', 'final-empty')">
                                <div id="final-empty">
                                    <i class="fas fa-camera text-gray-300 text-xl mb-1"></i>
                                    <p class="text-[11px] text-gray-400 mb-1.5">Foto kondisi akhir</p>
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
                <a href="{{ route('replacements.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function replacementForm() {
    return {
        selectedBookingId: '',
        handoverType: 'lepas_kunci',
        canChooseType: true,
        updateHandoverType() {
            const select = document.querySelector('select[name="booking_id"]');
            const selected = select.options[select.selectedIndex];
            const withDriver = selected.getAttribute('data-with-driver');
            if (withDriver === '1') {
                this.handoverType = 'with_driver';
                this.canChooseType = false;
            } else {
                this.handoverType = 'lepas_kunci';
                this.canChooseType = true;
            }
        }
    }
}
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
