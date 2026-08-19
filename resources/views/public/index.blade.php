@extends('layouts.public')
@section('title', 'MariRent - Rental Universal')

@section('content')
@php
    $categories = \App\Models\Category::where('is_active', true)->get();
    $catCounts = [];
    foreach ($categories as $cat) {
        $catCounts[$cat->slug] = match($cat->slug) {
            'mobil' => \App\Models\Vehicle::where('category_id', $cat->id)->where('status', 'available')->where('is_active', true)->count(),
            'motor' => \App\Models\Vehicle::where('category_id', $cat->id)->where('status', 'available')->where('is_active', true)->count(),
            'sewa-hp' => \App\Models\Phone::where('status', 'available')->where('is_active', true)->count(),
            'sewa-kamera' => \App\Models\Camera::where('status', 'available')->where('is_active', true)->count(),
            'sewa-tenda' => \App\Models\CampingEquipment::where('status', 'available')->where('is_active', true)->count(),
            default => 0,
        };
    }
    $featuredVehicles = \App\Models\Vehicle::with(['category', 'reviews'])
        ->where('is_active', true)->where('status', 'available')
        ->inRandomOrder()->limit(6)->get();
@endphp

{{-- HERO --}}
<section class="relative overflow-hidden" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 40%, #0ea5e9 100%);">
    <div class="absolute inset-0">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-white/5 rounded-full translate-y-1/3 -translate-x-1/4"></div>
        <div class="absolute top-1/2 left-1/2 w-[300px] h-[300px] bg-sky-400/10 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="fade-in">
                <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-sky-200 px-4 py-2 rounded-full text-[13px] font-medium mb-6">
                    <i class="fas fa-star text-amber-400"></i> Platform Rental #1 di Indonesia
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-[52px] font-extrabold text-white mb-6 leading-[1.15]">
                    Sewa Kendaraan &<br>Gadget <span class="text-sky-300">Mudah & Cepat</span>
                </h1>
                <p class="text-sky-200/80 text-[15px] max-w-lg mb-8 leading-relaxed">
                    Satu platform untuk sewa mobil, motor, kamera, dan tenda. Dengan driver atau tanpa driver, pilihan ada di tangan Anda.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('products') }}" class="bg-white text-sky-700 hover:bg-sky-50 px-7 py-3.5 rounded-xl font-bold text-[14px] shadow-xl shadow-black/10 transition flex items-center gap-2">
                        <i class="fas fa-search"></i> Lihat Produk
                    </a>
                    <a href="{{ route('about') }}" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white px-7 py-3.5 rounded-xl font-semibold text-[14px] transition border border-white/20">
                        Tentang Kami
                    </a>
                </div>
                <div class="flex items-center gap-6 mt-10">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                        <span class="text-sky-200 text-[13px]">{{ \App\Models\Vehicle::where('status','available')->count() }}+ Unit Tersedia</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></div>
                        <span class="text-sky-200 text-[13px]">{{ \App\Models\Booking::where('status','completed')->count() }}+ Booking Selesai</span>
                    </div>
                </div>
            </div>
            <div class="hidden lg:block relative">
                <div class="w-80 h-80 bg-white/10 rounded-3xl rotate-12 absolute -top-6 -right-6"></div>
                <div class="w-72 h-72 bg-white/10 rounded-3xl -rotate-6 absolute bottom-0 -left-4"></div>
                <div class="relative bg-white/10 backdrop-blur-md rounded-3xl p-8 border border-white/20 shadow-2xl">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white/10 rounded-2xl p-5 text-center">
                            <i class="fas fa-car text-sky-300 text-3xl mb-3"></i>
                            <p class="text-2xl font-bold text-white">{{ $catCounts['mobil'] ?? 0 }}</p>
                            <p class="text-sky-300 text-[11px] font-medium">Mobil</p>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-5 text-center">
                            <i class="fas fa-motorcycle text-amber-300 text-3xl mb-3"></i>
                            <p class="text-2xl font-bold text-white">{{ $catCounts['motor'] ?? 0 }}</p>
                            <p class="text-sky-300 text-[11px] font-medium">Motor</p>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-5 text-center">
                            <i class="fas fa-camera text-violet-300 text-3xl mb-3"></i>
                            <p class="text-2xl font-bold text-white">{{ $catCounts['sewa-kamera'] ?? 0 }}</p>
                            <p class="text-sky-300 text-[11px] font-medium">Kamera</p>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-5 text-center">
                            <i class="fas fa-campground text-emerald-300 text-3xl mb-3"></i>
                            <p class="text-2xl font-bold text-white">{{ $catCounts['sewa-tenda'] ?? 0 }}</p>
                            <p class="text-sky-300 text-[11px] font-medium">Tenda</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CATEGORIES --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20 mb-20">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($categories as $cat)
        <a href="{{ route('products', ['category' => $cat->slug]) }}" class="category-card bg-white rounded-2xl p-6 text-center shadow-lg border-2 border-transparent hover:border-sky-400">
            <div class="w-14 h-14 mx-auto mb-3 bg-sky-50 rounded-2xl flex items-center justify-center">
                @if($cat->slug == 'mobil') <i class="fas fa-car text-sky-500 text-xl"></i>
                @elseif($cat->slug == 'motor') <i class="fas fa-motorcycle text-amber-500 text-xl"></i>
                @elseif($cat->slug == 'sewa-hp') <i class="fas fa-mobile-alt text-violet-500 text-xl"></i>
                @elseif($cat->slug == 'sewa-kamera') <i class="fas fa-camera text-violet-500 text-xl"></i>
                @else <i class="fas fa-campground text-emerald-500 text-xl"></i>
                @endif
            </div>
            <h3 class="font-bold text-navy-800 text-[15px] mb-1">{{ $cat->name }}</h3>
            <p class="text-[12px] text-gray-400">{{ $catCounts[$cat->slug] ?? 0 }} unit tersedia</p>
        </a>
        @endforeach
    </div>
</section>

{{-- FEATURED PRODUCTS --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20">
    <div class="flex items-end justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-navy-900 mb-1">Produk Terpopuler</h2>
            <p class="text-gray-400 text-[13px]">Pilihan terbaik untuk kebutuhan Anda</p>
        </div>
        <a href="{{ route('products') }}" class="text-sky-600 text-[13px] font-semibold hover:text-sky-700 hidden md:block">Lihat Semua <i class="fas fa-arrow-right ml-1"></i></a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($featuredVehicles as $v)
        <div class="vehicle-card bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <div class="relative h-48 bg-gradient-to-br from-sky-50 to-sky-100 flex items-center justify-center overflow-hidden">
                @if($v->image)
                    <img src="{{ asset('storage/' . $v->image) }}" alt="{{ $v->name }}" class="w-full h-full object-cover">
                @else
                    @if($v->category->slug == 'mobil') <i class="fas fa-car text-sky-200 text-6xl"></i>
                    @elseif($v->category->slug == 'motor') <i class="fas fa-motorcycle text-amber-200 text-6xl"></i>
                    @elseif($v->category->slug == 'sewa-kamera') <i class="fas fa-camera text-violet-200 text-6xl"></i>
                    @else <i class="fas fa-campground text-emerald-200 text-6xl"></i>
                    @endif
                @endif
                <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-navy-700 px-3 py-1 rounded-full text-[11px] font-semibold">{{ $v->category->name }}</span>
                @if($v->with_driver)
                <span class="absolute top-3 right-3 bg-sky-600/90 text-white px-3 py-1 rounded-full text-[11px] font-semibold"><i class="fas fa-user-tie mr-1"></i> Driver</span>
                @endif
            </div>
            <div class="p-5">
                <h3 class="font-bold text-navy-900 text-[15px] mb-1">{{ $v->name }}</h3>
                <p class="text-[12px] text-gray-400 mb-3">{{ $v->brand }} {{ $v->model }} {{ $v->year }}</p>
                <div class="flex items-center gap-1 mb-3">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= round($v->getAverageRating()) ? 'text-amber-400' : 'text-gray-200' }} text-[11px]"></i>
                    @endfor
                    <span class="text-[11px] text-gray-400 ml-1">({{ $v->reviews->count() }})</span>
                </div>
                <div class="flex items-end justify-between border-t border-gray-100 pt-3">
                    <div>
                        <p class="text-[11px] text-gray-400">Mulai dari</p>
                        <p class="text-lg font-bold text-sky-600">Rp {{ number_format($v->daily_price, 0, ',', '.') }}<span class="text-[11px] font-normal text-gray-400">/hari</span></p>
                    </div>
                    <a href="{{ route('public.vehicle', $v->slug) }}" class="bg-sky-50 hover:bg-sky-100 text-sky-700 px-4 py-2 rounded-xl text-[12px] font-semibold transition">Detail</a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-16">
            <i class="fas fa-car text-gray-200 text-5xl mb-4"></i>
            <p class="text-gray-400">Belum ada produk tersedia</p>
        </div>
        @endforelse
    </div>
    <div class="text-center mt-8 md:hidden">
        <a href="{{ route('products') }}" class="btn-primary text-white px-6 py-3 rounded-xl font-semibold text-[13px] shadow-lg shadow-sky-500/25">Lihat Semua Produk</a>
    </div>
</section>

{{-- WHY US --}}
<section class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="inline-block bg-sky-50 text-sky-600 px-4 py-1.5 rounded-full text-[12px] font-semibold mb-4">Keunggulan Kami</span>
            <h2 class="text-2xl md:text-3xl font-bold text-navy-900 mb-3">Mengapa Pilih MariRent?</h2>
            <p class="text-gray-400 max-w-lg mx-auto text-[14px]">Layanan rental terbaik dengan fitur lengkap untuk kebutuhan Anda</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center p-7 rounded-2xl bg-sky-50/50 hover:bg-sky-50 transition">
                <div class="w-14 h-14 bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-sky-500/20">
                    <i class="fas fa-shield-alt text-white text-xl"></i>
                </div>
                <h3 class="font-bold text-navy-900 text-[15px] mb-2">Inspeksi Kendaraan</h3>
                <p class="text-gray-500 text-[13px] leading-relaxed">Setiap unit diinspeksi sebelum dan sesudah rental untuk menjamin keamanan.</p>
            </div>
            <div class="text-center p-7 rounded-2xl bg-sky-50/50 hover:bg-sky-50 transition">
                <div class="w-14 h-14 bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-sky-500/20">
                    <i class="fas fa-user-tie text-white text-xl"></i>
                </div>
                <h3 class="font-bold text-navy-900 text-[15px] mb-2">Driver Profesional</h3>
                <p class="text-gray-500 text-[13px] leading-relaxed">Driver terlatih dan bersertifikat untuk kenyamanan perjalanan Anda.</p>
            </div>
            <div class="text-center p-7 rounded-2xl bg-sky-50/50 hover:bg-sky-50 transition">
                <div class="w-14 h-14 bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-sky-500/20">
                    <i class="fas fa-exchange-alt text-white text-xl"></i>
                </div>
                <h3 class="font-bold text-navy-900 text-[15px] mb-2">Ganti Kendaraan</h3>
                <p class="text-gray-500 text-[13px] leading-relaxed">Jika terjadi masalah, unit bisa diganti tanpa biaya tambahan.</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl" style="background: linear-gradient(135deg, #0369a1, #0ea5e9);">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/3"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/3 -translate-x-1/4"></div>
            </div>
            <div class="relative px-8 md:px-14 py-14 text-center">
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">Siap Untuk Menyewa?</h2>
                <p class="text-sky-200 text-[14px] mb-8 max-w-lg mx-auto">Daftar sekarang dan dapatkan akses ke ratusan unit kendaraan dan alat terbaik.</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('register') }}" class="bg-white text-sky-700 hover:bg-sky-50 px-8 py-3.5 rounded-xl font-bold text-[14px] shadow-xl transition">Daftar Gratis</a>
                    <a href="{{ route('products') }}" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white px-8 py-3.5 rounded-xl font-semibold text-[14px] border border-white/20 transition">Lihat Produk</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
