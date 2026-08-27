@extends('layouts.app')
@section('title', 'Detail Kendaraan - MariRental')

@section('content')
<div class="pt-20 lg:pt-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Breadcrumb --}}
        <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">Beranda</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('vehicles.index') }}" class="hover:text-primary transition-colors">Kendaraan</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-secondary font-medium">Toyota Avanza</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left: Image Gallery & Details --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Image Gallery --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ activeImage: 0 }">
                    @php
                        $images = [
                            'https://placehold.co/800x450/2563eb/ffffff?text=Toyota+Avanza+Front',
                            'https://placehold.co/800x450/1e293b/ffffff?text=Toyota+Avanza+Side',
                            'https://placehold.co/800x450/10b981/ffffff?text=Toyota+Avanza+Interior',
                            'https://placehold.co/800x450/7c3aed/ffffff?text=Toyota+Avanza+Rear',
                        ];
                    @endphp

                    {{-- Main Image --}}
                    <div class="relative">
                        <img :src="'{{ implode("','", $images) }}'.split(',')[activeImage]" alt="Toyota Avanza" class="w-full h-[300px] sm:h-[450px] object-cover">
                        <div class="absolute top-4 left-4">
                            <span class="bg-primary text-white text-sm font-semibold px-4 py-2 rounded-xl">Mobil</span>
                        </div>
                        <div class="absolute top-4 right-4">
                            <span class="bg-green-500 text-white text-sm font-semibold px-4 py-2 rounded-xl">Tersedia</span>
                        </div>
                    </div>

                    {{-- Thumbnails --}}
                    <div class="flex items-center space-x-3 p-4">
                        @foreach($images as $index => $img)
                            <button @click="activeImage = {{ $index }}" :class="activeImage === {{ $index }} ? 'ring-2 ring-primary' : 'opacity-60 hover:opacity-100'" class="flex-shrink-0 rounded-xl overflow-hidden transition-all">
                                <img src="{{ $img }}" alt="Thumbnail" class="w-20 h-14 object-cover">
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Vehicle Info --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-gray-400 text-sm">Toyota</p>
                            <h1 class="text-2xl sm:text-3xl font-bold text-secondary">Toyota Avanza 1.5 G CVT</h1>
                            <div class="flex items-center space-x-2 mt-2">
                                <div class="flex items-center space-x-1">
                                    <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span class="text-sm font-semibold text-secondary">4.8</span>
                                </div>
                                <span class="text-gray-300">|</span>
                                <span class="text-sm text-gray-500">24 ulasan</span>
                            </div>
                        </div>
                    </div>

                    {{-- Pricing --}}
                    <div class="bg-gradient-to-r from-primary/5 to-blue-50 rounded-xl p-5 mb-6">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-primary">Rp 350.000</p>
                                <p class="text-gray-500 text-sm">per hari</p>
                            </div>
                            <div class="text-center border-x border-gray-200">
                                <p class="text-2xl font-bold text-primary">Rp 2.100.000</p>
                                <p class="text-gray-500 text-sm">per minggu</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-primary">Rp 7.500.000</p>
                                <p class="text-gray-500 text-sm">per bulan</p>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="mb-6">
                        <h3 class="font-bold text-secondary text-lg mb-3">Deskripsi</h3>
                        <p class="text-gray-600 leading-relaxed">Toyota Avanza 1.5 G CVT adalah mobil MPV yang sempurna untuk perjalanan keluarga atau bisnis. Dilengkapi dengan mesin 1.5L yang irit bahan bakar dan transmisi CVT yang halus. Interior luas dengan 7 kursi yang nyaman, fitur keselamatan lengkap termasuk 6 airbags, ABS, dan EBD. AC dingin, audio system dengan Bluetooth, dan kamera parkir belakang.</p>
                    </div>

                    {{-- Specifications --}}
                    <div>
                        <h3 class="font-bold text-secondary text-lg mb-4">Spesifikasi</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center space-x-3 bg-gray-50 rounded-xl p-4">
                                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Tahun</p>
                                    <p class="text-sm font-semibold text-secondary">2024</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3 bg-gray-50 rounded-xl p-4">
                                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Warna</p>
                                    <p class="text-sm font-semibold text-secondary">Putih</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3 bg-gray-50 rounded-xl p-4">
                                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Transmisi</p>
                                    <p class="text-sm font-semibold text-secondary">Automatic (CVT)</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3 bg-gray-50 rounded-xl p-4">
                                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Kapasitas</p>
                                    <p class="text-sm font-semibold text-secondary">7 Penumpang</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3 bg-gray-50 rounded-xl p-4">
                                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Bahan Bakar</p>
                                    <p class="text-sm font-semibold text-secondary">Bensin</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3 bg-gray-50 rounded-xl p-4">
                                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Plat Nomor</p>
                                    <p class="text-sm font-semibold text-secondary">B 1234 ABC</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Booking Form --}}
            <div class="space-y-6">
                {{-- Booking Form --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24" x-data="{ withDriver: false }">
                    <h3 class="font-bold text-secondary text-lg mb-5">Pesan Sekarang</h3>

                    <form action="{{ route('rentals.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="vehicle_id" value="1">

                        <div class="space-y-4">
                            {{-- Start Date --}}
                            <div>
                                <label class="text-sm font-medium text-secondary block mb-1.5">Tanggal Mulai</label>
                                <input type="date" name="start_date" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- End Date --}}
                            <div>
                                <label class="text-sm font-medium text-secondary block mb-1.5">Tanggal Selesai</label>
                                <input type="date" name="end_date" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- With Driver --}}
                            <div class="bg-gray-50 rounded-xl p-4">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm font-medium text-secondary">Dengan Driver</span>
                                    <input type="checkbox" x-model="withDriver" name="with_driver" class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary/20">
                                </label>
                                <p x-show="withDriver" x-cloak class="text-xs text-gray-500 mt-2">Biaya driver: Rp 150.000/hari</p>
                            </div>

                            {{-- Pickup Location --}}
                            <div>
                                <label class="text-sm font-medium text-secondary block mb-1.5">Lokasi Pengambilan</label>
                                <select name="pickup_location" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 pr-8">
                                    <option>Kantor Pusat MariRental</option>
                                    <option>Bandara Soekarno-Hatta</option>
                                    <option>Stasiun Gambir</option>
                                    <option>Hotel (Delivery)</option>
                                </select>
                            </div>

                            {{-- Notes --}}
                            <div>
                                <label class="text-sm font-medium text-secondary block mb-1.5">Catatan</label>
                                <textarea name="notes" rows="3" placeholder="Catatan tambahan..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 resize-none"></textarea>
                            </div>
                        </div>

                        {{-- Price Summary --}}
                        <div class="bg-gray-50 rounded-xl p-4 mt-6 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Sewa (3 hari)</span>
                                <span class="text-secondary font-medium">Rp 1.050.000</span>
                            </div>
                            <div x-show="withDriver" x-cloak class="flex justify-between text-sm">
                                <span class="text-gray-500">Driver (3 hari)</span>
                                <span class="text-secondary font-medium">Rp 450.000</span>
                            </div>
                            <hr class="border-gray-200">
                            <div class="flex justify-between">
                                <span class="font-semibold text-secondary">Total</span>
                                <span class="font-bold text-primary text-lg">Rp 1.050.000</span>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-primary hover:bg-blue-700 text-white py-4 rounded-xl font-bold text-lg transition-colors mt-6 shadow-lg shadow-primary/30 hover:shadow-xl">
                            Booking Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Related Vehicles --}}
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-secondary mb-8">Kendaraan Serupa</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $relatedVehicles = [
                        ['name' => 'Honda Mobilio', 'category' => 'Mobil', 'price' => 320000, 'image' => 'https://placehold.co/400x250/2563eb/ffffff?text=Mobilio', 'rating' => 4.6],
                        ['name' => 'Daihatsu Xenia', 'category' => 'Mobil', 'price' => 330000, 'image' => 'https://placehold.co/400x250/2563eb/ffffff?text=Xenia', 'rating' => 4.5],
                        ['name' => 'Suzuki Ertiga', 'category' => 'Mobil', 'price' => 360000, 'image' => 'https://placehold.co/400x250/2563eb/ffffff?text=Ertiga', 'rating' => 4.7],
                        ['name' => 'Toyota Rush', 'category' => 'Mobil', 'price' => 400000, 'image' => 'https://placehold.co/400x250/2563eb/ffffff?text=Rush', 'rating' => 4.8],
                    ];
                @endphp

                @foreach($relatedVehicles as $related)
                    <a href="{{ route('vehicles.show', 1) }}" class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 group">
                        <div class="relative overflow-hidden">
                            <img src="{{ $related['image'] }}" alt="{{ $related['name'] }}" class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-500">
                            <span class="absolute top-3 left-3 bg-primary/90 backdrop-blur-sm text-white text-xs font-semibold px-3 py-1.5 rounded-lg">{{ $related['category'] }}</span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-secondary">{{ $related['name'] }}</h3>
                            <div class="flex items-center justify-between mt-3">
                                <span class="text-primary font-bold">Rp {{ number_format($related['price'], 0, ',', '.') }}<span class="text-gray-400 text-xs font-normal">/hari</span></span>
                                <div class="flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span class="text-xs font-semibold text-secondary">{{ $related['rating'] }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
