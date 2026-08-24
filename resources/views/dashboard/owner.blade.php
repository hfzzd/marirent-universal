@extends('layouts.dashboard')
@section('page-title', 'Beranda Owner')

@section('content')
@php
    $owner = auth()->user();
    $ownerVehicles = $owner->vehicles()->with('category')->get();
    $vehicleIds = $ownerVehicles->pluck('id');
    
    $ownedCars = $ownerVehicles->where('category.slug', 'mobil')->count();
    $ownedMotors = $ownerVehicles->where('category.slug', 'motor')->count();
    $totalOwned = $ownerVehicles->count();

    $ownedAvailable = $ownerVehicles->where('status', 'available')->count();
    $ownedRented = $ownerVehicles->where('status', 'rented')->count();
    $ownedMaintenance = $ownerVehicles->where('status', 'maintenance')->count();

    $ownedDriversCount = $owner->ownedDrivers()->count();
    
    $pendingBookingsCount = \App\Models\Booking::whereIn('vehicle_id', $vehicleIds)->where('status', 'pending')->count();
    $ongoingBookingsCount = \App\Models\Booking::whereIn('vehicle_id', $vehicleIds)->where('status', 'ongoing')->count();
    
    $revenueThisMonth = \App\Models\Invoice::where('owner_id', $owner->id)->where('status', 'paid')->whereMonth('created_at', now()->month)->sum('total_amount');
    $revenueLastMonth = \App\Models\Invoice::where('owner_id', $owner->id)->where('status', 'paid')->whereMonth('created_at', now()->subMonth()->month)->sum('total_amount');
    $revenueChange = $revenueLastMonth > 0 ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100) : 0;
    
    $monthlyOwnerRevenue = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i);
        $monthlyOwnerRevenue[] = [
            'label' => $month->translatedFormat('M Y'),
            'value' => (int) \App\Models\Invoice::where('owner_id', $owner->id)->where('status', 'paid')->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum('total_amount')
        ];
    }

    $recentOwnerBookings = \App\Models\Booking::whereIn('vehicle_id', $vehicleIds)->with(['user', 'vehicle'])->latest()->limit(5)->get();

    // Jadwal pembayaran untuk unit milik owner
    $ownerDueToday = \App\Models\Booking::whereIn('vehicle_id', $vehicleIds)->whereIn('payment_status', ['unpaid','partial'])
        ->whereDate('payment_due_date', today())->count();
    $ownerOverdue = \App\Models\Booking::whereIn('vehicle_id', $vehicleIds)->whereIn('payment_status', ['unpaid','partial'])
        ->whereNotNull('payment_due_date')->whereDate('payment_due_date', '<', today())->count();
    $ownerUpcomingPayments = \App\Models\Booking::with(['user', 'vehicle'])
        ->whereIn('vehicle_id', $vehicleIds)
        ->whereIn('payment_status', ['unpaid', 'partial'])
        ->whereNotNull('payment_due_date')
        ->whereDate('payment_due_date', '>=', today())
        ->orderBy('payment_due_date')
        ->limit(4)
        ->get();
@endphp

{{-- DAdmin Hero Welcome Banner for Owner --}}
<div class="relative overflow-hidden rounded-3xl mb-6 shadow-xl shadow-sky-900/10" style="background: linear-gradient(135deg, #09203f 0%, #173b6c 50%, #0284c7 100%);">
    <div class="absolute -right-8 -bottom-8 w-72 h-72 bg-sky-400/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="relative px-6 py-7 md:px-8 md:py-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-sky-200 text-xs font-semibold mb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Mitra Pemilik Armada (Owner)</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">
                Halo, {{ $owner->name }} 👋
            </h1>
            <p class="text-sky-100/80 text-xs md:text-sm mt-1 max-w-lg">
                Kelola performa unit kendaraan, pantau penyewaan yang masuk, dan cek pendapatan secara transparan.
            </p>
            <div class="flex flex-wrap items-center gap-3 mt-4">
                <div class="flex items-center gap-2 bg-black/20 backdrop-blur-md rounded-xl px-3.5 py-1.5 border border-white/10 text-white text-xs">
                    <i class="fas fa-car text-blue-300"></i>
                    <span><strong>{{ $ownedCars }}</strong> Mobil</span>
                </div>
                <div class="flex items-center gap-2 bg-black/20 backdrop-blur-md rounded-xl px-3.5 py-1.5 border border-white/10 text-white text-xs">
                    <i class="fas fa-motorcycle text-amber-300"></i>
                    <span><strong>{{ $ownedMotors }}</strong> Motor</span>
                </div>
                <div class="flex items-center gap-2 bg-black/20 backdrop-blur-md rounded-xl px-3.5 py-1.5 border border-white/10 text-white text-xs">
                    <i class="fas fa-id-card text-emerald-300"></i>
                    <span><strong>{{ $ownedDriversCount }}</strong> Driver Dikelola</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('vehicles.create', ['type' => 'mobil']) }}" class="bg-white text-navy-900 hover:bg-sky-50 px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-lg flex items-center gap-1.5">
                <i class="fas fa-plus text-sky-600"></i> Tambah Mobil
            </a>
            <a href="{{ route('vehicles.create', ['type' => 'motor']) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-lg flex items-center gap-1.5">
                <i class="fas fa-plus"></i> Tambah Motor
            </a>
        </div>
    </div>
</div>

{{-- 4 Stat Widgets --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Armada Saya</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $totalOwned }} Unit</h3>
                <p class="text-[11px] text-gray-500 mt-2 font-medium">
                    <span class="text-blue-600 font-bold">{{ $ownedCars }} Mobil</span> &bull; 
                    <span class="text-amber-600 font-bold">{{ $ownedMotors }} Motor</span>
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-600 flex items-center justify-center text-white text-lg shadow-lg shadow-sky-500/25">
                <i class="fas fa-car-side"></i>
            </div>
        </div>
    </div>

    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Booking Aktif</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $ongoingBookingsCount }}</h3>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="badge badge-yellow text-[10px]">{{ $pendingBookingsCount }} Menunggu</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-lg shadow-lg shadow-amber-500/25">
                <i class="fas fa-key"></i>
            </div>
        </div>
    </div>

    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Pendapatan Bulan Ini</p>
                <h3 class="text-xl font-black text-navy-800 mt-1">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</h3>
                <div class="flex items-center gap-1 mt-2 text-[11px]">
                    @if($revenueChange >= 0)
                    <span class="text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded text-[10px]">+{{ $revenueChange }}%</span>
                    @else
                    <span class="text-red-500 font-bold bg-red-50 px-1.5 py-0.5 rounded text-[10px]">{{ $revenueChange }}%</span>
                    @endif
                    <span class="text-gray-400">vs bulan lalu</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-lg shadow-lg shadow-emerald-500/25">
                <i class="fas fa-hand-holding-dollar"></i>
            </div>
        </div>
    </div>

    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Tim Driver</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $ownedDriversCount }} Personil</h3>
                <p class="text-[11px] text-gray-500 mt-2 font-medium">Driver terdaftar di bawah akun</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white text-lg shadow-lg shadow-purple-500/25">
                <i class="fas fa-id-card"></i>
            </div>
        </div>
    </div>
</div>

{{-- Analytics & Fleet Status --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                    <i class="fas fa-chart-area text-sky-500"></i> Tren Penghasilan Owner (6 Bulan)
                </h3>
                <p class="text-[11px] text-gray-400">Total pendapatan sewa yang telah dibayarkan.</p>
            </div>
        </div>
        <div class="relative w-full" style="height: 230px;">
            <canvas id="ownerRevenueChart"></canvas>
        </div>
    </div>

    <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm flex flex-col justify-between">
        <div>
            <h3 class="text-sm font-extrabold text-navy-800 mb-1 flex items-center gap-2">
                <i class="fas fa-car-side text-sky-500"></i> Status Armada Saya
            </h3>
            <p class="text-[11px] text-gray-400 mb-4">Kondisi armada Mobil & Motor Anda.</p>

            @php
                $tot = max(1, $totalOwned);
                $pAvail = round(($ownedAvailable / $tot) * 100);
                $pRented = round(($ownedRented / $tot) * 100);
                $pMaint = round(($ownedMaintenance / $tot) * 100);
            @endphp

            <div class="space-y-3.5 text-xs">
                <div>
                    <div class="flex justify-between font-semibold mb-1">
                        <span class="text-emerald-700 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tersedia</span>
                        <span class="text-navy-800">{{ $ownedAvailable }} Unit ({{ $pAvail }}%)</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $pAvail }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between font-semibold mb-1">
                        <span class="text-amber-700 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Sedang Disewa</span>
                        <span class="text-navy-800">{{ $ownedRented }} Unit ({{ $pRented }}%)</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-amber-500 rounded-full" style="width: {{ $pRented }}%"></div>
                </div>

                <div>
                    <div class="flex justify-between font-semibold mb-1">
                        <span class="text-red-700 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-500"></span> Maintenance</span>
                        <span class="text-navy-800">{{ $ownedMaintenance }} Unit ({{ $pMaint }}%)</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-red-500 rounded-full" style="width: {{ $pMaint }}%"></div>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs mt-4">
            <a href="{{ route('vehicles.index') }}" class="text-sky-600 hover:text-sky-700 font-bold">Kelola Mobil &rarr;</a>
            <a href="{{ route('motors.index') }}" class="text-amber-600 hover:text-amber-700 font-bold">Kelola Motor &rarr;</a>
        </div>
    </div>
</div>

{{-- Jadwal Pembayaran Unit Saya --}}
<div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-calendar-days text-orange-500"></i> Jadwal Pembayaran Unit Saya
            </h3>
            <p class="text-[11px] text-gray-400">Tagihan penyewaan unit Anda yang belum lunas.</p>
        </div>
        <div class="flex items-center gap-2">
            @if($ownerDueToday > 0) <span class="badge badge-red">{{ $ownerDueToday }} Jatuh Tempo Hari Ini</span> @endif
            @if($ownerOverdue > 0) <span class="badge badge-yellow">{{ $ownerOverdue }} Terlambat</span> @endif
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        @forelse($ownerUpcomingPayments as $p)
        <a href="{{ route('bookings.show', $p) }}" class="flex items-center justify-between gap-2 bg-sky-50/60 hover:bg-sky-50 rounded-xl px-4 py-3 transition">
            <div class="min-w-0">
                <p class="text-[12px] font-bold text-navy-800 truncate">{{ $p->vehicle?->name ?? 'Unit' }}</p>
                <p class="text-[10px] text-gray-400">{{ $p->booking_code }} &bull; Tempo {{ \Carbon\Carbon::parse($p->payment_due_date)->translatedFormat('d M Y') }}</p>
                <p class="text-[10px] text-gray-400 truncate">Penyewa: {{ $p->user?->name ?? '-' }}</p>
            </div>
            <div class="text-right whitespace-nowrap">
                <p class="text-[12px] font-black text-navy-800">Rp {{ number_format($p->final_price, 0, ',', '.') }}</p>
                @if($p->payment_status == 'partial') <span class="badge badge-yellow text-[9px]">Sebagian</span>
                @else <span class="badge badge-red text-[9px]">Belum Bayar</span> @endif
            </div>
        </a>
        @empty
        <div class="sm:col-span-2 lg:col-span-4 text-center py-5 text-gray-300 text-xs">
            <i class="fas fa-circle-check text-emerald-400 mr-1"></i> Semua tagihan unit Anda lunas / belum ada jadwal.
        </div>
        @endforelse
    </div>
</div>

{{-- Recent Bookings Table --}}
<div class="glass-card rounded-2xl overflow-hidden shadow-sm border border-sky-100/50">
    <div class="px-6 py-4 border-b border-sky-100/60 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-list-check text-sky-500"></i> Booking Unit Anda
            </h3>
            <p class="text-[11px] text-gray-400">Transaksi penyewaan untuk unit mobil dan motor milik Anda.</p>
        </div>
        <a href="{{ route('bookings.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1">
            Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-sky-50/40 border-b border-sky-100/50 text-[10px] uppercase font-bold text-gray-500">
                    <th class="py-3 px-6 text-left">Unit Kendaraan</th>
                    <th class="py-3 px-6 text-left">Penyewa</th>
                    <th class="py-3 px-6 text-left">Periode Sewa</th>
                    <th class="py-3 px-6 text-left">Pendapatan</th>
                    <th class="py-3 px-6 text-center">Status</th>
                    <th class="py-3 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($recentOwnerBookings as $b)
                <tr class="hover:bg-sky-50/30 transition-colors">
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg {{ $b->vehicle && $b->vehicle->category && $b->vehicle->category->slug === 'motor' ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600' }} flex items-center justify-center text-xs">
                                <i class="fas fa-{{ $b->vehicle && $b->vehicle->category && $b->vehicle->category->slug === 'motor' ? 'motorcycle' : 'car' }}"></i>
                            </div>
                            <div>
                                <p class="font-bold text-navy-800">{{ $b->vehicle->name ?? '-' }}</p>
                                <span class="font-mono text-[10px] text-gray-400">{{ $b->vehicle->license_plate ?? '-' }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-6">
                        <p class="font-bold text-navy-800">{{ $b->user->name ?? 'Pelanggan' }}</p>
                        <span class="text-[10px] text-gray-400 font-mono">{{ $b->booking_code }}</span>
                    </td>
                    <td class="py-3.5 px-6 text-navy-600">
                        {{ $b->start_date ? \Carbon\Carbon::parse($b->start_date)->translatedFormat('d M') : '-' }} s/d {{ $b->end_date ? \Carbon\Carbon::parse($b->end_date)->translatedFormat('d M Y') : '-' }}
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
                    <td colspan="6" class="py-8 text-center text-gray-400">Belum ada transaksi penyewaan unit Anda.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ownerCtx = document.getElementById('ownerRevenueChart');
    if (ownerCtx) {
        const data = @json($monthlyOwnerRevenue);
        const labels = data.map(m => m.label);
        const values = data.map(m => m.value);

        const gradient = ownerCtx.getContext('2d').createLinearGradient(0, 0, 0, 220);
        gradient.addColorStop(0, 'rgba(14, 165, 233, 0.35)');
        gradient.addColorStop(1, 'rgba(14, 165, 233, 0.0)');

        new Chart(ownerCtx, {
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
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        callbacks: {
                            label: function(ctx) {
                                return 'Rp ' + ctx.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#64748b' } },
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
});
</script>
@endpush
@endsection
