@extends('layouts.dashboard')
@section('page-title', 'Beranda')
@section('content')
@php
    $user = auth()->user();
    $userBookings = $user->bookings();
    $totalBookings = $userBookings->count();
    $activeBookings = $userBookings->whereIn('status',['confirmed','ongoing'])->count();
    $pendingBookings = $userBookings->where('status','pending')->count();
    $completedBookings = $userBookings->where('status','completed')->count();
    $totalSpent = $userBookings->where('status','completed')->sum('final_price');
    $recentBookings = $userBookings->with(['vehicle','category'])->latest()->limit(3)->get();
@endphp

{{-- Greeting --}}
<div class="mb-5">
    <h2 class="text-[16px] font-bold text-navy-800">Halo, {{ $user->name }}!</h2>
    <p class="text-[12px] text-gray-400 mt-0.5">Selamat datang kembali di MariRent</p>
</div>

{{-- Stat Ringkas --}}
<div class="grid grid-cols-3 gap-3 mb-5">
    <a href="{{ route('bookings.index', ['status' => 'pending']) }}" class="glass-card rounded-2xl p-4 text-center hover:shadow-md transition-all">
        <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-clock text-amber-500"></i>
        </div>
        <p class="text-[20px] font-bold text-navy-800">{{ $pendingBookings }}</p>
        <p class="text-[10px] text-gray-400 font-medium">Menunggu</p>
    </a>
    <a href="{{ route('bookings.index', ['status' => 'confirmed']) }}" class="glass-card rounded-2xl p-4 text-center hover:shadow-md transition-all">
        <div class="w-10 h-10 bg-sky-50 rounded-xl flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-check-circle text-sky-500"></i>
        </div>
        <p class="text-[20px] font-bold text-navy-800">{{ $activeBookings }}</p>
        <p class="text-[10px] text-gray-400 font-medium">Aktif</p>
    </a>
    <a href="{{ route('bookings.index', ['status' => 'completed']) }}" class="glass-card rounded-2xl p-4 text-center hover:shadow-md transition-all">
        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-check-double text-emerald-500"></i>
        </div>
        <p class="text-[20px] font-bold text-navy-800">{{ $completedBookings }}</p>
        <p class="text-[10px] text-gray-400 font-medium">Selesai</p>
    </a>
</div>

{{-- Menu Cepat --}}
<div class="grid grid-cols-2 gap-3 mb-5">
    <a href="{{ route('home') }}" class="glass-card rounded-2xl p-4 flex items-center gap-3 hover:shadow-md hover:bg-sky-50/50 transition-all group">
        <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-500 rounded-xl flex items-center justify-center shadow-md shadow-sky-500/20 group-hover:scale-105 transition-transform">
            <i class="fas fa-plus text-white text-sm"></i>
        </div>
        <div>
            <p class="text-[13px] font-semibold text-navy-800">Booking Baru</p>
            <p class="text-[10px] text-gray-400">Sewa sekarang</p>
        </div>
    </a>
    <a href="{{ route('invoices.index') }}" class="glass-card rounded-2xl p-4 flex items-center gap-3 hover:shadow-md hover:bg-sky-50/50 transition-all group">
        <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-xl flex items-center justify-center shadow-md shadow-emerald-500/20 group-hover:scale-105 transition-transform">
            <i class="fas fa-file-invoice-dollar text-white text-sm"></i>
        </div>
        <div>
            <p class="text-[13px] font-semibold text-navy-800">Invoice</p>
            <p class="text-[10px] text-gray-400">Lihat tagihan</p>
        </div>
    </a>
</div>

{{-- Booking Terakhir --}}
<div class="glass-card rounded-2xl">
    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-[13px] font-semibold text-navy-800">Booking Terakhir</h3>
        <a href="{{ route('bookings.index') }}" class="text-sky-500 text-[11px] font-medium hover:text-sky-600">Lihat Semua</a>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($recentBookings as $b)
        <a href="{{ route('bookings.show', $b) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-sky-50/30 transition-colors">
            @php $cat = $b->category->slug ?? ''; @endphp
            <div class="w-10 h-10 {{ $cat == 'sewa-kamera' ? 'bg-violet-50' : ($cat == 'sewa-tenda' ? 'bg-emerald-50' : 'bg-sky-50') }} rounded-xl flex items-center justify-center flex-shrink-0">
                @if($cat == 'sewa-kamera') <i class="fas fa-camera text-violet-400 text-xs"></i>
                @elseif($cat == 'sewa-tenda') <i class="fas fa-campground text-emerald-400 text-xs"></i>
                @elseif($cat == 'sewa-hp') <i class="fas fa-mobile-alt text-blue-400 text-xs"></i>
                @else <i class="fas fa-car text-sky-400 text-xs"></i>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[13px] font-medium text-navy-800 truncate">{{ $b->vehicle->name ?? ($b->category->name ?? '-') }}</p>
                <p class="text-[11px] text-gray-400">{{ $b->start_date->format('d M') }} - {{ $b->end_date->format('d M Y') }}</p>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-[12px] font-semibold text-navy-700">Rp {{ number_format($b->final_price,0,',','.') }}</p>
                @if($b->status == 'pending') <span class="badge badge-blue text-[10px]">Menunggu</span>
                @elseif($b->status == 'confirmed') <span class="badge badge-teal text-[10px]">Aktif</span>
                @elseif($b->status == 'ongoing') <span class="badge badge-yellow text-[10px]">Jalan</span>
                @elseif($b->status == 'completed') <span class="badge badge-green text-[10px]">Selesai</span>
                @elseif($b->status == 'cancelled') <span class="badge badge-red text-[10px]">Batal</span>
                @endif
            </div>
        </a>
        @empty
        <div class="py-10 text-center">
            <div class="w-14 h-14 bg-sky-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-calendar-plus text-sky-300 text-xl"></i>
            </div>
            <p class="text-[13px] font-medium text-navy-700">Belum ada booking</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Yuk mulai sewa!</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
