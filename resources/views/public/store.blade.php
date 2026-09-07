@extends('layouts.public')
@section('title', ($merchant->name ?? 'Toko') . ' - MariRent')
@section('meta_description', ($merchant->description ?? '') . ' Tersedia di platform rental MariRent.')

@section('content')
<style>
    .product-card { transition: all 0.4s cubic-bezier(0.4,0,0.2,1); }
    .product-card:hover { transform: translateY(-6px); box-shadow: 0 22px 50px rgba(14,165,233,0.12); }
    .product-card:hover .product-img { transform: scale(1.07); }
    .product-img { transition: transform 0.6s cubic-bezier(0.4,0,0.2,1); }
    .store-hero { background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 45%, #0ea5e9 100%); }
</style>

<section class="store-hero relative overflow-hidden py-16">
    <div class="absolute inset-0 opacity-15">
        <div class="absolute -top-16 -right-16 w-80 h-80 bg-white rounded-full"></div>
        <div class="absolute -bottom-24 -left-12 w-72 h-72 bg-white rounded-full"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-center gap-6">
            <div class="w-24 h-24 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center border border-white/20 shadow-xl flex-shrink-0 overflow-hidden">
                @if($merchant->logo)
                    <img src="{{ asset('storage/' . $merchant->logo) }}" alt="{{ $merchant->name }}" class="w-full h-full object-cover">
                @else
                    <span class="text-white text-4xl font-bold">{{ strtoupper(substr($merchant->name, 0, 1)) }}</span>
                @endif
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2 text-sky-200 text-[11px] font-semibold uppercase tracking-widest mb-1">
                    <i class="fas fa-store"></i> Toko Resmi MariRent
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-2">{{ $merchant->name }}</h1>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-sky-100/90 text-[13px]">
                    @if($merchant->getAverageRating() > 0)
                    <span class="inline-flex items-center gap-1">
                        <i class="fas fa-star text-amber-300"></i>
                        <span class="font-semibold text-white">{{ number_format($merchant->getAverageRating(), 1) }}</span>
                        ({{ $merchant->getReviewCount() }} ulasan)
                    </span>
                    @endif
                    @if($merchant->city)
                    <span class="inline-flex items-center gap-1"><i class="fas fa-map-marker-alt"></i> {{ $merchant->city }}</span>
                    @endif
                    <span class="inline-flex items-center gap-1"><i class="fas fa-box-open"></i> {{ $products->total() }} unit aktif</span>
                </div>
                @if($merchant->description)
                <p class="text-sky-100/80 text-[13px] mt-3 max-w-2xl">{{ $merchant->description }}</p>
                @endif
            </div>
            @if($merchant->pickup_address || $merchant->phone)
            <div class="bg-white/10 border border-white/15 rounded-2xl p-4 text-sky-100 text-[12px] space-y-2 flex-shrink-0 backdrop-blur-sm">
                @if($merchant->pickup_address)
                <div class="flex items-start gap-2"><i class="fas fa-map-marked-alt mt-0.5 text-sky-300"></i><span>Penjemputan: {{ $merchant->pickup_address }}</span></div>
                @endif
                @if($merchant->phone)
                <div class="flex items-center gap-2"><i class="fas fa-phone text-sky-300"></i><span>{{ $merchant->phone }}</span></div>
                @endif
                @if($merchant->operational_hours)
                <div class="flex items-center gap-2"><i class="fas fa-clock text-sky-300"></i><span>{{ $merchant->operational_hours }}</span></div>
                @endif
            </div>
            @endif
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-navy-900">Produk Toko Ini</h2>
        <a href="{{ route('products') }}" class="text-[12px] text-sky-600 hover:text-sky-700 font-medium"><i class="fas fa-arrow-left mr-1"></i> Semua Produk</a>
    </div>

    @if($products->isEmpty())
    <div class="text-center py-20 bg-white rounded-2xl border border-gray-100">
        <div class="w-20 h-20 bg-sky-50 rounded-3xl flex items-center justify-center mx-auto mb-5">
            <i class="fas fa-store text-sky-300 text-3xl"></i>
        </div>
        <h3 class="text-lg font-bold text-navy-800 mb-2">Belum ada produk</h3>
        <p class="text-gray-400 text-[13px]">Toko ini belum memiliki unit yang tersedia saat ini.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($products as $p)
        @php
            $typeStyles = ['vehicle' => ['bg' => 'from-sky-50 to-blue-50','icon' => 'text-sky-300'],'phone' => ['bg' => 'from-blue-50 to-indigo-50','icon' => 'text-blue-300'],'camera' => ['bg' => 'from-violet-50 to-purple-50','icon' => 'text-violet-300'],'camping' => ['bg' => 'from-emerald-50 to-teal-50','icon' => 'text-emerald-300'],'ps' => ['bg' => 'from-indigo-50 to-blue-50','icon' => 'text-indigo-300'],'drone' => ['bg' => 'from-cyan-50 to-sky-50','icon' => 'text-cyan-300'],'musik' => ['bg' => 'from-rose-50 to-pink-50','icon' => 'text-rose-300'],'default' => ['bg' => 'from-gray-50 to-slate-50','icon' => 'text-gray-300']];
            $ts = $typeStyles[$p['type']] ?? $typeStyles['default'];
            $itemType = match($p['type']) { 'phone' => 'hp', 'camera' => 'kamera', 'camping' => 'tenda', default => $p['type'] };
        @endphp
        <a href="{{ $p['link'] }}" class="product-card bg-white rounded-2xl overflow-hidden border border-gray-100/60 group reveal">
            <div class="relative h-44 bg-gradient-to-br {{ $ts['bg'] }} flex items-center justify-center overflow-hidden">
                @if($p['image'])
                    <img src="{{ asset('storage/' . $p['image']) }}" alt="{{ $p['name'] }}" class="w-full h-full object-cover product-img">
                @else
                    <i class="fas {{ $p['icon'] }} text-5xl {{ $ts['icon'] }} product-img"></i>
                @endif
                <span class="absolute bottom-3 right-3 bg-emerald-500 text-white px-2.5 py-1 rounded-full text-[10px] font-bold shadow-md"><i class="fas fa-check-circle mr-1"></i> Tersedia</span>
            </div>
            <div class="p-4">
                <h3 class="font-bold text-navy-900 text-[14px] leading-tight truncate">{{ $p['name'] }}</h3>
                <p class="text-[11px] text-gray-400 mt-0.5">{{ $p['brand'] }} {{ $p['subtitle'] }}</p>
                @if(!empty($p['company']))
                <p class="text-[10px] text-emerald-600 mt-1 flex items-center gap-1 truncate">
                    <i class="fas fa-building text-[8px]"></i> {{ $p['company'] }}
                </p>
                @endif
                <div class="flex items-end justify-between border-t border-gray-100/60 pt-3 mt-3">
                    <div class="bg-sky-50 px-3 py-1.5 rounded-xl">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold">Harga sewa</p>
                        <p class="text-base font-extrabold text-sky-600 leading-tight">Rp {{ number_format($p['daily_price'], 0, ',', '.') }}<span class="text-[9px] font-normal text-gray-400">/hari</span></p>
                    </div>
                    <span class="text-sky-600 text-[11px] font-bold flex items-center gap-1"><span>Detail</span> <i class="fas fa-arrow-right text-[9px]"></i></span>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    @if($products->hasPages())
    <div class="mt-10 flex justify-center">
        {{ $products->links() }}
    </div>
    @endif
    @endif
</section>
@endsection
