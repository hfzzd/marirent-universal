@extends('layouts.public')
@section('title', 'Daftar Toko - MariRent')
@section('meta_description', 'Temukan lokasi semua toko penyedia rental di MariRent melalui peta Google Maps.')

@section('content')
<style>
    .store-card { transition: all 0.4s cubic-bezier(0.4,0,0.2,1); }
    .store-card:hover { transform: translateY(-4px); box-shadow: 0 20px 45px rgba(14,165,233,0.12); }
</style>

<section class="store-hero relative overflow-hidden py-14">
    <div class="absolute inset-0 opacity-15">
        <div class="absolute -top-16 -right-16 w-80 h-80 bg-white rounded-full"></div>
        <div class="absolute -bottom-24 -left-12 w-72 h-72 bg-white rounded-full"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <p class="text-sky-200 text-[11px] font-semibold uppercase tracking-widest mb-2"><i class="fas fa-store mr-1"></i> Marketplace Rental</p>
        <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-3">Daftar Toko</h1>
        <p class="text-sky-100/80 text-[13px] max-w-2xl">Jelajahi semua toko penyedia rental di MariRent. Pilih toko untuk melihat unit tersedia, atau buka langsung lokasinya di Google Maps.</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    @if($merchants->isEmpty())
    <div class="text-center py-20 bg-white rounded-2xl border border-gray-100">
        <div class="w-20 h-20 bg-sky-50 rounded-3xl flex items-center justify-center mx-auto mb-5">
            <i class="fas fa-store text-sky-300 text-3xl"></i>
        </div>
        <h3 class="text-lg font-bold text-navy-800 mb-2">Belum ada toko terdaftar</h3>
        <p class="text-gray-400 text-[13px]">Toko-toko penyedia rental akan tampil di sini.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($merchants as $m)
        <div class="store-card bg-white rounded-2xl overflow-hidden border border-gray-100/60">
            <div class="p-5 pb-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-sky-50 flex items-center justify-center text-sky-400 text-lg font-bold flex-shrink-0">
                        @if($m->logo)
                            <img src="{{ asset('storage/' . $m->logo) }}" alt="{{ $m->name }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($m->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-bold text-navy-900 text-[14px] leading-tight truncate">{{ $m->name }}</h3>
                        <div class="flex items-center gap-2 text-[11px] text-gray-400 mt-0.5 flex-wrap">
                            @if($m->getAverageRating() > 0)
                            <span class="inline-flex items-center gap-1 text-amber-500"><i class="fas fa-star"></i> {{ number_format($m->getAverageRating(), 1) }}</span>
                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                            @endif
                            @if($m->city)
                            <span class="inline-flex items-center gap-1"><i class="fas fa-map-marker-alt text-sky-400"></i> {{ $m->city }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="space-y-1.5 text-[12px] text-gray-500">
                    @if($m->operational_hours)
                    <div class="flex items-center gap-2"><i class="fas fa-clock text-sky-400 w-3.5"></i><span>{{ $m->operational_hours }}</span></div>
                    @endif
                    @if($m->phone)
                    <div class="flex items-center gap-2"><i class="fas fa-phone text-sky-400 w-3.5"></i><span>{{ $m->phone }}</span></div>
                    @endif
                    @if($m->description)
                    <p class="text-[11px] text-gray-400 leading-relaxed line-clamp-2 pt-0.5">{{ $m->description }}</p>
                    @endif
                </div>
            </div>
            <div class="relative h-44 bg-gray-100">
                <iframe src="{{ $m->mapsEmbedUrl() }}" class="w-full h-full" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Lokasi {{ $m->name }}"></iframe>
            </div>
            <div class="p-4 pt-3 flex items-center gap-2">
                <a href="{{ route('public.store', $m->slug) }}" class="flex-1 bg-sky-50 hover:bg-sky-100 text-sky-700 px-4 py-2.5 rounded-xl text-[12px] font-bold transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-box-open text-[10px]"></i> Lihat Toko
                </a>
                <a href="{{ $m->mapsDirectionsUrl() }}" target="_blank" rel="noopener" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-[12px] font-bold transition flex items-center justify-center gap-1.5">
                    <i class="fab fa-google text-[10px]"></i> Buka di Maps
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</section>
@endsection