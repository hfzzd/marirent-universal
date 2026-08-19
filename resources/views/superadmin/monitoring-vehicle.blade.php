@extends('layouts.dashboard')
@section('page-title', 'Monitoring Vehicle')

@section('content')
@php
    $vehicles = \App\Models\Vehicle::with('category', 'owner')->latest()->paginate(20);
    $totalAvailable = \App\Models\Vehicle::where('status', 'available')->count();
    $totalRented = \App\Models\Vehicle::where('status', 'rented')->count();
    $totalMaintenance = \App\Models\Vehicle::where('status', 'maintenance')->count();
    $totalReserved = \App\Models\Vehicle::where('status', 'reserved')->count();
@endphp

{{-- Summary --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-500/20"><i class="fas fa-check-circle text-white text-sm"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Tersedia</p>
                <p class="text-lg font-bold text-navy-800">{{ $totalAvailable }}</p>
            </div>
        </div>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-amber-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-amber-500/20"><i class="fas fa-key text-white text-sm"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Disewa</p>
                <p class="text-lg font-bold text-navy-800">{{ $totalRented }}</p>
            </div>
        </div>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-red-400 to-red-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-red-500/20"><i class="fas fa-wrench text-white text-sm"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Maintenance</p>
                <p class="text-lg font-bold text-navy-800">{{ $totalMaintenance }}</p>
            </div>
        </div>
    </div>
    <div class="stat-card glass-card rounded-2xl p-5 relative overflow-hidden animate-slide-up">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-sky-500/20"><i class="fas fa-bookmark text-white text-sm"></i></div>
            <div>
                <p class="text-[11px] text-gray-400 font-medium">Direservasi</p>
                <p class="text-lg font-bold text-navy-800">{{ $totalReserved }}</p>
            </div>
        </div>
    </div>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kendaraan</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kategori</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Plat Nomor</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kondisi</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Owner</th>
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Harga/Hari</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $v)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30">
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-3">
                            @php $cat = $v->category->slug ?? ''; @endphp
                            <div class="w-9 h-9 bg-gray-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                @if($cat == 'mobil') <i class="fas fa-car text-blue-500 text-sm"></i>
                                @elseif($cat == 'motor') <i class="fas fa-motorcycle text-amber-500 text-sm"></i>
                                @elseif($cat == 'sewa-kamera') <i class="fas fa-camera text-violet-500 text-sm"></i>
                                @elseif($cat == 'sewa-tenda') <i class="fas fa-campground text-emerald-500 text-sm"></i>
                                @else <i class="fas fa-box text-gray-400 text-sm"></i>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-navy-800">{{ $v->name }}</p>
                                <p class="text-[11px] text-gray-400">{{ $v->brand }} {{ $v->model }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-5 text-navy-600">{{ $v->category->name }}</td>
                    <td class="py-3 px-5 font-mono text-[12px] text-navy-600">{{ $v->license_plate }}</td>
                    <td class="py-3 px-5">
                        @if($v->condition == 'excellent') <span class="badge badge-green">Sangat Baik</span>
                        @elseif($v->condition == 'good') <span class="badge badge-teal">Baik</span>
                        @elseif($v->condition == 'fair') <span class="badge badge-yellow">Cukup</span>
                        @else <span class="badge badge-red">Kurang</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-center">
                        @if($v->status == 'available') <span class="badge badge-green">Tersedia</span>
                        @elseif($v->status == 'rented') <span class="badge badge-yellow">Disewa</span>
                        @elseif($v->status == 'maintenance') <span class="badge badge-red">Maintenance</span>
                        @else <span class="badge badge-blue">Direservasi</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-navy-600 text-[12px]">{{ $v->owner->name ?? '-' }}</td>
                    <td class="py-3 px-5 text-right font-medium text-navy-700">Rp {{ number_format($v->daily_price, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-12 text-center text-gray-300">Belum ada kendaraan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">
        {{ $vehicles->links() }}
    </div>
</div>
@endsection
