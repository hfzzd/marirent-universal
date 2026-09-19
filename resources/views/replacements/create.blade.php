@extends('layouts.dashboard')
@section('page-title', auth()->user()->role === 'user' ? 'Minta Penggantian Kendaraan' : 'Ajukan Penggantian Kendaraan')
@section('content')
@php
    $role = auth()->user()->role;
    $title = $role === 'user' ? 'Minta Penggantian Kendaraan' : 'Ajukan Penggantian Kendaraan';
    $selectedBookingId = (string) old('booking_id', $preselectedBookingId ?? request('booking_id'));
@endphp

<div class="max-w-5xl mx-auto" x-data="replacementForm()" x-init="init()">
    {{-- BREADCRUMB --}}
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
        <a href="{{ route('replacements.index') }}" class="text-sky-600 text-[13px] inline-flex items-center hover:text-sky-700 transition font-medium group">
            <span class="w-7 h-7 rounded-lg bg-sky-50 group-hover:bg-sky-100 flex items-center justify-center mr-2 transition"><i class="fas fa-arrow-left text-[11px]"></i></span>
            Kembali ke Daftar
        </a>
        <span class="text-[11px] text-gray-400">Penggantian Kendaraan <i class="fas fa-chevron-right text-[8px] mx-1"></i> <span class="text-navy-700 font-semibold">Form Lengkap</span></span>
    </div>

    {{-- HERO HEADER --}}
    <div class="bg-gradient-to-r from-sky-500 via-sky-600 to-blue-600 rounded-2xl p-6 mb-6 text-white relative overflow-hidden shadow-lg shadow-sky-500/20">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full"></div>
        <div class="absolute -right-2 top-10 w-16 h-16 bg-white/10 rounded-full"></div>
        <div class="absolute -left-6 -bottom-8 w-28 h-28 bg-white/5 rounded-full"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/15 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-inner flex-shrink-0">
                    <i class="fas fa-right-left text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold leading-tight">{{ $title }}</h2>
                    <p class="text-sky-200 text-[12px] mt-0.5">Lengkapi 3 langkah di bawah — permintaan diteruskan ke admin untuk persetujuan</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="bg-white/15 backdrop-blur-sm rounded-xl px-3 py-2 text-center">
                    <span class="block text-lg font-extrabold leading-none">{{ $bookings->count() }}</span>
                    <span class="block text-[9px] uppercase tracking-widest text-sky-200 mt-1">Booking eligible</span>
                </span>
                <span class="bg-white/15 backdrop-blur-sm rounded-xl px-3 py-2 text-center">
                    <span class="block text-lg font-extrabold leading-none">{{ $vehicles->count() }}</span>
                    <span class="block text-[9px] uppercase tracking-widest text-sky-200 mt-1">Unit tersedia</span>
                </span>
            </div>
        </div>
    </div>

    @if(in_array($role, ['driver', 'staff', 'user', 'inspector']))
    <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl mb-5 text-[13px] flex items-start gap-2">
        <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
        <span>Penggantian kendaraan dapat diajukan untuk booking yang sedang berjalan (ongoing). Permintaan akan menunggu <strong>persetujuan admin/owner/superadmin</strong>.</span>
    </div>
    @endif

    {{-- STEP INDICATOR --}}
    <div class="glass-card rounded-2xl border border-sky-100/50 px-5 py-4 mb-6 flex items-center gap-2 sm:gap-3 overflow-x-auto">
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white text-[13px] font-bold flex items-center justify-center shadow-md shadow-sky-500/25 flex-shrink-0">1</span>
            <div class="min-w-0"><p class="text-[12px] font-bold text-navy-800 whitespace-nowrap">Pilih Booking</p><p class="text-[10px] text-gray-400 whitespace-nowrap hidden sm:block">Unit yang sedang berjalan</p></div>
        </div>
        <div class="flex-1 h-0.5 bg-gradient-to-r from-sky-200 to-gray-100 rounded-full min-w-[16px]"></div>
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white text-[13px] font-bold flex items-center justify-center shadow-md shadow-emerald-500/25 flex-shrink-0">2</span>
            <div class="min-w-0"><p class="text-[12px] font-bold text-navy-800 whitespace-nowrap">Unit Pengganti</p><p class="text-[10px] text-gray-400 whitespace-nowrap hidden sm:block"> & selisih harga</p></div>
        </div>
        <div class="flex-1 h-0.5 bg-gradient-to-r from-emerald-200 to-gray-100 rounded-full min-w-[16px]"></div>
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 text-white text-[13px] font-bold flex items-center justify-center shadow-md shadow-amber-500/25 flex-shrink-0">3</span>
            <div class="min-w-0"><p class="text-[12px] font-bold text-navy-800 whitespace-nowrap">Alasan & Foto</p><p class="text-[10px] text-gray-400 whitespace-nowrap hidden sm:block">Kirim pengajuan</p></div>
        </div>
    </div>

    <form method="POST" action="{{ route('replacements.store') }}" enctype="multipart/form-data" @submit="loading = true">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

            {{-- ============ KOLOM FORM ============ --}}
            <div class="lg:col-span-2 space-y-5 min-w-0">

                {{-- STEP 1: BOOKING --}}
                <section class="glass-card rounded-2xl border border-sky-100/50 overflow-hidden">
                    <div class="flex items-center gap-3 px-6 pt-5 pb-4 border-b border-gray-50">
                        <span class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-calendar-check text-[15px]"></i></span>
                        <div>
                            <h3 class="text-[14px] font-bold text-navy-800 leading-tight"><span class="text-sky-500 font-extrabold mr-1">01</span> Pilih Booking</h3>
                            <p class="text-[11px] text-gray-400">Booking ongoing yang kendaraannya ingin diganti</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Booking <span class="text-red-400">*</span></label>
                        <x-searchable-select name="booking_id" placeholder="Pilih Booking" required x-model="bookingId" @change="onBookingChange()">
                            <option value="">— Pilih Booking —</option>
                            @foreach($bookings as $b)
                            <option value="{{ $b->id }}"
                                data-code="{{ $b->booking_code }}"
                                data-vehicle="{{ $b->vehicle?->name ?? '-' }}"
                                data-plate="{{ $b->vehicle?->license_plate ?? '' }}"
                                data-price="{{ (float) $b->final_price }}"
                                data-status="{{ $b->status }}"
                                {{ $selectedBookingId === (string) $b->id ? 'selected' : '' }}>{{ $b->booking_code }} · {{ $b->vehicle?->name ?? '-' }} @if($b->status === 'ongoing')(sedang berjalan)@endif</option>
                            @endforeach
                        </x-searchable-select>
                        @if($bookings->isEmpty())
                        <div class="flex items-start gap-2 mt-3 bg-amber-50 border border-amber-200 rounded-xl px-3.5 py-2.5">
                            <i class="fas fa-triangle-exclamation text-amber-500 text-[12px] mt-0.5"></i>
                            <p class="text-amber-700 text-[12px]">Belum ada booking ongoing yang dapat diajukan penggantian.</p>
                        </div>
                        @endif
                        @error('booking_id')<p class="text-red-500 text-[11px] mt-1.5 flex items-center gap-1"><i class="fas fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>@enderror

                        {{-- Preview booking terpilih --}}
                        <div x-show="bookingCode" x-cloak class="mt-3 flex items-center gap-3 bg-sky-50/70 border border-sky-100 rounded-xl px-4 py-3">
                            <span class="w-9 h-9 rounded-xl bg-white text-sky-500 flex items-center justify-center shadow-sm flex-shrink-0"><i class="fas fa-car-side text-[15px]"></i></span>
                            <div class="min-w-0">
                                <p class="text-[13px] font-bold text-navy-800 truncate" x-text="bookingCode + ' · ' + bookingVehicle"></p>
                                <p class="text-[11px] text-gray-400 truncate"><span x-text="bookingPlate"></span> · Total saat ini <span class="font-semibold text-navy-600" x-text="fmt(bookingPrice)"></span></p>
                            </div>
                            <span class="ml-auto badge-blue text-[10px] px-2 py-0.5 rounded-full font-semibold flex-shrink-0" x-text="bookingStatus"></span>
                        </div>
                    </div>
                </section>

                {{-- STEP 2: UNIT PENGGANTI --}}
                <section class="glass-card rounded-2xl border border-sky-100/50 overflow-hidden">
                    <div class="flex items-center gap-3 px-6 pt-5 pb-4 border-b border-gray-50">
                        <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center flex-shrink-0"><i class="fas fa-car text-[15px]"></i></span>
                        <div>
                            <h3 class="text-[14px] font-bold text-navy-800 leading-tight"><span class="text-emerald-500 font-extrabold mr-1">02</span> Unit Pengganti & Harga</h3>
                            <p class="text-[11px] text-gray-400">Harus satu kategori & company yang sama</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Pilih Unit <span class="text-red-400">*</span></label>
                            <x-searchable-select name="replacement_vehicle_id" placeholder="Pilih Unit Pengganti" required x-model="vehicleId" @change="onVehicleChange()">
                                <option value="">— Pilih Unit Pengganti —</option>
                                @foreach($vehicles as $v)
                                <option value="{{ $v->id }}"
                                    data-name="{{ $v->name }}"
                                    data-cat="{{ $v->category?->name ?? '-' }}"
                                    data-price="{{ (float) $v->daily_price }}"
                                    data-plate="{{ $v->license_plate ?? '' }}">{{ $v->name }} ({{ $v->category?->name ?? '-' }}) - Rp {{ number_format($v->daily_price,0,',','.') }}/hari</option>
                                @endforeach
                            </x-searchable-select>
                            @error('replacement_vehicle_id')<p class="text-red-500 text-[11px] mt-1.5 flex items-center gap-1"><i class="fas fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Selisih Harga (Rp)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-[13px] font-semibold">Rp</span>
                                <input type="number" step="0.01" min="-999999999" name="price_difference" x-model.number="priceDiff" placeholder="0" value="{{ old('price_difference', 0) }}"
                                    class="w-full border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-[13px] font-semibold text-navy-800 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all hover:border-gray-300">
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1.5"><i class="fas fa-circle-info text-sky-400 text-[10px]"></i> Positif = unit baru lebih mahal (ditagih), negatif = lebih murah</p>
                        </div>
                    </div>
                </section>

                {{-- STEP 3: ALASAN + KONDISI + FOTO --}}
                <section class="glass-card rounded-2xl border border-sky-100/50 overflow-hidden">
                    <div class="flex items-center gap-3 px-6 pt-5 pb-4 border-b border-gray-50">
                        <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0"><i class="fas fa-comment-dots text-[15px]"></i></span>
                        <div>
                            <h3 class="text-[14px] font-bold text-navy-800 leading-tight"><span class="text-amber-500 font-extrabold mr-1">03</span> Alasan & Kondisi Unit Asal</h3>
                            <p class="text-[11px] text-gray-400">Ceritakan kenapa unit perlu diganti</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Alasan Penggantian <span class="text-red-400">*</span></label>
                            <textarea name="reason" rows="3" required placeholder="Contoh: AC mati total & mesin overheat di tengah perjalanan…"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all hover:border-gray-300 resize-none">{{ old('reason') }}</textarea>
                            @error('reason')<p class="text-red-500 text-[11px] mt-1.5 flex items-center gap-1"><i class="fas fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>@enderror
                        </div>

                        <div class="rounded-2xl border border-amber-200/70 bg-gradient-to-br from-amber-50/80 to-orange-50/40 p-4">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="hidden" name="mark_maintenance" value="0">
                                <input type="checkbox" name="mark_maintenance" value="1" checked
                                    class="mt-0.5 w-4 h-4 rounded border-gray-300 text-amber-500 focus:ring-amber-400"
                                    onchange="document.getElementById('damageNotesField').classList.toggle('hidden', !this.checked)">
                                <span>
                                    <span class="text-[12px] font-bold text-navy-700 flex items-center gap-1.5"><i class="fas fa-wrench text-amber-500 text-[11px]"></i> Unit asal rusak, tandai sebagai maintenance</span>
                                    <span class="block text-[11px] text-amber-600/80 mt-0.5">Unit lama otomatis dikunci dari daftar sewa sampai diperbaiki</span>
                                </span>
                            </label>
                            <div id="damageNotesField" class="mt-3">
                                <label class="block text-[11px] font-bold text-amber-600/70 uppercase tracking-widest mb-1.5">Detail Kerusakan</label>
                                <textarea name="damage_notes" rows="2" maxlength="2000" placeholder="Jelaskan kerusakan unit asal…"
                                    class="w-full border border-amber-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none bg-white resize-none">{{ old('damage_notes') }}</textarea>
                                @error('damage_notes')<p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Foto Unit <span class="text-gray-300 font-normal normal-case">(opsional)</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="group border-2 border-dashed border-gray-200 rounded-2xl p-4 text-center hover:border-sky-300 hover:bg-sky-50/40 transition bg-gray-50/50">
                                    <input type="file" name="initial_vehicle_photo" accept="image/*" id="initial-photo" class="hidden" onchange="previewPhoto(this, 'initial-preview', 'initial-empty')">
                                    <div id="initial-empty">
                                        <span class="w-11 h-11 rounded-2xl bg-white shadow-sm border border-gray-100 text-sky-400 flex items-center justify-center mx-auto mb-2 group-hover:scale-105 transition"><i class="fas fa-camera text-lg"></i></span>
                                        <p class="text-[12px] font-semibold text-navy-700">Foto Awal Unit</p>
                                        <p class="text-[10px] text-gray-400 mb-2">Kondisi sebelum serah terima</p>
                                        <label for="initial-photo" class="bg-sky-500 hover:bg-sky-600 text-white px-3.5 py-1.5 rounded-lg text-[11px] font-semibold cursor-pointer transition inline-block shadow-sm shadow-sky-500/25">
                                            <i class="fas fa-upload text-[9px] mr-1"></i> Pilih Foto
                                        </label>
                                    </div>
                                    <div id="initial-preview" class="hidden">
                                        <img id="initial-preview-img" class="max-h-32 w-full object-cover mx-auto rounded-xl border border-gray-200 shadow-sm mb-2">
                                        <button type="button" onclick="removePhoto('initial-photo', 'initial-preview', 'initial-empty')" class="text-red-500 text-[11px] font-semibold hover:underline">Hapus foto</button>
                                    </div>
                                </div>
                                <div class="group border-2 border-dashed border-gray-200 rounded-2xl p-4 text-center hover:border-emerald-300 hover:bg-emerald-50/40 transition bg-gray-50/50">
                                    <input type="file" name="final_vehicle_photo" accept="image/*" id="final-photo" class="hidden" onchange="previewPhoto(this, 'final-preview', 'final-empty')">
                                    <div id="final-empty">
                                        <span class="w-11 h-11 rounded-2xl bg-white shadow-sm border border-gray-100 text-emerald-400 flex items-center justify-center mx-auto mb-2 group-hover:scale-105 transition"><i class="fas fa-camera text-lg"></i></span>
                                        <p class="text-[12px] font-semibold text-navy-700">Foto Akhir Unit</p>
                                        <p class="text-[10px] text-gray-400 mb-2">Kondisi setelah penggantian</p>
                                        <label for="final-photo" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3.5 py-1.5 rounded-lg text-[11px] font-semibold cursor-pointer transition inline-block shadow-sm shadow-emerald-500/25">
                                            <i class="fas fa-upload text-[9px] mr-1"></i> Pilih Foto
                                        </label>
                                    </div>
                                    <div id="final-preview" class="hidden">
                                        <img id="final-preview-img" class="max-h-32 w-full object-cover mx-auto rounded-xl border border-gray-200 shadow-sm mb-2">
                                        <button type="button" onclick="removePhoto('final-photo', 'final-preview', 'final-empty')" class="text-red-500 text-[11px] font-semibold hover:underline">Hapus foto</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ACTION BAR --}}
                <div class="glass-card rounded-2xl border border-sky-100/50 p-4 flex flex-col sm:flex-row gap-3 sm:items-center sticky bottom-4 shadow-xl shadow-sky-500/10">
                    <button type="submit" :disabled="loading"
                        class="flex-1 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white px-8 py-3.5 rounded-xl text-[14px] font-bold shadow-lg shadow-sky-500/30 transition-all duration-300 hover:scale-[1.01] flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-paper-plane" x-show="!loading"></i>
                        <i class="fas fa-spinner fa-spin" x-show="loading" x-cloak></i>
                        <span x-text="loading ? 'Mengirim pengajuan...' : 'Kirim Pengajuan Penggantian'"></span>
                    </button>
                    <a href="{{ route('replacements.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-3.5 rounded-xl text-[13px] font-semibold text-navy-700 transition text-center">Batal</a>
                </div>
            </div>

            {{-- ============ SIDEBAR RINGKASAN ============ --}}
            <aside class="space-y-5 lg:sticky lg:top-20 min-w-0">
                <div class="rounded-2xl overflow-hidden border border-sky-100 shadow-sm bg-white">
                    <div class="bg-gradient-to-r from-navy-800 to-navy-900 px-5 py-4">
                        <h4 class="text-[13px] font-bold text-white flex items-center gap-2"><i class="fas fa-receipt text-sky-400"></i> Ringkasan Pengajuan</h4>
                        <p class="text-[11px] text-gray-400 mt-0.5">Terisi otomatis saat Anda memilih</p>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex gap-3">
                            <span class="w-9 h-9 rounded-xl bg-red-50 text-red-400 flex items-center justify-center flex-shrink-0"><i class="fas fa-car-side text-[14px]"></i></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Unit Asal</p>
                                <p class="text-[13px] font-bold text-navy-800 truncate" x-text="bookingVehicle || '-'"></p>
                                <p class="text-[11px] text-gray-400 truncate" x-text="bookingCode ? bookingCode + (bookingPlate ? ' · ' + bookingPlate : '') : 'Belum pilih booking'"></p>
                            </div>
                        </div>
                        <div class="flex justify-center -my-1">
                            <span class="w-7 h-7 rounded-full bg-sky-500 text-white flex items-center justify-center shadow-md shadow-sky-500/30"><i class="fas fa-arrow-down text-[10px]"></i></span>
                        </div>
                        <div class="flex gap-3">
                            <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center flex-shrink-0"><i class="fas fa-car text-[14px]"></i></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Unit Pengganti</p>
                                <p class="text-[13px] font-bold text-navy-800 truncate" x-text="vehicleName || '-'"></p>
                                <p class="text-[11px] text-gray-400 truncate" x-text="vehicleCat ? vehicleCat + (vehiclePlate ? ' · ' + vehiclePlate : '') : 'Belum pilih unit'"></p>
                            </div>
                        </div>
                        <div class="border-t border-dashed border-gray-200 pt-4 space-y-2">
                            <div class="flex justify-between text-[12px]">
                                <span class="text-gray-400">Total booking saat ini</span>
                                <span class="font-bold text-navy-700" x-text="fmt(bookingPrice)"></span>
                            </div>
                            <div class="flex justify-between text-[12px]">
                                <span class="text-gray-400">Selisih harga</span>
                                <span class="font-bold" :class="priceDiff > 0 ? 'text-red-600' : (priceDiff < 0 ? 'text-emerald-600' : 'text-gray-400')" x-text="(priceDiff > 0 ? '+' : '') + ' ' + fmt(priceDiff || 0)"></span>
                            </div>
                            <div class="flex justify-between items-center bg-sky-50/70 border border-sky-100 rounded-xl px-3 py-2.5">
                                <span class="text-[12px] font-bold text-navy-800">Estimasi total baru</span>
                                <span class="text-[15px] font-extrabold text-sky-600" x-text="fmt((bookingPrice || 0) + (priceDiff || 0))"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h4 class="text-[13px] font-bold text-navy-800 mb-3 flex items-center gap-2"><i class="fas fa-route text-sky-500"></i> Alur Persetujuan</h4>
                    <div class="space-y-3.5 relative">
                        <div class="absolute left-[13px] top-2 bottom-2 w-0.5 bg-gray-100"></div>
                        <div class="flex items-start gap-3 relative">
                            <span class="w-7 h-7 bg-sky-500 text-white rounded-full flex items-center justify-center flex-shrink-0 z-10 text-[10px] font-bold shadow-md shadow-sky-500/30">1</span>
                            <div><p class="text-[12px] font-bold text-navy-700">Anda mengajukan</p><p class="text-[11px] text-gray-400">Form ini terkirim + notifikasi ke admin</p></div>
                        </div>
                        <div class="flex items-start gap-3 relative">
                            <span class="w-7 h-7 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center flex-shrink-0 z-10 text-[10px] font-bold">2</span>
                            <div><p class="text-[12px] font-bold text-navy-700">Admin me-review</p><p class="text-[11px] text-gray-400">Setujui / tolak + catat selisih harga</p></div>
                        </div>
                        <div class="flex items-start gap-3 relative">
                            <span class="w-7 h-7 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center flex-shrink-0 z-10 text-[10px] font-bold">3</span>
                            <div><p class="text-[12px] font-bold text-navy-700">Unit ditukar</p><p class="text-[11px] text-gray-400">Driver tetap, invoice menyesuaikan</p></div>
                        </div>
                    </div>
                </div>
            </aside>
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
function replacementForm() {
    return {
        loading: false,
        bookingId: @json($selectedBookingId),
        vehicleId: @json((string) old('replacement_vehicle_id', '')),
        priceDiff: @json((float) old('price_difference', 0)),
        bookingCode: '', bookingVehicle: '', bookingPlate: '', bookingPrice: 0, bookingStatus: '',
        vehicleName: '', vehicleCat: '', vehiclePlate: '',
        init() {
            this.onBookingChange();
            this.onVehicleChange();
        },
        opt(sel, val) { return sel ? sel.querySelector('option[value="' + val + '"]') : null; },
        onBookingChange() {
            const sel = document.querySelector('select[name="booking_id"]');
            const o = this.opt(sel, this.bookingId);
            if (!o || !this.bookingId) { this.bookingCode = ''; this.bookingVehicle = ''; this.bookingPlate = ''; this.bookingPrice = 0; this.bookingStatus = ''; return; }
            this.bookingCode = o.dataset.code || o.text;
            this.bookingVehicle = o.dataset.vehicle || '';
            this.bookingPlate = o.dataset.plate || '';
            this.bookingPrice = parseFloat(o.dataset.price || 0);
            this.bookingStatus = o.dataset.status || '';
        },
        onVehicleChange() {
            const sel = document.querySelector('select[name="replacement_vehicle_id"]');
            const o = this.opt(sel, this.vehicleId);
            if (!o || !this.vehicleId) { this.vehicleName = ''; this.vehicleCat = ''; this.vehiclePlate = ''; return; }
            this.vehicleName = o.dataset.name || o.text;
            this.vehicleCat = o.dataset.cat || '';
            this.vehiclePlate = o.dataset.plate || '';
        },
        fmt(n) {
            n = parseFloat(n || 0);
            return 'Rp ' + n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
        }
    };
}
</script>
@endsection
