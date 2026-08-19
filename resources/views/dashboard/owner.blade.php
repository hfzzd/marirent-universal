@extends('layouts.dashboard')
@section('page-title', 'Beranda Owner')

@section('content')
@php
    $vehicleIds = auth()->user()->vehicles()->pluck('id');
@endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-sky-500/20"><i class="fas fa-car text-white"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Kendaraan</p>
                <p class="text-xl font-bold text-navy-800">{{ auth()->user()->vehicles()->count() }}</p>
            </div>
        </div>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-amber-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-amber-500/20"><i class="fas fa-id-card text-white"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Driver</p>
                <p class="text-xl font-bold text-navy-800">{{ auth()->user()->ownedDrivers()->count() }}</p>
            </div>
        </div>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-500/20"><i class="fas fa-clock text-white"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Booking Pending</p>
                <p class="text-xl font-bold text-navy-800">{{ \App\Models\Booking::whereIn('vehicle_id', $vehicleIds)->where('status','pending')->count() }}</p>
            </div>
        </div>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-500/20"><i class="fas fa-wallet text-white"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Bulan Ini</p>
                <p class="text-lg font-bold text-navy-800">Rp {{ number_format(\App\Models\Invoice::where('owner_id',auth()->id())->where('status','paid')->whereMonth('created_at',now()->month)->sum('total_amount'),0,',','.') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="glass-card rounded-2xl">
        <div class="px-5 py-4 border-b border-sky-100/50 flex items-center justify-between">
            <h3 class="text-[13px] font-semibold text-navy-800">Booking Terbaru</h3>
            <a href="{{ route('bookings.index') }}" class="text-[11px] font-medium text-sky-600">Lihat Semua</a>
        </div>
        <div class="p-4 space-y-2">
            @forelse(\App\Models\Booking::whereIn('vehicle_id', $vehicleIds)->with(['user','vehicle'])->latest()->limit(5)->get() as $b)
            <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-sky-50 rounded-lg flex items-center justify-center"><i class="fas fa-car text-sky-500 text-xs"></i></div>
                    <div>
                        <p class="font-medium text-navy-800 text-[13px]">{{ $b->vehicle->name ?? ($b->category->name ?? '-') }}</p>
                        <p class="text-[11px] text-gray-400">{{ $b->booking_code }} &middot; {{ $b->user->name }}</p>
                    </div>
                </div>
                <div class="text-right">
                    @if($b->status == 'pending') <span class="badge badge-blue">{{ ucfirst($b->status) }}</span>
                    @elseif($b->status == 'confirmed') <span class="badge badge-teal">{{ ucfirst($b->status) }}</span>
                    @elseif($b->status == 'ongoing') <span class="badge badge-yellow">{{ ucfirst($b->status) }}</span>
                    @elseif($b->status == 'completed') <span class="badge badge-green">{{ ucfirst($b->status) }}</span>
                    @else <span class="badge badge-gray">{{ ucfirst($b->status) }}</span>
                    @endif
                    <p class="text-[12px] font-medium text-navy-700 mt-1">Rp {{ number_format($b->final_price,0,',','.') }}</p>
                </div>
            </div>
            @empty
            <p class="text-center text-gray-300 py-8 text-[13px]">Belum ada booking</p>
            @endforelse
        </div>
    </div>

    <div class="glass-card rounded-2xl">
        <div class="px-5 py-4 border-b border-sky-100/50 flex items-center justify-between">
            <h3 class="text-[13px] font-semibold text-navy-800">Status Kendaraan</h3>
            <a href="{{ route('vehicles.index') }}" class="text-[11px] font-medium text-sky-600">Kelola</a>
        </div>
        <div class="p-4 space-y-2">
            @foreach(auth()->user()->vehicles()->get() as $v)
            <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                <div class="flex items-center gap-3">
                    @php $cat = $v->category->slug ?? ''; @endphp
                    <div class="w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center">
                        @if($cat == 'mobil') <i class="fas fa-car text-blue-500 text-xs"></i>
                        @elseif($cat == 'motor') <i class="fas fa-motorcycle text-amber-500 text-xs"></i>
                        @elseif($cat == 'sewa-kamera') <i class="fas fa-camera text-violet-500 text-xs"></i>
                        @else <i class="fas fa-campground text-emerald-500 text-xs"></i>
                        @endif
                    </div>
                    <div>
                        <p class="font-medium text-navy-800 text-[13px]">{{ $v->name }}</p>
                        <p class="text-[11px] text-gray-400">{{ $v->license_plate }}</p>
                    </div>
                </div>
                @if($v->status == 'available') <span class="badge badge-green">Tersedia</span>
                @elseif($v->status == 'rented') <span class="badge badge-yellow">Disewa</span>
                @elseif($v->status == 'maintenance') <span class="badge badge-red">Maintenance</span>
                @else <span class="badge badge-blue">Reservasi</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
