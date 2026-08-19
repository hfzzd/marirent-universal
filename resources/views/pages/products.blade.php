@extends('layouts.public')
@section('title', 'Produk - MariRent')

@section('content')
{{-- HERO --}}
<section class="relative py-16 overflow-hidden" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-3">Semua Produk</h1>
        <p class="text-sky-200 text-[14px]">Temukan kendaraan dan alat terbaik untuk kebutuhan Anda</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- FILTER BAR --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-8">
        <form action="{{ route('products') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Cari</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, merek, atau tipe..."
                        class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Kategori</label>
                <select name="category" class="border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white min-w-[150px]">
                    <option value="">Semua</option>
                    @foreach(\App\Models\Category::where('is_active', true)->get() as $cat)
                    <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Harga Maks</label>
                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Rp Max"
                    class="border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none w-40">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-xl text-[13px] font-semibold shadow-lg shadow-sky-500/25">
                    <i class="fas fa-filter mr-1.5"></i> Filter
                </button>
                <a href="{{ route('products') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-[13px] font-medium transition">Reset</a>
            </div>
        </form>
    </div>

    {{-- RESULT COUNT --}}
    <p class="text-[13px] text-gray-400 mb-5">{{ $products->total() }} produk ditemukan</p>

    {{-- PRODUCT GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($products as $v)
        <div class="vehicle-card bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <div class="relative h-48 bg-gradient-to-br from-sky-50 to-sky-100 flex items-center justify-center overflow-hidden">
                @if($v->image)
                    <img src="{{ asset('storage/' . $v->image) }}" alt="{{ $v->name }}" class="w-full h-full object-cover">
                @else
                    @if($v->category->slug == 'mobil') <i class="fas fa-car text-sky-200 text-6xl"></i>
                    @elseif($v->category->slug == 'motor') <i class="fas fa-motorcycle text-amber-200 text-6xl"></i>
                    @elseif($v->category->slug == 'sewa-kamera') <i class="fas fa-camera text-violet-200 text-6xl"></i>
                    @else <i class="fas fa-campground text-emerald-200 text-6xl"></i>
                    @endif
                @endif
                <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-navy-700 px-3 py-1 rounded-full text-[11px] font-semibold">{{ $v->category->name }}</span>
                @if($v->with_driver)
                <span class="absolute top-3 right-3 bg-sky-600/90 text-white px-3 py-1 rounded-full text-[11px] font-semibold"><i class="fas fa-user-tie mr-1"></i> Driver</span>
                @endif
                <span class="absolute bottom-3 right-3 bg-emerald-500/90 text-white px-2.5 py-1 rounded-full text-[10px] font-semibold"><i class="fas fa-check-circle mr-1"></i> Tersedia</span>
            </div>
            <div class="p-5">
                <h3 class="font-bold text-navy-900 text-[15px] mb-1">{{ $v->name }}</h3>
                <p class="text-[12px] text-gray-400 mb-3">{{ $v->brand }} {{ $v->model }} {{ $v->year }}</p>
                <div class="flex items-center gap-1 mb-3">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= round($v->getAverageRating()) ? 'text-amber-400' : 'text-gray-200' }} text-[11px]"></i>
                    @endfor
                    <span class="text-[11px] text-gray-400 ml-1">({{ $v->reviews->count() }})</span>
                </div>
                <div class="flex flex-wrap gap-1.5 mb-4">
                    @if($v->seats) <span class="bg-gray-50 text-gray-500 px-2 py-0.5 rounded text-[10px]"><i class="fas fa-users mr-1"></i>{{ $v->seats }}</span> @endif
                    @if($v->transmission) <span class="bg-gray-50 text-gray-500 px-2 py-0.5 rounded text-[10px]"><i class="fas fa-cogs mr-1"></i>{{ ucfirst($v->transmission) }}</span> @endif
                    @if($v->fuel_type) <span class="bg-gray-50 text-gray-500 px-2 py-0.5 rounded text-[10px]"><i class="fas fa-gas-pump mr-1"></i>{{ ucfirst($v->fuel_type) }}</span> @endif
                </div>
                <div class="flex items-end justify-between border-t border-gray-100 pt-3">
                    <div>
                        <p class="text-[11px] text-gray-400">Harga sewa</p>
                        <p class="text-lg font-bold text-sky-600">Rp {{ number_format($v->daily_price, 0, ',', '.') }}<span class="text-[11px] font-normal text-gray-400">/hari</span></p>
                    </div>
                    <a href="{{ route('public.vehicle', $v->slug) }}" class="btn-primary text-white px-5 py-2.5 rounded-xl text-[12px] font-semibold shadow-md shadow-sky-500/20">
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-20">
            <i class="fas fa-search text-gray-200 text-5xl mb-4"></i>
            <h3 class="text-lg font-bold text-navy-800 mb-2">Tidak ada produk ditemukan</h3>
            <p class="text-gray-400 text-[13px]">Coba ubah filter pencarian Anda</p>
            <a href="{{ route('products') }}" class="btn-primary text-white px-6 py-2.5 rounded-xl text-[13px] font-semibold mt-4 inline-block">Reset Filter</a>
        </div>
        @endforelse
    </div>

    @if($products->hasPages())
    <div class="mt-10">{{ $products->withQueryString()->links() }}</div>
    @endif
</section>
@endsection
