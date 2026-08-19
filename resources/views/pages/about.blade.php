@extends('layouts.public')
@section('title', 'Tentang Kami - MariRent')

@section('content')
{{-- HERO --}}
<section class="relative py-20 overflow-hidden" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 100%);">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -translate-y-1/2 translate-x-1/4"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-4">Tentang Kami</h1>
        <p class="text-sky-200 text-[15px] max-w-xl mx-auto">Mengenal lebih dekat MariRent — platform rental universal terpercaya di Indonesia</p>
    </div>
</section>

{{-- ABOUT CONTENT --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
        <div>
            <span class="inline-block bg-sky-50 text-sky-600 px-4 py-1.5 rounded-full text-[12px] font-semibold mb-4">Cerita Kami</span>
            <h2 class="text-2xl md:text-3xl font-bold text-navy-900 mb-5">Platform Rental yang Lahir dari Kebutuhan Nyata</h2>
            <p class="text-gray-500 text-[14px] leading-relaxed mb-4">
                MariRent hadir sebagai solusi atas kesulitan masyarakat dalam menemukan layanan rental yang mudah, transparan, dan terpercaya. Kami memulai perjalanan ini dengan visi sederhana: membuat semua orang bisa menyewa apa saja dengan mudah.
            </p>
            <p class="text-gray-500 text-[14px] leading-relaxed mb-6">
                Dari mobil, motor, kamera, hingga tenda — semuanya tersedia dalam satu platform. Kami bekerja sama dengan ribuan mitra untuk memberikan pilihan terbaik dengan harga yang bersahabat.
            </p>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 bg-sky-50 rounded-2xl">
                    <p class="text-2xl font-extrabold text-sky-600">500+</p>
                    <p class="text-[11px] text-gray-500 font-medium mt-1">Unit Tersedia</p>
                </div>
                <div class="text-center p-4 bg-sky-50 rounded-2xl">
                    <p class="text-2xl font-extrabold text-sky-600">2000+</p>
                    <p class="text-[11px] text-gray-500 font-medium mt-1">Pengguna Puas</p>
                </div>
                <div class="text-center p-4 bg-sky-50 rounded-2xl">
                    <p class="text-2xl font-extrabold text-sky-600">50+</p>
                    <p class="text-[11px] text-gray-500 font-medium mt-1">Kota Terjangkau</p>
                </div>
            </div>
        </div>
        <div class="relative">
            <div class="bg-gradient-to-br from-sky-100 to-sky-200 rounded-3xl p-10 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-sky-300/30 rounded-full -translate-y-8 translate-x-8"></div>
                <div class="grid grid-cols-2 gap-4 relative z-10">
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 text-center shadow-sm">
                        <i class="fas fa-car text-sky-500 text-2xl mb-2"></i>
                        <p class="text-[13px] font-bold text-navy-800">Mobil</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 text-center shadow-sm">
                        <i class="fas fa-motorcycle text-amber-500 text-2xl mb-2"></i>
                        <p class="text-[13px] font-bold text-navy-800">Motor</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 text-center shadow-sm">
                        <i class="fas fa-camera text-violet-500 text-2xl mb-2"></i>
                        <p class="text-[13px] font-bold text-navy-800">Kamera</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 text-center shadow-sm">
                        <i class="fas fa-campground text-emerald-500 text-2xl mb-2"></i>
                        <p class="text-[13px] font-bold text-navy-800">Tenda</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- VISI MISI --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-20">
        <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm">
            <div class="w-12 h-12 bg-sky-100 rounded-xl flex items-center justify-center mb-4">
                <i class="fas fa-eye text-sky-600"></i>
            </div>
            <h3 class="font-bold text-navy-900 text-lg mb-3">Visi</h3>
            <p class="text-gray-500 text-[14px] leading-relaxed">Menjadi platform rental terbesar dan terpercaya di Indonesia yang menghubungkan penyewa dengan unit terbaik secara mudah dan transparan.</p>
        </div>
        <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm">
            <div class="w-12 h-12 bg-sky-100 rounded-xl flex items-center justify-center mb-4">
                <i class="fas fa-bullseye text-sky-600"></i>
            </div>
            <h3 class="font-bold text-navy-900 text-lg mb-3">Misi</h3>
            <ul class="text-gray-500 text-[14px] leading-relaxed space-y-2">
                <li class="flex items-start gap-2"><i class="fas fa-check text-sky-500 text-[10px] mt-1.5"></i> Menyediakan unit rental berkualitas dengan harga transparan</li>
                <li class="flex items-start gap-2"><i class="fas fa-check text-sky-500 text-[10px] mt-1.5"></i> Memberikan layanan terbaik untuk setiap pelanggan</li>
                <li class="flex items-start gap-2"><i class="fas fa-check text-sky-500 text-[10px] mt-1.5"></i> Mendukung mitra untuk berkembang bersama</li>
            </ul>
        </div>
    </div>

    {{-- TIM --}}
    <div class="text-center mb-10">
        <span class="inline-block bg-sky-50 text-sky-600 px-4 py-1.5 rounded-full text-[12px] font-semibold mb-4">Tim Kami</span>
        <h2 class="text-2xl font-bold text-navy-900">Orang-Orang Hebat di Balik MariRent</h2>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        @foreach(['Adi Pratama' => 'CEO & Founder', 'Sari Dewi' => 'COO', 'Budi Santoso' => 'CTO', 'Rina Wulan' => 'Head of Marketing'] as $name => $role)
        <div class="bg-white rounded-2xl p-6 text-center border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="w-16 h-16 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg shadow-sky-500/20">
                <span class="text-white font-bold text-xl">{{ substr($name, 0, 1) }}</span>
            </div>
            <h4 class="font-bold text-navy-800 text-[14px]">{{ $name }}</h4>
            <p class="text-[12px] text-gray-400 mt-1">{{ $role }}</p>
        </div>
        @endforeach
    </div>
</section>
@endsection
