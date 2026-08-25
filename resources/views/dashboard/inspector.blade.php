@extends('layouts.dashboard')
@section('page-title', 'Beranda Inspector')

@section('content')
{{-- Hero Banner --}}
<div class="relative overflow-hidden rounded-3xl mb-6 shadow-xl shadow-sky-900/10" style="background: linear-gradient(135deg, #0b1e36 0%, #134e4a 50%, #059669 100%);">
    <div class="absolute -right-10 -bottom-10 w-80 h-80 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="relative px-6 py-7 md:px-8 md:py-8 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-emerald-200 text-xs font-semibold mb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Inspector - Unit Quality Control</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">
                Selamat Datang, {{ auth()->user()->name }}!
            </h1>
            <p class="text-emerald-100/80 text-xs md:text-sm mt-1 max-w-xl">
                Pantau tugas inspeksi awal & akhir unit kendaraan, elektronik, maupun alat camping.
            </p>
            <div class="flex flex-wrap items-center gap-3 mt-4">
                <div class="flex items-center gap-2 bg-black/20 backdrop-blur-md rounded-xl px-3.5 py-1.5 border border-white/10 text-white text-xs">
                    <i class="fas fa-circle-play text-sky-300"></i>
                    <span><strong>{{ $pendingPre }}</strong> Butuh Inspeksi Awal</span>
                </div>
                <div class="flex items-center gap-2 bg-black/20 backdrop-blur-md rounded-xl px-3.5 py-1.5 border border-white/10 text-white text-xs">
                    <i class="fas fa-flag-checkered text-amber-300"></i>
                    <span><strong>{{ $pendingPost }}</strong> Butuh Inspeksi Akhir</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3 w-full lg:w-auto">
            <a href="{{ route('inspections.index') }}" class="flex-1 lg:flex-initial bg-white/15 hover:bg-white/25 text-white border border-white/20 px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 backdrop-blur-md">
                <i class="fas fa-clipboard-list"></i> Riwayat
            </a>
            <a href="{{ route('inspections.create') }}" class="flex-1 lg:flex-initial bg-white text-navy-900 hover:bg-emerald-50 px-5 py-2.5 rounded-xl text-xs font-extrabold transition shadow-lg flex items-center justify-center gap-2">
                <i class="fas fa-plus text-emerald-600"></i> Buat Inspeksi
            </a>
        </div>
    </div>
</div>

{{-- Stat Widgets --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Inspeksi Saya</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $totalInspections }}</h3>
                <span class="text-[11px] text-gray-500 mt-2 block">Seluruh riwayat pemeriksaan</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center text-white text-lg shadow-lg shadow-sky-500/25">
                <i class="fas fa-clipboard-check"></i>
            </div>
        </div>
    </div>

    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Bulan Ini</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $thisMonthInspections }}</h3>
                <span class="text-[11px] text-gray-500 mt-2 block">{{ now()->translatedFormat('F Y') }}</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-lg shadow-lg shadow-amber-500/25">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>
    </div>

    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Temuan Kerusakan</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $damageFindings }}</h3>
                <span class="badge badge-red text-[10px] mt-2">Perlu Tindak Lanjut</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center text-white text-lg shadow-lg shadow-red-500/25">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
        </div>
    </div>

    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Tugas Menunggu</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $pendingPre + $pendingPost }}</h3>
                <span class="text-[11px] text-gray-500 mt-2 block">Booking belum diinspeksi</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white text-lg shadow-lg shadow-purple-500/25">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
    </div>
</div>

{{-- Task Queue --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Butuh Inspeksi Awal --}}
    <div class="glass-card rounded-2xl overflow-hidden border border-sky-100/50 shadow-sm">
        <div class="px-6 py-4 border-b border-sky-100/60 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-circle-play text-sky-500"></i> Antrian Inspeksi Awal
            </h3>
            <span class="badge badge-blue">{{ $preQueue->count() }} booking</span>
        </div>
        <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
            @forelse($preQueue as $b)
            <div class="px-5 py-3.5 hover:bg-sky-50/30 transition flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-navy-800 text-[13px] truncate">{{ $b->vehicle?->name ?? $b->bookingItems->first()?->item_type ?? ($b->category?->name ?? 'Unit Sewa') }}</p>
                    <p class="text-[11px] text-gray-400 truncate">{{ $b->booking_code }} &bull; {{ $b->user->name ?? '-' }} &bull; Mulai {{ \Carbon\Carbon::parse($b->start_date)->translatedFormat('d M Y') }}</p>
                </div>
                <a href="{{ route('inspections.create', ['booking_id' => $b->id]) }}" class="btn-primary text-white text-[11px] font-bold px-3 py-1.5 rounded-lg whitespace-nowrap flex-shrink-0">
                    <i class="fas fa-play mr-1"></i> Awal
                </a>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400 text-xs">Tidak ada antrian inspeksi awal.</div>
            @endforelse
        </div>
    </div>

    {{-- Butuh Inspeksi Akhir --}}
    <div class="glass-card rounded-2xl overflow-hidden border border-sky-100/50 shadow-sm">
        <div class="px-6 py-4 border-b border-sky-100/60 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-flag-checkered text-amber-500"></i> Antrian Inspeksi Akhir
            </h3>
            <span class="badge badge-yellow">{{ $postQueue->count() }} booking</span>
        </div>
        <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
            @forelse($postQueue as $b)
            <div class="px-5 py-3.5 hover:bg-sky-50/30 transition flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-navy-800 text-[13px] truncate">{{ $b->vehicle?->name ?? $b->bookingItems->first()?->item_type ?? ($b->category?->name ?? 'Unit Sewa') }}</p>
                    <p class="text-[11px] text-gray-400 truncate">{{ $b->booking_code }} &bull; {{ $b->user->name ?? '-' }} &bull; Selesai {{ \Carbon\Carbon::parse($b->end_date)->translatedFormat('d M Y') }}</p>
                </div>
                <a href="{{ route('inspections.create', ['booking_id' => $b->id]) }}" class="bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold px-3 py-1.5 rounded-lg whitespace-nowrap flex-shrink-0">
                    <i class="fas fa-stop mr-1"></i> Akhir
                </a>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400 text-xs">Tidak ada antrian inspeksi akhir.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Riwayat Inspeksi Terbaru --}}
<div class="glass-card rounded-2xl overflow-hidden shadow-sm border border-sky-100/50">
    <div class="px-6 py-4 border-b border-sky-100/60 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-emerald-500"></i> Inspeksi Terbaru Saya
            </h3>
            <p class="text-[11px] text-gray-400">Pemeriksaan terakhir yang Anda catat.</p>
        </div>
        <a href="{{ route('inspections.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1">
            Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-sky-50/40 border-b border-sky-100/50 text-[10px] uppercase font-bold text-gray-500">
                    <th class="py-3 px-6 text-left">Unit / Barang</th>
                    <th class="py-3 px-6 text-left">Jenis</th>
                    <th class="py-3 px-6 text-left">Lama Pakai</th>
                    <th class="py-3 px-6 text-center">Kondisi</th>
                    <th class="py-3 px-6 text-center">Kerusakan</th>
                    <th class="py-3 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($recentInspections as $ins)
                <tr class="hover:bg-sky-50/30 transition-colors">
                    <td class="py-3.5 px-6 font-bold text-navy-800">{{ $ins->getItemName() }}</td>
                    <td class="py-3.5 px-6">
                        <span class="badge {{ $ins->type == 'pre_rental' ? 'badge-blue' : 'badge-yellow' }}">{{ $ins->getTypeLabel() }}</span>
                        <span class="text-[10px] text-gray-400 ml-1">{{ $ins->getScopeLabel() }}</span>
                    </td>
                    <td class="py-3.5 px-6 text-navy-600">{{ $ins->getUsageDurationLabel() }}</td>
                    <td class="py-3.5 px-6 text-center font-bold">{{ $ins->overall_condition }}/10 <span class="text-[10px] text-gray-400 font-normal">{{ $ins->getConditionLabel() }}</span></td>
                    <td class="py-3.5 px-6 text-center">
                        @if($ins->hasDamages()) <span class="badge badge-red">{{ count($ins->damage_items ?? []) }} Temuan</span>
                        @else <span class="badge badge-green">Aman</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-6 text-center">
                        <a href="{{ route('inspections.show', $ins) }}" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-600 transition" title="Detail">
                            <i class="fas fa-eye text-xs"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-8 text-center text-gray-400">Belum ada inspeksi yang Anda buat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
