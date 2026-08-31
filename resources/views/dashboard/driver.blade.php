@extends('layouts.dashboard')
@section('page-title', 'Beranda Driver')

@section('content')
@php
    $driver = \App\Models\Driver::where('user_id', auth()->id())->first();
@endphp

@if($driver)
@php
    $totalTrips = \App\Models\Booking::where('driver_id', $driver->id)->count();
    $ongoingTrips = \App\Models\Booking::where('driver_id', $driver->id)->where('status', 'ongoing')->count();
    $completedTrips = \App\Models\Booking::where('driver_id', $driver->id)->where('status', 'completed')->count();
    $lastSalary = $driver->salaries()->latest()->first();
    $assignedBookings = \App\Models\Booking::where('driver_id', $driver->id)
        ->whereIn('status', ['confirmed', 'ongoing', 'pending'])
        ->with(['vehicle', 'user', 'inspection'])
        ->latest()
        ->get();
@endphp

{{-- Driver Duty Banner --}}
<div class="relative overflow-hidden rounded-3xl mb-6 shadow-xl shadow-sky-900/10" style="background: linear-gradient(135deg, #09203f 0%, #1e3a8a 50%, #0284c7 100%);">
    <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-sky-400/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="relative px-6 py-7 md:px-8 md:py-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-sky-200 text-xs font-semibold mb-3">
                <span class="w-2.5 h-2.5 rounded-full {{ $driver->status == 'on_trip' ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400' }}"></span>
                <span>Status Tugas: <strong>{{ $driver->status == 'on_trip' ? 'Sedang Bertugas (On Trip)' : 'Siap / Standby' }}</strong></span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">
                Halo, {{ auth()->user()->name }}! 🚗
            </h1>
            <p class="text-sky-100/80 text-xs md:text-sm mt-1 max-w-lg">
                SIM: <span class="font-mono font-bold text-white">{{ $driver->sim_number ?? '-' }}</span> &bull; 
                No. HP: <span class="font-bold text-white">{{ $driver->phone ?? auth()->user()->phone ?? '-' }}</span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            @php
                $todayAtt = \App\Models\Attendance::where('driver_id', $driver->id)->where('date', now()->toDateString())->first();
            @endphp
            @if(!$todayAtt || $todayAtt->status === 'absent')
            <form method="POST" action="{{ route('attendance.check-in') }}">
                @csrf
                <button class="bg-emerald-500 text-white hover:bg-emerald-600 px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-lg flex items-center gap-2">
                    <i class="fas fa-fingerprint"></i> Absen Masuk
                </button>
            </form>
            @elseif($todayAtt->isCheckedin())
            <form method="POST" action="{{ route('attendance.check-out') }}">
                @csrf
                <button class="bg-amber-500 text-white hover:bg-amber-600 px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-lg flex items-center gap-2">
                    <i class="fas fa-sign-out-alt"></i> Absen Keluar
                </button>
            </form>
            @endif
            <a href="{{ route('bookings.index') }}" class="bg-white text-navy-900 hover:bg-sky-50 px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-lg flex items-center gap-2">
                <i class="fas fa-calendar-check text-sky-600"></i> Jadwal Perjalanan
            </a>
        </div>
    </div>
</div>

{{-- 4 Stat Widgets --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Perjalanan</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $totalTrips }}</h3>
                <span class="text-[11px] text-emerald-600 font-semibold mt-2 block">{{ $completedTrips }} trip selesai</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-600 flex items-center justify-center text-white text-lg shadow-lg shadow-sky-500/25">
                <i class="fas fa-route"></i>
            </div>
        </div>
    </div>

    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Trip Berjalan</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $ongoingTrips }}</h3>
                <span class="badge badge-yellow text-[10px] mt-2">{{ $driver->status == 'on_trip' ? 'On Duty' : 'Standby' }}</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-lg shadow-lg shadow-amber-500/25">
                <i class="fas fa-car-side"></i>
            </div>
        </div>
    </div>

    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Gaji Terakhir</p>
                <h3 class="text-xl font-black text-navy-800 mt-1">Rp {{ number_format($lastSalary?->total_salary ?? 0, 0, ',', '.') }}</h3>
                <span class="text-[10px] text-gray-400 mt-2 block">{{ $lastSalary?->period ? \Carbon\Carbon::parse($lastSalary->period)->translatedFormat('F Y') : 'Belum ada slip' }}</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-lg shadow-lg shadow-emerald-500/25">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
    </div>

    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Performa Driver</p>
                <div class="flex items-center gap-1 mt-1 text-amber-400 text-sm">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <span class="text-[11px] text-gray-500 font-semibold mt-2 block">Rating 5.0 (Sangat Baik)</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white text-lg shadow-lg shadow-purple-500/25">
                <i class="fas fa-award"></i>
            </div>
        </div>
    </div>

    <a href="{{ route('inspections.create') }}" class="stat-card glass-card rounded-2xl p-5 border border-emerald-100/50 shadow-sm hover:border-emerald-300 transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Inspeksi Saya</p>
                <h3 class="text-xl font-black {{ $totalInspections > 0 ? 'text-emerald-600' : 'text-navy-800' }} mt-1">{{ $totalInspections }}</h3>
                <span class="text-[11px] text-gray-500 mt-2 block">{{ $pendingPre + $pendingPost }} tugas menunggu</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-lg shadow-lg shadow-emerald-500/25">
                <i class="fas fa-clipboard-check"></i>
            </div>
        </div>
    </a>
</div>

{{-- Assigned Bookings / Trips --}}
<div class="glass-card rounded-2xl overflow-hidden shadow-sm border border-sky-100/50 mb-6">
    <div class="px-6 py-4 border-b border-sky-100/60 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-clipboard-list text-sky-500"></i> Tugas & Perjalanan Penugasan
            </h3>
            <p class="text-[11px] text-gray-400">Daftar perjalanan yang ditugaskan kepada Anda.</p>
        </div>
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($assignedBookings as $b)
        <div class="p-5 hover:bg-sky-50/30 transition flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0 shadow-sm border border-blue-100/60">
                    <i class="fas fa-car"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="font-bold text-navy-800 text-sm">{{ $b->vehicle?->name ?? 'Unit Kendaraan' }}</span>
                        <span class="font-mono text-[11px] bg-gray-100 px-2 py-0.5 rounded text-navy-700 font-semibold">{{ $b->vehicle?->license_plate ?? '-' }}</span>
                        @if($b->status == 'ongoing') <span class="badge badge-blue">Sedang Berjalan</span>
                        @elseif($b->status == 'confirmed') <span class="badge badge-teal">Terkonfirmasi</span>
                        @else <span class="badge badge-yellow">Pending</span>
                        @endif
                        <span class="badge {{ $b->with_driver ? 'badge-teal' : 'badge-gray' }}">{{ $b->with_driver ? 'Dengan Driver' : 'Lepas Kunci' }}</span>
                        @php
                            $ins = $b->inspection;
                            $insNeeded = $b->with_driver
                                && (($b->status == 'confirmed' && (!$ins || $ins->type !== 'pre_rental'))
                                || ($b->status == 'ongoing' && (!$ins || $ins->type !== 'post_rental')));
                        @endphp
                        @if($ins && $b->with_driver)
                            <span class="badge {{ $ins->type == 'pre_rental' ? 'badge-blue' : 'badge-green' }}">{{ $ins->getTypeLabel() }} Selesai</span>
                        @elseif($insNeeded)
                            <a href="{{ route('inspections.create', ['booking_id' => $b->id]) }}" class="badge badge-red hover:opacity-80 transition"><i class="fas fa-triangle-exclamation mr-1"></i> Perlu Inspeksi</a>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 flex items-center gap-2">
                        <span><i class="fas fa-user text-gray-400 mr-1"></i> Penyewa: <strong>{{ $b->user->name }}</strong> ({{ $b->user->phone ?? 'N/A' }})</span>
                    </p>
                    <p class="text-xs text-navy-600 mt-1 flex items-center gap-2">
                        <i class="fas fa-location-dot text-rose-500"></i>
                        <span>{{ $b->pickup_location ?? 'Penjemputan' }} &rarr; {{ $b->dropoff_location ?? 'Tujuan' }}</span>
                        <span class="text-gray-400">&bull; {{ $b->start_date ? \Carbon\Carbon::parse($b->start_date)->translatedFormat('d M Y H:i') : '-' }}</span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-end md:self-center">
                <a href="{{ route('bookings.show', $b) }}" class="btn-primary text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md shadow-sky-500/20 flex items-center gap-1.5">
                    <i class="fas fa-arrow-up-right-from-square"></i> Buka Detail
                </a>
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-gray-400">
            <i class="fas fa-calendar-check text-3xl text-gray-300 mb-2"></i>
            <p class="font-semibold text-navy-700">Tidak ada penugasan aktif saat ini</p>
            <p class="text-xs text-gray-400">Perjalanan baru yang ditugaskan akan otomatis muncul di sini.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Tugas Inspeksi (rental dengan driver) --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Butuh Inspeksi Awal --}}
    <div class="glass-card rounded-2xl overflow-hidden border border-sky-100/50 shadow-sm">
        <div class="px-6 py-4 border-b border-sky-100/60 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-circle-play text-sky-500"></i> Butuh Inspeksi Awal
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
                <i class="fas fa-flag-checkered text-amber-500"></i> Butuh Inspeksi Akhir
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
<div class="glass-card rounded-2xl overflow-hidden shadow-sm border border-sky-100/50 mb-6">
    <div class="px-6 py-4 border-b border-sky-100/60 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-emerald-500"></i> Inspeksi Terbaru Saya
            </h3>
            <p class="text-[11px] text-gray-400">Pemeriksaan yang Anda catat untuk rental dengan driver.</p>
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
                    <th class="py-3 px-6 text-left">Booking</th>
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
                    <td class="py-3.5 px-6 text-sky-600 font-medium">{{ $ins->booking?->booking_code ?? '-' }}</td>
                    <td class="py-3.5 px-6 text-center font-bold">{{ $ins->overall_condition }}/10</td>
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

@else
<div class="glass-card rounded-2xl p-12 text-center max-w-lg mx-auto my-8">
    <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-2xl mx-auto mb-4">
        <i class="fas fa-id-card"></i>
    </div>
    <h3 class="text-lg font-bold text-navy-800 mb-1">Profil Driver Belum Terdaftar</h3>
    <p class="text-xs text-gray-400">Akun Anda belum ditautkan ke data Driver sistem. Silakan hubungi Administrator untuk aktivasi.</p>
</div>
@endif
@endsection
