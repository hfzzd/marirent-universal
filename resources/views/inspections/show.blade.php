@extends('layouts.dashboard')
@section('page-title', 'Detail Inspeksi')
@section('content')
<div class="max-w-4xl">
    <a href="{{ route('inspections.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>

    <div class="bg-white rounded-2xl shadow-sm p-8">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-navy-900">Detail Inspeksi #{{ $inspection->id }}</h2>
                <p class="text-sm text-navy-500 mt-1">{{ $inspection->created_at->format('d M Y H:i') }}</p>
            </div>
            <div class="flex gap-2">
                @php
                    $scopeColors = ['kendaraan' => 'bg-blue-100 text-blue-700', 'elektronik' => 'bg-purple-100 text-purple-700', 'camping' => 'bg-green-100 text-green-700'];
                @endphp
                <span class="px-3 py-1.5 rounded-full text-sm font-medium {{ $scopeColors[$inspection->scope] ?? 'bg-gray-100 text-gray-700' }}">{{ $inspection->getScopeLabel() }}</span>
                <span class="px-3 py-1.5 rounded-full text-sm font-medium {{ $inspection->type === 'pre_rental' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700' }}">{{ $inspection->getTypeLabel() }}</span>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-navy-500">Booking</p>
                    <p class="font-bold text-navy-800">{{ $inspection->booking->booking_code ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-navy-500">Item yang Diinspeksi</p>
                    <p class="font-bold text-navy-800">{{ $inspection->getItemName() }}</p>
                </div>
                <div>
                    <p class="text-xs text-navy-500">Inspektur</p>
                    <p class="font-bold text-navy-800">{{ $inspection->inspector->name ?? '-' }}</p>
                </div>
            </div>
            <div class="space-y-3">
                @if($inspection->usage_duration_hours)
                <div>
                    <p class="text-xs text-navy-500">Lama Pemakaian</p>
                    <p class="font-bold text-navy-800">{{ $inspection->getUsageDurationLabel() }}</p>
                </div>
                @endif
                @if($inspection->fuel_level !== null)
                <div>
                    <p class="text-xs text-navy-500">Level Bensin</p>
                    <p class="font-bold text-navy-800">{{ $inspection->fuel_level }}%</p>
                </div>
                @endif
                @if($inspection->odometer_reading)
                <div>
                    <p class="text-xs text-navy-500">Odometer</p>
                    <p class="font-bold text-navy-800">{{ number_format($inspection->odometer_reading, 1) }} km</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Vehicle Conditions (if applicable) --}}
        @if($inspection->scope === 'kendaraan')
        <div class="mb-6">
            <h3 class="text-sm font-bold text-navy-800 mb-3"><i class="fas fa-gauge-high mr-2 text-emerald-500"></i>Kondisi Kendaraan</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach([
                    'exterior_condition' => 'Eksterior',
                    'interior_condition' => 'Interior',
                    'engine_condition' => 'Mesin',
                    'tire_condition' => 'Ban',
                    'brake_condition' => 'Rem',
                    'electrical_condition' => 'Kelistrikan',
                ] as $field => $label)
                @if($inspection->$field)
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium text-navy-600">{{ $label }}</span>
                        <span class="font-bold text-navy-800">{{ $inspection->$field }}/10</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                        <div class="h-full rounded-full {{ $inspection->$field >= 7 ? 'bg-emerald-500' : ($inspection->$field >= 4 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $inspection->$field * 10 }}%"></div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Overall Condition --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-navy-800 mb-3"><i class="fas fa-star mr-2 text-emerald-500"></i>Kondisi Keseluruhan</h3>
            <div class="bg-gray-50 rounded-xl p-4 flex items-center gap-4">
                <div class="text-3xl font-black text-navy-800">{{ $inspection->overall_condition ?? '-' }}/10</div>
                <div>
                    <p class="text-sm font-bold text-navy-800">{{ $inspection->getConditionLabel() }}</p>
                    <div class="w-48 h-2 rounded-full bg-gray-200 overflow-hidden mt-1">
                        <div class="h-full rounded-full {{ ($inspection->overall_condition ?? 0) >= 7 ? 'bg-emerald-500' : (($inspection->overall_condition ?? 0) >= 4 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ ($inspection->overall_condition ?? 0) * 10 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Damage Items --}}
        @if(!empty($inspection->damage_items) && count($inspection->damage_items) > 0)
        <div class="mb-6">
            <h3 class="text-sm font-bold text-navy-800 mb-3"><i class="fas fa-triangle-exclamation mr-2 text-red-500"></i>Temuan Kerusakan ({{ count($inspection->damage_items) }})</h3>
            <div class="bg-red-50 rounded-xl p-4">
                <ul class="space-y-1.5">
                    @foreach($inspection->damage_items as $damage)
                    <li class="flex items-start gap-2 text-sm text-red-700">
                        <i class="fas fa-circle text-[5px] text-red-400 mt-1.5"></i>
                        {{ $damage }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- Completeness --}}
        @if(!empty($inspection->completeness) && count($inspection->completeness) > 0)
        <div class="mb-6">
            <h3 class="text-sm font-bold text-navy-800 mb-3"><i class="fas fa-list-check mr-2 text-blue-500"></i>Kelengkapan ({{ count($inspection->completeness) }})</h3>
            <div class="bg-blue-50 rounded-xl p-4">
                <ul class="space-y-1.5">
                    @foreach($inspection->completeness as $item)
                    <li class="flex items-start gap-2 text-sm text-blue-700">
                        <i class="fas fa-check-circle text-blue-400 mt-0.5"></i>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- Notes & Recommendations --}}
        @if($inspection->notes || $inspection->recommendations)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($inspection->notes)
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs text-navy-500 mb-1"><i class="fas fa-sticky-note mr-1"></i>Catatan</p>
                <p class="text-sm text-navy-700">{{ $inspection->notes }}</p>
            </div>
            @endif
            @if($inspection->recommendations)
            <div class="bg-amber-50 rounded-xl p-4">
                <p class="text-xs text-navy-500 mb-1"><i class="fas fa-lightbulb mr-1"></i>Rekomendasi</p>
                <p class="text-sm text-navy-700">{{ $inspection->recommendations }}</p>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
