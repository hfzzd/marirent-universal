@extends('layouts.dashboard')
@section('page-title', 'Beranda Driver')

@section('content')
@php
    $driver = \App\Models\Driver::where('user_id', auth()->id())->first();
@endphp
@if($driver)
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 {{ $driver->status == 'on_trip' ? 'bg-gradient-to-br from-amber-400 to-amber-500' : 'bg-gradient-to-br from-emerald-400 to-emerald-500' }} rounded-xl flex items-center justify-center flex-shrink-0 {{ $driver->status == 'on_trip' ? 'shadow-lg shadow-amber-500/20' : 'shadow-lg shadow-emerald-500/20' }}">
                <i class="fas fa-{{ $driver->status == 'on_trip' ? 'car' : 'check-circle' }} text-white"></i>
            </div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Status</p>
                <p class="text-lg font-bold text-navy-800 capitalize">{{ $driver->status == 'on_trip' ? 'Sedang Bertugas' : 'Siap' }}</p>
            </div>
        </div>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-500/20"><i class="fas fa-route text-white"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Total Perjalanan</p>
                <p class="text-xl font-bold text-navy-800">{{ \App\Models\Booking::where('driver_id', $driver->id)->count() }}</p>
            </div>
        </div>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-sky-500/20"><i class="fas fa-play text-white"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Sedang Berjalan</p>
                <p class="text-xl font-bold text-navy-800">{{ \App\Models\Booking::where('driver_id', $driver->id)->where('status','ongoing')->count() }}</p>
            </div>
        </div>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-500/20"><i class="fas fa-money-bill-wave text-white"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Gaji Terakhir</p>
                <p class="text-lg font-bold text-navy-800">Rp {{ number_format($driver->salaries()->latest()->first()?->total_salary ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="glass-card rounded-2xl">
    <div class="px-5 py-4 border-b border-sky-100/50">
        <h3 class="text-[13px] font-semibold text-navy-800">Perjalanan Mendatang</h3>
    </div>
    <div class="p-4 space-y-2">
        @forelse(\App\Models\Booking::where('driver_id', $driver->id)->whereIn('status',['confirmed','ongoing'])->with(['vehicle','user'])->get() as $b)
        <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-sky-50 rounded-lg flex items-center justify-center"><i class="fas fa-car text-sky-500 text-xs"></i></div>
                <div>
                    <p class="font-medium text-navy-800 text-[13px]">{{ $b->vehicle->name ?? ($b->category->name ?? '-') }} &rarr; {{ $b->dropoff_location ?? 'N/A' }}</p>
                    <p class="text-[11px] text-gray-400">{{ $b->user->name }} &middot; {{ $b->start_date->format('d M Y H:i') }}</p>
                </div>
            </div>
            @if($b->status == 'confirmed') <span class="badge badge-teal">{{ ucfirst($b->status) }}</span>
            @else <span class="badge badge-yellow">{{ ucfirst($b->status) }}</span>
            @endif
        </div>
        @empty
        <p class="text-center text-gray-300 py-8 text-[13px]">Tidak ada perjalanan aktif</p>
        @endforelse
    </div>
</div>
@else
<div class="glass-card rounded-2xl p-12 text-center">
    <i class="fas fa-id-card text-gray-200 text-5xl mb-4"></i>
    <h3 class="text-lg font-bold text-navy-800 mb-2">Profil Driver Belum Terdaftar</h3>
    <p class="text-[13px] text-gray-400">Hubungi admin untuk mendaftarkan akun driver Anda.</p>
</div>
@endif
@endsection
