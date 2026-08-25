@extends('layouts.dashboard')
@section('page-title', 'Penggantian Kendaraan')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex gap-2">
        <a href="{{ route('replacements.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !request('status') ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Semua</a>
        @foreach(['pending','approved','rejected'] as $s)
        <a href="{{ route('replacements.index', ['status' => $s]) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status') == $s ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">{{ ucfirst($s) }}</a>
        @endforeach
    </div>
    @if(in_array(auth()->user()->role, ['user', 'driver']))
    <a href="{{ route('replacements.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm font-medium"><i class="fas fa-plus mr-1"></i> Ajukan Penggantian</a>
    @endif
</div>
<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-sky-50/50 border-b border-sky-100/50">
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Booking</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Kendaraan Asal</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Kendaraan Pengganti</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Tipe</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Foto</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Selisih Harga</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Status</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($replacements as $r)
                <tr class="border-b hover:bg-sky-50/30">
                    <td class="py-3 px-4 font-medium text-sky-600">{{ $r->booking->booking_code }}</td>
                    <td class="py-3 px-4">{{ $r->originalVehicle->name ?? '-' }}</td>
                    <td class="py-3 px-4">{{ $r->replacementVehicle->name }}</td>
                    <td class="py-3 px-4">
                        @if($r->handover_type === 'with_driver')
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700"><i class="fas fa-user-tie mr-1"></i>Dengan Supir</span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700"><i class="fas fa-key mr-1"></i>Lepas Kunci</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex gap-1">
                            @if($r->initial_vehicle_photo)
                            <a href="{{ asset('storage/' . $r->initial_vehicle_photo) }}" target="_blank" class="w-8 h-8 rounded-lg overflow-hidden border border-gray-200 hover:border-sky-400 transition">
                                <img src="{{ asset('storage/' . $r->initial_vehicle_photo) }}" class="w-full h-full object-cover" alt="Awal">
                            </a>
                            @endif
                            @if($r->final_vehicle_photo)
                            <a href="{{ asset('storage/' . $r->final_vehicle_photo) }}" target="_blank" class="w-8 h-8 rounded-lg overflow-hidden border border-gray-200 hover:border-sky-400 transition">
                                <img src="{{ asset('storage/' . $r->final_vehicle_photo) }}" class="w-full h-full object-cover" alt="Akhir">
                            </a>
                            @endif
                            @if(!$r->initial_vehicle_photo && !$r->final_vehicle_photo)
                            <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-4 font-medium {{ $r->price_difference > 0 ? 'text-red-600' : ($r->price_difference < 0 ? 'text-green-600' : 'text-navy-500') }}">
                        {{ $r->price_difference > 0 ? '+' : '' }} Rp {{ number_format($r->price_difference,0,',','.') }}
                    </td>
                    <td class="py-3 px-4"><span class="status-{{ $r->status == 'approved' ? 'completed' : ($r->status == 'rejected' ? 'cancelled' : 'pending') }} px-2 py-1 rounded-full text-xs font-medium capitalize">{{ $r->status }}</span></td>
                    <td class="py-3 px-4">
                        @if($r->status == 'pending' && in_array(auth()->user()->role, ['superadmin','owner']))
                        <form method="POST" action="{{ route('replacements.approve', $r) }}" class="inline">@csrf
                            <button class="text-green-600 text-xs font-medium mr-2"><i class="fas fa-check"></i> Setuju</button>
                        </form>
                        <form method="POST" action="{{ route('replacements.reject', $r) }}" class="inline">@csrf
                            <button class="text-red-600 text-xs font-medium"><i class="fas fa-times"></i> Tolak</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-8 text-center text-navy-400">Belum ada permintaan penggantian</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $replacements->links() }}</div>
</div>
@endsection
