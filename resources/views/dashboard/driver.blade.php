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

    $needInspectionCount = $assignedBookings->filter(function ($b) {
        if ($b->status === 'confirmed') {
            return !$b->inspection || $b->inspection->type !== 'pre_rental';
        }
        if ($b->status === 'ongoing') {
            return !$b->inspection || $b->inspection->type !== 'post_rental';
        }
        return false;
    })->count();
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
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Inspeksi Unit</p>
                <h3 class="text-xl font-black {{ $needInspectionCount > 0 ? 'text-emerald-600' : 'text-navy-800' }} mt-1">{{ $needInspectionCount }} Tugas</h3>
                <span class="text-[11px] text-gray-500 mt-2 block">Cek awal & akhir sewa</span>
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
                        <span class="font-bold text-navy-800 text-sm">{{ $b->vehicle->name ?? 'Unit Kendaraan' }}</span>
                        <span class="font-mono text-[11px] bg-gray-100 px-2 py-0.5 rounded text-navy-700 font-semibold">{{ $b->vehicle->license_plate ?? '-' }}</span>
                        @if($b->status == 'ongoing') <span class="badge badge-blue">Sedang Berjalan</span>
                        @elseif($b->status == 'confirmed') <span class="badge badge-teal">Terkonfirmasi</span>
                        @else <span class="badge badge-yellow">Pending</span>
                        @endif
                        @php
                            $ins = $b->inspection;
                            $insNeeded = ($b->status == 'confirmed' && (!$ins || $ins->type !== 'pre_rental'))
                                || ($b->status == 'ongoing' && (!$ins || $ins->type !== 'post_rental'));
                        @endphp
                        @if($ins)
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
