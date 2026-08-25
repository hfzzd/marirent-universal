@extends('layouts.dashboard')
@section('page-title', 'Detail Laporan Perjalanan')
@section('content')
<div class="max-w-4xl">
    <a href="{{ route('reports.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-bold text-navy-800 mb-4">Informasi Perjalanan</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-navy-500">Booking</span><span class="font-medium text-sky-600">{{ $tripReport->booking->booking_code ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Kendaraan</span><span class="font-medium">{{ $tripReport->vehicle->name }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Driver</span><span class="font-medium">{{ $tripReport->driver?->user->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Odometer Awal</span><span class="font-medium">{{ $tripReport->start_odometer ? number_format($tripReport->start_odometer, 0) . ' km' : '-' }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Odometer Akhir</span><span class="font-medium">{{ $tripReport->end_odometer ? number_format($tripReport->end_odometer, 0) . ' km' : '-' }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Total Jarak</span><span class="font-bold text-sky-600">{{ $tripReport->total_distance ? number_format($tripReport->total_distance, 1) . ' km' : '-' }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-bold text-navy-800 mb-4">Rincian Biaya</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-navy-500">BBM</span><span class="font-medium">Rp {{ number_format($tripReport->fuel_cost,0,',','.') }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Tol</span><span class="font-medium">Rp {{ number_format($tripReport->toll_cost,0,',','.') }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Parkir</span><span class="font-medium">Rp {{ number_format($tripReport->parking_cost,0,',','.') }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Lainnya</span><span class="font-medium">Rp {{ number_format($tripReport->other_cost,0,',','.') }}</span></div>
                <div class="border-t pt-3 flex justify-between"><span class="font-bold text-navy-800">Total Operasional</span><span class="font-bold text-sky-600">Rp {{ number_format($tripReport->total_operational_cost,0,',','.') }}</span></div>
            </div>
            @if($tripReport->notes)
            <div class="mt-4 bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-navy-500 mb-1">Catatan</p>
                <p class="text-sm text-navy-700">{{ $tripReport->notes }}</p>
            </div>
            @endif
            @if($tripReport->issues_reported)
            <div class="mt-3 bg-red-50 p-4 rounded-xl">
                <p class="text-xs text-red-600 mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Masalah</p>
                <p class="text-sm text-red-700">{{ $tripReport->issues_reported }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
