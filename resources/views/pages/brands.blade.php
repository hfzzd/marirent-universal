@extends('layouts.public')
@section('title', 'Brand - MariRent')

@section('content')
<style>
    .brand-card { transition: all 0.3s ease; }
    .brand-card:hover { transform: translateY(-5px); box-shadow: 0 12px 32px rgba(2,132,199,0.12); border-color: rgba(2,132,199,0.25) !important; }
    .cat-section { animation: fadeInUp 0.5s ease forwards; opacity: 0; }
    .cat-section:nth-child(1) { animation-delay: 0.1s; }
    .cat-section:nth-child(2) { animation-delay: 0.2s; }
    .cat-section:nth-child(3) { animation-delay: 0.3s; }
    .cat-section:nth-child(4) { animation-delay: 0.4s; }
    .cat-section:nth-child(5) { animation-delay: 0.5s; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .hero-mesh { background-image: radial-gradient(at 40% 20%, rgba(56,189,248,0.15) 0px, transparent 50%), radial-gradient(at 80% 0%, rgba(14,165,233,0.1) 0px, transparent 50%); }
    .glass-pill { background: rgba(255,255,255,0.15); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.2); }
    .brand-logo-img { filter: drop-shadow(0 2px 6px rgba(0,0,0,0.08)); }
</style>

<section class="relative py-16 md:py-20 overflow-hidden" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 40%, #0ea5e9 100%);">
    <div class="absolute inset-0 hero-mesh"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center">
            <span class="inline-flex items-center gap-2 glass-pill text-sky-100 px-4 py-1.5 rounded-full text-[11px] font-semibold mb-4 tracking-wide uppercase">
                <i class="fas fa-th-large"></i> Katalog Brand
            </span>
            <h1 class="text-3xl md:text-[42px] font-extrabold text-white mb-3 leading-tight tracking-tight">Semua <span class="text-sky-300">Brand</span></h1>
            <p class="text-sky-200/70 text-[14px] max-w-md mx-auto">Pilih brand favorit Anda untuk melihat koleksi produk yang tersedia</p>
        </div>
    </div>
</section>

{{-- Category quick nav --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
    <div class="flex flex-wrap justify-center gap-3">
        @foreach($brandData as $typeKey => $section)
        <a href="#kategori-{{ $typeKey }}" class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-white border border-gray-200 text-[12px] font-semibold text-navy-700 hover:border-sky-400 hover:text-sky-600 hover:shadow-sm transition">
            <span class="w-6 h-6 rounded-full bg-gradient-to-br {{ $section['config']['color'] }} flex items-center justify-center">
                <i class="fas {{ $section['config']['icon'] }} text-white text-[9px]"></i>
            </span>
            {{ $section['config']['label'] }}
            <span class="text-[10px] text-gray-400 font-medium">{{ $section['brands']->count() }}</span>
        </a>
        @endforeach
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    @forelse($brandData as $typeKey => $section)
    <div class="cat-section mb-14" id="kategori-{{ $typeKey }}">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br {{ $section['config']['color'] }} flex items-center justify-center shadow-lg shadow-sky-500/10">
                <i class="fas {{ $section['config']['icon'] }} text-white"></i>
            </div>
            <div>
                <h2 class="text-[18px] font-bold text-navy-800 leading-tight">{{ $section['config']['label'] }}</h2>
                <p class="text-[11px] text-gray-400">{{ $section['brands']->count() }} brand tersedia</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($section['brands'] as $b)
            <a href="{{ route('public.brand', [$typeKey, $b->brand]) }}" class="brand-card bg-white rounded-2xl border border-gray-100 overflow-hidden group" style="box-shadow: 0 2px 16px rgba(0,0,0,0.04);">
                @php
                    $brandPhotos = $section['photos']->get($b->brand, collect());
                    $firstPhoto = $brandPhotos->first();
                @endphp
                <div class="flex items-center justify-center h-32 px-5 bg-gradient-to-br {{ $section['config']['bg'] }}">
                    @if($firstPhoto)
                    <img src="{{ $firstPhoto->photo_url }}" alt="{{ $b->brand }}" class="brand-logo-img max-h-20 max-w-full object-contain group-hover:scale-110 transition duration-300">
                    @else
                    <div class="flex items-center justify-center">
                        <i class="fas {{ $section['config']['icon'] }} text-3xl" style="color: {{ $typeKey === 'mobil' ? '#0ea5e9' : ($typeKey === 'motor' ? '#f59e0b' : ($typeKey === 'hp' ? '#3b82f6' : ($typeKey === 'kamera' ? '#8b5cf6' : '#10b981'))) }};"></i>
                    </div>
                    @endif
                </div>
                <div class="p-3.5 border-t border-gray-50">
                    <h3 class="font-bold text-navy-800 text-[15px] leading-snug text-center">{{ $b->brand }}</h3>
                    <p class="text-[11px] text-gray-400 text-center mt-1">{{ $b->item_count }} produk</p>
                    <p class="text-[11px] text-sky-500 font-semibold text-center mt-0.5">Mulai Rp {{ number_format($b->min_price, 0, ',', '.') }}/hari</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @empty
    <div class="text-center py-20">
        <div class="w-20 h-20 bg-gradient-to-br from-sky-50 to-blue-50 rounded-3xl flex items-center justify-center mx-auto mb-5 border border-sky-100">
            <i class="fas fa-tags text-sky-300 text-3xl"></i>
        </div>
        <h3 class="text-lg font-bold text-navy-800 mb-2">Belum ada brand</h3>
        <p class="text-gray-400 text-[13px]">Brand akan muncul setelah produk ditambahkan</p>
    </div>
    @endforelse
</section>
@endsection
