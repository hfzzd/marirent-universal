@extends('layouts.dashboard')
@section('page-title', 'Beranda Superadmin')

@section('content')
@php
    $totalUsers = \App\Models\User::count();
    $totalVehicles = \App\Models\Vehicle::count();
    $totalBookings = \App\Models\Booking::count();
    $pendingBookings = \App\Models\Booking::where('status','pending')->count();
    $ongoingBookings = \App\Models\Booking::where('status','ongoing')->count();
    $completedBookings = \App\Models\Booking::where('status','completed')->count();
    $cancelledBookings = \App\Models\Booking::where('status','cancelled')->count();
    
    $revenueThisMonth = \App\Models\Invoice::where('status','paid')->whereMonth('created_at', now()->month)->sum('total_amount');
    $revenueLastMonth = \App\Models\Invoice::where('status','paid')->whereMonth('created_at', now()->subMonth()->month)->sum('total_amount');
    $revenueChange = $revenueLastMonth > 0 ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100) : 0;
    $totalRevenue = \App\Models\Invoice::where('status','paid')->sum('total_amount');
    
    $activeDrivers = \App\Models\Driver::where('status','on_trip')->count();
    $totalDrivers = \App\Models\Driver::count();

    $mobilCount = \App\Models\Vehicle::whereHas('category', fn($q) => $q->where('slug','mobil'))->count();
    $motorCount = \App\Models\Vehicle::whereHas('category', fn($q) => $q->where('slug','motor'))->count();
    $kameraCount = \App\Models\Camera::count();
    $tendaCount = \App\Models\CampingEquipment::count();
    $hpCount = \App\Models\Phone::count();
    $totalAllProducts = $mobilCount + $motorCount + $kameraCount + $tendaCount + $hpCount;

    $availableVehicles = \App\Models\Vehicle::where('status', 'available')->count();
    $rentedVehicles = \App\Models\Vehicle::where('status', 'rented')->count();
    $maintenanceVehicles = \App\Models\Vehicle::where('status', 'maintenance')->count();

    $monthlyRevenue = [];
    $monthlyBookings = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i);
        $monthlyRevenue[] = [
            'label' => $month->translatedFormat('M Y'),
            'value' => (int) \App\Models\Invoice::where('status','paid')->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum('total_amount')
        ];
        $monthlyBookings[] = (int) \App\Models\Booking::whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count();
    }

    $recentBookings = \App\Models\Booking::with(['user', 'vehicle', 'category'])->latest()->limit(6)->get();
    $driversOnTrip = \App\Models\Driver::with(['user', 'bookings' => fn($q) => $q->where('status','ongoing')->with('vehicle')])->where('status', 'on_trip')->limit(4)->get();

    // Jadwal pembayaran terdekat
    $totalInspectors = \App\Models\User::where('role', 'inspector')->count();
    $dueToday = \App\Models\Booking::whereIn('payment_status', ['unpaid', 'partial'])
        ->whereDate('payment_due_date', today())->count();
    $overduePayments = \App\Models\Booking::whereIn('payment_status', ['unpaid', 'partial'])
        ->whereNotNull('payment_due_date')->whereDate('payment_due_date', '<', today())->count();
    $upcomingPayments = \App\Models\Booking::with(['user', 'vehicle', 'category'])
        ->whereIn('payment_status', ['unpaid', 'partial'])
        ->whereNotNull('payment_due_date')
        ->whereDate('payment_due_date', '>=', today())
        ->orderBy('payment_due_date')
        ->limit(5)
        ->get();
@endphp

{{-- DAdmin Hero Welcome Banner --}}
<div class="relative overflow-hidden rounded-3xl mb-6 shadow-xl shadow-sky-900/10" style="background: linear-gradient(135deg, #0b1e36 0%, #0f3259 45%, #0284c7 100%);">
    <div class="absolute -right-10 -bottom-10 w-80 h-80 bg-sky-400/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute right-1/3 -top-12 w-60 h-60 bg-sky-300/10 rounded-full blur-2xl pointer-events-none"></div>
    <div class="relative px-6 py-7 md:px-8 md:py-8 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-sky-200 text-xs font-semibold mb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Sistem Operasional Aktif</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">
                Selamat Datang, {{ auth()->user()->name }}! 👋
            </h1>
            <p class="text-sky-100/80 text-xs md:text-sm mt-1 max-w-xl">
                Pantau performa rental armada universal, pendapatan harian, dan status penugasan secara real-time.
            </p>
            <div class="flex flex-wrap items-center gap-3 mt-4">
                <div class="flex items-center gap-2 bg-black/20 backdrop-blur-md rounded-xl px-3.5 py-1.5 border border-white/10 text-white text-xs">
                    <i class="fas fa-id-card text-emerald-400"></i>
                    <span><strong>{{ $activeDrivers }}</strong> / {{ $totalDrivers }} Driver Bertugas</span>
                </div>
                <div class="flex items-center gap-2 bg-black/20 backdrop-blur-md rounded-xl px-3.5 py-1.5 border border-white/10 text-white text-xs">
                    <i class="fas fa-clock text-amber-400"></i>
                    <span><strong>{{ $pendingBookings }}</strong> Menunggu Konfirmasi</span>
                </div>
                <div class="flex items-center gap-2 bg-black/20 backdrop-blur-md rounded-xl px-3.5 py-1.5 border border-white/10 text-white text-xs">
                    <i class="fas fa-car-side text-sky-300"></i>
                    <span><strong>{{ $ongoingBookings }}</strong> Sedang Berjalan</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3 w-full lg:w-auto">
            <a href="{{ route('superadmin.monitoring') }}" class="flex-1 lg:flex-initial bg-white/15 hover:bg-white/25 text-white border border-white/20 px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 backdrop-blur-md">
                <i class="fas fa-chart-line"></i> Monitoring
            </a>
            <a href="{{ route('superadmin.finance') }}" class="flex-1 lg:flex-initial bg-white text-navy-900 hover:bg-sky-50 px-5 py-2.5 rounded-xl text-xs font-extrabold transition shadow-lg flex items-center justify-center gap-2">
                <i class="fas fa-wallet text-sky-600"></i> Finance Center
            </a>
        </div>
    </div>
</div>

{{-- 4 DAdmin Mini Stat Widgets --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Widget 1: Total Armada & Produk --}}
    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Unit Produk</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $totalAllProducts }}</h3>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-gray-500 font-medium">
                    <span class="text-blue-600 font-bold">{{ $mobilCount }} Mobil</span> &bull; 
                    <span class="text-amber-600 font-bold">{{ $motorCount }} Motor</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-600 flex items-center justify-center text-white text-lg shadow-lg shadow-sky-500/25">
                <i class="fas fa-boxes-stacked"></i>
            </div>
        </div>
    </div>

    {{-- Widget 2: Booking Berjalan --}}
    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Booking Aktif</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $ongoingBookings }}</h3>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="badge badge-yellow text-[10px]">{{ $pendingBookings }} Pending</span>
                    <span class="badge badge-green text-[10px]">{{ $completedBookings }} Sukses</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-lg shadow-lg shadow-amber-500/25">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>
    </div>

    {{-- Widget 3: Pendapatan Bulan Ini --}}
    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Omset Bulan Ini</p>
                <h3 class="text-xl font-black text-navy-800 mt-1">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</h3>
                <div class="flex items-center gap-1 mt-2 text-[11px]">
                    @if($revenueChange >= 0)
                    <span class="inline-flex items-center text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded text-[10px]">
                        <i class="fas fa-arrow-up text-[9px] mr-1"></i>+{{ $revenueChange }}%
                    </span>
                    @else
                    <span class="inline-flex items-center text-red-500 font-bold bg-red-50 px-1.5 py-0.5 rounded text-[10px]">
                        <i class="fas fa-arrow-down text-[9px] mr-1"></i>{{ $revenueChange }}%
                    </span>
                    @endif
                    <span class="text-gray-400">vs bln lalu</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-lg shadow-lg shadow-emerald-500/25">
                <i class="fas fa-wallet"></i>
            </div>
        </div>
    </div>

    {{-- Widget 4: Pelanggan & User --}}
    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Pengguna</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $totalUsers }}</h3>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-gray-500 font-medium">
                    <span class="text-purple-600 font-bold">{{ $totalDrivers }} Driver</span> &bull; 
                    <span>{{ $totalUsers - $totalDrivers }} Klien</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white text-lg shadow-lg shadow-purple-500/25">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
</div>

{{-- Main Analytics Row (DAdmin Style Charts) --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Left Chart (2 Cols): Revenue & Rental Volume Trend --}}
    <div class="lg:col-span-2 glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-5">
            <div>
                <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                    <i class="fas fa-chart-area text-sky-500"></i> Tren Pendapatan 6 Bulan Terakhir
                </h3>
                <p class="text-[11px] text-gray-400 mt-0.5">Ringkasan pendapatan invoice lunas per bulan.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1.5 text-xs text-navy-700 font-semibold">
                    <span class="w-3 h-3 rounded-full bg-sky-500"></span> Pendapatan
                </div>
            </div>
        </div>
        <div class="relative w-full" style="height: 250px;">
            <canvas id="dadminRevenueChart"></canvas>
        </div>
    </div>

    {{-- Right Chart (1 Col): Category Distribution Donut --}}
    <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm flex flex-col justify-between">
        <div>
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2 mb-1">
                <i class="fas fa-pie-chart text-purple-500"></i> Distribusi Kategori Produk
            </h3>
            <p class="text-[11px] text-gray-400 mb-4">Proporsi seluruh jenis inventaris sewa.</p>
            <div class="relative flex items-center justify-center my-2" style="height: 170px;">
                <canvas id="dadminCategoryChart"></canvas>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 pt-3 border-t border-gray-100 text-xs">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                <span class="text-gray-500 text-[11px]">Mobil ({{ $mobilCount }})</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span class="text-gray-500 text-[11px]">Motor ({{ $motorCount }})</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                <span class="text-gray-500 text-[11px]">Kamera ({{ $kameraCount }})</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-gray-500 text-[11px]">Camping ({{ $tendaCount }})</span>
            </div>
        </div>
    </div>
</div>

{{-- Secondary Row: Payment Scheduler, Fleet Status & Quick Actions --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Payment Scheduler Mini --}}
    <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm flex flex-col">
        <div class="flex items-center justify-between mb-1">
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-calendar-days text-orange-500"></i> Jadwal Pembayaran
            </h3>
            @if($overduePayments > 0) <span class="badge badge-red">{{ $overduePayments }} Telat</span> @endif
        </div>
        <p class="text-[11px] text-gray-400 mb-4">Tagihan jatuh tempo terdekat.</p>

        <div class="grid grid-cols-2 gap-2 mb-4">
            <a href="{{ route('superadmin.monitoring') }}" class="bg-red-50 hover:bg-red-100 rounded-xl p-3 text-center transition">
                <p class="text-lg font-black text-red-600">{{ $dueToday }}</p>
                <p class="text-[10px] font-semibold text-red-500 uppercase">Jatuh Tempo Hari Ini</p>
            </a>
            <a href="{{ route('superadmin.monitoring') }}" class="bg-amber-50 hover:bg-amber-100 rounded-xl p-3 text-center transition">
                <p class="text-lg font-black text-amber-600">{{ $overduePayments }}</p>
                <p class="text-[10px] font-semibold text-amber-600 uppercase">Terlambat Bayar</p>
            </a>
        </div>

        <div class="space-y-2.5 flex-1">
            @forelse($upcomingPayments as $p)
            <div class="flex items-center justify-between gap-2 bg-sky-50/50 rounded-xl px-3 py-2">
                <div class="min-w-0">
                    <p class="text-[12px] font-bold text-navy-800 truncate">{{ $p->vehicle?->name ?? ($p->category?->name ?? 'Unit Sewa') }}</p>
                    <p class="text-[10px] text-gray-400 truncate">{{ $p->booking_code }} &bull; {{ \Carbon\Carbon::parse($p->payment_due_date)->translatedFormat('d M Y') }}</p>
                </div>
                <span class="text-[11px] font-bold text-navy-700 whitespace-nowrap">Rp {{ number_format($p->final_price, 0, ',', '.') }}</span>
            </div>
            @empty
            <div class="text-center py-4 text-gray-300 text-xs">Belum ada tagihan terjadwal.</div>
            @endforelse
        </div>

        <a href="{{ route('superadmin.monitoring') }}" class="mt-4 pt-3 border-t border-gray-100 text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center justify-between">
            Buka Scheduler Pembayaran <i class="fas fa-arrow-right text-[10px]"></i>
        </a>
    </div>

    {{-- Fleet Availability Tracker --}}
    <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm">
        <h3 class="text-sm font-extrabold text-navy-800 mb-1 flex items-center gap-2">
            <i class="fas fa-tachometer-alt text-teal-500"></i> Status Ketersediaan Kendaraan
        </h3>
        <p class="text-[11px] text-gray-400 mb-4">Kondisi operasional armada Mobil & Motor.</p>
        
        @php
            $totalVehicleUnits = max(1, $totalVehicles);
            $availPct = round(($availableVehicles / $totalVehicleUnits) * 100);
            $rentedPct = round(($rentedVehicles / $totalVehicleUnits) * 100);
            $maintPct = round(($maintenanceVehicles / $totalVehicleUnits) * 100);
        @endphp

        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-emerald-700 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tersedia</span>
                    <span class="text-navy-800">{{ $availableVehicles }} Unit ({{ $availPct }}%)</span>
                </div>
                <div class="w-full h-2 rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full" style="width: {{ $availPct }}%"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-amber-700 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Sedang Disewa</span>
                    <span class="text-navy-800">{{ $rentedVehicles }} Unit ({{ $rentedPct }}%)</span>
                </div>
                <div class="w-full h-2 rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-amber-400 to-amber-600 rounded-full" style="width: {{ $rentedPct }}%"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-red-700 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-500"></span> Dalam Perawatan (Maintenance)</span>
                    <span class="text-navy-800">{{ $maintenanceVehicles }} Unit ({{ $maintPct }}%)</span>
                </div>
                <div class="w-full h-2 rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-red-400 to-red-600 rounded-full" style="width: {{ $maintPct }}%"></div>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
            <span class="text-gray-400 font-medium">Total Armada: <strong>{{ $totalVehicles }} Unit</strong></span>
            <a href="{{ route('superadmin.monitoring-vehicle') }}" class="text-sky-600 hover:text-sky-700 font-bold">Detail Unit &rarr;</a>
        </div>
    </div>

    {{-- Tim & Inspector --}}
    <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm flex flex-col">
        <h3 class="text-sm font-extrabold text-navy-800 mb-1 flex items-center gap-2">
            <i class="fas fa-users-gear text-purple-500"></i> Tim Operasional
        </h3>
        <p class="text-[11px] text-gray-400 mb-4">Ringkasan personel yang bertugas hari ini.</p>

        <div class="space-y-3 flex-1">
            <a href="{{ route('drivers.index') }}" class="flex items-center justify-between bg-blue-50/60 hover:bg-blue-50 rounded-xl px-4 py-3 transition group">
                <span class="flex items-center gap-2.5 text-[12px] font-bold text-navy-800"><i class="fas fa-id-card text-blue-500"></i> Driver</span>
                <span class="text-[12px] font-black text-navy-800">{{ $activeDrivers }}<span class="text-gray-400 font-medium">/{{ $totalDrivers }} tugas</span></span>
            </a>
            <a href="{{ route('inspections.index') }}" class="flex items-center justify-between bg-emerald-50/60 hover:bg-emerald-50 rounded-xl px-4 py-3 transition group">
                <span class="flex items-center gap-2.5 text-[12px] font-bold text-navy-800"><i class="fas fa-magnifying-glass text-emerald-500"></i> Inspector</span>
                <span class="text-[12px] font-black text-navy-800">{{ $totalInspectors }}<span class="text-gray-400 font-medium"> personel</span></span>
            </a>
            <a href="{{ route('bookings.index', ['status' => 'pending']) }}" class="flex items-center justify-between bg-amber-50/60 hover:bg-amber-50 rounded-xl px-4 py-3 transition group">
                <span class="flex items-center gap-2.5 text-[12px] font-bold text-navy-800"><i class="fas fa-hourglass-half text-amber-500"></i> Perlu Konfirmasi</span>
                <span class="text-[12px] font-black text-navy-800">{{ $pendingBookings }}<span class="text-gray-400 font-medium"> booking</span></span>
            </a>
            <a href="{{ route('bookings.manual-create') }}" class="flex items-center justify-between bg-sky-50/60 hover:bg-sky-50 rounded-xl px-4 py-3 transition group">
                <span class="flex items-center gap-2.5 text-[12px] font-bold text-navy-800"><i class="fas fa-user-pen text-sky-500"></i> Booking Manual</span>
                <i class="fas fa-arrow-right text-[10px] text-sky-500 group-hover:translate-x-1 transition"></i>
            </a>
        </div>

        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
            <span class="text-gray-400 font-medium">Absensi & Penggajian</span>
            <a href="{{ route('superadmin.absen') }}" class="text-sky-600 hover:text-sky-700 font-bold">Kelola Tim &rarr;</a>
        </div>
    </div>
</div>

{{-- Quick Shortcuts --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-3 glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm">
        <h3 class="text-sm font-extrabold text-navy-800 mb-1 flex items-center gap-2">
            <i class="fas fa-compass text-sky-500"></i> Modul Pintas Superadmin
        </h3>
        <p class="text-[11px] text-gray-400 mb-4">Akses cepat ke seluruh fitur utama operasional dan inventaris.</p>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('vehicles.index') }}" class="group p-3.5 rounded-xl bg-blue-50/60 hover:bg-blue-50 border border-blue-100/60 transition flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-xl bg-blue-500 text-white flex items-center justify-center text-base shadow-md shadow-blue-500/20 group-hover:scale-110 transition">
                    <i class="fas fa-car"></i>
                </div>
                <span class="text-xs font-bold text-navy-800 mt-2">Mobil</span>
                <span class="text-[10px] text-gray-400">{{ $mobilCount }} unit</span>
            </a>

            <a href="{{ route('motors.index') }}" class="group p-3.5 rounded-xl bg-amber-50/60 hover:bg-amber-50 border border-amber-100/60 transition flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-base shadow-md shadow-amber-500/20 group-hover:scale-110 transition">
                    <i class="fas fa-motorcycle"></i>
                </div>
                <span class="text-xs font-bold text-navy-800 mt-2">Motor</span>
                <span class="text-[10px] text-gray-400">{{ $motorCount }} unit</span>
            </a>

            <a href="{{ route('superadmin.elektronik.type', 'kamera') }}" class="group p-3.5 rounded-xl bg-purple-50/60 hover:bg-purple-50 border border-purple-100/60 transition flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-xl bg-purple-500 text-white flex items-center justify-center text-base shadow-md shadow-purple-500/20 group-hover:scale-110 transition">
                    <i class="fas fa-camera"></i>
                </div>
                <span class="text-xs font-bold text-navy-800 mt-2">Elektronik</span>
                <span class="text-[10px] text-gray-400">{{ $kameraCount + $hpCount + $tendaCount }} unit</span>
            </a>

            <a href="{{ route('drivers.index') }}" class="group p-3.5 rounded-xl bg-emerald-50/60 hover:bg-emerald-50 border border-emerald-100/60 transition flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-base shadow-md shadow-emerald-500/20 group-hover:scale-110 transition">
                    <i class="fas fa-id-card"></i>
                </div>
                <span class="text-xs font-bold text-navy-800 mt-2">Driver</span>
                <span class="text-[10px] text-gray-400">{{ $totalDrivers }} personil</span>
            </a>

            <a href="{{ route('superadmin.finance') }}" class="group p-3.5 rounded-xl bg-teal-50/60 hover:bg-teal-50 border border-teal-100/60 transition flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-xl bg-teal-500 text-white flex items-center justify-center text-base shadow-md shadow-teal-500/20 group-hover:scale-110 transition">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <span class="text-xs font-bold text-navy-800 mt-2">Finance</span>
                <span class="text-[10px] text-gray-400">Kas & Invoice</span>
            </a>

            <a href="{{ route('superadmin.absen') }}" class="group p-3.5 rounded-xl bg-indigo-50/60 hover:bg-indigo-50 border border-indigo-100/60 transition flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-xl bg-indigo-500 text-white flex items-center justify-center text-base shadow-md shadow-indigo-500/20 group-hover:scale-110 transition">
                    <i class="fas fa-clipboard-user"></i>
                </div>
                <span class="text-xs font-bold text-navy-800 mt-2">Absensi</span>
                <span class="text-[10px] text-gray-400">Kehadiran Tim</span>
            </a>

            <a href="{{ route('bookings.index') }}" class="group p-3.5 rounded-xl bg-sky-50/60 hover:bg-sky-50 border border-sky-100/60 transition flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center text-base shadow-md shadow-sky-500/20 group-hover:scale-110 transition">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <span class="text-xs font-bold text-navy-800 mt-2">Booking</span>
                <span class="text-[10px] text-gray-400">{{ $totalBookings }} transaksi</span>
            </a>

            <a href="{{ route('reports.index') }}" class="group p-3.5 rounded-xl bg-rose-50/60 hover:bg-rose-50 border border-rose-100/60 transition flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center text-base shadow-md shadow-rose-500/20 group-hover:scale-110 transition">
                    <i class="fas fa-file-lines"></i>
                </div>
                <span class="text-xs font-bold text-navy-800 mt-2">Laporan</span>
                <span class="text-[10px] text-gray-400">Trip & Rekap</span>
            </a>
        </div>
    </div>
</div>

{{-- Recent Bookings Table (DAdmin Style) --}}
<div class="glass-card rounded-2xl overflow-hidden shadow-sm border border-sky-100/50 mb-6">
    <div class="px-6 py-4 border-b border-sky-100/60 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-sky-500"></i> Transaksi Sewa Terbaru
            </h3>
            <p class="text-[11px] text-gray-400">Daftar booking yang masuk ke dalam sistem secara real-time.</p>
        </div>
        <a href="{{ route('bookings.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1">
            Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-sky-50/40 border-b border-sky-100/50 text-[10px] uppercase font-bold text-gray-500">
                    <th class="py-3 px-6 text-left">Penyewa</th>
                    <th class="py-3 px-6 text-left">Item Sewa</th>
                    <th class="py-3 px-6 text-left">Jadwal Sewa</th>
                    <th class="py-3 px-6 text-left">Total Biaya</th>
                    <th class="py-3 px-6 text-center">Status</th>
                    <th class="py-3 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($recentBookings as $b)
                <tr class="hover:bg-sky-50/30 transition-colors">
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-700 font-bold flex items-center justify-center text-xs">
                                {{ strtoupper(substr($b->user->name ?? 'U', 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-bold text-navy-800">{{ $b->user->name ?? 'Pelanggan' }}</p>
                                <p class="text-[10px] text-gray-400 font-mono">{{ $b->booking_code }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-6">
                        <p class="font-bold text-navy-800">{{ $b->vehicle->name ?? ($b->category->name ?? 'Unit Sewa') }}</p>
                        <span class="text-[10px] text-gray-400">{{ $b->with_driver ? '+ Dengan Supir' : 'Lepas Kunci' }}</span>
                    </td>
                    <td class="py-3.5 px-6 text-navy-600">
                        <p class="font-medium">{{ $b->start_date ? \Carbon\Carbon::parse($b->start_date)->translatedFormat('d M Y') : '-' }}</p>
                        <span class="text-[10px] text-gray-400">s/d {{ $b->end_date ? \Carbon\Carbon::parse($b->end_date)->translatedFormat('d M Y') : '-' }}</span>
                    </td>
                    <td class="py-3.5 px-6 font-bold text-navy-800">
                        Rp {{ number_format($b->final_price ?? $b->total_price, 0, ',', '.') }}
                    </td>
                    <td class="py-3.5 px-6 text-center">
                        @if($b->status == 'pending') <span class="badge badge-yellow">Pending</span>
                        @elseif($b->status == 'confirmed') <span class="badge badge-teal">Dikonfirmasi</span>
                        @elseif($b->status == 'ongoing') <span class="badge badge-blue">Berjalan</span>
                        @elseif($b->status == 'completed') <span class="badge badge-green">Selesai</span>
                        @else <span class="badge badge-red">Batal</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-6 text-center">
                        <a href="{{ route('bookings.show', $b) }}" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-600 transition" title="Lihat Detail">
                            <i class="fas fa-eye text-xs"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-400">Belum ada transaksi sewa terbaru.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Revenue Chart
    const revCtx = document.getElementById('dadminRevenueChart');
    if (revCtx) {
        const monthsData = @json($monthlyRevenue);
        const labels = monthsData.map(m => m.label);
        const values = monthsData.map(m => m.value);

        const gradient = revCtx.getContext('2d').createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(14, 165, 233, 0.35)');
        gradient.addColorStop(1, 'rgba(14, 165, 233, 0.0)');

        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: values,
                    borderColor: '#0284c7',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#0284c7',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, color: '#64748b' }
                    },
                    y: {
                        border: { dash: [4, 4] },
                        grid: { color: '#e2e8f0' },
                        ticks: {
                            font: { size: 10 },
                            color: '#64748b',
                            callback: function(val) {
                                if (val >= 1000000) return (val/1000000).toFixed(1) + 'M';
                                if (val >= 1000) return (val/1000).toFixed(0) + 'k';
                                return val;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Category Donut Chart
    const catCtx = document.getElementById('dadminCategoryChart');
    if (catCtx) {
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: ['Mobil', 'Motor', 'Kamera', 'Camping', 'HP'],
                datasets: [{
                    data: [
                        {{ $mobilCount }},
                        {{ $motorCount }},
                        {{ $kameraCount }},
                        {{ $tendaCount }},
                        {{ $hpCount }}
                    ],
                    backgroundColor: [
                        '#3b82f6',
                        '#f59e0b',
                        '#a855f7',
                        '#10b981',
                        '#ec4899'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
