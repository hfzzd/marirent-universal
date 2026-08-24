@extends('layouts.public')
@section('title', 'Produk - MariRent')

@section('content')
<section class="hero-section relative py-20 overflow-hidden" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 40%, #0ea5e9 100%);">
    <div class="absolute inset-0">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4 float"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-white/5 rounded-full translate-y-1/3 -translate-x-1/4 float-reverse"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-sky-200 px-4 py-1.5 rounded-full text-[12px] font-semibold mb-4 fade-in-up">
                    <i class="fas fa-th-large"></i> Katalog Produk
                </span>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-3 fade-in-up fade-in-up-delay-1">Semua <span class="text-sky-300">Produk</span></h1>
                <p class="text-sky-200/80 text-[14px] fade-in-up fade-in-up-delay-2">Temukan kendaraan, gadget, dan alat terbaik untuk kebutuhan Anda</p>
            </div>
            <div class="flex gap-2 fade-in-up fade-in-up-delay-3">
                @php
                    $counts = [
                        'all' => $products->total(),
                        'vehicle' => \App\Models\Vehicle::count(),
                        'phone' => \App\Models\Phone::count(),
                        'camera' => \App\Models\Camera::count(),
                        'camping' => \App\Models\CampingEquipment::count(),
                    ];
                @endphp
                <span class="bg-white/10 backdrop-blur-sm text-white px-4 py-2 rounded-xl text-[12px] font-semibold"><i class="fas fa-boxes mr-1.5"></i> {{ $counts['all'] }} Total</span>
                <span class="bg-white/10 backdrop-blur-sm text-white px-4 py-2 rounded-xl text-[12px] font-semibold"><i class="fas fa-car mr-1.5"></i> {{ $counts['vehicle'] }} Kendaraan</span>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- FILTER BAR --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-8 reveal">
        <form action="{{ route('products') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
            <div class="flex-1 w-full">
                <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Cari Produk</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, merek, atau tipe..."
                        class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all duration-200 hover:border-gray-300">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Kategori</label>
                <select name="category" class="border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white min-w-[160px] transition-all duration-200 hover:border-gray-300">
                    <option value="">Semua Kategori</option>
                    @foreach(\App\Models\Category::where('is_active', true)->get() as $cat)
                    <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Harga Maks</label>
                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Rp Max"
                    class="border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none w-40 transition-all duration-200 hover:border-gray-300">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-white px-6 py-3 rounded-xl text-[13px] font-semibold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105">
                    <i class="fas fa-filter mr-1.5"></i> Filter
                </button>
                @if(request()->hasAny(['search','category','max_price']))
                <a href="{{ route('products') }}" class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-3 rounded-xl text-[13px] font-medium transition flex items-center gap-1.5">
                    <i class="fas fa-times text-[10px]"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- INFO --}}
    <div class="flex items-center justify-between mb-6 reveal">
        <p class="text-[13px] text-gray-400">
            <span class="font-semibold text-navy-800">{{ $products->total() }}</span> produk ditemukan
            @if(request('search'))
                untuk "<span class="text-sky-600 font-medium">{{ request('search') }}</span>"
            @endif
        </p>
        <div class="flex items-center gap-2 text-[12px] text-gray-400">
            <i class="fas fa-th-large text-sky-400"></i> Grid View
        </div>
    </div>

    {{-- PRODUCT GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($products as $p)
        <div class="vehicle-card bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group reveal">
            <div class="relative h-52 bg-gradient-to-br from-sky-50 to-sky-100 flex items-center justify-center overflow-hidden">
                @if($p['image'])
                    <img src="{{ asset('storage/' . $p['image']) }}" alt="{{ $p['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                    <i class="fas {{ $p['icon'] }} text-6xl
                        {{ $p['type'] == 'phone' ? 'text-blue-200' : ($p['type'] == 'camera' ? 'text-violet-200' : ($p['type'] == 'camping' ? 'text-emerald-200' : ($p['type'] == 'vehicle' && isset($p['category']->slug) && $p['category']->slug == 'motor' ? 'text-amber-200' : 'text-sky-200'))) }} group-hover:scale-110 transition-transform duration-500"></i>
                @endif

                {{-- TYPE BADGE --}}
                @php
                    $typeColors = [
                        'vehicle' => 'bg-sky-600/90 text-white',
                        'phone' => 'bg-blue-600/90 text-white',
                        'camera' => 'bg-violet-600/90 text-white',
                        'camping' => 'bg-emerald-600/90 text-white',
                    ];
                    $typeLabels = [
                        'vehicle' => 'Kendaraan',
                        'phone' => 'Handphone',
                        'camera' => 'Kamera',
                        'camping' => 'Camping',
                    ];
                @endphp
                <span class="absolute top-3 left-3 {{ $typeColors[$p['type']] ?? 'bg-gray-600/90 text-white' }} px-3 py-1 rounded-full text-[10px] font-bold backdrop-blur-sm shadow-sm">
                    {{ $typeLabels[$p['type']] ?? ucfirst($p['type']) }}
                </span>

                @if($p['type'] == 'vehicle')
                    @php $v = \App\Models\Vehicle::find($p['id']); @endphp
                    @if($v && $v->with_driver)
                    <span class="absolute top-3 right-3 bg-amber-500/90 text-white px-2.5 py-1 rounded-full text-[10px] font-semibold backdrop-blur-sm shadow-sm"><i class="fas fa-user-tie mr-1"></i> Driver</span>
                    @endif
                @endif

                <span class="absolute bottom-3 right-3 bg-emerald-500/90 text-white px-2.5 py-1 rounded-full text-[10px] font-semibold backdrop-blur-sm shadow-sm"><i class="fas fa-check-circle mr-1"></i> Tersedia</span>
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h3 class="font-bold text-navy-900 text-[15px] leading-tight">{{ $p['name'] }}</h3>
                </div>
                <p class="text-[12px] text-gray-400 mb-3">{{ $p['brand'] }} {{ $p['subtitle'] }}</p>

                @if($p['rating'] > 0)
                <div class="flex items-center gap-1 mb-3">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= $p['rating'] ? 'text-amber-400' : 'text-gray-200' }} text-[11px]"></i>
                    @endfor
                    <span class="text-[11px] text-gray-400 ml-1">({{ $p['review_count'] }})</span>
                </div>
                @endif

                @if(!empty($p['tags']))
                <div class="flex flex-wrap gap-1.5 mb-4">
                    @foreach(array_slice($p['tags'], 0, 3) as $tag)
                    <span class="bg-sky-50 text-sky-600 px-2 py-0.5 rounded-md text-[10px] font-medium">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif

                <div class="flex items-end justify-between border-t border-gray-100 pt-3">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Harga sewa</p>
                        <p class="text-lg font-bold text-sky-600 leading-tight">Rp {{ number_format($p['daily_price'], 0, ',', '.') }}<span class="text-[10px] font-normal text-gray-400">/hari</span></p>
                    </div>
                    @if($p['type'] == 'vehicle')
                        <a href="{{ route('public.vehicle', $p['slug']) }}" class="btn-primary text-white px-5 py-2.5 rounded-xl text-[12px] font-semibold shadow-md shadow-sky-500/20 transition-all duration-300 hover:scale-105 flex items-center gap-1.5">
                            <span>Detail</span> <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    @else
                        <a href="{{ route('public.item', [$p['type'], $p['slug']]) }}" class="btn-primary text-white px-5 py-2.5 rounded-xl text-[12px] font-semibold shadow-md shadow-sky-500/20 transition-all duration-300 hover:scale-105 flex items-center gap-1.5">
                            <span>Detail</span> <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-20 reveal">
            <div class="w-20 h-20 bg-sky-50 rounded-3xl flex items-center justify-center mx-auto mb-5">
                <i class="fas fa-search text-sky-300 text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-navy-800 mb-2">Tidak ada produk ditemukan</h3>
            <p class="text-gray-400 text-[13px] mb-5">Coba ubah filter pencarian atau lihat semua produk</p>
            <a href="{{ route('products') }}" class="btn-primary text-white px-6 py-2.5 rounded-xl text-[13px] font-semibold inline-flex items-center gap-2 transition-all duration-300 hover:scale-105">
                <i class="fas fa-redo text-[11px]"></i> Reset Filter
            </a>
        </div>
        @endforelse
    </div>

    @if(method_exists($products, 'hasPages') && $products->hasPages())
    <div class="mt-10 flex justify-center">{{ $products->withQueryString()->links() }}</div>
    @endif
</section>

{{-- CTA --}}
<section class="py-16 reveal">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl gradient-shift" style="background: linear-gradient(135deg, #0369a1, #0ea5e9, #0284c7, #0369a1); background-size: 300% 300%;">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/3"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/3 -translate-x-1/4"></div>
            </div>
            <div class="relative px-8 md:px-14 py-14 text-center">
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">Butuh Bantuan Memilih?</h2>
                <p class="text-sky-200 text-[14px] mb-8 max-w-lg mx-auto">Hubungi kami untuk rekomendasi produk yang sesuai dengan kebutuhan Anda.</p>
                <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 bg-white text-sky-700 hover:bg-sky-50 px-8 py-3.5 rounded-xl font-bold text-[14px] shadow-xl transition-all duration-300 hover:scale-105">
                    <i class="fas fa-headset"></i> Hubungi Kami
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
