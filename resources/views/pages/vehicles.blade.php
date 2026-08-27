@extends('layouts.app')
@section('title', 'Kendaraan - MariRental')

@section('content')
<div class="pt-20 lg:pt-24">
    {{-- Page Header --}}
    <div class="bg-gradient-to-r from-secondary to-blue-900 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-white">Katalog Kendaraan</h1>
            <p class="text-gray-300 mt-2">Temukan kendaraan dan gadget terbaik untuk kebutuhan Anda</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">

            {{-- Mobile Filter Toggle --}}
            <div class="lg:hidden">
                <button @click="mobileFilter = !mobileFilter" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 flex items-center justify-between text-secondary font-medium">
                    <span class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filter & Pencarian</span>
                    </span>
                    <svg :class="mobileFilter ? 'rotate-180' : ''" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>

            {{-- Sidebar Filter --}}
            <aside x-data="{ mobileFilter: false }" :class="mobileFilter ? 'block' : 'hidden lg:block'" class="w-full lg:w-72 flex-shrink-0">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24 space-y-6">

                    {{-- Search --}}
                    <div>
                        <label class="text-sm font-semibold text-secondary block mb-2">Cari Kendaraan</label>
                        <div class="relative">
                            <input type="text" placeholder="Nama kendaraan..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 pl-10 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="text-sm font-semibold text-secondary block mb-3">Kategori</label>
                        <div class="space-y-2">
                            @foreach(['Mobil', 'Motor', 'Sewa HP', 'Kamera'] as $cat)
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <input type="checkbox" name="category[]" value="{{ strtolower($cat) }}" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary/20">
                                    <span class="text-sm text-gray-600 group-hover:text-secondary transition-colors">{{ $cat }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Price Range --}}
                    <div>
                        <label class="text-sm font-semibold text-secondary block mb-3">Harga per Hari</label>
                        <div class="flex items-center space-x-2">
                            <input type="number" placeholder="Min" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <span class="text-gray-400">-</span>
                            <input type="number" placeholder="Maks" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                    </div>

                    {{-- Transmission --}}
                    <div>
                        <label class="text-sm font-semibold text-secondary block mb-3">Transmisi</label>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <input type="checkbox" name="transmission[]" value="automatic" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary/20">
                                <span class="text-sm text-gray-600 group-hover:text-secondary transition-colors">Automatic</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <input type="checkbox" name="transmission[]" value="manual" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary/20">
                                <span class="text-sm text-gray-600 group-hover:text-secondary transition-colors">Manual</span>
                            </label>
                        </div>
                    </div>

                    {{-- Capacity --}}
                    <div>
                        <label class="text-sm font-semibold text-secondary block mb-3">Kapasitas</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['2', '4', '6', '7+'] as $cap)
                                <button class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 hover:border-primary hover:text-primary transition-colors focus:ring-2 focus:ring-primary/20">{{ $cap }} Orang</button>
                            @endforeach
                        </div>
                    </div>

                    <button class="w-full bg-primary hover:bg-blue-700 text-white py-3 rounded-xl font-semibold transition-colors">
                        Terapkan Filter
                    </button>
                    <button class="w-full text-gray-500 hover:text-secondary text-sm font-medium py-2 transition-colors">
                        Reset Filter
                    </button>
                </div>
            </aside>

            {{-- Main Content --}}
            <div class="flex-1">
                {{-- Sort & Results --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 space-y-4 sm:space-y-0">
                    <p class="text-gray-500 text-sm">Menampilkan <span class="font-semibold text-secondary">128</span> kendaraan</p>
                    <div class="flex items-center space-x-3">
                        <label class="text-sm text-gray-500">Urutkan:</label>
                        <select class="bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-secondary focus:outline-none focus:ring-2 focus:ring-primary/20 pr-8">
                            <option>Terbaru</option>
                            <option>Harga: Rendah ke Tinggi</option>
                            <option>Harga: Tinggi ke Rendah</option>
                            <option>Rating Tertinggi</option>
                        </select>
                    </div>
                </div>

                {{-- Vehicle Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @php
                        $vehicles = [
                            ['name' => 'Toyota Avanza', 'brand' => 'Toyota', 'category' => 'Mobil', 'price' => 350000, 'image' => 'https://placehold.co/400x250/2563eb/ffffff?text=Avanza', 'transmission' => 'Automatic', 'capacity' => 7, 'fuel' => 'Bensin', 'available' => true, 'rating' => 4.8],
                            ['name' => 'Honda Brio', 'brand' => 'Honda', 'category' => 'Mobil', 'price' => 300000, 'image' => 'https://placehold.co/400x250/2563eb/ffffff?text=Brio', 'transmission' => 'Automatic', 'capacity' => 5, 'fuel' => 'Bensin', 'available' => true, 'rating' => 4.7],
                            ['name' => 'Honda Vario 160', 'brand' => 'Honda', 'category' => 'Motor', 'price' => 100000, 'image' => 'https://placehold.co/400x250/10b981/ffffff?text=Vario', 'transmission' => 'Automatic', 'capacity' => 2, 'fuel' => 'Bensin', 'available' => true, 'rating' => 4.9],
                            ['name' => 'Yamaha NMAX', 'brand' => 'Yamaha', 'category' => 'Motor', 'price' => 120000, 'image' => 'https://placehold.co/400x250/10b981/ffffff?text=NMAX', 'transmission' => 'Automatic', 'capacity' => 2, 'fuel' => 'Bensin', 'available' => false, 'rating' => 4.8],
                            ['name' => 'iPhone 15 Pro Max', 'brand' => 'Apple', 'category' => 'Sewa HP', 'price' => 150000, 'image' => 'https://placehold.co/400x250/7c3aed/ffffff?text=iPhone+15', 'transmission' => '-', 'capacity' => '-', 'fuel' => '-', 'available' => true, 'rating' => 4.9],
                            ['name' => 'Sony A7IV Kit', 'brand' => 'Sony', 'category' => 'Kamera', 'price' => 250000, 'image' => 'https://placehold.co/400x250/d97706/ffffff?text=Sony+A7IV', 'transmission' => '-', 'capacity' => '-', 'fuel' => '-', 'available' => true, 'rating' => 4.9],
                            ['name' => 'Toyota Innova Reborn', 'brand' => 'Toyota', 'category' => 'Mobil', 'price' => 500000, 'image' => 'https://placehold.co/400x250/2563eb/ffffff?text=Innova', 'transmission' => 'Automatic', 'capacity' => 7, 'fuel' => 'Diesel', 'available' => true, 'rating' => 4.8],
                            ['name' => 'Samsung Galaxy S24', 'brand' => 'Samsung', 'category' => 'Sewa HP', 'price' => 130000, 'image' => 'https://placehold.co/400x250/7c3aed/ffffff?text=Galaxy+S24', 'transmission' => '-', 'capacity' => '-', 'fuel' => '-', 'available' => true, 'rating' => 4.7],
                            ['name' => 'Honda Beat Street', 'brand' => 'Honda', 'category' => 'Motor', 'price' => 80000, 'image' => 'https://placehold.co/400x250/10b981/ffffff?text=Beat', 'transmission' => 'Automatic', 'capacity' => 2, 'fuel' => 'Bensin', 'available' => true, 'rating' => 4.6],
                        ];
                    @endphp

                    @foreach($vehicles as $vehicle)
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 group overflow-hidden border border-gray-100">
                            <div class="relative overflow-hidden">
                                <img src="{{ $vehicle['image'] }}" alt="{{ $vehicle['name'] }}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute top-3 left-3 flex items-center space-x-2">
                                    <span class="bg-primary/90 backdrop-blur-sm text-white text-xs font-semibold px-3 py-1.5 rounded-lg">{{ $vehicle['category'] }}</span>
                                </div>
                                @if(!$vehicle['available'])
                                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                        <span class="bg-red-500 text-white text-sm font-semibold px-4 py-2 rounded-xl">Tidak Tersedia</span>
                                    </div>
                                @endif
                            </div>

                            <div class="p-5">
                                <div class="flex items-start justify-between mb-1">
                                    <div>
                                        <p class="text-xs text-gray-400">{{ $vehicle['brand'] }}</p>
                                        <h3 class="font-bold text-secondary text-lg">{{ $vehicle['name'] }}</h3>
                                    </div>
                                    <div class="flex items-center space-x-1 bg-amber-50 px-2 py-1 rounded-lg">
                                        <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        <span class="text-xs font-semibold text-secondary">{{ $vehicle['rating'] }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3 text-xs text-gray-500 my-3">
                                    @if($vehicle['transmission'] !== '-')
                                        <span class="flex items-center space-x-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>{{ $vehicle['transmission'] }}</span>
                                        </span>
                                    @endif
                                    @if($vehicle['capacity'] !== '-')
                                        <span class="flex items-center space-x-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>{{ $vehicle['capacity'] }} kursi</span>
                                        </span>
                                    @endif
                                    @if($vehicle['fuel'] !== '-')
                                        <span class="flex items-center space-x-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            <span>{{ $vehicle['fuel'] }}</span>
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div>
                                        <span class="text-2xl font-bold text-primary">Rp {{ number_format($vehicle['price'], 0, ',', '.') }}</span>
                                        <span class="text-gray-400 text-sm">/hari</span>
                                    </div>
                                    <a href="{{ route('vehicles.show', 1) }}" class="{{ $vehicle['available'] ? 'bg-primary hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed' }} text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                                        {{ $vehicle['available'] ? 'Booking' : 'Full' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-10 flex items-center justify-center">
                    <nav class="flex items-center space-x-2">
                        <a href="#" class="px-3 py-2 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <a href="#" class="px-4 py-2 bg-primary text-white rounded-lg font-medium">1</a>
                        <a href="#" class="px-4 py-2 text-secondary hover:bg-gray-100 rounded-lg font-medium transition-colors">2</a>
                        <a href="#" class="px-4 py-2 text-secondary hover:bg-gray-100 rounded-lg font-medium transition-colors">3</a>
                        <span class="px-2 py-2 text-gray-400">...</span>
                        <a href="#" class="px-4 py-2 text-secondary hover:bg-gray-100 rounded-lg font-medium transition-colors">10</a>
                        <a href="#" class="px-3 py-2 rounded-lg text-secondary hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
