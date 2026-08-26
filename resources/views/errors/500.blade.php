@extends('layouts.public')
@section('title', '500 - Kesalahan Server')

@section('content')
<section class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <div class="relative mb-8">
            <span class="text-[120px] font-black text-red-100 leading-none">500</span>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-20 h-20 bg-gradient-to-br from-red-400 to-red-600 rounded-2xl flex items-center justify-center shadow-xl shadow-red-500/20 animate-pulse">
                    <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                </div>
            </div>
        </div>
        <h1 class="text-2xl font-bold text-navy-900 mb-3">Kesalahan Server</h1>
        <p class="text-gray-400 text-[14px] mb-8">Terjadi kesalahan tak terduga. Tim kami telah diberitahu dan sedang memperbaikinya.</p>
        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}" class="btn-primary text-white px-6 py-3 rounded-xl font-semibold text-[13px] shadow-lg shadow-sky-500/25">
                <i class="fas fa-home mr-2"></i> Kembali ke Beranda
            </a>
            <button onclick="location.reload()" class="bg-gray-100 hover:bg-gray-200 text-navy-700 px-6 py-3 rounded-xl font-semibold text-[13px] transition">
                <i class="fas fa-redo mr-2"></i> Coba Lagi
            </button>
        </div>
    </div>
</section>
@endsection
