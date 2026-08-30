@extends('layouts.public')
@section('title', 'Tentang Kami - MariRent')

@section('content')
{{-- HERO --}}
<section class="hero-section relative py-24 overflow-hidden" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 40%, #0ea5e9 100%);">
    <div class="absolute inset-0">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4 float"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-white/5 rounded-full translate-y-1/3 -translate-x-1/4 float-reverse"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-sky-200 px-4 py-1.5 rounded-full text-[12px] font-semibold mb-5 fade-in-up">
            <i class="fas fa-info-circle"></i> Tentang Kami
        </span>
        <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4 fade-in-up fade-in-up-delay-1">Kenali <span class="text-sky-300">MariRent</span> Lebih Dekat</h1>
        <p class="text-sky-200/80 text-[15px] max-w-xl mx-auto fade-in-up fade-in-up-delay-2">Platform rental universal terpercaya yang hadir untuk memudahkan hidup Anda</p>
    </div>
</section>

{{-- CERITA KAMI --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-24">
        <div class="reveal">
            <span class="inline-block bg-sky-50 text-sky-600 px-4 py-1.5 rounded-full text-[12px] font-semibold mb-4">Cerita Kami</span>
            <h2 class="text-2xl md:text-3xl font-bold text-navy-900 mb-5">Platform Rental yang Lahir dari Kebutuhan Nyata</h2>
            <p class="text-gray-500 text-[14px] leading-relaxed mb-4">
                MariRent hadir sebagai solusi atas kesulitan masyarakat dalam menemukan layanan rental yang mudah, transparan, dan terpercaya. Kami memulai perjalanan ini dengan visi sederhana: membuat semua orang bisa menyewa apa saja dengan mudah.
            </p>
            <p class="text-gray-500 text-[14px] leading-relaxed mb-6">
                Dari mobil, motor, kamera, hingga alat camping — semuanya tersedia dalam satu platform. Kami bekerja sama dengan ribuan mitra untuk memberikan pilihan terbaik dengan harga yang bersahabat.
            </p>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 bg-gradient-to-br from-sky-50 to-sky-100 rounded-2xl hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <p class="text-2xl font-extrabold text-sky-600">500+</p>
                    <p class="text-[11px] text-gray-500 font-medium mt-1">Unit Tersedia</p>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-sky-50 to-sky-100 rounded-2xl hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <p class="text-2xl font-extrabold text-sky-600">2000+</p>
                    <p class="text-[11px] text-gray-500 font-medium mt-1">Pengguna Puas</p>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-sky-50 to-sky-100 rounded-2xl hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <p class="text-2xl font-extrabold text-sky-600">50+</p>
                    <p class="text-[11px] text-gray-500 font-medium mt-1">Kota Terjangkau</p>
                </div>
            </div>
        </div>
        <div class="relative reveal reveal-delay-2">
            <div class="bg-gradient-to-br from-sky-100 to-sky-200 rounded-3xl p-10 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-sky-300/30 rounded-full -translate-y-8 translate-x-8 float"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-sky-300/20 rounded-full translate-y-6 -translate-x-6 float-reverse"></div>
                <div class="grid grid-cols-2 gap-4 relative z-10">
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 text-center shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default">
                        <div class="w-14 h-14 bg-sky-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-car text-sky-500 text-2xl"></i>
                        </div>
                        <p class="text-[14px] font-bold text-navy-800">Mobil</p>
                        <p class="text-[11px] text-gray-400 mt-1">Sewa harian</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 text-center shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default">
                        <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-motorcycle text-amber-500 text-2xl"></i>
                        </div>
                        <p class="text-[14px] font-bold text-navy-800">Motor</p>
                        <p class="text-[11px] text-gray-400 mt-1">Matic & sport</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 text-center shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default">
                        <div class="w-14 h-14 bg-violet-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-camera text-violet-500 text-2xl"></i>
                        </div>
                        <p class="text-[14px] font-bold text-navy-800">Kamera</p>
                        <p class="text-[11px] text-gray-400 mt-1">Mirrorless & DSLR</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 text-center shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default">
                        <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-campground text-emerald-500 text-2xl"></i>
                        </div>
                        <p class="text-[14px] font-bold text-navy-800">Alat Camping</p>
                        <p class="text-[11px] text-gray-400 mt-1">Camping gear</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- VISI MISI --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-24">
        <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 reveal reveal-delay-1">
            <div class="w-14 h-14 bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl flex items-center justify-center mb-5 shadow-lg shadow-sky-500/20 pulse-glow">
                <i class="fas fa-eye text-white text-lg"></i>
            </div>
            <h3 class="font-bold text-navy-900 text-lg mb-3">Visi Kami</h3>
            <p class="text-gray-500 text-[14px] leading-relaxed">Menjadi platform rental terbesar dan terpercaya di Indonesia yang menghubungkan penyewa dengan unit terbaik secara mudah dan transparan.</p>
        </div>
        <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 reveal reveal-delay-2">
            <div class="w-14 h-14 bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl flex items-center justify-center mb-5 shadow-lg shadow-sky-500/20 pulse-glow">
                <i class="fas fa-bullseye text-white text-lg"></i>
            </div>
            <h3 class="font-bold text-navy-900 text-lg mb-3">Misi Kami</h3>
            <ul class="text-gray-500 text-[14px] leading-relaxed space-y-3">
                <li class="flex items-start gap-3">
                    <div class="w-5 h-5 bg-sky-50 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-check text-sky-500 text-[9px]"></i></div>
                    Menyediakan unit rental berkualitas dengan harga transparan
                </li>
                <li class="flex items-start gap-3">
                    <div class="w-5 h-5 bg-sky-50 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-check text-sky-500 text-[9px]"></i></div>
                    Memberikan layanan terbaik untuk setiap pelanggan
                </li>
                <li class="flex items-start gap-3">
                    <div class="w-5 h-5 bg-sky-50 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-check text-sky-500 text-[9px]"></i></div>
                    Mendukung mitra untuk berkembang bersama
                </li>
            </ul>
        </div>
    </div>

    {{-- TIM --}}
    <div class="text-center mb-10 reveal">
        <span class="inline-block bg-sky-50 text-sky-600 px-4 py-1.5 rounded-full text-[12px] font-semibold mb-4">Tim Kami</span>
        <h2 class="text-2xl font-bold text-navy-900">Orang-Orang Hebat di Balik MariRent</h2>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 reveal">
        @foreach([
            ['name' => 'Adi Pratama', 'role' => 'CEO & Founder', 'color' => 'from-sky-400 to-sky-600'],
            ['name' => 'Sari Dewi', 'role' => 'COO', 'color' => 'from-violet-400 to-violet-600'],
            ['name' => 'Budi Santoso', 'role' => 'CTO', 'color' => 'from-emerald-400 to-emerald-600'],
            ['name' => 'Rina Wulan', 'role' => 'Head of Marketing', 'color' => 'from-amber-400 to-amber-600'],
        ] as $m)
        <div class="bg-white rounded-2xl p-6 text-center border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
            <div class="w-16 h-16 bg-gradient-to-br {{ $m['color'] }} rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                <span class="text-white font-bold text-xl">{{ substr($m['name'], 0, 1) }}</span>
            </div>
            <h4 class="font-bold text-navy-800 text-[14px]">{{ $m['name'] }}</h4>
            <p class="text-[12px] text-gray-400 mt-1">{{ $m['role'] }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- CTA --}}
<section class="py-20 reveal">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl gradient-shift" style="background: linear-gradient(135deg, #0369a1, #0ea5e9, #0284c7, #0369a1); background-size: 300% 300%;">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/3"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/3 -translate-x-1/4"></div>
            </div>
            <div class="relative px-8 md:px-14 py-14 text-center">
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">Siap Untuk Menyewa?</h2>
                <p class="text-sky-200 text-[14px] mb-8 max-w-lg mx-auto">Bergabung dengan ribuan pengguna yang sudah merasakan kemudahan MariRent.</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('register') }}" class="bg-white text-sky-700 hover:bg-sky-50 px-8 py-3.5 rounded-xl font-bold text-[14px] shadow-xl transition-all duration-300 hover:scale-105">Daftar Gratis</a>
                    <a href="{{ route('products') }}" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white px-8 py-3.5 rounded-xl font-semibold text-[14px] border border-white/20 transition-all duration-300 hover:scale-105">Lihat Produk</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
