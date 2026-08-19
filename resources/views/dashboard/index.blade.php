@extends('layouts.dashboard')
@section('title', 'Dashboard - MariRental')
@section('page-title', 'Dashboard')

@section('content')
@php
    $role = Auth::user()->role ?? 'user';
@endphp

@if(in_array($role, ['superadmin', 'owner']))
    {{-- Admin Dashboard --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Total Revenue --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-semibold text-accent bg-accent/10 px-2.5 py-1 rounded-lg">+12.5%</span>
            </div>
            <h3 class="text-2xl font-bold text-secondary">Rp 85.400.000</h3>
            <p class="text-gray-500 text-sm mt-1">Total Pendapatan</p>
        </div>

        {{-- Active Rentals --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <span class="text-xs font-semibold text-accent bg-accent/10 px-2.5 py-1 rounded-lg">+5.2%</span>
            </div>
            <h3 class="text-2xl font-bold text-secondary">24</h3>
            <p class="text-gray-500 text-sm mt-1">Rental Aktif</p>
        </div>

        {{-- Available Vehicles --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <span class="text-xs font-semibold text-red-500 bg-red-50 px-2.5 py-1 rounded-lg">-3.1%</span>
            </div>
            <h3 class="text-2xl font-bold text-secondary">156</h3>
            <p class="text-gray-500 text-sm mt-1">Kendaraan Tersedia</p>
        </div>

        {{-- Pending Invoices --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                </div>
                <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg">Perlu Ditindak</span>
            </div>
            <h3 class="text-2xl font-bold text-secondary">8</h3>
            <p class="text-gray-500 text-sm mt-1">Invoice Pending</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Chart Placeholder --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-secondary text-lg">Pendapatan Bulanan</h3>
                <select class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-secondary focus:outline-none">
                    <option>2026</option>
                    <option>2025</option>
                </select>
            </div>
            <div class="h-64 flex items-end justify-between space-x-2 px-4">
                @php
                    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                    $values = [65, 78, 82, 70, 90, 85, 95, 88, 0, 0, 0, 0];
                @endphp
                @foreach($values as $i => $val)
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full rounded-t-lg {{ $val > 0 ? 'bg-primary/80 hover:bg-primary transition-colors' : 'bg-gray-100' }}" style="height: {{ $val }}%"></div>
                        <span class="text-xs text-gray-400 mt-2">{{ $months[$i] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-secondary text-lg mb-4">Aktivitas Terbaru</h3>
            <div class="space-y-4">
                @php
                    $activities = [
                        ['text' => 'Rental #R001 dikonfirmasi', 'time' => '5 menit lalu', 'color' => 'bg-accent'],
                        ['text' => 'Invoice #INV012 dibayar', 'time' => '30 menit lalu', 'color' => 'bg-primary'],
                        ['text' => 'Driver Andi menyelesaikan trip', 'time' => '1 jam lalu', 'color' => 'bg-purple-500'],
                        ['text' => 'Inspeksi kendaraan #K023 selesai', 'time' => '2 jam lalu', 'color' => 'bg-amber-500'],
                        ['text' => 'Pelanggan baru: Sari Register', 'time' => '3 jam lalu', 'color' => 'bg-pink-500'],
                    ];
                @endphp

                @foreach($activities as $activity)
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 {{ $activity['color'] }} rounded-full mt-2 flex-shrink-0"></div>
                        <div>
                            <p class="text-sm text-secondary">{{ $activity['text'] }}</p>
                            <p class="text-xs text-gray-400">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Recent Rentals Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-secondary text-lg">Rental Terbaru</h3>
            <a href="{{ route('rentals.index') }}" class="text-sm text-primary font-semibold hover:underline">Lihat Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Kode</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Pelanggan</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Kendaraan</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                        $recentRentals = [
                            ['code' => 'R001', 'customer' => 'Ahmad Rizky', 'vehicle' => 'Toyota Avanza', 'date' => '15-17 Agu 2026', 'status' => 'active', 'total' => 'Rp 1.050.000'],
                            ['code' => 'R002', 'customer' => 'Siti Rahmawati', 'vehicle' => 'Honda Vario 160', 'date' => '14-16 Agu 2026', 'status' => 'completed', 'total' => 'Rp 300.000'],
                            ['code' => 'R003', 'customer' => 'Budi Santoso', 'vehicle' => 'Innova Reborn', 'date' => '16-20 Agu 2026', 'status' => 'pending', 'total' => 'Rp 2.500.000'],
                            ['code' => 'R004', 'customer' => 'Diana Putri', 'vehicle' => 'iPhone 15 Pro', 'date' => '15-16 Agu 2026', 'status' => 'active', 'total' => 'Rp 150.000'],
                            ['code' => 'R005', 'customer' => 'Eko Prasetyo', 'vehicle' => 'Honda Brio', 'date' => '13-15 Agu 2026', 'status' => 'completed', 'total' => 'Rp 600.000'],
                        ];
                    @endphp

                    @foreach($recentRentals as $rental)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-semibold text-primary">{{ $rental['code'] }}</td>
                            <td class="px-6 py-4 text-sm text-secondary font-medium">{{ $rental['customer'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $rental['vehicle'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $rental['date'] }}</td>
                            <td class="px-6 py-4">
                                @if($rental['status'] === 'active')
                                    <span class="bg-green-50 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-lg">Aktif</span>
                                @elseif($rental['status'] === 'completed')
                                    <span class="bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg">Selesai</span>
                                @else
                                    <span class="bg-amber-50 text-amber-700 text-xs font-semibold px-3 py-1.5 rounded-lg">Pending</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-secondary">{{ $rental['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@elseif($role === 'user')
    {{-- User Dashboard --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="text-2xl font-bold text-secondary">3</h3>
            <p class="text-gray-500 text-sm">Rental Aktif</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            </div>
            <h3 class="text-2xl font-bold text-secondary">2</h3>
            <p class="text-gray-500 text-sm">Invoice Pending</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-2xl font-bold text-secondary">12</h3>
            <p class="text-gray-500 text-sm">Total Sewa</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-gradient-to-r from-primary to-blue-700 rounded-2xl p-6 mb-8 text-white">
        <div class="flex flex-col sm:flex-row items-center justify-between">
            <div>
                <h3 class="text-xl font-bold mb-1">Butuh Kendaraan?</h3>
                <p class="text-blue-100">Sewa kendaraan impian Anda sekarang juga!</p>
            </div>
            <a href="{{ route('vehicles.index') }}" class="mt-4 sm:mt-0 bg-white text-primary hover:bg-gray-100 px-6 py-3 rounded-xl font-bold transition-colors">
                Mulai Sewa
            </a>
        </div>
    </div>

    {{-- Active Rentals --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <h3 class="font-bold text-secondary text-lg mb-4">Rental Aktif Saya</h3>
        <div class="space-y-4">
            @php
                $myRentals = [
                    ['code' => 'R001', 'vehicle' => 'Toyota Avanza', 'period' => '15-17 Agu 2026', 'status' => 'Aktif'],
                    ['code' => 'R004', 'vehicle' => 'iPhone 15 Pro', 'period' => '15-16 Agu 2026', 'status' => 'Aktif'],
                ];
            @endphp
            @foreach($myRentals as $rental)
                <div class="flex items-center justify-between bg-gray-50 rounded-xl p-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-secondary">{{ $rental['code'] }} - {{ $rental['vehicle'] }}</p>
                            <p class="text-sm text-gray-500">{{ $rental['period'] }}</p>
                        </div>
                    </div>
                    <span class="bg-green-50 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-lg">{{ $rental['status'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

@elseif($role === 'driver')
    {{-- Driver Dashboard --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
            <h3 class="text-2xl font-bold text-secondary">1</h3>
            <p class="text-gray-500 text-sm">Trip Aktif</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-2xl font-bold text-secondary">Rp 2.450.000</h3>
            <p class="text-gray-500 text-sm">Pendapatan Bulan Ini</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-2xl font-bold text-secondary">18</h3>
            <p class="text-gray-500 text-sm">Trip Selesai</p>
        </div>
    </div>

    {{-- Active Trip --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-secondary text-lg mb-4">Trip Aktif</h3>
        <div class="bg-gradient-to-r from-primary/5 to-blue-50 rounded-xl p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                <div>
                    <p class="text-lg font-bold text-secondary">Rental #R001 - Toyota Avanza</p>
                    <p class="text-gray-500 mt-1">Pelanggan: Ahmad Rizky</p>
                    <p class="text-gray-500">15 - 17 Agustus 2026</p>
                </div>
                <a href="{{ route('reports.create') }}" class="mt-4 sm:mt-0 bg-primary hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors">
                    Buat Laporan Trip
                </a>
            </div>
        </div>
    </div>
@endif
@endsection
