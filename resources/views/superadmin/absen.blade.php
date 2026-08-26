@extends('layouts.dashboard')
@section('page-title', 'Absen Driver')

@section('content')
@php
    $today = now()->format('Y-m-d');
    $drivers = \App\Models\Driver::with('user')->where('is_active', true)->get();
@endphp

<div class="flex items-center justify-between mb-5">
    <div>
        <p class="text-[13px] text-gray-400">{{ now()->format('l, d F Y') }}</p>
    </div>
    <div class="flex items-center gap-2">
        <span class="badge badge-green">{{ $drivers->where('status', 'active')->count() }} Hadir</span>
        <span class="badge badge-yellow">{{ $drivers->where('status', 'on_trip')->count() }} Bertugas</span>
        <span class="badge badge-gray">{{ $drivers->where('status', 'inactive')->count() }} Absen</span>
    </div>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Driver</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">SIM</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Gaji/Hari</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status Hari Ini</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Perjalanan Aktif</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $d)
                @php
                    $activeTrips = \App\Models\Booking::where('driver_id', $d->id)->where('status', 'ongoing')->count();
                @endphp
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30">
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-sky-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-sky-700 font-bold text-[11px]">{{ strtoupper(substr($d->user?->name ?? '', 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-navy-800">{{ $d->user?->name }}</p>
                                <p class="text-[11px] text-gray-400">{{ $d->user?->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-5 text-navy-600 text-[12px]">{{ $d->license_number ?? '-' }}</td>
                    <td class="py-3 px-5 font-medium text-navy-700">Rp {{ number_format($d->daily_salary, 0, ',', '.') }}</td>
                    <td class="py-3 px-5 text-center">
                        @if($d->status == 'active')
                            <span class="badge badge-green">Hadir</span>
                        @elseif($d->status == 'on_trip')
                            <span class="badge badge-yellow">Sedang Bertugas</span>
                        @elseif($d->status == 'off_duty')
                            <span class="badge badge-gray">Istirahat</span>
                        @else
                            <span class="badge badge-red">Tidak Aktif</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-center">
                        @if($activeTrips > 0)
                            <span class="text-amber-600 font-semibold">{{ $activeTrips }}</span>
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-[12px] text-gray-400">{{ $d->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-12 text-center text-gray-300">Belum ada driver terdaftar</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
