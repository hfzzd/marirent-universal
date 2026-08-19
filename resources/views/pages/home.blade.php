@extends('layouts.app')
@section('title', 'MariRental - Sewa Kendaraan & Gadget Mudah dan Terpercaya')

@section('content')
{{-- Hero Section --}}
<section class="relative min-h-screen flex items-center bg-gradient-to-br from-secondary via-blue-900 to-primary overflow-hidden">
    {{-- Background Pattern --}}
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-20 left-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-accent rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32 lg:py-40">
        <div class="text-center max-w-4xl mx-auto">
            <div class="inline-flex items-center bg-white/10 backdrop-blur-sm rounded-full px-4 py-2 mb-8">
                <span class="w-2 h-2 bg-accent rounded-full mr-2 animate-pulse"></span>
                <span class="text-white/80 text-sm font-medium">Solusi Rental #1 di Indonesia</span>
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-7xl font-bold text-white leading-tight mb-6">
                Sewa Kendaraan & <br class="hidden sm:block">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-accent to-green-300">Gadget</span> Mudah <br class="hidden sm:block">
                dan Terpercaya
            </h1>

            <p class="text-lg sm:text-xl text-white/70 max-w-2xl mx-auto mb-10 leading-relaxed">
                Nikmati perjalanan tanpa khawatir dengan pilihan kendaraan lengkap dan gadget terbaru. Harga terjangkau, pelayanan terbaik.
            </p>

            {{-- Search Bar --}}
            <div class="max-w-3xl mx-auto" x-data="{ category: 'all' }">
                <div class="bg-white rounded-2xl p-2 shadow-2xl">
                    <div class="flex flex-col sm:flex-row items-stretch gap-2">
                        <div class="flex-1 relative">
                            <select x-model="category" class="w-full appearance-none bg-gray-50 rounded-xl px-5 py-4 text-secondary font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 pr-10">
                                <option value="all">Semua Kategori</option>
                                <option value="mobil">Mobil</option>
                                <option value="motor">Motor</option>
                                <option value="hp">Sewa HP</option>
                                <option value="kamera">Kamera</option>
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <a :href="category === 'all' ? '{{ route('vehicles.index') }}' : '{{ route('vehicles.index') }}?category=' + category" class="bg-primary hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-semibold transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <span>Cari</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="flex flex-wrap items-center justify-center gap-8 mt-12">
                <div class="text-center">
                    <p class="text-3xl font-bold text-white">500+</p>
                    <p class="text-white/60 text-sm">Unit Kendaraan</p>
                </div>
                <div class="w-px h-10 bg-white/20 hidden sm:block"></div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-white">10K+</p>
                    <p class="text-white/60 text-sm">Pelanggan Puas</p>
                </div>
                <div class="w-px h-10 bg-white/20 hidden sm:block"></div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-white">50+</p>
                    <p class="text-white/60 text-sm">Lokasi</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Categories Section --}}
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-primary font-semibold text-sm uppercase tracking-wider">Kategori Populer</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-secondary mt-2">Pilih Kebutuhan Anda</h2>
            <p class="text-gray-500 mt-3 max-w-xl mx-auto">Kami menyediakan berbagai pilihan kendaraan dan gadget untuk memenuhi kebutuhan Anda</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- Mobil --}}
            <a href="{{ route('vehicles.index', ['category' => 'mobil']) }}" class="group bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-2xl p-6 sm:p-8 text-center hover:shadow-xl hover:shadow-blue-100 transition-all duration-300 hover:-translate-y-1 border border-transparent hover:border-blue-200">
                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-primary group-hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-primary group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <h3 class="font-bold text-secondary text-lg mb-1">Mobil</h3>
                <p class="text-gray-500 text-sm">{{ $vehicleCounts['mobil'] ?? 120 }} unit tersedia</p>
            </a>

            {{-- Motor --}}
            <a href="{{ route('vehicles.index', ['category' => 'motor']) }}" class="group bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-2xl p-6 sm:p-8 text-center hover:shadow-xl hover:shadow-emerald-100 transition-all duration-300 hover:-translate-y-1 border border-transparent hover:border-emerald-200">
                <div class="w-16 h-16 bg-accent/10 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-accent group-hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-accent group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="font-bold text-secondary text-lg mb-1">Motor</h3>
                <p class="text-gray-500 text-sm">{{ $vehicleCounts['motor'] ?? 80 }} unit tersedia</p>
            </a>

            {{-- Sewa HP --}}
            <a href="{{ route('vehicles.index', ['category' => 'hp']) }}" class="group bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-2xl p-6 sm:p-8 text-center hover:shadow-xl hover:shadow-purple-100 transition-all duration-300 hover:-translate-y-1 border border-transparent hover:border-purple-200">
                <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-purple-500 group-hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-purple-500 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="font-bold text-secondary text-lg mb-1">Sewa HP</h3>
                <p class="text-gray-500 text-sm">{{ $vehicleCounts['hp'] ?? 50 }} unit tersedia</p>
            </a>

            {{-- Kamera --}}
            <a href="{{ route('vehicles.index', ['category' => 'kamera']) }}" class="group bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-2xl p-6 sm:p-8 text-center hover:shadow-xl hover:shadow-amber-100 transition-all duration-300 hover:-translate-y-1 border border-transparent hover:border-amber-200">
                <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-amber-500 group-hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-amber-500 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="font-bold text-secondary text-lg mb-1">Kamera</h3>
                <p class="text-gray-500 text-sm">{{ $vehicleCounts['kamera'] ?? 30 }} unit tersedia</p>
            </a>
        </div>
    </div>
</section>

{{-- Featured Vehicles Section --}}
<section class="py-20 bg-background">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-12">
            <div>
                <span class="text-primary font-semibold text-sm uppercase tracking-wider">Pilihan Terbaik</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-secondary mt-2">Kendaraan Unggulan</h2>
            </div>
            <a href="{{ route('vehicles.index') }}" class="mt-4 sm:mt-0 inline-flex items-center text-primary font-semibold hover:text-blue-700 transition-colors group">
                Lihat Semua
                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $featuredVehicles = $featuredVehicles ?? [
                    ['name' => 'Toyota Avanza', 'category' => 'Mobil', 'price' => 350000, 'image' => 'https://placehold.co/400x250/2563eb/ffffff?text=Toyota+Avanza', 'rating' => 4.8, 'transmission' => 'Automatic', 'capacity' => 7, 'fuel' => 'Bensin'],
                    ['name' => 'Honda Brio', 'category' => 'Mobil', 'price' => 300000, 'image' => 'https://placehold.co/400x250/2563eb/ffffff?text=Honda+Brio', 'rating' => 4.7, 'transmission' => 'Automatic', 'capacity' => 5, 'fuel' => 'Bensin'],
                    ['name' => 'Honda Vario 160', 'category' => 'Motor', 'price' => 100000, 'image' => 'https://placehold.co/400x250/10b981/ffffff?text=Honda+Vario', 'rating' => 4.9, 'transmission' => 'Automatic', 'capacity' => 2, 'fuel' => 'Bensin'],
                    ['name' => 'Yamaha NMAX', 'category' => 'Motor', 'price' => 120000, 'image' => 'https://placehold.co/400x250/10b981/ffffff?text=Yamaha+NMAX', 'rating' => 4.8, 'transmission' => 'Automatic', 'capacity' => 2, 'fuel' => 'Bensin'],
                    ['name' => 'iPhone 15 Pro', 'category' => 'Sewa HP', 'price' => 150000, 'image' => 'https://placehold.co/400x250/7c3aed/ffffff?text=iPhone+15+Pro', 'rating' => 4.9, 'transmission' => '-', 'capacity' => '-', 'fuel' => '-'],
                    ['name' => 'Samsung S24 Ultra', 'category' => 'Sewa HP', 'price' => 130000, 'image' => 'https://placehold.co/400x250/7c3aed/ffffff?text=Samsung+S24', 'rating' => 4.7, 'transmission' => '-', 'capacity' => '-', 'fuel' => '-'],
                    ['name' => 'Sony A7IV', 'category' => 'Kamera', 'price' => 250000, 'image' => 'https://placehold.co/400x250/d97706/ffffff?text=Sony+A7IV', 'rating' => 4.9, 'transmission' => '-', 'capacity' => '-', 'fuel' => '-'],
                    ['name' => 'Toyota Innova Reborn', 'category' => 'Mobil', 'price' => 500000, 'image' => 'https://placehold.co/400x250/2563eb/ffffff?text=Innova+Reborn', 'rating' => 4.8, 'transmission' => 'Automatic', 'capacity' => 7, 'fuel' => 'Diesel'],
                ];
            @endphp

            @foreach($featuredVehicles as $vehicle)
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 group overflow-hidden border border-gray-100 hover:border-gray-200">
                    {{-- Image --}}
                    <div class="relative overflow-hidden">
                        <img src="{{ $vehicle['image'] }}" alt="{{ $vehicle['name'] }}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-3 left-3">
                            <span class="bg-primary/90 backdrop-blur-sm text-white text-xs font-semibold px-3 py-1.5 rounded-lg">{{ $vehicle['category'] }}</span>
                        </div>
                        <div class="absolute top-3 right-3">
                            <button class="w-8 h-8 bg-white/90 backdrop-blur-sm rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold text-secondary text-lg">{{ $vehicle['name'] }}</h3>
                            <div class="flex items-center space-x-1">
                                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span class="text-sm font-semibold text-secondary">{{ $vehicle['rating'] }}</span>
                            </div>
                        </div>

                        {{-- Features --}}
                        <div class="flex items-center space-x-3 text-xs text-gray-500 mb-4">
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

                        {{-- Price & Button --}}
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div>
                                <span class="text-2xl font-bold text-primary">Rp {{ number_format($vehicle['price'], 0, ',', '.') }}</span>
                                <span class="text-gray-400 text-sm">/hari</span>
                            </div>
                            <a href="{{ route('vehicles.show', 1) }}" class="bg-primary hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                                Booking
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- How It Works Section --}}
<section class="py-20 bg-white" id="how-it-works">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-primary font-semibold text-sm uppercase tracking-wider">Cara Kerja</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-secondary mt-2">Sewa Dalam 3 Langkah Mudah</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            {{-- Connection Line --}}
            <div class="hidden md:block absolute top-16 left-1/4 right-1/4 h-0.5 bg-gradient-to-r from-primary via-accent to-primary"></div>

            {{-- Step 1 --}}
            <div class="text-center relative">
                <div class="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-6 relative z-10 shadow-lg shadow-primary/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <span class="text-5xl font-bold text-gray-100 absolute top-10 left-1/2 -translate-x-1/2">01</span>
                <h3 class="text-xl font-bold text-secondary mb-3">Pilih Kendaraan</h3>
                <p class="text-gray-500 max-w-xs mx-auto">Browse katalog kami dan pilih kendaraan atau gadget sesuai kebutuhan Anda</p>
            </div>

            {{-- Step 2 --}}
            <div class="text-center relative">
                <div class="w-16 h-16 bg-accent rounded-2xl flex items-center justify-center mx-auto mb-6 relative z-10 shadow-lg shadow-accent/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <span class="text-5xl font-bold text-gray-100 absolute top-10 left-1/2 -translate-x-1/2">02</span>
                <h3 class="text-xl font-bold text-secondary mb-3">Pesan & Bayar</h3>
                <p class="text-gray-500 max-w-xs mx-auto">Pilih tanggal sewa, lakukan pembayaran, dan konfirmasi pesanan Anda</p>
            </div>

            {{-- Step 3 --}}
            <div class="text-center relative">
                <div class="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-6 relative z-10 shadow-lg shadow-primary/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <span class="text-5xl font-bold text-gray-100 absolute top-10 left-1/2 -translate-x-1/2">03</span>
                <h3 class="text-xl font-bold text-secondary mb-3">Berangkat!</h3>
                <p class="text-gray-500 max-w-xs mx-auto">Ambil kendaraan di lokasi atau kami antar ke tempat Anda. Selamat jalan!</p>
            </div>
        </div>
    </div>
</section>

{{-- Why Choose Us Section --}}
<section class="py-20 bg-gradient-to-br from-secondary to-blue-900" id="about">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-accent font-semibold text-sm uppercase tracking-wider">Mengapa Kami?</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-white mt-2">Keunggulan MariRental</h2>
            <p class="text-gray-400 mt-3 max-w-xl mx-auto">Kami berkomitmen memberikan pelayanan terbaik untuk setiap pelanggan</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Feature 1 --}}
            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:bg-white/10 transition-all duration-300 group">
                <div class="w-14 h-14 bg-primary/20 rounded-xl flex items-center justify-center mb-5 group-hover:bg-primary/30 transition-colors">
                    <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Driver Profesional</h3>
                <p class="text-gray-400 text-sm leading-relaxed">Driver berlisensi dan berpengalaman yang siap menemani perjalanan Anda dengan aman dan nyaman</p>
            </div>

            {{-- Feature 2 --}}
            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:bg-white/10 transition-all duration-300 group">
                <div class="w-14 h-14 bg-accent/20 rounded-xl flex items-center justify-center mb-5 group-hover:bg-accent/30 transition-colors">
                    <svg class="w-7 h-7 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Harga Terjangkau</h3>
                <p class="text-gray-400 text-sm leading-relaxed">Harga transparan tanpa biaya tersembunyi. Tersedia paket harian, mingguan, dan bulanan</p>
            </div>

            {{-- Feature 3 --}}
            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:bg-white/10 transition-all duration-300 group">
                <div class="w-14 h-14 bg-purple-500/20 rounded-xl flex items-center justify-center mb-5 group-hover:bg-purple-500/30 transition-colors">
                    <svg class="w-7 h-7 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Armada Lengkap</h3>
                <p class="text-gray-400 text-sm leading-relaxed">Pilihan kendaraan lengkap dari mobil, motor, hingga gadget terbaru untuk segala kebutuhan</p>
            </div>

            {{-- Feature 4 --}}
            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:bg-white/10 transition-all duration-300 group">
                <div class="w-14 h-14 bg-amber-500/20 rounded-xl flex items-center justify-center mb-5 group-hover:bg-amber-500/30 transition-colors">
                    <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Customer Service 24/7</h3>
                <p class="text-gray-400 text-sm leading-relaxed">Tim support kami siap membantu Anda kapan saja selama 24 jam penuh setiap hari</p>
            </div>
        </div>
    </div>
</section>

{{-- Testimonials Section --}}
<section class="py-20 bg-background">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-primary font-semibold text-sm uppercase tracking-wider">Testimoni</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-secondary mt-2">Apa Kata Pelanggan Kami</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @php
                $testimonials = [
                    ['name' => 'Ahmad Rizky', 'role' => 'Bisnisman', 'text' => 'Pelayanan luar biasa! Kendaraan dalam kondisi prima dan driver sangat profesional. Pasti akan sewa lagi.', 'avatar' => 'AR', 'rating' => 5],
                    ['name' => 'Siti Rahmawati', 'role' => 'Content Creator', 'text' => 'Sewa kamera untuk syuting sangat mudah. Kondisi barang bagus dan harga terjangkau. Highly recommended!', 'avatar' => 'SR', 'rating' => 5],
                    ['name' => 'Budi Santoso', 'role' => 'Wisatawan', 'text' => 'Liburan keluarga jadi lebih menyenangkan dengan sewa mobil dari MariRental. Proses cepat dan pelayanan ramah.', 'avatar' => 'BS', 'rating' => 5],
                ];
            @endphp

            @foreach($testimonials as $testimonial)
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-shadow duration-300 border border-gray-100">
                    <div class="flex items-center space-x-1 mb-4">
                        @for($i = 0; $i < $testimonial['rating']; $i++)
                            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-6">"{{ $testimonial['text'] }}"</p>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                            <span class="text-primary font-semibold text-sm">{{ $testimonial['avatar'] }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-secondary text-sm">{{ $testimonial['name'] }}</p>
                            <p class="text-gray-400 text-xs">{{ $testimonial['role'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA Section --}}
<section class="py-20 bg-gradient-to-r from-primary to-blue-700 relative overflow-hidden" id="contact">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-accent rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Siap Untuk Berangkat?</h2>
        <p class="text-blue-100 text-lg mb-8 max-w-2xl mx-auto">Sewa kendaraan impian Anda sekarang juga dan nikmati perjalanan tanpa khawatir. Hubungi kami untuk reservasi!</p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('vehicles.index') }}" class="bg-white text-primary hover:bg-gray-100 px-8 py-4 rounded-xl font-bold transition-colors shadow-lg">
                Mulai Sewa Sekarang
            </a>
            <a href="https://wa.me/6281234567890" class="bg-accent hover:bg-green-600 text-white px-8 py-4 rounded-xl font-bold transition-colors shadow-lg flex items-center space-x-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                <span>Hubungi via WhatsApp</span>
            </a>
        </div>
    </div>
</section>
@endsection
