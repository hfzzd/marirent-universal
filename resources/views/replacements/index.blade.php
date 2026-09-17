@extends('layouts.dashboard')
@section('page-title', 'Penggantian Kendaraan')
@section('title', 'Penggantian Kendaraan - MariRent')
@section('content')
@php
    $role = auth()->user()->role;
    $canRequest = in_array($role, ['driver', 'staff', 'user', 'owner', 'admin', 'superadmin', 'inspector']);
    $hasEligibleBookings = $hasEligibleBookings ?? (isset($modalBookings, $modalVehicles) && count($modalBookings) > 0 && count($modalVehicles) > 0);
    $counts = $counts ?? ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
@endphp

{{-- Wrapper Alpine tunggal: modal + tombol harus dalam satu scope agar @click="open = true" berfungsi di semua akun --}}
<div x-data="{ open: false, loading: false }">

{{-- HEADER & ACTION (selaras dengan halaman Penggantian Unit) --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-5 gap-3">
    <div>
        <h2 class="text-lg font-extrabold text-navy-800 flex items-center gap-2">
            <i class="fas fa-right-left text-sky-500"></i> Penggantian Kendaraan
        </h2>
        <p class="text-xs text-gray-400 mt-0.5">Kelola permintaan penggantian unit mobil & motor yang sedang berjalan.</p>
    </div>
    @if($canRequest)
    <div class="flex items-center gap-2 flex-wrap">
        {{-- Tombol utama SELALU pop-up modal, untuk semua role --}}
        <button type="button" @click="open = true" class="bg-gradient-to-r from-sky-500 to-sky-600 text-white px-4 py-2 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-1.5">
            <i class="fas fa-bolt text-[10px]"></i> Ajukan Penggantian
        </button>
        <a href="{{ route('replacements.create') }}" class="bg-white border border-gray-200 hover:border-sky-300 hover:text-sky-600 text-gray-500 px-4 py-2 rounded-xl text-[12px] font-semibold transition flex items-center gap-1.5">
            <i class="fas fa-plus text-[10px]"></i> Form Lengkap
        </a>
    </div>
    @endif
</div>

{{-- MODAL PENGAJUAN — selalu tersedia untuk semua akun yang boleh mengajukan --}}
@if($canRequest)
<div x-cloak>
    {{-- Backdrop --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50" @click="open = false"></div>

    {{-- Modal --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 rounded-t-2xl p-5 relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-right-left text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Ajukan Penggantian Kendaraan</h3>
                            <p class="text-sky-200 text-[11px]">Isi form di bawah tanpa buka halaman baru</p>
                        </div>
                    </div>
                    <button @click="open = false" class="text-white/70 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Alert Info --}}
            <div class="mx-5 mt-4 bg-amber-50 border border-amber-200 text-amber-700 px-4 py-2.5 rounded-xl text-[12px] flex items-start gap-2">
                <i class="fas fa-info-circle mt-0.5"></i>
                <span>Penggantian dapat diajukan untuk booking yang sedang berjalan (ongoing). Permintaan menunggu persetujuan admin/owner/superadmin.</span>
            </div>

            @if($hasEligibleBookings)
            {{-- Modal Body --}}
            <form method="POST" action="{{ route('replacements.store') }}" enctype="multipart/form-data" @submit="loading = true" class="p-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Booking *</label>
                    <select name="booking_id" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all hover:border-gray-300">
                        <option value="">Pilih Booking</option>
                        @foreach($modalBookings as $b)
                        <option value="{{ $b['id'] }}">{{ $b['label'] }}</option>
                        @endforeach
                    </select>
                    @error('booking_id') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Unit Pengganti *</label>
                    <select name="replacement_vehicle_id" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all hover:border-gray-300">
                        <option value="">Pilih Unit Pengganti</option>
                        @foreach($modalVehicles as $v)
                        <option value="{{ $v['id'] }}">{{ $v['label'] }}</option>
                        @endforeach
                    </select>
                    @error('replacement_vehicle_id') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Alasan Penggantian *</label>
                    <textarea name="reason" rows="3" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all hover:border-gray-300 resize-none" placeholder="Jelaskan alasan penggantian kendaraan...">{{ old('reason') }}</textarea>
                    @error('reason') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Detail Kerusakan Unit Asal <span class="text-gray-300 font-normal normal-case">(opsional)</span></label>
                    <textarea name="damage_notes" rows="2" maxlength="2000" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none bg-gray-50/50 transition-all hover:border-gray-300 resize-none" placeholder="Jelaskan kerusakan unit asal...">{{ old('damage_notes') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Selisih Harga (Rp)</label>
                        <input type="number" step="0.01" min="-999999999" name="price_difference" placeholder="0" value="{{ old('price_difference', 0) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all hover:border-gray-300">
                        <p class="text-[10px] text-gray-400 mt-1"><i class="fas fa-info-circle text-[9px] mr-1"></i> Positif = lebih mahal, negatif = lebih murah</p>
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer bg-gray-50 px-4 py-2.5 rounded-xl border border-gray-200 hover:border-sky-300 transition w-full">
                            <input type="hidden" name="mark_maintenance" value="0">
                            <input type="checkbox" name="mark_maintenance" value="1" checked class="rounded border-gray-300 text-sky-600 focus:ring-sky-500 w-4 h-4">
                            <div>
                                <span class="text-[11px] font-medium text-navy-700">Unit lama → maintenance</span>
                                <p class="text-[10px] text-gray-400">Tandai unit asal untuk perawatan</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Foto Awal Unit</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-3 text-center hover:border-sky-300 transition bg-gray-50/50">
                            <input type="file" name="initial_vehicle_photo" accept="image/*" class="w-full text-[11px] text-navy-600 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100 file:transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Foto Akhir Unit</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-3 text-center hover:border-sky-300 transition bg-gray-50/50">
                            <input type="file" name="final_vehicle_photo" accept="image/*" class="w-full text-[11px] text-navy-600 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100 file:transition">
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex gap-3 pt-2 border-t border-gray-100">
                    <button type="submit" :disabled="loading" class="bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white px-6 py-2.5 rounded-xl text-[13px] font-bold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-paper-plane" x-show="!loading"></i>
                        <i class="fas fa-spinner fa-spin" x-show="loading" x-cloak></i>
                        <span x-text="loading ? 'Mengirim...' : 'Ajukan Penggantian'"></span>
                    </button>
                    <button type="button" @click="open = false" class="bg-gray-100 hover:bg-gray-200 px-5 py-2.5 rounded-xl text-[13px] font-medium text-navy-700 transition">Batal</button>
                </div>
            </form>
            @else
            {{-- Empty state di dalam modal: tetap pop-up meski belum ada booking eligible --}}
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-sky-50 to-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-sky-100">
                    <i class="fas fa-calendar-xmark text-sky-300 text-2xl"></i>
                </div>
                <h4 class="text-[15px] font-bold text-navy-800 mb-1.5">Belum ada booking yang bisa diajukan</h4>
                <p class="text-[12px] text-gray-400 mb-5 max-w-sm mx-auto">Penggantian hanya untuk booking <span class="font-semibold text-gray-500">ongoing</span> yang memiliki kendaraan. Begitu ada booking berjalan, form di sini langsung bisa dipakai.</p>
                <div class="flex gap-2 justify-center flex-wrap">
                    <a href="{{ route('bookings.index') }}" class="bg-gradient-to-r from-sky-500 to-sky-600 text-white px-5 py-2.5 rounded-xl text-[12px] font-bold shadow-lg shadow-sky-500/25 transition hover:scale-105 inline-flex items-center gap-1.5">
                        <i class="fas fa-calendar-check text-[10px]"></i> Lihat Booking
                    </a>
                    <a href="{{ route('replacements.create') }}" class="bg-white border border-gray-200 hover:border-sky-300 hover:text-sky-600 text-gray-500 px-5 py-2.5 rounded-xl text-[12px] font-semibold transition inline-flex items-center gap-1.5">
                        <i class="fas fa-arrow-right text-[10px]"></i> Buka Form Lengkap
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- RINGKASAN (dari controller, scope role konsisten) --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
    <div class="glass-card rounded-2xl p-4 border border-sky-100/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-500/20"><i class="fas fa-right-left text-white text-sm"></i></div>
            <div>
                <p class="text-[20px] font-extrabold text-navy-800 leading-none">{{ $counts['all'] }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mt-1">Total</p>
            </div>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-4 border border-blue-100/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/20"><i class="fas fa-clock text-white text-sm"></i></div>
            <div>
                <p class="text-[20px] font-extrabold text-blue-600 leading-none">{{ $counts['pending'] }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mt-1">Pending</p>
            </div>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-4 border border-emerald-100/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20"><i class="fas fa-check text-white text-sm"></i></div>
            <div>
                <p class="text-[20px] font-extrabold text-emerald-600 leading-none">{{ $counts['approved'] }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mt-1">Disetujui</p>
            </div>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-4 border border-red-100/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-lg shadow-red-500/20"><i class="fas fa-times text-white text-sm"></i></div>
            <div>
                <p class="text-[20px] font-extrabold text-red-600 leading-none">{{ $counts['rejected'] }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mt-1">Ditolak</p>
            </div>
        </div>
    </div>
</div>

{{-- FILTER & PENCARIAN --}}
<div class="glass-card rounded-2xl p-4 mb-5 border border-sky-100/50 shadow-sm">
    <form action="{{ route('replacements.index') }}" method="GET" class="flex flex-col gap-3">
        <div class="flex flex-col md:flex-row gap-3 md:items-end">
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cari</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-[11px]"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode booking, nama unit asal / pengganti..."
                        class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50">
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-white px-4 py-2.5 rounded-xl text-[12px] font-semibold shadow-sm">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                @if(request()->hasAny(['search','status']))
                <a href="{{ route('replacements.index') }}" class="bg-red-50 hover:bg-red-100 text-red-500 px-3 py-2.5 rounded-xl text-[12px] font-medium transition border border-red-100">
                    <i class="fas fa-times text-[10px]"></i> Reset
                </a>
                @endif
            </div>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('replacements.index', array_filter(['search' => request('search')])) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ !request('status') ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Semua</a>
            <a href="{{ route('replacements.index', array_filter(['search' => request('search'), 'status' => 'pending'])) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('status') == 'pending' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-blue-300 hover:text-blue-600' }}">Pending</a>
            <a href="{{ route('replacements.index', array_filter(['search' => request('search'), 'status' => 'approved'])) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('status') == 'approved' ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-emerald-300 hover:text-emerald-600' }}">Disetujui</a>
            <a href="{{ route('replacements.index', array_filter(['search' => request('search'), 'status' => 'rejected'])) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('status') == 'rejected' ? 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-red-300 hover:text-red-600' }}">Ditolak</a>
        </div>
    </form>
</div>

{{-- DAFTAR PENGGANTIAN --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($replacements as $r)
    @php
        // Mapping eksplisit per status (hindari class dinamis text-*-* yang tidak ter-compile Tailwind)
        $statusConfig = match($r->status) {
            'approved' => ['badge' => 'badge-green', 'icon' => 'fa-check-circle', 'iconClass' => 'text-emerald-500', 'label' => 'Disetujui', 'labelClass' => 'text-emerald-600', 'headBg' => 'from-emerald-50 to-teal-50', 'border' => 'border-emerald-200'],
            'rejected' => ['badge' => 'badge-red', 'icon' => 'fa-times-circle', 'iconClass' => 'text-red-500', 'label' => 'Ditolak', 'labelClass' => 'text-red-600', 'headBg' => 'from-red-50 to-rose-50', 'border' => 'border-red-200'],
            default => ['badge' => 'badge-blue', 'icon' => 'fa-clock', 'iconClass' => 'text-blue-500', 'label' => 'Pending', 'labelClass' => 'text-blue-600', 'headBg' => 'from-blue-50 to-indigo-50', 'border' => 'border-blue-200'],
        };
        $requester = $r->requestedBy;
        $requesterRole = $requester?->role ?? '-';
        $requesterBadge = match(true) {
            $requesterRole === 'driver' => '<span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">Driver</span>',
            $requesterRole === 'user' => '<span class="text-[10px] bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full font-medium">Penyewa</span>',
            in_array($requesterRole, ['superadmin', 'owner', 'admin']) => '<span class="text-[10px] bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full font-medium">Admin</span>',
            $requesterRole === 'inspector' => '<span class="text-[10px] bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full font-medium">Inspektor</span>',
            in_array($requesterRole, ['staff']) => '<span class="text-[10px] bg-cyan-50 text-cyan-600 px-2 py-0.5 rounded-full font-medium">Staff</span>',
            default => '',
        };
    @endphp
    <div class="glass-card rounded-2xl overflow-hidden border {{ $statusConfig['border'] }} hover:shadow-lg transition-all duration-300 flex flex-col" style="box-shadow: 0 2px 16px rgba(0,0,0,0.03);">
        <div class="bg-gradient-to-r {{ $statusConfig['headBg'] }} px-4 py-3 border-b {{ $statusConfig['border'] }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas {{ $statusConfig['icon'] }} {{ $statusConfig['iconClass'] }} text-sm"></i>
                    <span class="text-[11px] font-bold {{ $statusConfig['labelClass'] }} uppercase tracking-wider">{{ $statusConfig['label'] }}</span>
                </div>
                <span class="text-[11px] text-gray-400">{{ $r->created_at->format('d M Y') }}</span>
            </div>
        </div>
        <div class="p-4 flex-1 flex flex-col">
            <div class="flex items-start justify-between mb-3 gap-2">
                <div class="min-w-0">
                    <p class="text-[13px] font-bold text-navy-800 truncate">{{ $r->booking->booking_code ?? '-' }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Diajukan oleh {{ $requester?->name ?? '-' }}</p>
                </div>
                {!! $requesterBadge !!}
            </div>

            <div class="flex items-stretch gap-2 mb-3">
                <div class="flex-1 bg-gray-50 rounded-xl p-2.5 text-center min-w-0">
                    <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold">Asal</p>
                    <p class="text-[12px] font-bold text-navy-700 truncate">{{ $r->originalVehicle?->name ?? '-' }}</p>
                    @if($r->originalVehicle?->license_plate)
                    <p class="text-[10px] text-gray-400 truncate mt-0.5">{{ $r->originalVehicle->license_plate }}</p>
                    @endif
                </div>
                <div class="flex items-center flex-shrink-0">
                    <span class="w-6 h-6 rounded-full bg-sky-100 flex items-center justify-center"><i class="fas fa-arrow-right text-sky-500 text-[9px]"></i></span>
                </div>
                <div class="flex-1 bg-gray-50 rounded-xl p-2.5 text-center min-w-0">
                    <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold">Pengganti</p>
                    <p class="text-[12px] font-bold text-navy-700 truncate">{{ $r->replacementVehicle?->name ?? '-' }}</p>
                    @if($r->replacementVehicle?->license_plate)
                    <p class="text-[10px] text-gray-400 truncate mt-0.5">{{ $r->replacementVehicle->license_plate }}</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between mt-auto pt-1">
                <div class="font-bold text-[13px] {{ $r->price_difference > 0 ? 'text-red-600' : ($r->price_difference < 0 ? 'text-emerald-600' : 'text-gray-500') }}">
                    {{ $r->price_difference > 0 ? '+' : '' }} Rp {{ number_format((float) $r->price_difference, 0, ',', '.') }}
                </div>
                <div class="flex items-center gap-2">
                     @if($r->status == 'pending' && in_array($role, ['superadmin','owner','admin']))
                     <form method="POST" action="{{ route('replacements.approve', $r) }}" class="inline" onsubmit="return confirm('Setujui penggantian ini?')">@csrf
                         <button class="bg-emerald-50 hover:bg-emerald-100 text-emerald-600 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold transition flex items-center gap-1"><i class="fas fa-check text-[9px]"></i> Setuju</button>
                     </form>
                     <form method="POST" action="{{ route('replacements.reject', $r) }}" class="inline" onsubmit="return confirm('Tolak permintaan ini?')">@csrf
                         <button class="bg-red-50 hover:bg-red-100 text-red-600 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold transition flex items-center gap-1"><i class="fas fa-times text-[9px]"></i> Tolak</button>
                     </form>
                     @endif
                    <a href="{{ route('replacements.show', $r) }}" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold transition flex items-center gap-1">
                        <i class="fas fa-eye text-[9px]"></i> Detail
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-16">
        <div class="w-20 h-20 bg-gradient-to-br from-sky-50 to-blue-50 rounded-3xl flex items-center justify-center mx-auto mb-5 border border-sky-100">
            <i class="fas fa-right-left text-sky-300 text-3xl"></i>
        </div>
        <h3 class="text-lg font-bold text-navy-800 mb-2">Belum ada permintaan penggantian</h3>
        <p class="text-gray-400 text-[13px] max-w-sm mx-auto">{{ in_array($role, ['driver', 'staff', 'user', 'inspector']) ? 'Gunakan tombol Ajukan Penggantian di atas untuk mengajukan dari booking ongoing Anda' : 'Permintaan penggantian akan muncul di sini setelah ada pengajuan' }}</p>
        @if($canRequest)
        <button type="button" @click="open = true" class="mt-4 inline-flex items-center gap-1.5 bg-gradient-to-r from-sky-500 to-sky-600 text-white px-5 py-2.5 rounded-xl text-[13px] font-bold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105">
            <i class="fas fa-plus text-[10px]"></i> Ajukan Sekarang
        </button>
        @endif
    </div>
    @endforelse
</div>

@if($replacements->hasPages())
<div class="mt-8 flex justify-center">{{ $replacements->withQueryString()->links() }}</div>
@endif

</div>{{-- /wrapper Alpine tunggal --}}

@endsection
