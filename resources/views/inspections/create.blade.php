@extends('layouts.dashboard')
@section('page-title', 'Buat Inspeksi')

@section('content')
<div class="max-w-4xl">
    <a href="{{ route('inspections.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>

    <form method="POST" action="{{ route('inspections.store') }}" enctype="multipart/form-data" x-data="{ scope: '{{ $scope }}', type: '{{ old('type', 'pre_rental') }}' }">
        @csrf

        {{-- Pilih Booking & Jenis --}}
        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-clipboard-list text-sky-500"></i> Data Inspeksi</h3>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Pilih Booking *</label>
                    <select name="booking_id" id="booking-select" required
                            @change="scope = $event.target.selectedOptions[0].dataset.scope || 'kendaraan'"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <option value="" data-scope="kendaraan">-- Pilih booking aktif --</option>
                        @foreach($bookings as $b)
                        @php
                            $bScope = in_array($b->category->slug ?? '', ['sewa-kamera','sewa-hp']) ? 'elektronik' : (($b->category->slug ?? '') === 'sewa-tenda' ? 'camping' : 'kendaraan');
                            $itemName = $b->vehicle?->name ?? ($b->item?->name ?? ($b->category->name ?? 'Unit Sewa'));
                        @endphp
                        <option value="{{ $b->id }}" data-scope="{{ $bScope }}" {{ ($booking?->id ?? old('booking_id')) == $b->id ? 'selected' : '' }}>
                            {{ $b->booking_code }} - {{ $itemName }} | {{ $b->user?->name ?? '-' }} ({{ \Carbon\Carbon::parse($b->start_date)->format('d/m/y') }})
                        </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1"><i class="fas fa-circle-info mr-1"></i> Kategori inspeksi otomatis mengikuti jenis barang pada booking.</p>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-2">Jenis Inspeksi *</label>
                    <input type="hidden" name="type" :value="type">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button type="button" @click="type = 'pre_rental'" :class="type === 'pre_rental' ? 'border-sky-400 bg-sky-50 ring-2 ring-sky-100' : 'border-gray-200 hover:border-sky-300'" class="text-left p-4 rounded-xl border transition">
                            <p class="font-bold text-navy-800 text-[13px]" :class="type === 'pre_rental' && 'text-sky-600'"><i class="fas fa-circle-play mr-1.5" :class="type === 'pre_rental' ? 'text-sky-500' : 'text-gray-300'"></i> Inspeksi Awal</p>
                            <p class="text-[11px] text-gray-400 mt-1">Dilakukan saat serah terima unit ke penyewa.</p>
                        </button>
                        <button type="button" @click="type = 'post_rental'" :class="type === 'post_rental' ? 'border-amber-400 bg-amber-50 ring-2 ring-amber-100' : 'border-gray-200 hover:border-amber-300'" class="text-left p-4 rounded-xl border transition">
                            <p class="font-bold text-navy-800 text-[13px]" :class="type === 'post_rental' && 'text-amber-600'"><i class="fas fa-flag-checkered mr-1.5" :class="type === 'post_rental' ? 'text-amber-500' : 'text-gray-300'"></i> Inspeksi Akhir</p>
                            <p class="text-[11px] text-gray-400 mt-1">Dilakukan saat penerimaan kembali dari penyewa.</p>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kondisi Kendaraan (hanya scope kendaraan) --}}
        <div x-show="scope === 'kendaraan'" x-cloak class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-car text-blue-500"></i> Kondisi Kendaraan (skala 1-10)</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                @foreach(['exterior_condition' => 'Eksterior', 'interior_condition' => 'Interior', 'engine_condition' => 'Mesin', 'tire_condition' => 'Ban', 'brake_condition' => 'Rem', 'electrical_condition' => 'Kelistrikan'] as $key => $label)
                <div>
                    <label class="block text-xs text-navy-500 mb-1">{{ $label }}</label>
                    <input type="number" name="{{ $key }}" value="{{ old($key, 8) }}" min="1" max="10" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                @endforeach
                <div>
                    <label class="block text-xs text-navy-500 mb-1">Keseluruhan *</label>
                    <input type="number" name="overall_condition" value="{{ old('overall_condition', 8) }}" min="1" max="10" required class="w-full border border-sky-200 bg-sky-50/40 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Level Bahan Bakar (%)</label>
                    <input type="number" name="fuel_level" value="{{ old('fuel_level', 100) }}" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Odometer (km)</label>
                    <input type="number" name="odometer_reading" value="{{ old('odometer_reading') }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
            </div>
        </div>

        {{-- Kelengkapan Barang (elektronik / camping) --}}
        <div x-show="scope !== 'kendaraan'" x-cloak class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-1 flex items-center gap-2">
                <span x-text="scope === 'camping' ? 'Kelengkapan Alat Camping' : 'Kelengkapan Barang Elektronik'"></span>
                <i class="fas fa-box-open text-emerald-500"></i>
            </h3>
            <p class="text-[11px] text-gray-400 mb-4">Centang komponen yang lengkap / tersedia saat pemeriksaan.</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                @foreach(collect($completenessOptions['elektronik']['hp'])->merge($completenessOptions['elektronik']['kamera'])->unique() as $item)
                <label class="flex items-center gap-2 bg-white border border-gray-200 hover:border-emerald-300 rounded-lg px-3 py-2 cursor-pointer text-[12px] font-medium text-navy-700 transition has-[:checked]:bg-emerald-50 has-[:checked]:border-emerald-400">
                    <input type="checkbox" name="completeness[]" value="{{ $item }}" class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-400"> {{ $item }}
                </label>
                @endforeach
                @foreach($completenessOptions['camping']['default'] as $item)
                <label class="flex items-center gap-2 bg-white border border-gray-200 hover:border-emerald-300 rounded-lg px-3 py-2 cursor-pointer text-[12px] font-medium text-navy-700 transition has-[:checked]:bg-emerald-50 has-[:checked]:border-emerald-400">
                    <input type="checkbox" name="completeness[]" value="{{ $item }}" class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-400"> {{ $item }}
                </label>
                @endforeach
            </div>
            <div class="mt-4">
                <label class="block text-[12px] font-semibold text-navy-700 mb-1">Kondisi Keseluruhan Barang * (skala 1-10)</label>
                <input type="number" name="overall_condition" value="{{ old('overall_condition', 8) }}" min="1" max="10" required class="w-32 border border-emerald-200 bg-emerald-50/40 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
        </div>

        {{-- Lama Pemakaian --}}
        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-1 flex items-center gap-2"><i class="fas fa-hourglass-half text-purple-500"></i> Lama Kendaraan / Barang Digunakan</h3>
            <p class="text-[11px] text-gray-400 mb-3">Kosongkan untuk hitung otomatis dari jadwal sewa.</p>
            <div class="flex items-center gap-2 max-w-xs">
                <input type="number" name="usage_duration_hours" value="{{ old('usage_duration_hours') }}" min="0" placeholder="Otomatis" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                <span class="text-[12px] font-bold text-gray-500 whitespace-nowrap">jam</span>
            </div>
        </div>

        {{-- Pilihan Catatan Kerusakan --}}
        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-1 flex items-center gap-2"><i class="fas fa-triangle-exclamation text-red-500"></i> Catatan Kerusakan (Pilihan)</h3>
            <p class="text-[11px] text-gray-400 mb-4">Centang jika ditemukan kerusakan berikut:</p>

            <div x-show="scope === 'kendaraan'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($damageOptions['kendaraan'] as $opt)
                <label class="flex items-center gap-2 bg-white border border-gray-200 hover:border-red-300 rounded-lg px-3 py-2 cursor-pointer text-[12px] font-medium text-navy-700 transition has-[:checked]:bg-red-50 has-[:checked]:border-red-400 has-[:checked]:text-red-700">
                    <input type="checkbox" name="damage_items[]" value="{{ $opt }}" {{ in_array($opt, old('damage_items', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-red-500 focus:ring-red-400"> {{ $opt }}
                </label>
                @endforeach
            </div>
            <div x-show="scope === 'elektronik'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($damageOptions['elektronik'] as $opt)
                <label class="flex items-center gap-2 bg-white border border-gray-200 hover:border-red-300 rounded-lg px-3 py-2 cursor-pointer text-[12px] font-medium text-navy-700 transition has-[:checked]:bg-red-50 has-[:checked]:border-red-400 has-[:checked]:text-red-700">
                    <input type="checkbox" name="damage_items[]" value="{{ $opt }}" {{ in_array($opt, old('damage_items', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-red-500 focus:ring-red-400"> {{ $opt }}
                </label>
                @endforeach
            </div>
            <div x-show="scope === 'camping'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($damageOptions['camping'] as $opt)
                <label class="flex items-center gap-2 bg-white border border-gray-200 hover:border-red-300 rounded-lg px-3 py-2 cursor-pointer text-[12px] font-medium text-navy-700 transition has-[:checked]:bg-red-50 has-[:checked]:border-red-400 has-[:checked]:text-red-700">
                    <input type="checkbox" name="damage_items[]" value="{{ $opt }}" {{ in_array($opt, old('damage_items', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-red-500 focus:ring-red-400"> {{ $opt }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Foto Bukti --}}
        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5" x-data="{ count: 0 }">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-1 flex items-center gap-2"><i class="fas fa-camera-retro text-violet-500"></i> Foto Bukti</h3>
            <p class="text-[11px] text-gray-400 mb-4">Unggah foto kondisi unit/barang sebagai dokumentasi (maks. 8 foto, 4MB/foto).</p>
            <label class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-gray-200 hover:border-violet-300 rounded-xl py-7 cursor-pointer transition">
                <i class="fas fa-cloud-arrow-up text-3xl text-gray-300"></i>
                <span class="text-[12px] text-gray-500 font-medium">Klik untuk memilih foto</span>
                <span class="text-[11px] text-gray-400" x-show="count > 0" x-text="count + ' foto dipilih'"></span>
                <input type="file" name="photos[]" multiple accept="image/*" class="hidden" @change="count = $event.target.files.length">
            </label>
        </div>

        {{-- Catatan & Rekomendasi --}}
        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5">
            <div class="space-y-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Catatan Tambahan (opsional)</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500" placeholder="Deskripsikan kondisi umum unit/barang...">{{ old('notes') }}</textarea>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Rekomendasi Tindak Lanjut (opsional)</label>
                    <textarea name="recommendations" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500" placeholder="Contoh: ganti ban, cek AC ke bengkel...">{{ old('recommendations') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pb-2">
            <button type="submit" class="btn-primary text-white px-8 py-3 rounded-xl text-sm font-bold shadow-lg shadow-sky-500/25 transition"><i class="fas fa-save mr-2"></i> Simpan Inspeksi</button>
            <a href="{{ route('inspections.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-3 rounded-xl text-sm font-semibold text-navy-700 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
