@extends('layouts.dashboard')
@section('page-title', 'Detail Inspeksi')
@section('content')
<div class="max-w-4xl">
    <a href="{{ route('inspections.index') }}" class="text-primary-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-bold text-navy-800 mb-4">Informasi Inspeksi</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-navy-500">Kendaraan</span><span class="font-medium">{{ $inspection->vehicle->name }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Tipe</span><span class="font-medium">{{ $inspection->type == 'pre_rental' ? 'Sebelum Rental' : 'Sesudah Rental' }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Inspektur</span><span class="font-medium">{{ $inspection->inspector->name }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Tanggal</span><span class="font-medium">{{ $inspection->created_at->format('d M Y H:i') }}</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Bahan Bakar</span><span class="font-medium">{{ $inspection->fuel_level }}%</span></div>
                <div class="flex justify-between"><span class="text-navy-500">Odometer</span><span class="font-medium">{{ $inspection->odometer_reading ? number_format($inspection->odometer_reading, 0, ',', '.') . ' km' : '-' }}</span></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-bold text-navy-800 mb-4">Kondisi Kendaraan</h3>
            <div class="space-y-3">
                @foreach(['Eksterior' => 'exterior_condition', 'Interior' => 'interior_condition', 'Mesin' => 'engine_condition', 'Ban' => 'tire_condition', 'Rem' => 'brake_condition', 'Kelistrikan' => 'electrical_condition', 'Keseluruhan' => 'overall_condition'] as $label => $key)
                <div class="flex items-center">
                    <span class="text-sm text-navy-600 w-28">{{ $label }}</span>
                    <div class="flex-1 mx-3 bg-gray-200 rounded-full h-3"><div class="bg-primary-500 h-3 rounded-full" style="width:{{ $inspection->$key * 10 }}%"></div></div>
                    <span class="text-sm font-bold text-navy-800 w-10 text-right">{{ $inspection->$key }}/10</span>
                </div>
                @endforeach
            </div>
            @if($inspection->notes)
            <div class="mt-6 bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-navy-500 mb-1">Catatan</p>
                <p class="text-sm text-navy-700">{{ $inspection->notes }}</p>
            </div>
            @endif
            @if($inspection->recommendations)
            <div class="mt-3 bg-yellow-50 p-4 rounded-xl">
                <p class="text-xs text-yellow-700 mb-1">Rekomendasi</p>
                <p class="text-sm text-yellow-800">{{ $inspection->recommendations }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
