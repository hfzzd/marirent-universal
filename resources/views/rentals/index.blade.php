@extends('layouts.dashboard')
@section('title', 'Daftar Rental - MariRental')
@section('page-title', 'Rental')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 space-y-4 sm:space-y-0">
    <div>
        <p class="text-gray-500 text-sm">Kelola semua data rental kendaraan</p>
    </div>
    <a href="{{ route('rentals.create') }}" class="bg-sky-500 hover:bg-sky-600 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition-colors flex items-center space-x-2 shadow-lg shadow-sky-500/25">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Buat Rental</span>
    </a>
</div>

{{-- Filters --}}
<form class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6" method="GET" action="{{ route('rentals.index') }}">
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode rental, nama pelanggan..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20">
        </div>
        <select name="status" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-navy-700 focus:outline-none pr-8">
            <option value="">Semua Status</option>
            @foreach(['pending' => 'Pending', 'confirmed' => 'Dikonfirmasi', 'ongoing' => 'Aktif', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-lg shadow-sky-500/25">Filter</button>
    </div>
</form>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Kode</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Pelanggan</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Kendaraan</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Tanggal Mulai</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Tanggal Akhir</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Driver</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($rentals as $rental)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-semibold text-sky-600">{{ $rental->rental_code }}</td>
                        <td class="px-6 py-4 text-sm text-navy-700 font-medium">{{ $rental->user->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $rental->vehicle->name ?? $rental->category_type }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $rental->start_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $rental->end_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $rental->with_driver ? ($rental->driver?->user?->name ?? $rental->driver?->name ?? 'Ya') : 'Tidak' }}</td>
                        <td class="px-6 py-4">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-amber-50 text-amber-700',
                                    'confirmed' => 'bg-blue-50 text-blue-700',
                                    'ongoing' => 'bg-green-50 text-green-700',
                                    'completed' => 'bg-gray-100 text-gray-600',
                                    'cancelled' => 'bg-red-50 text-red-700',
                                ];
                                $statusLabels = [
                                    'pending' => 'Pending', 'confirmed' => 'Dikonfirmasi',
                                    'ongoing' => 'Aktif', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan',
                                ];
                            @endphp
                            <span class="{{ $statusClasses[$rental->status] ?? 'bg-gray-100 text-gray-600' }} text-xs font-semibold px-3 py-1.5 rounded-lg">
                                {{ $statusLabels[$rental->status] ?? ucfirst($rental->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-navy-800">Rp {{ number_format($rental->total_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('rentals.show', $rental->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-600 text-xs font-semibold transition-colors"><i class="fas fa-eye text-[11px]"></i> Detail</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-10 text-center text-sm text-gray-500">Tidak ada data rental.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-100">
        {{ $rentals->links() }}
    </div>
</div>
@endsection
