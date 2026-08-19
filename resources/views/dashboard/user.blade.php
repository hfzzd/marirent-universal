@extends('layouts.dashboard')
@section('page-title', 'Beranda Saya')

@section('content')
@php
    $userBookings = auth()->user()->bookings();
    $totalBookings = $userBookings->count();
    $activeBookings = $userBookings->whereIn('status',['confirmed','ongoing'])->count();
    $pendingBookings = $userBookings->where('status','pending')->count();
    $completedBookings = $userBookings->where('status','completed')->count();
    $totalSpent = $userBookings->where('status','completed')->sum('final_price');
    $recentBookings = $userBookings->with(['vehicle','category'])->latest()->limit(5)->get();
@endphp

<div class="mb-6">
    <h2 class="text-[15px] font-bold text-navy-800">Selamat datang, {{ auth()->user()->name }}! 👋</h2>
    <p class="text-[12px] text-gray-400 mt-1">Kelola booking dan aktivitas rental Anda di sini</p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="glass-card rounded-2xl p-5 relative overflow-hidden group hover:shadow-lg hover:shadow-sky-500/10 transition-all duration-300">
        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-400/10 to-blue-500/5 rounded-bl-[40px]"></div>
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-gradient-to-br from-blue-400 to-blue-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-500/20"><i class="fas fa-calendar-check text-white text-sm"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Total Booking</p>
                <p class="text-xl font-bold text-navy-800">{{ $totalBookings }}</p>
            </div>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-5 relative overflow-hidden group hover:shadow-lg hover:shadow-sky-500/10 transition-all duration-300">
        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-sky-400/10 to-sky-500/5 rounded-bl-[40px]"></div>
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-gradient-to-br from-sky-400 to-sky-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-sky-500/20"><i class="fas fa-play-circle text-white text-sm"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Booking Aktif</p>
                <p class="text-xl font-bold text-navy-800">{{ $activeBookings }}</p>
            </div>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-5 relative overflow-hidden group hover:shadow-lg hover:shadow-sky-500/10 transition-all duration-300">
        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-400/10 to-amber-500/5 rounded-bl-[40px]"></div>
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-gradient-to-br from-amber-400 to-amber-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-amber-500/20"><i class="fas fa-clock text-white text-sm"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Menunggu</p>
                <p class="text-xl font-bold text-navy-800">{{ $pendingBookings }}</p>
            </div>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-5 relative overflow-hidden group hover:shadow-lg hover:shadow-sky-500/10 transition-all duration-300">
        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-400/10 to-emerald-500/5 rounded-bl-[40px]"></div>
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-500/20"><i class="fas fa-wallet text-white text-sm"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Total Pengeluaran</p>
                <p class="text-lg font-bold text-navy-800">Rp {{ number_format($totalSpent,0,',','.') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="glass-card rounded-2xl">
            <div class="px-5 py-4 border-b border-sky-100/50 flex items-center justify-between">
                <h3 class="text-[13px] font-semibold text-navy-800"><i class="fas fa-history text-sky-500 mr-2"></i>Riwayat Booking Terbaru</h3>
                <a href="{{ route('bookings.index') }}" class="text-sky-500 text-[12px] font-medium hover:text-sky-600 transition">Lihat Semua <i class="fas fa-arrow-right ml-1"></i></a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-gray-50">
                            <th class="text-left py-2.5 px-5 text-gray-400 font-medium text-[11px] uppercase tracking-wider">Kode</th>
                            <th class="text-left py-2.5 px-5 text-gray-400 font-medium text-[11px] uppercase tracking-wider">Barang</th>
                            <th class="text-left py-2.5 px-5 text-gray-400 font-medium text-[11px] uppercase tracking-wider">Tanggal</th>
                            <th class="text-center py-2.5 px-5 text-gray-400 font-medium text-[11px] uppercase tracking-wider">Status</th>
                            <th class="text-right py-2.5 px-5 text-gray-400 font-medium text-[11px] uppercase tracking-wider">Total</th>
                            <th class="text-left py-2.5 px-5 text-gray-400 font-medium text-[11px] uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $b)
                        <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30 transition-colors">
                            <td class="py-3 px-5">
                                <span class="font-medium text-sky-600">{{ $b->booking_code }}</span>
                            </td>
                            <td class="py-3 px-5">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 {{ $b->category->slug == 'sewa-kamera' ? 'bg-violet-50' : ($b->category->slug == 'sewa-tenda' ? 'bg-emerald-50' : 'bg-sky-50') }} rounded-lg flex items-center justify-center flex-shrink-0">
                                        @if($b->category->slug == 'sewa-kamera') <i class="fas fa-camera text-violet-400 text-[10px]"></i>
                                        @elseif($b->category->slug == 'sewa-tenda') <i class="fas fa-campground text-emerald-400 text-[10px]"></i>
                                        @elseif($b->category->slug == 'sewa-hp') <i class="fas fa-mobile-alt text-blue-400 text-[10px]"></i>
                                        @else <i class="fas fa-car text-sky-400 text-[10px]"></i>
                                        @endif
                                    </div>
                                    <span class="text-navy-700">{{ $b->vehicle->name ?? ($b->category->name ?? '-') }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-5 text-[12px] text-navy-500">{{ $b->start_date->format('d M') }} - {{ $b->end_date->format('d M Y') }}</td>
                            <td class="py-3 px-5 text-center">
                                @if($b->status == 'pending') <span class="badge badge-blue">{{ ucfirst($b->status) }}</span>
                                @elseif($b->status == 'confirmed') <span class="badge badge-teal">{{ ucfirst($b->status) }}</span>
                                @elseif($b->status == 'ongoing') <span class="badge badge-yellow">{{ ucfirst($b->status) }}</span>
                                @elseif($b->status == 'completed') <span class="badge badge-green">Selesai</span>
                                @else <span class="badge badge-gray">{{ ucfirst($b->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-5 text-right font-medium text-navy-700">Rp {{ number_format($b->final_price,0,',','.') }}</td>
                            <td class="py-3 px-5">
                                <a href="{{ route('bookings.show', $b) }}" class="text-sky-600 text-[12px] font-medium hover:text-sky-700 transition">
                                    <i class="fas fa-eye mr-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 bg-sky-50 rounded-2xl flex items-center justify-center">
                                        <i class="fas fa-calendar-plus text-sky-300 text-2xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-medium text-navy-700">Belum ada booking</p>
                                        <p class="text-[12px] text-gray-400 mt-1">Mulai sewa kendaraan atau barang sekarang!</p>
                                    </div>
                                    <a href="{{ route('home') }}" class="btn-primary text-white px-5 py-2 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition mt-1">
                                        <i class="fas fa-plus mr-1.5"></i> Booking Baru
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-[13px] font-semibold text-navy-800 mb-4"><i class="fas fa-user-circle text-sky-500 mr-2"></i>Profil Saya</h3>
            <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-50">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-sky-500/20 overflow-hidden">
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-user text-white text-xl"></i>
                    @endif
                </div>
                <div>
                    <p class="font-bold text-navy-800 text-[14px]">{{ auth()->user()->name }}</p>
                    <p class="text-[12px] text-gray-400">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <div class="space-y-2.5 text-[12px]">
                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Telepon</span>
                    <span class="font-medium text-navy-700">{{ auth()->user()->phone ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-400">Member Sejak</span>
                    <span class="font-medium text-navy-700">{{ auth()->user()->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-[13px] font-semibold text-navy-800 mb-4"><i class="fas fa-chart-pie text-sky-500 mr-2"></i>Ringkasan</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-2.5 bg-blue-50/50 rounded-xl">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                        <span class="text-[12px] text-navy-600">Selesai</span>
                    </div>
                    <span class="text-[13px] font-bold text-navy-800">{{ $completedBookings }}</span>
                </div>
                <div class="flex items-center justify-between p-2.5 bg-sky-50/50 rounded-xl">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-sky-400 rounded-full"></div>
                        <span class="text-[12px] text-navy-600">Aktif</span>
                    </div>
                    <span class="text-[13px] font-bold text-navy-800">{{ $activeBookings }}</span>
                </div>
                <div class="flex items-center justify-between p-2.5 bg-amber-50/50 rounded-xl">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-amber-400 rounded-full"></div>
                        <span class="text-[12px] text-navy-600">Menunggu</span>
                    </div>
                    <span class="text-[13px] font-bold text-navy-800">{{ $pendingBookings }}</span>
                </div>
            </div>
        </div>

        <a href="{{ route('home') }}" class="glass-card rounded-2xl p-5 flex items-center gap-3 group hover:bg-sky-50/50 transition-all duration-300 block">
            <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-sky-500/20 group-hover:scale-105 transition-transform">
                <i class="fas fa-plus text-white text-sm"></i>
            </div>
            <div>
                <p class="text-[13px] font-semibold text-navy-800">Booking Baru</p>
                <p class="text-[11px] text-gray-400">Sewa kendaraan atau barang</p>
            </div>
        </a>
    </div>
</div>
@endsection
