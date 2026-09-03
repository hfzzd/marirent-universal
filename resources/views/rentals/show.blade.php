@extends('layouts.dashboard')
@section('title', 'Detail Rental - MariRental')
@section('page-title', 'Detail Rental #' . $rental->rental_code)

@php
    $statusLabels = [
        'pending' => ['Pending', 'bg-amber-50 text-amber-700'],
        'confirmed' => ['Dikonfirmasi', 'bg-blue-50 text-blue-700'],
        'ongoing' => ['Aktif', 'bg-green-50 text-green-700'],
        'completed' => ['Selesai', 'bg-gray-100 text-gray-600'],
        'cancelled' => ['Dibatalkan', 'bg-red-50 text-red-700'],
    ];
    $stLabel = $statusLabels[$rental->status] ?? [ucfirst($rental->status), 'bg-gray-100 text-gray-600'];
@endphp

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 space-y-4 sm:space-y-0">
    <div class="flex items-center space-x-3">
        <a href="{{ route('rentals.index') }}" class="text-gray-400 hover:text-navy-800 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-navy-800">Rental #{{ $rental->rental_code }}</h2>
            <p class="text-sm text-gray-500">Dibuat pada {{ $rental->created_at->format('d F Y') }}</p>
        </div>
    </div>
    <div class="flex items-center flex-wrap gap-3">
        <span class="{{ $stLabel[1] }} text-sm font-semibold px-4 py-2 rounded-xl">{{ $stLabel[0] }}</span>
        @if(in_array($rental->status, ['pending', 'confirmed', 'ongoing']) && (auth()->user()->isSuperAdmin() || auth()->user()->isOwner()))
            <form method="POST" action="{{ route('rentals.complete', $rental->id) }}" class="inline">
                @csrf
                <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors shadow-lg shadow-emerald-500/25">Selesai</button>
            </form>
            @if($rental->status === 'pending' && (auth()->user()->isSuperAdmin() || auth()->user()->isOwner()))
                <form method="POST" action="{{ route('rentals.confirm', $rental->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors shadow-lg shadow-sky-500/25">Konfirmasi</button>
                </form>
            @endif
            @if(in_array($rental->status, ['pending', 'confirmed']))
                <a href="{{ route('rentals.edit', $rental->id) }}" class="bg-gray-100 hover:bg-gray-200 text-navy-800 px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Edit</a>
                <form method="POST" action="{{ route('rentals.cancel', $rental->id) }}" class="inline">
                    @csrf
                    <input type="hidden" name="cancelled_reason" value="Dibatalkan oleh admin">
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Batalkan</button>
                </form>
            @endif
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-navy-800 text-lg mb-4">Informasi Rental</h3>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Kode Rental</p>
                    <p class="font-semibold text-sky-600">{{ $rental->rental_code }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Status</p>
                    <span class="{{ $stLabel[1] }} text-xs font-semibold px-3 py-1.5 rounded-lg">{{ $stLabel[0] }}</span>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Pelanggan</p>
                    <p class="font-semibold text-navy-800">{{ $rental->user->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Kontak</p>
                    <p class="font-semibold text-navy-800">{{ $rental->user->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Kendaraan</p>
                    <p class="font-semibold text-navy-800">{{ $rental->vehicle->name ?? $rental->category_type }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Driver</p>
                    <p class="font-semibold text-navy-800">{{ $rental->with_driver ? ($rental->driver?->user?->name ?? $rental->driver?->name ?? 'Ya') : 'Tanpa Driver' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Tanggal Mulai</p>
                    <p class="font-semibold text-navy-800">{{ $rental->start_date->format('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Tanggal Akhir</p>
                    <p class="font-semibold text-navy-800">{{ $rental->end_date->format('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Lokasi Pengambilan</p>
                    <p class="font-semibold text-navy-800">{{ $rental->pickup_location }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Lokasi Pengantaran</p>
                    <p class="font-semibold text-navy-800">{{ $rental->dropoff_location }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Keperluan</p>
                    <p class="font-semibold text-navy-800">{{ $rental->purpose ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Total Hari</p>
                    <p class="font-semibold text-navy-800">{{ $rental->total_days }} hari</p>
                </div>
            </div>

            @if($rental->notes)
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <p class="text-xs text-gray-400 mb-1">Catatan</p>
                    <p class="text-sm text-gray-600">{{ $rental->notes }}</p>
                </div>
            @endif
            @if($rental->cancelled_reason)
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <p class="text-xs text-gray-400 mb-1">Alasan Pembatalan</p>
                    <p class="text-sm text-red-500">{{ $rental->cancelled_reason }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-navy-800 text-lg mb-4">Ringkasan Pembayaran</h3>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Sewa kendaraan ({{ $rental->total_days }} hari x Rp {{ number_format($rental->daily_rate, 0, ',', '.') }})</span>
                    <span class="text-navy-800 font-medium">Rp {{ number_format($rental->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($rental->driver_fee > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Driver</span>
                        <span class="text-navy-800 font-medium">Rp {{ number_format($rental->driver_fee, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Pajak (11%)</span>
                    <span class="text-navy-800 font-medium">Rp {{ number_format($rental->tax, 0, ',', '.') }}</span>
                </div>
                <hr class="border-gray-100">
                <div class="flex justify-between">
                    <span class="font-semibold text-navy-800">Total</span>
                    <span class="font-bold text-sky-600 text-xl">Rp {{ number_format($rental->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-navy-800 text-lg mb-4">Aksi Cepat</h3>
            <div class="space-y-3">
                @if($rental->vehicle)
                    <a href="{{ route('public.vehicle', $rental->vehicle->slug) }}" class="flex items-center space-x-3 p-3 bg-gray-50 rounded-xl hover:bg-sky-50 transition-colors group">
                        <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center group-hover:bg-sky-200 transition-colors">
                            <i class="fas fa-car text-sky-600"></i>
                        </div>
                        <span class="text-sm font-medium text-navy-800">Lihat Kendaraan</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
