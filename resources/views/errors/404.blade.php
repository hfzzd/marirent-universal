@extends('layouts.public')
@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
<section class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <div class="relative mb-8">
            <span class="text-[120px] font-black text-sky-100 leading-none">404</span>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-20 h-20 bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl flex items-center justify-center shadow-xl shadow-sky-500/20 animate-bounce">
                    <i class="fas fa-search text-white text-2xl"></i>
                </div>
            </div>
        </div>
        <h1 class="text-2xl font-bold text-navy-900 mb-3">Halaman Tidak Ditemukan</h1>
        <p class="text-gray-400 text-[14px] mb-8">Sepertinya halaman yang Anda cari sudah dipindahkan atau tidak tersedia.</p>
        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}" class="btn-primary text-white px-6 py-3 rounded-xl font-semibold text-[13px] shadow-lg shadow-sky-500/25">
                <i class="fas fa-home mr-2"></i> Kembali ke Beranda
            </a>
            <a href="{{ route('products') }}" class="bg-gray-100 hover:bg-gray-200 text-navy-700 px-6 py-3 rounded-xl font-semibold text-[13px] transition">
                <i class="fas fa-car mr-2"></i> Lihat Produk
            </a>
        </div>
    </div>
</section>
@endsection
