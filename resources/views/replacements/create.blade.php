@extends('layouts.dashboard')
@section('page-title', auth()->user()->role === 'user' ? 'Minta Penggantian Kendaraan' : 'Ajukan Penggantian Kendaraan')
@section('content')
@php $role = auth()->user()->role; @endphp

<div class="max-w-2xl">
    <a href="{{ route('replacements.index') }}" class="text-sky-600 text-[13px] mb-4 inline-flex items-center hover:text-sky-700 transition font-medium">
        <i class="fas fa-arrow-left mr-1.5"></i> Kembali ke Daftar
    </a>

    {{-- HEADER --}}
    <div class="bg-gradient-to-r from-sky-500 to-blue-600 rounded-2xl p-6 mb-6 text-white relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full"></div>
        <div class="absolute -left-4 -bottom-4 w-24 h-24 bg-white/5 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center backdrop-blur-sm">
                    <i class="fas fa-right-left text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold">{{ in_array($role, ['driver']) ? 'Ajukan Penggantian Kendaraan' : ($role === 'user' ? 'Minta Penggantian Kendaraan' : 'Ajukan Penggantian Kendaraan') }}</h2>
                    <p class="text-sky-200 text-[12px]">Isi form di bawah untuk mengajukan penggantian unit</p>
                </div>
            </div>
        </div>
    </div>

    @if(in_array($role, ['driver', 'user']))
    <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl mb-5 text-[13px] flex items-start gap-2">
        <i class="fas fa-info-circle mt-0.5"></i>
        <span>Penggantian kendaraan hanya dapat diajukan untuk booking yang sedang berjalan (ongoing) dan setelah <strong>setengah masa sewa</strong> telah berlalu.</span>
    </div>
    @endif

    <form method="POST" action="{{ route('replacements.store') }}" enctype="multipart/form-data" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <div class="glass-card rounded-2xl p-6 mb-5 border border-sky-100/50">
            <h3 class="text-[13px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                <i class="fas fa-calendar-check text-sky-500"></i> Pilih Booking
            </h3>
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Booking *</label>
                <select name="booking_id" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300 cursor-pointer">
                    <option value="">Pilih Booking</option>
                    @foreach($bookings as $b)
                    <option value="{{ $b->id }}" data-vehicle-id="{{ $b->vehicle_id }}">{{ $b->booking_code }} - {{ $b->vehicle?->name ?? '-' }} @if($b->status === 'ongoing')(sedang berjalan)@endif</option>
                    @endforeach
                </select>
                @error('booking_id')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6 mb-5 border border-sky-100/50">
            <h3 class="text-[13px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                <i class="fas fa-car text-emerald-500"></i> Unit Pengganti
            </h3>
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Pilih Unit *</label>
                <select name="replacement_vehicle_id" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300 cursor-pointer">
                    <option value="">Pilih Unit Pengganti</option>
                    @foreach($vehicles as $v)
                    <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->category?->name ?? '-' }}) - Rp {{ number_format($v->daily_price,0,',','.') }}/hari</option>
                    @endforeach
                </select>
                @error('replacement_vehicle_id')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6 mb-5 border border-sky-100/50">
            <h3 class="text-[13px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                <i class="fas fa-comment-dots text-amber-500"></i> Alasan & Detail
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Alasan Penggantian *</label>
                    <textarea name="reason" rows="3" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300 resize-none" placeholder="Jelaskan alasan penggantian kendaraan...">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Selisih Harga (Rp)</label>
                        <input type="number" step="0.01" min="-999999999" name="price_difference" placeholder="0" value="{{ old('price_difference', 0) }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all duration-300 hover:border-gray-300">
                        <p class="text-[11px] text-gray-400 mt-1.5"><i class="fas fa-info-circle text-[9px] mr-1"></i> Positif = lebih mahal, Negatif = lebih murah</p>
                    </div>
                    <div class="flex items-center pb-1">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer bg-gray-50 px-4 py-3 rounded-xl border border-gray-200 hover:border-sky-300 transition w-full">
                            <input type="hidden" name="mark_maintenance" value="0">
                            <input type="checkbox" name="mark_maintenance" value="1" checked class="rounded border-gray-300 text-sky-600 focus:ring-sky-500 w-4 h-4">
                            <div>
                                <span class="text-[12px] font-medium text-navy-700">Unit lama → maintenance</span>
                                <p class="text-[10px] text-gray-400">Tandai unit asal untuk perawatan</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6 mb-6 border border-sky-100/50">
            <h3 class="text-[13px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                <i class="fas fa-camera text-violet-500"></i> Foto Unit (Opsional)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Foto Awal Unit</label>
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-sky-300 transition bg-gray-50/50">
                        <input type="file" name="initial_vehicle_photo" accept="image/*" class="w-full text-[12px] text-navy-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-[12px] file:font-semibold file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100 file:transition">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Foto Akhir Unit</label>
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-sky-300 transition bg-gray-50/50">
                        <input type="file" name="final_vehicle_photo" accept="image/*" class="w-full text-[12px] text-navy-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-[12px] file:font-semibold file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100 file:transition">
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
            <a href="{{ route('replacements.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-3 rounded-xl text-[13px] font-medium text-navy-700 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
