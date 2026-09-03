@extends('layouts.dashboard')
@section('page-title', 'Detail Laporan Perjalanan')
@section('content')
<div class="max-w-4xl">
    <a href="{{ route('reports.index') }}" class="text-sky-600 text-[13px] mb-5 inline-flex items-center hover:text-sky-700 transition"><i class="fas fa-arrow-left mr-1.5"></i> Kembali ke Laporan</a>

    {{-- Header --}}
    <div class="glass-card rounded-2xl p-6 mb-6">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-500/25 flex-shrink-0">
                    <i class="fas fa-route text-white text-lg"></i>
                </div>
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-navy-900">Laporan Perjalanan</h2>
                    <p class="text-[12px] text-gray-400 mt-0.5">Booking: <span class="font-semibold text-sky-600">{{ $tripReport->booking?->booking_code ?? '-' }}</span></p>
                </div>
            </div>
            @if($tripReport->total_distance)
            <div class="text-right">
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Total Jarak</p>
                <p class="text-xl font-extrabold text-sky-600">{{ number_format($tripReport->total_distance, 1) }} <span class="text-[12px] font-medium">km</span></p>
            </div>
            @endif
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="glass-card rounded-2xl p-4 border border-sky-100/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-500/20"><i class="fas fa-car text-white text-sm"></i></div>
                <div>
                    <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Kendaraan</p>
                    <p class="text-[13px] font-bold text-navy-800 truncate max-w-[120px]">{{ $tripReport->vehicle?->name ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-blue-100/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/20"><i class="fas fa-user-tie text-white text-sm"></i></div>
                <div>
                    <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Driver</p>
                    <p class="text-[13px] font-bold text-navy-800 truncate max-w-[120px]">{{ $tripReport->driver?->user?->name ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-emerald-100/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20"><i class="fas fa-gauge-high text-white text-sm"></i></div>
                <div>
                    <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Odo. Awal</p>
                    <p class="text-[13px] font-bold text-navy-800">{{ $tripReport->start_odometer ? number_format($tripReport->start_odometer, 0) . ' km' : '-' }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 border border-amber-100/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/20"><i class="fas fa-flag-checkered text-white text-sm"></i></div>
                <div>
                    <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Odo. Akhir</p>
                    <p class="text-[13px] font-bold text-navy-800">{{ $tripReport->end_odometer ? number_format($tripReport->end_odometer, 0) . ' km' : '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Informasi Perjalanan --}}
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-[14px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                <i class="fas fa-road text-sky-500"></i> Informasi Perjalanan
            </h3>
            <div class="space-y-3.5 text-[13px]">
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-400">Booking</span>
                    <span class="font-semibold text-sky-600">{{ $tripReport->booking?->booking_code ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-400">Kendaraan</span>
                    <span class="font-medium text-navy-700">{{ $tripReport->vehicle?->name ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-400">Driver</span>
                    <span class="font-medium text-navy-700">{{ $tripReport->driver?->user?->name ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-400">Odometer Awal</span>
                    <span class="font-medium text-navy-700">{{ $tripReport->start_odometer ? number_format($tripReport->start_odometer, 0) . ' km' : '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-400">Odometer Akhir</span>
                    <span class="font-medium text-navy-700">{{ $tripReport->end_odometer ? number_format($tripReport->end_odometer, 0) . ' km' : '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="font-bold text-navy-800">Total Jarak</span>
                    <span class="font-bold text-sky-600 text-[15px]">{{ $tripReport->total_distance ? number_format($tripReport->total_distance, 1) . ' km' : '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Rincian Biaya --}}
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-[14px] font-bold text-navy-800 mb-4 flex items-center gap-2">
                <i class="fas fa-coins text-amber-500"></i> Rincian Biaya
            </h3>
            <div class="space-y-3.5 text-[13px]">
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-400 flex items-center gap-2"><i class="fas fa-gas-pump text-[10px] text-gray-300"></i> BBM</span>
                    <span class="font-medium text-navy-700">Rp {{ number_format($tripReport->fuel_cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-400 flex items-center gap-2"><i class="fas fa-road text-[10px] text-gray-300"></i> Tol</span>
                    <span class="font-medium text-navy-700">Rp {{ number_format($tripReport->toll_cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-400 flex items-center gap-2"><i class="fas fa-square-parking text-[10px] text-gray-300"></i> Parkir</span>
                    <span class="font-medium text-navy-700">Rp {{ number_format($tripReport->parking_cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-400 flex items-center gap-2"><i class="fas fa-ellipsis-h text-[10px] text-gray-300"></i> Lainnya</span>
                    <span class="font-medium text-navy-700">Rp {{ number_format($tripReport->other_cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between py-2.5 bg-gradient-to-r from-sky-50 to-blue-50 -mx-3 px-3 rounded-xl mt-2">
                    <span class="font-bold text-navy-800">Total Operasional</span>
                    <span class="font-bold text-sky-600 text-[15px]">Rp {{ number_format($tripReport->total_operational_cost, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Catatan --}}
    @if($tripReport->notes || $tripReport->issues_reported)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        @if($tripReport->notes)
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-[14px] font-bold text-navy-800 mb-3 flex items-center gap-2">
                <i class="fas fa-sticky-note text-sky-500"></i> Catatan
            </h3>
            <div class="bg-sky-50/60 border border-sky-100 p-4 rounded-xl">
                <p class="text-[13px] text-gray-600 leading-relaxed">{{ $tripReport->notes }}</p>
            </div>
        </div>
        @endif
        @if($tripReport->issues_reported)
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-[14px] font-bold text-navy-800 mb-3 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-red-500"></i> Masalah Dilaporkan
            </h3>
            <div class="bg-red-50/60 border border-red-200 p-4 rounded-xl">
                <p class="text-[13px] text-red-700 leading-relaxed">{{ $tripReport->issues_reported }}</p>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection