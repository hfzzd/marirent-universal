@extends('layouts.dashboard')
@section('title', 'Admin Dashboard - MariRental')
@section('page-title', 'Dashboard Admin')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <span class="text-xs font-semibold text-accent bg-accent/10 px-2.5 py-1 rounded-lg">+8.3%</span>
        </div>
        <h3 class="text-2xl font-bold text-secondary">180</h3>
        <p class="text-gray-500 text-sm mt-1">Total Pelanggan</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
        </div>
        <h3 class="text-2xl font-bold text-secondary">180</h3>
        <p class="text-gray-500 text-sm mt-1">Total Kendaraan</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-secondary text-lg mb-4">Pendapatan Bulanan</h3>
        <div class="h-48 flex items-end justify-between space-x-2">
            @php
                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags'];
                $values = [65, 78, 82, 70, 90, 85, 95, 88];
            @endphp
            @foreach($values as $i => $val)
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-full bg-primary/80 hover:bg-primary rounded-t-lg transition-colors" style="height: {{ $val }}%"></div>
                    <span class="text-xs text-gray-400 mt-2">{{ $months[$i] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-secondary text-lg mb-4">Distribusi Kategori</h3>
        <div class="space-y-4 mt-6">
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Mobil</span>
                    <span class="font-semibold text-secondary">45%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="bg-primary rounded-full h-3" style="width: 45%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Motor</span>
                    <span class="font-semibold text-secondary">30%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="bg-accent rounded-full h-3" style="width: 30%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Sewa HP</span>
                    <span class="font-semibold text-secondary">15%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="bg-purple-500 rounded-full h-3" style="width: 15%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Kamera</span>
                    <span class="font-semibold text-secondary">10%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="bg-amber-500 rounded-full h-3" style="width: 10%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h3 class="font-bold text-secondary text-lg">Rental Terbaru</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Kode</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Pelanggan</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Kendaraan</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach(['R001' => ['Ahmad Rizky', 'Toyota Avanza', 'active', 'Rp 1.050.000'], 'R002' => ['Siti Rahmawati', 'Honda Vario 160', 'completed', 'Rp 300.000'], 'R003' => ['Budi Santoso', 'Innova Reborn', 'pending', 'Rp 2.500.000']] as $code => $data)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-semibold text-primary">{{ $code }}</td>
                        <td class="px-6 py-4 text-sm text-secondary font-medium">{{ $data[0] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $data[1] }}</td>
                        <td class="px-6 py-4">
                            @if($data[2] === 'active')
                                <span class="bg-green-50 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-lg">Aktif</span>
                            @elseif($data[2] === 'completed')
                                <span class="bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg">Selesai</span>
                            @else
                                <span class="bg-amber-50 text-amber-700 text-xs font-semibold px-3 py-1.5 rounded-lg">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-secondary">{{ $data[3] }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('rentals.show', 1) }}" class="text-primary hover:underline text-sm font-medium">Detail</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
