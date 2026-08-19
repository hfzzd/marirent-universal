@extends('layouts.dashboard')
@section('title', 'Daftar Rental - MariRental')
@section('page-title', 'Rental')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 space-y-4 sm:space-y-0">
    <div>
        <p class="text-gray-500 text-sm">Kelola semua data rental kendaraan</p>
    </div>
    <a href="{{ route('rentals.create') }}" class="bg-primary hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition-colors flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Buat Rental</span>
    </a>
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <div class="flex-1">
            <input type="text" placeholder="Cari kode rental, nama pelanggan..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
        </div>
        <select class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-secondary focus:outline-none pr-8">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Dikonfirmasi</option>
            <option value="active">Aktif</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
        </select>
        <input type="date" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
        <button class="bg-primary hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors">
            Filter
        </button>
    </div>
</div>

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
                @php
                    $rentals = [
                        ['code' => 'R001', 'customer' => 'Ahmad Rizky', 'vehicle' => 'Toyota Avanza', 'start' => '15 Agu 2026', 'end' => '17 Agu 2026', 'driver' => 'Ya', 'status' => 'active', 'total' => 'Rp 1.500.000'],
                        ['code' => 'R002', 'customer' => 'Siti Rahmawati', 'vehicle' => 'Honda Vario 160', 'start' => '14 Agu 2026', 'end' => '16 Agu 2026', 'driver' => 'Tidak', 'status' => 'completed', 'total' => 'Rp 200.000'],
                        ['code' => 'R003', 'customer' => 'Budi Santoso', 'vehicle' => 'Innova Reborn', 'start' => '16 Agu 2026', 'end' => '20 Agu 2026', 'driver' => 'Ya', 'status' => 'pending', 'total' => 'Rp 3.000.000'],
                        ['code' => 'R004', 'customer' => 'Diana Putri', 'vehicle' => 'iPhone 15 Pro', 'start' => '15 Agu 2026', 'end' => '16 Agu 2026', 'driver' => '-', 'status' => 'active', 'total' => 'Rp 150.000'],
                        ['code' => 'R005', 'customer' => 'Eko Prasetyo', 'vehicle' => 'Honda Brio', 'start' => '13 Agu 2026', 'end' => '15 Agu 2026', 'driver' => 'Tidak', 'status' => 'completed', 'total' => 'Rp 600.000'],
                        ['code' => 'R006', 'customer' => 'Fani Amalia', 'vehicle' => 'Yamaha NMAX', 'start' => '12 Agu 2026', 'end' => '14 Agu 2026', 'driver' => '-', 'status' => 'cancelled', 'total' => 'Rp 240.000'],
                    ];
                @endphp

                @foreach($rentals as $rental)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-semibold text-primary">{{ $rental['code'] }}</td>
                        <td class="px-6 py-4 text-sm text-secondary font-medium">{{ $rental['customer'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $rental['vehicle'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $rental['start'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $rental['end'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $rental['driver'] }}</td>
                        <td class="px-6 py-4">
                            @if($rental['status'] === 'active')
                                <span class="bg-green-50 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-lg">Aktif</span>
                            @elseif($rental['status'] === 'completed')
                                <span class="bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg">Selesai</span>
                            @elseif($rental['status'] === 'pending')
                                <span class="bg-amber-50 text-amber-700 text-xs font-semibold px-3 py-1.5 rounded-lg">Pending</span>
                            @else
                                <span class="bg-red-50 text-red-700 text-xs font-semibold px-3 py-1.5 rounded-lg">Dibatalkan</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-secondary">{{ $rental['total'] }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('rentals.show', 1) }}" class="text-primary hover:text-blue-700 text-sm font-medium">Detail</a>
                                <span class="text-gray-300">|</span>
                                <a href="{{ route('invoices.show', 1) }}" class="text-accent hover:text-green-600 text-sm font-medium">Invoice</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-100">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Menampilkan 1-6 dari 24 data</p>
            <nav class="flex items-center space-x-1">
                <a href="#" class="px-3 py-2 rounded-lg text-gray-400 hover:bg-gray-100 text-sm">Sebelumnya</a>
                <a href="#" class="px-3 py-2 bg-primary text-white rounded-lg text-sm font-medium">1</a>
                <a href="#" class="px-3 py-2 text-secondary hover:bg-gray-100 rounded-lg text-sm">2</a>
                <a href="#" class="px-3 py-2 text-secondary hover:bg-gray-100 rounded-lg text-sm">3</a>
                <a href="#" class="px-3 py-2 text-secondary hover:bg-gray-100 rounded-lg text-sm">4</a>
                <a href="#" class="px-3 py-2 rounded-lg text-secondary hover:bg-gray-100 text-sm">Selanjutnya</a>
            </nav>
        </div>
    </div>
</div>
@endsection
