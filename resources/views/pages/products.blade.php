@extends('layouts.public')
@section('title', 'Produk - MariRent')

@section('content')
<style>
    .product-card { transition: all 0.4s cubic-bezier(0.4,0,0.2,1); }
    .product-card:hover { transform: translateY(-8px) scale(1.01); box-shadow: 0 25px 60px rgba(14,165,233,0.12); }
    .product-card:hover .product-img { transform: scale(1.08); }
    .product-img { transition: transform 0.6s cubic-bezier(0.4,0,0.2,1); }
    .filter-chip { transition: all 0.25s ease; }
    .filter-chip:hover { transform: translateY(-1px); }
    .filter-chip.active { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white; box-shadow: 0 4px 15px rgba(14,165,233,0.3); }
    .glass-pill { background: rgba(255,255,255,0.15); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.2); }
    .page-btn { transition: all 0.25s ease; }
    .page-btn:hover:not(.active):not(.disabled) { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(14,165,233,0.2); }
    .page-btn.active { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white; box-shadow: 0 4px 15px rgba(14,165,233,0.3); }
    .page-btn.disabled { opacity: 0.4; cursor: not-allowed; }
    .price-tag { background: linear-gradient(135deg, #f0f9ff, #e0f2fe); }
    .hero-mesh { background-image: radial-gradient(at 40% 20%, rgba(56,189,248,0.15) 0px, transparent 50%), radial-gradient(at 80% 0%, rgba(14,165,233,0.1) 0px, transparent 50%), radial-gradient(at 0% 50%, rgba(3,105,161,0.08) 0px, transparent 50%); }
    .stat-glass { background: rgba(255,255,255,0.12); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.18); }
    .search-glow:focus { box-shadow: 0 0 0 3px rgba(14,165,233,0.15), 0 4px 20px rgba(14,165,233,0.1); }
    @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
    .shimmer-badge { background: linear-gradient(90deg, transparent 30%, rgba(255,255,255,0.4) 50%, transparent 70%); background-size: 200% 100%; animation: shimmer 2s infinite; }
</style>

@php
    $filterParams = array_filter(['search' => request('search'), 'category' => request('category'), 'max_price' => request('max_price')]);
    $filterQs = $filterParams ? '?' . http_build_query($filterParams) : '';
    $noPageQs = $filterParams ? '?' . http_build_query($filterParams) : '';
@endphp

<section class="hero-section relative py-16 md:py-20 overflow-hidden" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 40%, #0ea5e9 100%);">
    <div class="absolute inset-0 hero-mesh"></div>
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-20 -right-20 w-[500px] h-[500px] bg-white/[0.03] rounded-full"></div>
        <div class="absolute -bottom-32 -left-20 w-[400px] h-[400px] bg-white/[0.03] rounded-full"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-white/[0.02] rounded-full"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-2 glass-pill text-sky-100 px-4 py-1.5 rounded-full text-[11px] font-semibold mb-4 fade-in-up tracking-wide uppercase">
                    <i class="fas fa-th-large text-sky-300"></i> Katalog Produk
                </span>
                <h1 class="text-3xl md:text-[42px] font-extrabold text-white mb-3 fade-in-up fade-in-up-delay-1 leading-tight tracking-tight">Semua <span class="text-sky-300">Produk</span></h1>
                <p class="text-sky-200/70 text-[14px] fade-in-up fade-in-up-delay-2 max-w-md">Temukan kendaraan, gadget, dan alat terbaik untuk kebutuhan Anda</p>
            </div>
            <div class="flex gap-3 fade-in-up fade-in-up-delay-3">
                @php
                    $counts = [
                        'all' => $products->total(),
                        'vehicle' => \App\Models\Vehicle::where('status','available')->where('is_active',true)->count(),
                        'phone' => \App\Models\Phone::where('status','available')->where('is_active',true)->count(),
                        'camera' => \App\Models\Camera::where('status','available')->where('is_active',true)->count(),
                        'camping' => \App\Models\CampingEquipment::where('status','available')->where('is_active',true)->count(),
                    ];
                @endphp
                <span class="stat-glass text-white px-4 py-2.5 rounded-2xl text-[12px] font-semibold flex items-center gap-2">
                    <span class="w-8 h-8 bg-white/10 rounded-xl flex items-center justify-center"><i class="fas fa-boxes text-sky-300 text-sm"></i></span>
                    <div><span class="text-sky-300 font-bold text-sm">{{ $counts['all'] }}</span><br><span class="text-sky-200/60 text-[10px]">Total</span></div>
                </span>
                <span class="stat-glass text-white px-4 py-2.5 rounded-2xl text-[12px] font-semibold flex items-center gap-2">
                    <span class="w-8 h-8 bg-white/10 rounded-xl flex items-center justify-center"><i class="fas fa-car text-sky-300 text-sm"></i></span>
                    <div><span class="text-sky-300 font-bold text-sm">{{ $counts['vehicle'] }}</span><br><span class="text-sky-200/60 text-[10px]">Kendaraan</span></div>
                </span>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 -mt-7 relative z-20">
    {{-- QUICK FILTER CHIPS --}}
    @php
        $catList = \App\Models\Category::where('is_active', true)->get();
    @endphp
    <div class="flex flex-wrap gap-2 mb-6 reveal">
        <a href="{{ route('products') }}{{ $noPageQs ? '&' . ltrim($noPageQs, '?') : '' }}"
           class="filter-chip px-4 py-2 rounded-full text-[12px] font-semibold border border-gray-200 bg-white {{ !request('category') ? 'active border-transparent' : 'text-gray-500 hover:border-sky-300 hover:text-sky-600' }}">
            <i class="fas fa-grip-horizontal mr-1.5 text-[10px]"></i> Semua
        </a>
        @foreach($catList as $cat)
        <a href="{{ route('products', array_merge(request()->except('page','category'), ['category' => $cat->slug])) }}"
           class="filter-chip px-4 py-2 rounded-full text-[12px] font-semibold border border-gray-200 bg-white {{ request('category') == $cat->slug ? 'active border-transparent' : 'text-gray-500 hover:border-sky-300 hover:text-sky-600' }}">
            <i class="fas {{ $cat->icon ?? 'fa-box' }} mr-1.5 text-[10px]"></i> {{ $cat->name }}
        </a>
        @endforeach
    </div>

    {{-- SEARCH & FILTER BAR --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100/80 mb-8 reveal" style="box-shadow: 0 4px 24px rgba(0,0,0,0.03);">
        <form action="{{ route('products') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
            <div class="flex-1 w-full">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Cari Produk</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, merek, atau tipe..."
                        class="w-full border border-gray-200 rounded-2xl pl-11 pr-4 py-3 text-[13px] focus:ring-0 focus:border-sky-400 outline-none transition-all duration-300 hover:border-gray-300 search-glow bg-gray-50/50">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Kategori</label>
                <select name="category" class="border border-gray-200 rounded-2xl px-4 py-3 text-[13px] focus:ring-0 focus:border-sky-400 outline-none bg-gray-50/50 min-w-[170px] transition-all duration-300 hover:border-gray-300 cursor-pointer">
                    <option value="">Semua Kategori</option>
                    @foreach($catList as $cat)
                    <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Harga Maks</label>
                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Rp Max"
                    class="border border-gray-200 rounded-2xl px-4 py-3 text-[13px] focus:ring-0 focus:border-sky-400 outline-none w-40 bg-gray-50/50 transition-all duration-300 hover:border-gray-300">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-white px-6 py-3 rounded-2xl text-[13px] font-semibold shadow-lg shadow-sky-500/20 transition-all duration-300 hover:shadow-xl hover:shadow-sky-500/30">
                    <i class="fas fa-filter mr-1.5"></i> Filter
                </button>
                @if(request()->hasAny(['search','category','max_price']))
                <a href="{{ route('products') }}" class="bg-red-50 hover:bg-red-100 text-red-500 px-4 py-3 rounded-2xl text-[13px] font-medium transition-all duration-200 flex items-center gap-1.5 border border-red-100">
                    <i class="fas fa-times text-[10px]"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- INFO BAR --}}
    <div class="flex items-center justify-between mb-6 reveal">
        <p class="text-[13px] text-gray-400">
            Menampilkan <span class="font-bold text-navy-800">{{ $products->count() }}</span> dari <span class="font-bold text-navy-800">{{ $products->total() }}</span> produk
            @if(request('search'))
                untuk "<span class="text-sky-600 font-semibold">{{ request('search') }}</span>"
            @endif
        </p>
        <div class="flex items-center gap-1.5 text-[11px] text-gray-400 bg-white px-3 py-1.5 rounded-xl border border-gray-100">
            <i class="fas fa-th-large text-sky-400"></i> Grid
        </div>
    </div>

    {{-- PRODUCT GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($products as $p)
        @php
            $typeStyles = match($p['type']) {
                'vehicle' => ['bg' => 'from-sky-50 to-blue-50', 'badge' => 'bg-sky-600', 'icon' => 'text-sky-300', 'accent' => 'text-sky-600'],
                'phone' => ['bg' => 'from-blue-50 to-indigo-50', 'badge' => 'bg-blue-600', 'icon' => 'text-blue-300', 'accent' => 'text-blue-600'],
                'camera' => ['bg' => 'from-violet-50 to-purple-50', 'badge' => 'bg-violet-600', 'icon' => 'text-violet-300', 'accent' => 'text-violet-600'],
                'camping' => ['bg' => 'from-emerald-50 to-teal-50', 'badge' => 'bg-emerald-600', 'icon' => 'text-emerald-300', 'accent' => 'text-emerald-600'],
                default => ['bg' => 'from-gray-50 to-slate-50', 'badge' => 'bg-gray-600', 'icon' => 'text-gray-300', 'accent' => 'text-gray-600'],
            };
            $typeLabels = ['vehicle' => 'Kendaraan', 'phone' => 'Handphone', 'camera' => 'Kamera', 'camping' => 'Camping'];
        @endphp
        <div class="product-card bg-white rounded-2xl overflow-hidden border border-gray-100/60 group reveal" style="box-shadow: 0 2px 16px rgba(0,0,0,0.03);">
            <div class="relative h-48 bg-gradient-to-br {{ $typeStyles['bg'] }} flex items-center justify-center overflow-hidden">
                @if($p['image'])
                    <img src="{{ asset('storage/' . $p['image']) }}" alt="{{ $p['name'] }}" class="w-full h-full object-cover product-img">
                @else
                    <i class="fas {{ $p['icon'] }} text-5xl {{ $typeStyles['icon'] }} product-img"></i>
                @endif

                {{-- TYPE BADGE --}}
                <span class="absolute top-3 left-3 {{ $typeStyles['badge'] }} text-white px-3 py-1 rounded-full text-[10px] font-bold shadow-md">
                    {{ $typeLabels[$p['type']] ?? ucfirst($p['type']) }}
                </span>

                @if($p['type'] == 'vehicle')
                    @php $v = \App\Models\Vehicle::find($p['id']); @endphp
                    @if($v && $v->with_driver)
                    <span class="absolute top-3 right-3 bg-amber-500 text-white px-2.5 py-1 rounded-full text-[10px] font-bold shadow-md"><i class="fas fa-user-tie mr-1"></i> Driver</span>
                    @endif
                @endif

                <span class="absolute bottom-3 right-3 bg-emerald-500 text-white px-2.5 py-1 rounded-full text-[10px] font-bold shadow-md shimmer-badge">
                    <i class="fas fa-check-circle mr-1"></i> Tersedia
                </span>
            </div>

            <div class="p-4">
                <div class="mb-2">
                    <h3 class="font-bold text-navy-900 text-[14px] leading-tight truncate">{{ $p['name'] }}</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $p['brand'] }} {{ $p['subtitle'] }}</p>
                </div>

                @if($p['rating'] > 0)
                <div class="flex items-center gap-1 mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= $p['rating'] ? 'text-amber-400' : 'text-gray-200' }} text-[10px]"></i>
                    @endfor
                    <span class="text-[10px] text-gray-400 ml-0.5">({{ $p['review_count'] }})</span>
                </div>
                @endif

                @if(!empty($p['tags']))
                <div class="flex flex-wrap gap-1 mb-3">
                    @foreach(array_slice($p['tags'], 0, 3) as $tag)
                    <span class="bg-sky-50 text-sky-600 px-2 py-0.5 rounded-md text-[10px] font-medium">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif

                <div class="flex items-end justify-between border-t border-gray-100/60 pt-3">
                    <div class="price-tag px-3 py-1.5 rounded-xl">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold">Harga sewa</p>
                        <p class="text-base font-extrabold text-sky-600 leading-tight">Rp {{ number_format($p['daily_price'], 0, ',', '.') }}<span class="text-[9px] font-normal text-gray-400">/hari</span></p>
                    </div>
                    @if($p['type'] == 'vehicle')
                        <a href="{{ route('public.vehicle', $p['slug']) }}" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-4 py-2 rounded-xl text-[11px] font-bold transition-all duration-300 hover:shadow-md flex items-center gap-1.5">
                            <span>Detail</span> <i class="fas fa-arrow-right text-[9px]"></i>
                        </a>
                    @else
                        @php
                            $itemType = match($p['type']) {
                                'phone' => 'hp',
                                'camera' => 'kamera',
                                'camping' => 'tenda',
                                default => $p['type'],
                            };
                        @endphp
                        <a href="{{ route('public.item', [$itemType, $p['slug']]) }}" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-4 py-2 rounded-xl text-[11px] font-bold transition-all duration-300 hover:shadow-md flex items-center gap-1.5">
                            <span>Detail</span> <i class="fas fa-arrow-right text-[9px]"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-20 reveal">
            <div class="w-20 h-20 bg-gradient-to-br from-sky-50 to-blue-50 rounded-3xl flex items-center justify-center mx-auto mb-5 border border-sky-100">
                <i class="fas fa-search text-sky-300 text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-navy-800 mb-2">Tidak ada produk ditemukan</h3>
            <p class="text-gray-400 text-[13px] mb-5">Coba ubah filter pencarian atau lihat semua produk</p>
            <a href="{{ route('products') }}" class="btn-primary text-white px-6 py-2.5 rounded-2xl text-[13px] font-semibold inline-flex items-center gap-2 transition-all duration-300 hover:shadow-lg">
                <i class="fas fa-redo text-[11px]"></i> Reset Filter
            </a>
        </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    @if($products->hasPages())
    <div class="mt-10 flex justify-center">
        @php
            $pgBase = route('products');
            $pgQs = $filterParams;
        @endphp
        <nav class="flex items-center gap-1.5">
            {{-- Prev --}}
            @if($products->onFirstPage())
                <span class="page-btn disabled px-4 py-2.5 rounded-xl text-[12px] font-semibold text-gray-300 bg-gray-50 border border-gray-100">
                    <i class="fas fa-chevron-left text-[10px] mr-1"></i> Prev
                </span>
            @else
                @php $prevQs = array_merge($pgQs, ['page' => $products->currentPage() - 1]); @endphp
                <a href="{{ $pgBase }}?{{ http_build_query($prevQs) }}"
                   class="page-btn px-4 py-2.5 rounded-xl text-[12px] font-semibold text-sky-600 bg-white border border-sky-200 hover:border-sky-300">
                    <i class="fas fa-chevron-left text-[10px] mr-1"></i> Prev
                </a>
            @endif

            @php
                $curPage = $products->currentPage();
                $startPage = max(1, $curPage - 2);
                $endPage = min($products->lastPage(), $curPage + 2);
            @endphp

            {{-- First page + ellipsis --}}
            @if($startPage > 1)
                @php $pg1 = array_merge($pgQs, ['page' => 1]); @endphp
                <a href="{{ $pgBase }}?{{ http_build_query($pg1) }}"
                   class="page-btn w-10 h-10 flex items-center justify-center rounded-xl text-[13px] font-semibold text-gray-500 bg-white border border-gray-100 hover:border-sky-200 hover:text-sky-600">
                    1
                </a>
                @if($startPage > 2)
                    <span class="text-gray-300 px-1">...</span>
                @endif
            @endif

            {{-- Page numbers --}}
            @for($i = $startPage; $i <= $endPage; $i++)
                @php $pgI = array_merge($pgQs, ['page' => $i]); @endphp
                <a href="{{ $pgBase }}?{{ http_build_query($pgI) }}"
                   class="page-btn w-10 h-10 flex items-center justify-center rounded-xl text-[13px] font-semibold transition-all duration-200 {{ $i == $curPage ? 'active border-transparent' : 'text-gray-500 bg-white border border-gray-100 hover:border-sky-200 hover:text-sky-600' }}">
                    {{ $i }}
                </a>
            @endfor

            {{-- Last page + ellipsis --}}
            @if($endPage < $products->lastPage())
                @if($endPage < $products->lastPage() - 1)
                    <span class="text-gray-300 px-1">...</span>
                @endif
                @php $pgLast = array_merge($pgQs, ['page' => $products->lastPage()]); @endphp
                <a href="{{ $pgBase }}?{{ http_build_query($pgLast) }}"
                   class="page-btn w-10 h-10 flex items-center justify-center rounded-xl text-[13px] font-semibold text-gray-500 bg-white border border-gray-100 hover:border-sky-200 hover:text-sky-600">
                    {{ $products->lastPage() }}
                </a>
            @endif

            {{-- Next --}}
            @if($products->hasMorePages())
                @php $nextQs = array_merge($pgQs, ['page' => $products->currentPage() + 1]); @endphp
                <a href="{{ $pgBase }}?{{ http_build_query($nextQs) }}"
                   class="page-btn px-4 py-2.5 rounded-xl text-[12px] font-semibold text-sky-600 bg-white border border-sky-200 hover:border-sky-300">
                    Next <i class="fas fa-chevron-right text-[10px] ml-1"></i>
                </a>
            @else
                <span class="page-btn disabled px-4 py-2.5 rounded-xl text-[12px] font-semibold text-gray-300 bg-gray-50 border border-gray-100">
                    Next <i class="fas fa-chevron-right text-[10px] ml-1"></i>
                </span>
            @endif
        </nav>
    </div>
    @endif
</section>

{{-- CTA --}}
<section class="py-16 reveal">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl" style="background: linear-gradient(135deg, #0369a1, #0ea5e9, #0284c7, #0369a1); background-size: 300% 300%; animation: gradientShift 8s ease infinite;">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/3"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/3 -translate-x-1/4"></div>
            </div>
            <div class="relative px-8 md:px-14 py-14 text-center">
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">Butuh Bantuan Memilih?</h2>
                <p class="text-sky-200 text-[14px] mb-8 max-w-lg mx-auto">Hubungi kami untuk rekomendasi produk yang sesuai dengan kebutuhan Anda.</p>
                <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 bg-white text-sky-700 hover:bg-sky-50 px-8 py-3.5 rounded-2xl font-bold text-[14px] shadow-xl transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                    <i class="fas fa-headset"></i> Hubungi Kami
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
