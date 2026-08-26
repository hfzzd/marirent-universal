@extends('layouts.dashboard')
@section('page-title', 'Finance')

@section('content')
@php
    $totalRevenue = \App\Models\Invoice::where('status','paid')->sum('total_amount');
    $thisMonthRevenue = \App\Models\Invoice::where('status','paid')->whereMonth('created_at', now()->month)->sum('total_amount');
    $lastMonthRevenue = \App\Models\Invoice::where('status','paid')->whereMonth('created_at', now()->subMonth()->month)->sum('total_amount');
    $revenueChange = $lastMonthRevenue > 0 ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100) : 0;
    $totalExpenses = \App\Models\DriverSalary::where('status','paid')->sum('total_salary') + \App\Models\TripReport::sum('total_operational_cost');
    $thisMonthExpenses = \App\Models\DriverSalary::where('status','paid')->whereMonth('created_at', now()->month)->sum('total_salary') + \App\Models\TripReport::whereMonth('created_at', now()->month)->sum('total_operational_cost');
    $netProfit = $thisMonthRevenue - $thisMonthExpenses;
    $pendingPayments = \App\Models\Invoice::where('status','partial')->orWhere('status','sent')->sum('due_amount');

    $categories = \App\Models\Category::all();
    $revenuePerCategory = [];
    foreach($categories as $cat) {
        $revenuePerCategory[$cat->name] = \App\Models\Invoice::where('status','paid')
            ->whereHas('booking', fn($q) => $q->where('category_id', $cat->id))
            ->sum('total_amount');
    }

    $monthlyRevenue = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i);
        $monthlyRevenue[] = [
            'label' => $month->format('M'),
            'revenue' => \App\Models\Invoice::where('status','paid')->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum('total_amount'),
            'expense' => \App\Models\DriverSalary::where('status','paid')->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum('total_salary') + \App\Models\TripReport::whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum('total_operational_cost')
        ];
    }
@endphp

{{-- Hero --}}
<div class="relative overflow-hidden rounded-2xl mb-6" style="background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-white rounded-full translate-y-1/2 -translate-x-1/4"></div>
    </div>
    <div class="relative px-8 py-7 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white mb-1">Finance Overview</h2>
            <p class="text-emerald-100 text-[13px]">Ringkasan keuangan MariRent bulan ini.</p>
        </div>
        <div class="hidden md:flex items-center gap-6 text-right">
            <div>
                <p class="text-emerald-200 text-[11px] font-medium">Laba Bersih</p>
                <p class="text-xl font-bold text-white">Rp {{ number_format($netProfit, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-60"></div>
        <div class="w-11 h-11 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/25 mb-3">
            <i class="fas fa-arrow-up text-white"></i>
        </div>
        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Total Pendapatan</p>
        <p class="text-xl font-extrabold text-navy-800 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-16 h-16 bg-sky-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-60"></div>
        <div class="w-11 h-11 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/25 mb-3">
            <i class="fas fa-calendar text-white"></i>
        </div>
        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Bulan Ini</p>
        <p class="text-xl font-extrabold text-navy-800 mt-1">Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}</p>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-16 h-16 bg-red-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-60"></div>
        <div class="w-11 h-11 bg-gradient-to-br from-red-400 to-red-500 rounded-xl flex items-center justify-center shadow-lg shadow-red-500/25 mb-3">
            <i class="fas fa-arrow-down text-white"></i>
        </div>
        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Pengeluaran</p>
        <p class="text-xl font-extrabold text-navy-800 mt-1">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</p>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-16 h-16 bg-amber-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-60"></div>
        <div class="w-11 h-11 bg-gradient-to-br from-amber-400 to-amber-500 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/25 mb-3">
            <i class="fas fa-clock text-white"></i>
        </div>
        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Belum Dibayar</p>
        <p class="text-xl font-extrabold text-navy-800 mt-1">Rp {{ number_format($pendingPayments, 0, ',', '.') }}</p>
    </div>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Revenue vs Expense Chart --}}
    <div class="lg:col-span-2 glass-card rounded-2xl p-5">
        <h3 class="text-[14px] font-bold text-navy-800 mb-4">Pendapatan vs Pengeluaran</h3>
        <div style="height: 240px;">
            <canvas id="financeBarChart"></canvas>
        </div>
    </div>

    {{-- Category Pie --}}
    <div class="glass-card rounded-2xl p-5">
        <h3 class="text-[14px] font-bold text-navy-800 mb-3">per Kategori</h3>
        <div style="height: 200px;">
            <canvas id="categoryPieChart"></canvas>
        </div>
        <div class="mt-3 space-y-1.5">
            @foreach($revenuePerCategory as $catName => $amount)
            <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">{{ $catName }}</span>
                <span class="font-semibold text-navy-700">Rp {{ number_format($amount, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Invoice Table --}}
<div class="glass-card rounded-2xl overflow-hidden">
    <div class="px-5 py-4 border-b border-sky-100/50 flex items-center justify-between">
        <h3 class="text-[14px] font-bold text-navy-800">Invoice Terbaru</h3>
        <a href="{{ route('invoices.index') }}" class="text-[11px] font-semibold text-sky-600 hover:text-sky-700">Lihat Semua <i class="fas fa-arrow-right ml-0.5"></i></a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="border-b border-sky-50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Nomor</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Tipe</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Pengguna</th>
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Total</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse(\App\Models\Invoice::with('user')->latest()->limit(8)->get() as $inv)
                <tr class="border-b border-sky-50/50 last:border-0 hover:bg-sky-50/30 transition">
                    <td class="py-3 px-5 font-semibold text-sky-600">{{ $inv->invoice_number }}</td>
                    <td class="py-3 px-5 text-navy-600">
                        @if($inv->type == 'rental') Sewa
                        @elseif($inv->type == 'driver_salary') Gaji
                        @elseif($inv->type == 'replacement') Penggantian
                        @elseif($inv->type == 'damage') Kerusakan
                        @else Lainnya @endif
                    </td>
                    <td class="py-3 px-5 text-navy-700">{{ $inv->user?->name }}</td>
                    <td class="py-3 px-5 text-right font-bold text-navy-800">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                    <td class="py-3 px-5 text-center">
                        @if($inv->status == 'paid') <span class="badge badge-green">Lunas</span>
                        @elseif($inv->status == 'partial') <span class="badge badge-yellow">Sebagian</span>
                        @elseif($inv->status == 'overdue') <span class="badge badge-red">Terlambat</span>
                        @elseif($inv->status == 'sent') <span class="badge badge-blue">Terkirim</span>
                        @else <span class="badge badge-gray">Draft</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-12 text-center text-gray-300">Belum ada invoice</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Bar Chart - Revenue vs Expense
    const barCtx = document.getElementById('financeBarChart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($monthlyRevenue, 'label')) !!},
                datasets: [
                    {
                        label: 'Pendapatan',
                        data: {!! json_encode(array_column($monthlyRevenue, 'revenue')) !!},
                        backgroundColor: 'rgba(14, 165, 233, 0.8)',
                        borderRadius: 6,
                        barPercentage: 0.6,
                    },
                    {
                        label: 'Pengeluaran',
                        data: {!! json_encode(array_column($monthlyRevenue, 'expense')) !!},
                        backgroundColor: 'rgba(239, 68, 68, 0.6)',
                        borderRadius: 6,
                        barPercentage: 0.6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { padding: 16, font: { size: 11 }, usePointStyle: true, pointStyleWidth: 8 } },
                    tooltip: { backgroundColor: '#0f172a', padding: 10, cornerRadius: 8, callbacks: { label: ctx => ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID') } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 }, color: '#94a3b8', callback: v => 'Rp ' + (v/1000000).toFixed(0) + 'jt' } }
                }
            }
        });
    }

    // Pie Chart - Category
    const pieCtx = document.getElementById('categoryPieChart');
    if (pieCtx) {
        const catLabels = {!! json_encode(array_keys($revenuePerCategory)) !!};
        const catData = {!! json_encode(array_values($revenuePerCategory)) !!};
        const pieColors = ['#0ea5e9', '#f59e0b', '#8b5cf6', '#10b981', '#ef4444', '#6366f1'];
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: pieColors.slice(0, catLabels.length),
                    borderWidth: 0,
                    spacing: 2,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: '#0f172a', padding: 8, cornerRadius: 6, callbacks: { label: ctx => 'Rp ' + ctx.parsed.toLocaleString('id-ID') } }
                }
            }
        });
    }
</script>
@endpush
