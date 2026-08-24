@extends('layouts.dashboard')
@section('page-title', 'Detail Inspeksi')
@section('content')
<div class="max-w-5xl">
    <a href="{{ route('inspections.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>

    {{-- Header --}}
    <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-1.5">
                    <span class="badge {{ $inspection->type == 'pre_rental' ? 'badge-blue' : 'badge-yellow' }} text-[11px]">{{ $inspection->getTypeLabel() }}</span>
                    <span class="badge badge-gray text-[11px]">{{ $inspection->getScopeLabel() }}</span>
                    @if($inspection->hasDamages()) <span class="badge badge-red text-[11px]">Ada Kerusakan</span>
                    @else <span class="badge badge-green text-[11px]">Kondisi Aman</span> @endif
                </div>
                <h2 class="text-lg font-extrabold text-navy-800">{{ $inspection->getItemName() }}</h2>
                <p class="text-[12px] text-gray-400 mt-0.5">
                    Booking: {{ $inspection->booking?->booking_code ?? '-' }} &bull;
                    Penyewa: {{ $inspection->booking?->user?->name ?? '-' }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Inspektur</p>
                <p class="text-[13px] font-bold text-navy-800">{{ $inspection->inspector?->name ?? '-' }}</p>
                <p class="text-[11px] text-gray-400">{{ $inspection->created_at->translatedFormat('d M Y H:i') }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Kolom Kiri --}}
        <div class="space-y-5">
            {{-- Ringkasan --}}
            <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm">
                <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-gauge-high text-sky-500"></i> Ringkasan</h3>
                <div class="space-y-3 text-[13px]">
                    <div class="flex justify-between"><span class="text-navy-500">Lama Digunakan</span><span class="font-bold text-navy-800">{{ $inspection->getUsageDurationLabel() }}</span></div>
                    @if($inspection->scope === 'kendaraan')
                    <div class="flex justify-between"><span class="text-navy-500">Bahan Bakar</span><span class="font-medium">{{ $inspection->fuel_level }}%</span></div>
                    <div class="flex justify-between"><span class="text-navy-500">Odometer</span><span class="font-medium">{{ $inspection->odometer_reading ? number_format($inspection->odometer_reading, 0, ',', '.') . ' km' : '-' }}</span></div>
                    @endif
                    <div class="pt-3 border-t border-gray-100 flex items-center gap-3">
                        <span class="text-navy-500">Kondisi Keseluruhan</span>
                        <div class="flex-1 mx-1 bg-gray-200 rounded-full h-3"><div class="{{ $inspection->overall_condition >= 7 ? 'bg-emerald-500' : ($inspection->overall_condition >= 4 ? 'bg-amber-500' : 'bg-red-500') }} h-3 rounded-full" style="width:{{ $inspection->overall_condition * 10 }}%"></div></div>
                        <span class="font-black text-navy-800 whitespace-nowrap">{{ $inspection->overall_condition }}/10 &bull; {{ $inspection->getConditionLabel() }}</span>
                    </div>
                </div>
            </div>

            {{-- Detail kondisi kendaraan --}}
            @if($inspection->scope === 'kendaraan')
            <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm">
                <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-car-side text-blue-500"></i> Rincian Kondisi Kendaraan</h3>
                <div class="space-y-3">
                    @foreach(['Eksterior' => 'exterior_condition', 'Interior' => 'interior_condition', 'Mesin' => 'engine_condition', 'Ban' => 'tire_condition', 'Rem' => 'brake_condition', 'Kelistrikan' => 'electrical_condition'] as $label => $key)
                    <div class="flex items-center">
                        <span class="text-[12px] text-navy-600 w-24">{{ $label }}</span>
                        <div class="flex-1 mx-3 bg-gray-200 rounded-full h-2.5"><div class="bg-sky-500 h-2.5 rounded-full" style="width:{{ ($inspection->$key ?? 0) * 10 }}%"></div></div>
                        <span class="text-[12px] font-bold text-navy-800 w-9 text-right">{{ $inspection->$key ?? '-' }}/10</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Kelengkapan barang --}}
            @if($inspection->scope !== 'kendaraan' && $inspection->completeness)
            <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm">
                <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-box-open text-emerald-500"></i> Kelengkapan Barang ({{ count($inspection->completeness) }} tersedia)</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($inspection->completeness as $c)
                    <span class="inline-flex items-center gap-1 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg px-2.5 py-1.5 text-[12px] font-semibold"><i class="fas fa-check text-[9px]"></i>{{ $c }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Kolom Kanan --}}
        <div class="space-y-5">
            {{-- Kerusakan --}}
            <div class="glass-card rounded-2xl p-6 border {{ $inspection->hasDamages() ? 'border-red-100' : 'border-sky-100/50' }} shadow-sm">
                <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-triangle-exclamation {{ $inspection->hasDamages() ? 'text-red-500' : 'text-gray-300' }}"></i> Catatan Kerusakan
                </h3>
                @if($inspection->hasDamages())
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach(array_merge($inspection->damage_items ?? [], is_array($inspection->damages) ? array_filter($inspection->damages, 'is_string') : []) as $d)
                    <span class="inline-flex items-center gap-1.5 bg-red-50 border border-red-200 text-red-700 rounded-lg px-2.5 py-1.5 text-[12px] font-semibold"><i class="fas fa-triangle-exclamation text-[9px]"></i>{{ $d }}</span>
                    @endforeach
                </div>
                @else
                <p class="text-[12px] text-emerald-600 bg-emerald-50 rounded-xl px-4 py-3 font-semibold"><i class="fas fa-circle-check mr-1.5"></i> Tidak ditemukan kerusakan.</p>
                @endif
            </div>

            {{-- Foto Bukti --}}
            <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm">
                <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-images text-violet-500"></i> Foto Bukti</h3>
                @if($inspection->photos && count($inspection->photos))
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($inspection->photos as $photo)
                    <a href="{{ asset('storage/' . $photo) }}" target="_blank" class="group relative aspect-square rounded-xl overflow-hidden border border-gray-100 hover:border-violet-300 transition">
                        <img src="{{ asset('storage/' . $photo) }}" alt="Foto bukti inspeksi" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    </a>
                    @endforeach
                </div>
                @else
                <p class="text-[12px] text-gray-300 text-center py-6"><i class="fas fa-camera-retro text-2xl block mb-2 opacity-40"></i> Tidak ada foto bukti.</p>
                @endif
            </div>

            {{-- Catatan & Rekomendasi --}}
            @if($inspection->notes || $inspection->recommendations)
            <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm space-y-4">
                @if($inspection->notes)
                <div class="bg-gray-50 p-4 rounded-xl">
                    <p class="text-[11px] text-navy-500 font-bold uppercase tracking-wider mb-1">Catatan</p>
                    <p class="text-[13px] text-navy-700">{{ $inspection->notes }}</p>
                </div>
                @endif
                @if($inspection->recommendations)
                <div class="bg-amber-50 p-4 rounded-xl">
                    <p class="text-[11px] text-amber-700 font-bold uppercase tracking-wider mb-1">Rekomendasi</p>
                    <p class="text-[13px] text-amber-800">{{ $inspection->recommendations }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
