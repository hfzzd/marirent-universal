@extends('layouts.public')
@section('title', $brand . ' - ' . $config['label'] . ' - MariRent')

@section('content')
<style>
    .product-card { transition: all 0.4s cubic-bezier(0.4,0,0.2,1); }
    .product-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 25px 60px rgba(14,165,233,0.12); }
    .product-card:hover .product-img { transform: scale(1.05); }
    .product-img { transition: transform 0.6s cubic-bezier(0.4,0,0.2,1); }
    .filter-chip { transition: all 0.25s ease; }
    .filter-chip:hover { transform: translateY(-1px); }
    .hero-mesh { background-image: radial-gradient(at 40% 20%, rgba(56,189,248,0.15) 0px, transparent 50%), radial-gradient(at 80% 0%, rgba(14,165,233,0.1) 0px, transparent 50%); }
    .stat-glass { background: rgba(255,255,255,0.12); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.18); }
    .glass-pill { background: rgba(255,255,255,0.15); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.2); }
</style>

<section class="relative py-16 md:py-20 overflow-hidden" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 40%, #0ea5e9 100%);">
    <div class="absolute inset-0 hero-mesh"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div>
                <a href="{{ route('public.brands') }}" class="inline-flex items-center gap-2 text-sky-300/80 hover:text-white text-[12px] font-medium mb-3 transition">
                    <i class="fas fa-arrow-left text-[10px]"></i> Semua Brand
                </a>
                <h1 class="text-3xl md:text-[42px] font-extrabold text-white mb-3 leading-tight tracking-tight">
                    <i class="fas {{ $config['icon'] }} text-sky-300 mr-2"></i>{{ $brand }}
                </h1>
                <p class="text-sky-200/70 text-[14px] max-w-md">
                    {{ $totalProducts }} produk dari <span class="text-white font-semibold">{{ $brand }}</span>
                </p>
            </div>
            <div class="flex gap-3">
                <span class="stat-glass text-white px-4 py-2.5 rounded-2xl text-[12px] font-semibold flex items-center gap-2">
                    <span class="w-8 h-8 bg-white/10 rounded-xl flex items-center justify-center"><i class="fas fa-box text-sky-300 text-sm"></i></span>
                    <div><span class="text-sky-300 font-bold text-sm">{{ $totalProducts }}</span><br><span class="text-sky-200/60 text-[10px]">Produk</span></div>
                </span>
                <span class="stat-glass text-white px-4 py-2.5 rounded-2xl text-[12px] font-semibold flex items-center gap-2">
                    <span class="w-8 h-8 bg-white/10 rounded-xl flex items-center justify-center"><i class="fas fa-tags text-sky-300 text-sm"></i></span>
                    <div><span class="text-sky-300 font-bold text-sm">Rp {{ number_format($products->min('daily_price'), 0, ',', '.') }}</span><br><span class="text-sky-200/60 text-[10px]">Mulai /hari</span></div>
                </span>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 -mt-7 relative z-20">
    {{-- PRODUCTS GRID --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse($products as $p)
        <a href="{{ $p['link'] }}" class="product-card bg-white rounded-2xl overflow-hidden border border-gray-100 group" style="box-shadow: 0 2px 16px rgba(0,0,0,0.04);">
            <div class="relative h-40 bg-gradient-to-br from-sky-50 to-blue-50 flex items-center justify-center overflow-hidden">
                @if($p['image'])
                    <img src="{{ asset('storage/' . $p['image']) }}" alt="{{ $p['name'] }}" class="w-full h-full object-cover product-img">
                @else
                    <i class="fas {{ $p['icon'] }} text-5xl text-sky-200 product-img"></i>
                @endif
                <span class="absolute top-2.5 left-2.5 bg-white/90 backdrop-blur-sm text-navy-700 px-2.5 py-1 rounded-full text-[10px] font-bold shadow-md border border-gray-100">
                    {{ $brand }}
                </span>
            </div>
            <div class="p-3.5">
                <h3 class="font-bold text-navy-900 text-[13px] leading-tight line-clamp-1">{{ $p['name'] }}</h3>
                <p class="text-[11px] text-gray-400 mt-0.5 line-clamp-1">{{ $p['subtitle'] }}</p>
                @if(!empty($p['tags']))
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @foreach(array_slice($p['tags'], 0, 3) as $tag)
                    <span class="bg-gray-50 border border-gray-100 text-gray-500 px-2 py-0.5 rounded-md text-[9px] font-medium">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
                <div class="flex items-end justify-between mt-3 pt-3 border-t border-gray-100">
                    <div>
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold">Mulai dari</p>
                        <p class="text-base font-extrabold text-sky-600">Rp {{ number_format($p['daily_price'], 0, ',', '.') }}<span class="text-[9px] font-normal text-gray-400">/hari</span></p>
                    </div>
                    <span class="bg-sky-50 text-sky-600 px-3 py-1.5 rounded-xl text-[11px] font-bold group-hover:bg-sky-100 transition flex items-center gap-1.5">
                        Detail <i class="fas fa-arrow-right text-[9px]"></i>
                    </span>
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-20">
            <div class="w-20 h-20 bg-gradient-to-br from-sky-50 to-blue-50 rounded-3xl flex items-center justify-center mx-auto mb-5 border border-sky-100">
                <i class="fas {{ $config['icon'] }} text-sky-300 text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-navy-800 mb-2">Tidak ada produk {{ $brand }}</h3>
            <p class="text-gray-400 text-[13px] mb-5">Brand ini belum memiliki produk yang tersedia</p>
            <a href="{{ route('public.brands') }}" class="bg-sky-500 hover:bg-sky-600 text-white px-6 py-2.5 rounded-2xl text-[13px] font-semibold inline-flex items-center gap-2 transition-all duration-300 hover:shadow-lg">
                <i class="fas fa-arrow-left text-[11px]"></i> Lihat Semua Brand
            </a>
        </div>
        @endforelse
    </div>

    {{-- OTHER BRANDS --}}
    @if($otherBrands->isNotEmpty())
    <div class="mt-12 bg-white rounded-2xl p-6 border border-gray-100/60" style="box-shadow: 0 2px 16px rgba(0,0,0,0.03);">
        <h3 class="text-[14px] font-bold text-navy-800 mb-4">Brand Lainnya di {{ $config['label'] }}</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($otherBrands as $b)
            <a href="{{ route('public.brand', [$type, $b]) }}" class="filter-chip px-4 py-2 rounded-full text-[12px] font-semibold border border-gray-200 bg-white text-gray-600 hover:border-sky-300 hover:text-sky-600 transition-all duration-200">
                <i class="fas fa-tag mr-1.5 text-[10px] text-gray-400"></i>{{ $b }}
            </a>
            @endforeach
        </div>
    </div>
    @endif
</section>
@endsection
