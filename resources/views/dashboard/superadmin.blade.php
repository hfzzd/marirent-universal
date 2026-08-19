@extends('layouts.dashboard')
@section('page-title', 'Beranda')

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

    $monthlyRevenue = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i);
        $monthlyRevenue[] = [
            'label' => $month->format('M'),
            'value' => \App\Models\Invoice::where('status','paid')->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum('total_amount')
        ];
    }
@endphp

{{-- Hero Welcome --}}
<div class="relative overflow-hidden rounded-2xl mb-6 animate-fade-in" style="background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 50%, #0c4a6e 100%);">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/2 -translate-x-1/4"></div>
        <div class="absolute top-1/2 right-1/4 w-32 h-32 bg-white rounded-full opacity-50"></div>
    </div>
    <div class="relative px-8 py-8 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white mb-2">Selamat Datang, {{ auth()->user()->name }} 👋</h2>
            <p class="text-sky-200 text-[14px]">Berikut ringkasan aktivitas MariRent hari ini.</p>
            <div class="flex items-center gap-4 mt-4">
                <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-lg px-3 py-1.5">
                    <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                    <span class="text-sky-100 text-[12px] font-medium">{{ $activeDrivers }} driver aktif</span>
                </div>
                <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-lg px-3 py-1.5">
                    <div class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></div>
                    <span class="text-sky-100 text-[12px] font-medium">{{ $pendingBookings }} booking pending</span>
                </div>
            </div>
        </div>
        <div class="hidden lg:flex items-center gap-3">
            <a href="{{ route('superadmin.monitoring') }}" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition flex items-center gap-2">
                <i class="fas fa-chart-bar"></i> Monitoring
            </a>
            <a href="{{ route('superadmin.finance') }}" class="bg-white text-sky-700 hover:bg-sky-50 px-5 py-2.5 rounded-xl text-[13px] font-semibold transition shadow-lg flex items-center gap-2">
                <i class="fas fa-wallet"></i> Finance
            </a>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up" style="animation-delay: 0.1s">
        <div class="absolute top-0 right-0 w-20 h-20 bg-sky-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-60"></div>
        <div class="relative">
            <div class="w-11 h-11 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/25 mb-3">
                <i class="fas fa-car text-white"></i>
            </div>
            <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Total Produk</p>
            <p class="text-3xl font-extrabold text-navy-800 mt-1" data-count="{{ $totalAllProducts }}">0</p>
        </div>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up" style="animation-delay: 0.2s">
        <div class="absolute top-0 right-0 w-20 h-20 bg-amber-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-60"></div>
        <div class="relative">
            <div class="w-11 h-11 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/25 mb-3">
                <i class="fas fa-calendar-check text-white"></i>
            </div>
            <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Booking Aktif</p>
            <p class="text-3xl font-extrabold text-navy-800 mt-1" data-count="{{ $ongoingBookings }}">0</p>
        </div>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up" style="animation-delay: 0.3s">
        <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-60"></div>
        <div class="relative">
            <div class="w-11 h-11 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/25 mb-3">
                <i class="fas fa-wallet text-white"></i>
            </div>
            <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Bulan Ini</p>
            <p class="text-xl font-extrabold text-navy-800 mt-1">Rp <span data-count="{{ $revenueThisMonth }}" data-prefix="">0</span></p>
        </div>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up" style="animation-delay: 0.4s">
        <div class="absolute top-0 right-0 w-20 h-20 bg-violet-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-60"></div>
        <div class="relative">
            <div class="w-11 h-11 bg-gradient-to-br from-violet-400 to-violet-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/25 mb-3">
                <i class="fas fa-users text-white"></i>
            </div>
            <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Pengguna</p>
            <p class="text-3xl font-extrabold text-navy-800 mt-1" data-count="{{ $totalUsers }}">0</p>
        </div>
    </div>
</div>

{{-- Chart + Quick Access --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Revenue Chart --}}
    <div class="lg:col-span-2 glass-card rounded-2xl p-5 animate-slide-up" style="animation-delay: 0.3s">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[14px] font-bold text-navy-800">Pendapatan 6 Bulan</h3>
            <div class="flex items-center gap-1.5 text-[11px] text-gray-400">
                @if($revenueChange >= 0)
                <span class="text-emerald-500 font-semibold"><i class="fas fa-arrow-up mr-0.5"></i>{{ $revenueChange }}%</span>
                @else
                <span class="text-red-500 font-semibold"><i class="fas fa-arrow-down mr-0.5"></i>{{ abs($revenueChange) }}%</span>
                @endif
                <span>dari bulan lalu</span>
            </div>
        </div>
        <div style="height: 220px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Quick Access --}}
    <div class="glass-card rounded-2xl p-5 animate-slide-up" style="animation-delay: 0.4s">
        <h3 class="text-[14px] font-bold text-navy-800 mb-4">Akses Cepat</h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('superadmin.monitoring') }}" class="group flex flex-col items-center p-3.5 rounded-xl bg-sky-50 hover:bg-sky-100 transition">
                <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-md shadow-sky-500/20 mb-2 group-hover:scale-110 transition">
                    <i class="fas fa-chart-bar text-white text-sm"></i>
                </div>
                <span class="text-[11px] font-semibold text-navy-700">Monitoring</span>
            </a>
            <a href="{{ route('superadmin.finance') }}" class="group flex flex-col items-center p-3.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 transition">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center shadow-md shadow-emerald-500/20 mb-2 group-hover:scale-110 transition">
                    <i class="fas fa-wallet text-white text-sm"></i>
                </div>
                <span class="text-[11px] font-semibold text-navy-700">Finance</span>
            </a>
            <a href="{{ route('superadmin.absen') }}" class="group flex flex-col items-center p-3.5 rounded-xl bg-amber-50 hover:bg-amber-100 transition">
                <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center shadow-md shadow-amber-500/20 mb-2 group-hover:scale-110 transition">
                    <i class="fas fa-clipboard-list text-white text-sm"></i>
                </div>
                <span class="text-[11px] font-semibold text-navy-700">Absen</span>
            </a>
            <a href="{{ route('superadmin.monitoring-vehicle') }}" class="group flex flex-col items-center p-3.5 rounded-xl bg-violet-50 hover:bg-violet-100 transition">
                <div class="w-10 h-10 bg-gradient-to-br from-violet-400 to-violet-600 rounded-xl flex items-center justify-center shadow-md shadow-violet-500/20 mb-2 group-hover:scale-110 transition">
                    <i class="fas fa-car text-white text-sm"></i>
                </div>
                <span class="text-[11px] font-semibold text-navy-700">Vehicle</span>
            </a>
        </div>
    </div>
</div>

{{-- Bottom Section --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Booking Terbaru --}}
    <div class="lg:col-span-2 glass-card rounded-2xl overflow-hidden animate-slide-up" style="animation-delay: 0.5s">
        <div class="px-5 py-4 border-b border-sky-100/50 flex items-center justify-between">
            <h3 class="text-[14px] font-bold text-navy-800">Booking Terbaru</h3>
            <a href="{{ route('bookings.index') }}" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700 transition">Lihat Semua <i class="fas fa-arrow-right ml-0.5"></i></a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="border-b border-sky-50">
                        <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Kode</th>
                        <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Pengguna</th>
                        <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Kendaraan</th>
                        <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Status</th>
                        <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(\App\Models\Booking::with(['user','vehicle'])->latest()->limit(8)->get() as $b)
                    <tr class="border-b border-sky-50/50 last:border-0 hover:bg-sky-50/30 transition cursor-pointer" onclick="window.location='{{ route('bookings.show', $b) }}'">
                        <td class="py-3 px-5 font-semibold text-sky-600">{{ $b->booking_code }}</td>
                        <td class="py-3 px-5 text-navy-700">{{ $b->user->name }}</td>
                        <td class="py-3 px-5 text-navy-700">{{ $b->vehicle->name ?? ($b->category->name ?? '-') }}</td>
                        <td class="py-3 px-5 text-center">
                            @if($b->status == 'pending') <span class="badge badge-blue">{{ ucfirst($b->status) }}</span>
                            @elseif($b->status == 'confirmed') <span class="badge badge-teal">{{ ucfirst($b->status) }}</span>
                            @elseif($b->status == 'ongoing') <span class="badge badge-yellow">{{ ucfirst($b->status) }}</span>
                            @elseif($b->status == 'completed') <span class="badge badge-green">{{ ucfirst($b->status) }}</span>
                            @else <span class="badge badge-gray">{{ ucfirst($b->status) }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-5 text-right font-bold text-navy-800">Rp {{ number_format($b->final_price, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-12 text-center text-gray-300">Belum ada booking</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Kategori Inventaris + Status Chart --}}
    <div class="space-y-5 animate-slide-up" style="animation-delay: 0.6s">
        <div class="glass-card rounded-2xl p-5">
            <h3 class="text-[14px] font-bold text-navy-800 mb-3">Status Booking</h3>
            <div style="height: 160px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <h3 class="text-[14px] font-bold text-navy-800 mb-3">Inventaris</h3>
            <div class="space-y-2.5">
                <a href="{{ route('vehicles.index') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-sky-50 transition group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center"><i class="fas fa-car text-sky-500 text-xs"></i></div>
                        <span class="text-[13px] font-medium text-navy-700">Mobil</span>
                    </div>
                    <span class="text-[13px] font-bold text-navy-800 bg-sky-50 px-2.5 py-0.5 rounded-full">{{ $mobilCount }}</span>
                </a>
                <a href="{{ route('superadmin.motor') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-amber-50 transition group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center"><i class="fas fa-motorcycle text-amber-500 text-xs"></i></div>
                        <span class="text-[13px] font-medium text-navy-700">Motor</span>
                    </div>
                    <span class="text-[13px] font-bold text-navy-800 bg-amber-50 px-2.5 py-0.5 rounded-full">{{ $motorCount }}</span>
                </a>
                <a href="{{ route('superadmin.elektronik.type', 'kamera') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-violet-50 transition group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-violet-100 rounded-lg flex items-center justify-center"><i class="fas fa-camera text-violet-500 text-xs"></i></div>
                        <span class="text-[13px] font-medium text-navy-700">Kamera</span>
                    </div>
                    <span class="text-[13px] font-bold text-navy-800 bg-violet-50 px-2.5 py-0.5 rounded-full">{{ $kameraCount }}</span>
                </a>
                <a href="{{ route('superadmin.elektronik.type', 'hp') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-sky-50 transition group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center"><i class="fas fa-mobile-alt text-sky-500 text-xs"></i></div>
                        <span class="text-[13px] font-medium text-navy-700">Handphone</span>
                    </div>
                    <span class="text-[13px] font-bold text-navy-800 bg-sky-50 px-2.5 py-0.5 rounded-full">{{ $hpCount }}</span>
                </a>
                <a href="{{ route('superadmin.elektronik.type', 'tenda') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-emerald-50 transition group">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center"><i class="fas fa-campground text-emerald-500 text-xs"></i></div>
                        <span class="text-[13px] font-medium text-navy-700">Tenda & Alat</span>
                    </div>
                    <span class="text-[13px] font-bold text-navy-800 bg-emerald-50 px-2.5 py-0.5 rounded-full">{{ $tendaCount }}</span>
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($monthlyRevenue, 'label')) !!},
                datasets: [{
                    label: 'Pendapatan',
                    data: {!! json_encode(array_column($monthlyRevenue, 'value')) !!},
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0ea5e9',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, weight: '600' },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 }, color: '#94a3b8', callback: v => 'Rp ' + (v/1000000).toFixed(0) + 'jt' } }
                }
            }
        });
    }

    // Status Doughnut Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Proses', 'Selesai', 'Batal'],
                datasets: [{
                    data: [{{ $pendingBookings }}, {{ $ongoingBookings }}, {{ $completedBookings }}, {{ $cancelledBookings }}],
                    backgroundColor: ['#38bdf8', '#fbbf24', '#34d399', '#cbd5e1'],
                    borderWidth: 0,
                    spacing: 2,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 }, usePointStyle: true, pointStyleWidth: 8 } },
                    tooltip: { backgroundColor: '#0f172a', padding: 8, cornerRadius: 6, titleFont: { size: 11 } }
                }
            }
        });
    }
</script>
@endpush
