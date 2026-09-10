@extends('layouts.dashboard')
@section('page-title', 'Laporan Perjalanan')

@section('content')
@php
    $role = auth()->user()->role;
    $isDriver = $role === 'driver';
    $totalReports = $reports->total();
    $completedCount = $reports->where('status', 'completed')->count();
    $issuesCount = $reports->where('status', 'has_issues')->count();
    $totalDistance = $reports->sum('total_distance');
    $totalCost = $reports->sum('total_operational_cost');
@endphp

{{-- SUMMARY CARDS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="glass-card rounded-2xl p-4 border border-sky-100/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-500/20">
                <i class="fas fa-route text-white text-sm"></i>
            </div>
            <div>
                <p class="text-[20px] font-extrabold text-navy-800">{{ $totalReports }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Total Laporan</p>
            </div>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-4 border border-emerald-100/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                <i class="fas fa-check-circle text-white text-sm"></i>
            </div>
            <div>
                <p class="text-[20px] font-extrabold text-emerald-600">{{ $completedCount }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Selesai</p>
            </div>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-4 border border-amber-100/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/20">
                <i class="fas fa-road text-white text-sm"></i>
            </div>
            <div>
                <p class="text-[20px] font-extrabold text-amber-600">{{ number_format($totalDistance, 1) }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Total KM</p>
            </div>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-4 border border-violet-100/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/20">
                <i class="fas fa-coins text-white text-sm"></i>
            </div>
            <div>
                <p class="text-[20px] font-extrabold text-violet-600">Rp {{ number_format($totalCost / 1000, 0, ',', '.') }}k</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Biaya Operasional</p>
            </div>
        </div>
    </div>
</div>

{{-- HEADER & ACTION --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-5 gap-3">
    <div>
        <h2 class="text-lg font-extrabold text-navy-800 flex items-center gap-2">
            <i class="fas fa-route text-sky-500"></i> Laporan Perjalanan
        </h2>
        <p class="text-xs text-gray-400 mt-0.5">Kelola laporan perjalanan dan kondisi kendaraan.</p>
    </div>
    <a href="{{ route('reports.create') }}" class="btn-primary text-white px-4 py-2 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-1.5">
        <i class="fas fa-plus text-[10px]"></i> Buat Laporan
    </a>
</div>

{{-- FILTERS --}}
<div class="glass-card rounded-2xl p-4 mb-5 border border-sky-100/50 shadow-sm">
    <form action="{{ route('reports.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
        <div class="flex-1 w-full">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cari</label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-[11px]"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode booking, nama kendaraan..."
                    class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50">
            </div>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Status</label>
            <select name="status" class="border border-gray-200 rounded-xl px-3 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50 min-w-[140px]">
                <option value="">Semua</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="has_issues" {{ request('status') == 'has_issues' ? 'selected' : '' }}>Bermasalah</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2.5 rounded-xl text-[12px] font-semibold shadow-sm">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('reports.index') }}" class="bg-red-50 hover:bg-red-100 text-red-500 px-3 py-2.5 rounded-xl text-[12px] font-medium transition border border-red-100">
                <i class="fas fa-times text-[10px]"></i> Reset
            </a>
            @endif
        </div>
    </form>
</div>

{{-- REPORTS CARDS --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($reports as $r)
    @php
        $statusConfig = match($r->status) {
            'completed' => ['color' => 'emerald', 'icon' => 'fa-check-circle', 'label' => 'Selesai', 'bg' => 'from-emerald-50 to-teal-50', 'border' => 'border-emerald-200'],
            'has_issues' => ['color' => 'red', 'icon' => 'fa-exclamation-triangle', 'label' => 'Bermasalah', 'bg' => 'from-red-50 to-rose-50', 'border' => 'border-red-200'],
            default => ['color' => 'amber', 'icon' => 'fa-clock', 'label' => 'Berlangsung', 'bg' => 'from-amber-50 to-orange-50', 'border' => 'border-amber-200'],
        };
    @endphp
    <div class="glass-card rounded-2xl overflow-hidden border {{ $statusConfig['border'] }} hover:shadow-lg transition-all duration-300 group" style="box-shadow: 0 2px 16px rgba(0,0,0,0.03);">
        <div class="bg-gradient-to-r {{ $statusConfig['bg'] }} p-4 border-b {{ $statusConfig['border'] }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas {{ $statusConfig['icon'] }} text-{{ $statusConfig['color'] }}-500 text-sm"></i>
                    <span class="text-[11px] font-bold text-{{ $statusConfig['color'] }}-600 uppercase tracking-wider">{{ $statusConfig['label'] }}</span>
                </div>
                <span class="text-[11px] text-gray-400">{{ $r->created_at->format('d M Y') }}</span>
            </div>
        </div>
        <div class="p-4">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-[13px] font-bold text-navy-800">{{ $r->booking->booking_code ?? '-' }}</p>
                    <p class="text-[12px] text-gray-400 mt-0.5">{{ $r->vehicle?->name ?? '-' }}</p>
                </div>
                <div class="w-8 h-8 bg-sky-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-car text-sky-400 text-sm"></i>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="bg-gray-50 rounded-xl p-2.5">
                    <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold">Jarak</p>
                    <p class="text-[13px] font-bold text-navy-700">{{ $r->total_distance ? number_format($r->total_distance, 1) . ' km' : '-' }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-2.5">
                    <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold">Biaya</p>
                    <p class="text-[13px] font-bold text-navy-700">Rp {{ number_format($r->total_operational_cost, 0, ',', '.') }}</p>
                </div>
            </div>

            @if($r->notes)
            <p class="text-[11px] text-gray-500 mb-3 line-clamp-2">{{ Str::limit($r->notes, 80) }}</p>
            @endif

            <div class="flex items-center justify-between pt-3 border-t border-gray-100/60">
                <div class="flex items-center gap-1.5">
                    @if($r->booking?->driver?->user)
                    <div class="w-5 h-5 bg-sky-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-tie text-sky-500 text-[8px]"></i>
                    </div>
                    <span class="text-[11px] text-gray-500">{{ $r->booking->driver->user->name }}</span>
                    @endif
                </div>
                @if(
                    in_array($role, ['superadmin','owner','admin']) ||
                    ($isDriver && $r->booking?->driver_id === (\App\Models\Driver::where('user_id', auth()->id())->first()?->id ?? 0)) ||
                    ($role === 'user' && $r->booking?->user_id === auth()->id())
                )
                <a href="{{ route('reports.show', $r) }}" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-3 py-1.5 rounded-lg text-[11px] font-semibold transition flex items-center gap-1.5">
                    <i class="fas fa-eye text-[9px]"></i> Detail
                </a>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-16">
        <div class="w-20 h-20 bg-gradient-to-br from-sky-50 to-blue-50 rounded-3xl flex items-center justify-center mx-auto mb-5 border border-sky-100">
            <i class="fas fa-route text-sky-300 text-3xl"></i>
        </div>
        <h3 class="text-lg font-bold text-navy-800 mb-2">Belum ada laporan</h3>
        <p class="text-gray-400 text-[13px] mb-5">Laporan perjalanan akan muncul di sini</p>
        <a href="{{ route('reports.create') }}" class="bg-sky-500 hover:bg-sky-600 text-white px-6 py-2.5 rounded-2xl text-[13px] font-semibold inline-flex items-center gap-2 transition-all duration-300 hover:shadow-lg">
            <i class="fas fa-plus text-[11px]"></i> Buat Laporan
        </a>
    </div>
    @endforelse
</div>

@if($reports->hasPages())
<div class="mt-8 flex justify-center">
    {{ $reports->withQueryString()->links() }}
</div>
@endif
@endsection
