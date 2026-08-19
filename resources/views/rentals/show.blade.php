@extends('layouts.dashboard')
@section('title', 'Detail Rental - MariRental')
@section('page-title', 'Detail Rental #R001')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 space-y-4 sm:space-y-0">
    <div class="flex items-center space-x-3">
        <a href="{{ route('rentals.index') }}" class="text-gray-400 hover:text-secondary transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-secondary">Rental #R001</h2>
            <p class="text-sm text-gray-500">Dibuat pada 14 Agustus 2026</p>
        </div>
    </div>
    <div class="flex items-center space-x-3">
        <span class="bg-green-50 text-green-700 text-sm font-semibold px-4 py-2 rounded-xl">Aktif</span>
        <div class="flex items-center space-x-2">
            <button class="bg-accent hover:bg-green-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Selesai</button>
            <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Batalkan</button>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left: Details --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-secondary text-lg mb-4">Informasi Rental</h3>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Kode Rental</p>
                    <p class="font-semibold text-primary">R001</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Status</p>
                    <span class="bg-green-50 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-lg">Aktif</span>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Pelanggan</p>
                    <p class="font-semibold text-secondary">Ahmad Rizky</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Kontak</p>
                    <p class="font-semibold text-secondary">0812-3456-7890</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Kendaraan</p>
                    <p class="font-semibold text-secondary">Toyota Avanza 1.5 G CVT</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Driver</p>
                    <p class="font-semibold text-secondary">Andi (L-001)</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Tanggal Mulai</p>
                    <p class="font-semibold text-secondary">15 Agustus 2026</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Tanggal Akhir</p>
                    <p class="font-semibold text-secondary">17 Agustus 2026</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Lokasi Pengambilan</p>
                    <p class="font-semibold text-secondary">Kantor Pusat MariRental</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Keperluan</p>
                    <p class="font-semibold text-secondary">Wisata Keluarga</p>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-1">Catatan</p>
                <p class="text-sm text-gray-600">Mohon siapkan car seat untuk anak.</p>
            </div>
        </div>

        {{-- Status Timeline --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-secondary text-lg mb-6">Status Timeline</h3>
            <div class="relative">
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                @php
                    $timeline = [
                        ['status' => 'Dibuat', 'date' => '14 Agu 2026, 10:00', 'desc' => 'Rental dibuat oleh pelanggan', 'active' => true],
                        ['status' => 'Dikonfirmasi', 'date' => '14 Agu 2026, 11:30', 'desc' => 'Dikonfirmasi oleh admin', 'active' => true],
                        ['status' => 'Kendaraan Disiapkan', 'date' => '15 Agu 2026, 08:00', 'desc' => 'Kendaraan telah disiapkan dan diinspeksi', 'active' => true],
                        ['status' => 'Berjalan', 'date' => '15 Agu 2026, 09:00', 'desc' => 'Kendaraan diserahkan ke pelanggan', 'active' => true],
                        ['status' => 'Selesai', 'date' => '', 'desc' => 'Menunggu pengembalian kendaraan', 'active' => false],
                    ];
                @endphp

                @foreach($timeline as $item)
                    <div class="relative flex items-start pb-8 pl-12">
                        <div class="absolute left-2.5 w-3 h-3 rounded-full {{ $item['active'] ? 'bg-primary' : 'bg-gray-300' }} border-2 border-white"></div>
                        <div>
                            <p class="font-semibold text-secondary text-sm {{ !$item['active'] ? 'text-gray-400' : '' }}">{{ $item['status'] }}</p>
                            @if($item['date'])
                                <p class="text-xs text-gray-400">{{ $item['date'] }}</p>
                            @endif
                            <p class="text-sm text-gray-500 mt-0.5">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right: Payment Summary --}}
    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-secondary text-lg mb-4">Ringkasan Pembayaran</h3>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Sewa kendaraan (3 hari x Rp 350.000)</span>
                    <span class="text-secondary font-medium">Rp 1.050.000</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Driver (3 hari x Rp 150.000)</span>
                    <span class="text-secondary font-medium">Rp 450.000</span>
                </div>
                <hr class="border-gray-100">
                <div class="flex justify-between">
                    <span class="font-semibold text-secondary">Total</span>
                    <span class="font-bold text-primary text-xl">Rp 1.500.000</span>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">Status Pembayaran</span>
                    <span class="bg-amber-50 text-amber-700 text-xs font-semibold px-3 py-1.5 rounded-lg">DP Dibayar</span>
                </div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">DP (50%)</span>
                    <span class="text-sm font-semibold text-secondary">Rp 750.000</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Sisa Pembayaran</span>
                    <span class="text-sm font-semibold text-red-500">Rp 750.000</span>
                </div>
            </div>

            <a href="{{ route('invoices.show', 1) }}" class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-secondary py-3 rounded-xl font-semibold text-sm transition-colors mt-6">
                Lihat Invoice
            </a>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-secondary text-lg mb-4">Aksi Cepat</h3>
            <div class="space-y-3">
                <a href="{{ route('inspections.create') }}" class="flex items-center space-x-3 p-3 bg-gray-50 rounded-xl hover:bg-primary/5 transition-colors group">
                    <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-sm font-medium text-secondary">Inspeksi Kendaraan</span>
                </a>
                <a href="{{ route('reports.create') }}" class="flex items-center space-x-3 p-3 bg-gray-50 rounded-xl hover:bg-primary/5 transition-colors group">
                    <div class="w-10 h-10 bg-accent/10 rounded-lg flex items-center justify-center group-hover:bg-accent/20 transition-colors">
                        <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="text-sm font-medium text-secondary">Buat Laporan Trip</span>
                </a>
                <a href="{{ route('invoices.print', 1) }}" class="flex items-center space-x-3 p-3 bg-gray-50 rounded-xl hover:bg-primary/5 transition-colors group">
                    <div class="w-10 h-10 bg-amber-500/10 rounded-lg flex items-center justify-center group-hover:bg-amber-500/20 transition-colors">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    </div>
                    <span class="text-sm font-medium text-secondary">Cetak Invoice</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
