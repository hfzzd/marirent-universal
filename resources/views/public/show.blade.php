@extends('layouts.public')

@section('title', $vehicle->name . ' - MariRent')

@section('content')
@php
    $typeLabels = [
        'mobil' => 'Sewa Mobil', 'motor' => 'Sewa Motor',
        'sewa-hp' => 'Sewa HP', 'sewa-kamera' => 'Sewa Kamera', 'sewa-tenda' => 'Sewa Alat Camping',
        'sewa-ps' => 'Sewa Playstation', 'sewa-drone' => 'Sewa Drone', 'sewa-alat-musik' => 'Sewa Alat Musik',
    ];
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- BREADCRUMBS --}}
    <nav class="flex items-center gap-2 text-[13px] text-gray-400 mb-6 reveal">
        <a href="{{ route('home') }}" class="hover:text-sky-600 transition"><i class="fas fa-home"></i></a>
        <i class="fas fa-chevron-right text-[9px]"></i>
        <a href="{{ route('products') }}" class="hover:text-sky-600 transition">Produk</a>
        <i class="fas fa-chevron-right text-[9px]"></i>
        <a href="{{ route('products', ['category' => $vehicle->category->slug ?? '']) }}" class="hover:text-sky-600 transition">{{ $vehicle->category->name ?? '-' }}</a>
        <i class="fas fa-chevron-right text-[9px]"></i>
        <span class="text-navy-700 font-medium">{{ $vehicle->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">

            {{-- IMAGE GALLERY --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden reveal" x-data="imageGallery()">
                <div class="relative h-80 md:h-96 bg-gradient-to-br from-sky-50 to-sky-100 flex items-center justify-center overflow-hidden">
                    @if($vehicle->image)
                        <img :src="mainImage" alt="{{ $vehicle->name }}" class="w-full h-full object-cover transition-all duration-500">
                    @else
                        <div class="text-center">
                            @if($vehicle->category->slug == 'mobil')
                                <i class="fas fa-car text-sky-300 text-8xl"></i>
                            @elseif($vehicle->category->slug == 'motor')
                                <i class="fas fa-motorcycle text-sky-300 text-8xl"></i>
                            @elseif($vehicle->category->slug == 'sewa-hp')
                                <i class="fas fa-mobile-alt text-sky-300 text-8xl"></i>
                            @elseif($vehicle->category->slug == 'sewa-kamera')
                                <i class="fas fa-camera text-sky-300 text-8xl"></i>
                            @elseif($vehicle->category->slug == 'sewa-tenda')
                                <i class="fas fa-campground text-sky-300 text-8xl"></i>
                            @elseif($vehicle->category->slug == 'sewa-ps')
                                <i class="fas fa-gamepad text-sky-300 text-8xl"></i>
                            @elseif($vehicle->category->slug == 'sewa-drone')
                                <i class="fas fa-drone text-sky-300 text-8xl"></i>
                            @elseif($vehicle->category->slug == 'sewa-alat-musik')
                                <i class="fas fa-guitar text-sky-300 text-8xl"></i>
                            @else
                                <i class="fas fa-box text-sky-300 text-8xl"></i>
                            @endif
                        </div>
                    @endif

                    {{-- Wishlist --}}
                    <button onclick="toggleWishlistShow(this)" class="wishlist-btn absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 transition shadow-lg">
                        <i class="far fa-heart text-lg"></i>
                    </button>

                    {{-- Share --}}
                    <button onclick="shareVehicle()" class="absolute top-4 right-16 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-400 hover:text-sky-500 transition shadow-lg">
                        <i class="fas fa-share-alt text-lg"></i>
                    </button>
                </div>

                {{-- Thumbnails --}}
                <div class="p-3 flex gap-2 overflow-x-auto">
                    <button @click="activeThumb = 0; mainImage = '{{ $vehicle->image ? asset('storage/' . $vehicle->image) : '' }}'" :class="activeThumb === 0 ? 'ring-2 ring-sky-500' : ''" class="flex-shrink-0 w-20 h-16 rounded-xl overflow-hidden bg-sky-50 border-2 border-transparent hover:border-sky-300 transition">
                        @if($vehicle->image)
                            <img src="{{ asset('storage/' . $vehicle->image) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center"><i class="fas fa-image text-sky-300"></i></div>
                        @endif
                    </button>
                </div>
            </div>

            {{-- VEHICLE INFO --}}
            <div class="bg-white rounded-2xl shadow-sm p-6 reveal">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <span class="bg-sky-50 text-sky-700 px-3 py-1 rounded-full text-xs font-medium">{{ $vehicle->category->name ?? '-' }}</span>
                        <h1 class="text-2xl font-bold text-navy-900 mt-2">{{ $vehicle->name }}</h1>
                        <p class="text-navy-500">{{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->year }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-medium {{ $vehicle->status === 'available' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                        {{ $vehicle->status === 'available' ? 'Tersedia' : 'Tidak Tersedia' }}
                    </span>
                </div>

                <div class="flex items-center mb-4">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= round($vehicle->getAverageRating()) ? 'text-amber-400' : 'text-gray-300' }} text-sm"></i>
                    @endfor
                    <span class="text-sm text-navy-500 ml-2">{{ number_format($vehicle->getAverageRating(), 1) }} ({{ $vehicle->reviews->count() }} ulasan)</span>
                </div>

                <p class="text-navy-600 mb-6">{{ $vehicle->description }}</p>

                <h3 class="font-bold text-navy-800 mb-3">Spesifikasi</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    @if($vehicle->seats)
                    <div class="bg-gray-50 p-3 rounded-xl text-center">
                        <i class="fas fa-users text-sky-600 mb-1"></i>
                        <p class="text-sm font-medium text-navy-800">{{ $vehicle->seats }} Kursi</p>
                    </div>
                    @endif
                    @if($vehicle->transmission)
                    <div class="bg-gray-50 p-3 rounded-xl text-center">
                        <i class="fas fa-cogs text-sky-600 mb-1"></i>
                        <p class="text-sm font-medium text-navy-800">{{ ucfirst($vehicle->transmission) }}</p>
                    </div>
                    @endif
                    @if($vehicle->fuel_type)
                    <div class="bg-gray-50 p-3 rounded-xl text-center">
                        <i class="fas fa-gas-pump text-sky-600 mb-1"></i>
                        <p class="text-sm font-medium text-navy-800">{{ ucfirst($vehicle->fuel_type) }}</p>
                    </div>
                    @endif
                    @if($vehicle->color)
                    <div class="bg-gray-50 p-3 rounded-xl text-center">
                        <i class="fas fa-palette text-sky-600 mb-1"></i>
                        <p class="text-sm font-medium text-navy-800">{{ $vehicle->color }}</p>
                    </div>
                    @endif
                </div>

                @if($vehicle->license_plate && $vehicle->license_plate != '-')
                <div class="bg-gray-50 p-4 rounded-xl">
                    <p class="text-sm text-navy-500">Plat Nomor</p>
                    <p class="font-bold text-navy-800">{{ $vehicle->license_plate }}</p>
                </div>
                @endif
            </div>

            {{-- REVIEWS --}}
            <div class="bg-white rounded-2xl shadow-sm p-6 reveal">
                <h3 class="font-bold text-navy-800 mb-4">Ulasan ({{ $vehicle->reviews->count() }})</h3>
                @forelse($vehicle->reviews as $review)
                <div class="border-b last:border-0 py-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-sky-100 rounded-full flex items-center justify-center mr-2">
                                <span class="text-sky-700 font-bold text-xs">{{ strtoupper(substr($review->user?->name ?? '', 0, 1)) }}</span>
                            </div>
                            <span class="font-medium text-navy-800 text-sm">{{ $review->user?->name }}</span>
                        </div>
                        <div class="flex">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-300' }} text-xs"></i>
                            @endfor
                        </div>
                    </div>
                    <p class="text-navy-600 text-sm">{{ $review->comment }}</p>
                </div>
                @empty
                <p class="text-navy-500 text-sm text-center py-4">Belum ada ulasan</p>
                @endforelse
            </div>
        </div>

        {{-- BOOKING SIDEBAR --}}
        <div>
            <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-24">
                <div class="mb-6">
                    <p class="text-xs text-navy-500 mb-1">Harga Sewa</p>
                    <p class="text-3xl font-bold text-sky-600">Rp {{ number_format($vehicle->daily_price, 0, ',', '.') }}</p>
                    <p class="text-sm text-navy-500">/hari</p>
                </div>

                <div class="space-y-3 mb-6">
                    @if($vehicle->hourly_price)
                    <div class="flex justify-between text-sm">
                        <span class="text-navy-500">Per Jam</span>
                        <span class="font-medium text-navy-800">Rp {{ number_format($vehicle->hourly_price, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-navy-500">Per Minggu</span>
                        <span class="font-medium text-navy-800">Rp {{ number_format($vehicle->weekly_price ?? $vehicle->daily_price * 7, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-navy-500">Per Bulan</span>
                        <span class="font-medium text-navy-800">Rp {{ number_format($vehicle->monthly_price ?? $vehicle->daily_price * 30, 0, ',', '.') }}</span>
                    </div>
                    @if($vehicle->with_driver && $vehicle->with_driver_daily_price)
                    <div class="flex justify-between text-sm bg-sky-50 p-2 rounded-lg">
                        <span class="text-sky-700"><i class="fas fa-user-tie mr-1"></i> + Driver/Hari</span>
                        <span class="font-medium text-sky-700">Rp {{ number_format($vehicle->with_driver_daily_price, 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>

                @auth
                <a href="{{ route('bookings.create', ['vehicle' => $vehicle->slug]) }}" class="btn-primary text-white w-full py-3 rounded-xl font-semibold text-center block">
                    <i class="fas fa-calendar-plus mr-2"></i> Booking Sekarang
                </a>
                @else
                <a href="{{ route('login') }}" class="btn-primary text-white w-full py-3 rounded-xl font-semibold text-center block">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login untuk Booking
                </a>
                @endauth

                <div class="mt-4 space-y-2">
                    <div class="flex items-center text-sm text-navy-500">
                        <i class="fas fa-check-circle text-sky-500 mr-2"></i> Free cancellation 24 jam sebelumnya
                    </div>
                    <div class="flex items-center text-sm text-navy-500">
                        <i class="fas fa-check-circle text-sky-500 mr-2"></i> Asuransi kendaraan termasuk
                    </div>
                    <div class="flex items-center text-sm text-navy-500">
                        <i class="fas fa-check-circle text-sky-500 mr-2"></i> Dukungan 24/7
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RELATED --}}
    @if($relatedVehicles->count())
    <div class="mt-16 reveal">
        <h2 class="text-2xl font-bold text-navy-900 mb-6">Kendaraan Serupa</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedVehicles as $rv)
            <div class="vehicle-card bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
                <div class="h-40 bg-gradient-to-br from-sky-50 to-sky-100 flex items-center justify-center overflow-hidden">
                    @if($rv->image)
                        <img src="{{ asset('storage/' . $rv->image) }}" alt="{{ $rv->name }}" loading="lazy" class="w-full h-full object-cover">
                    @elseif($rv->category->slug == 'mobil')
                        <i class="fas fa-car text-sky-300 text-5xl"></i>
                    @elseif($rv->category->slug == 'motor')
                        <i class="fas fa-motorcycle text-sky-300 text-5xl"></i>
                    @elseif($rv->category->slug == 'sewa-hp')
                        <i class="fas fa-mobile-alt text-sky-300 text-5xl"></i>
                    @elseif($rv->category->slug == 'sewa-kamera')
                        <i class="fas fa-camera text-sky-300 text-5xl"></i>
                    @elseif($rv->category->slug == 'sewa-tenda')
                        <i class="fas fa-campground text-sky-300 text-5xl"></i>
                    @elseif($rv->category->slug == 'sewa-ps')
                        <i class="fas fa-gamepad text-sky-300 text-5xl"></i>
                    @elseif($rv->category->slug == 'sewa-drone')
                        <i class="fas fa-drone text-sky-300 text-5xl"></i>
                    @elseif($rv->category->slug == 'sewa-alat-musik')
                        <i class="fas fa-guitar text-sky-300 text-5xl"></i>
                    @else
                        <i class="fas fa-camera text-sky-300 text-5xl"></i>
                    @endif
                </div>
                <div class="p-4">
                    <h4 class="font-bold text-navy-900">{{ $rv->name }}</h4>
                    <p class="text-sm text-navy-500 mb-3">{{ $rv->brand }} {{ $rv->model }}</p>
                    <div class="flex items-end justify-between">
                        <p class="text-lg font-bold text-sky-600">Rp {{ number_format($rv->daily_price, 0, ',', '.') }}<span class="text-xs text-navy-400">/hari</span></p>
                        <a href="{{ route('public.vehicle', $rv->slug) }}" class="text-sky-600 text-sm font-medium">Detail &rarr;</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- BACK TO TOP --}}
<button id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" class="back-to-top">
    <i class="fas fa-chevron-up"></i>
</button>

@endsection

@push('scripts')
<script>
function imageGallery() {
    return {
        mainImage: '{{ $vehicle->image ? asset('storage/' . $vehicle->image) : '' }}',
        activeThumb: 0,
    }
}

function toggleWishlistShow(btn) {
    btn.classList.toggle('active');
    const icon = btn.querySelector('i');
    if (btn.classList.contains('active')) {
        icon.classList.remove('far'); icon.classList.add('fas');
    } else {
        icon.classList.remove('fas'); icon.classList.add('far');
    }
}

function shareVehicle() {
    if (navigator.share) {
        navigator.share({ title: '{{ $vehicle->name }}', url: window.location.href });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Link berhasil disalin!');
    }
}

// Back to top
const backToTopBtn = document.getElementById('backToTop');
window.addEventListener('scroll', () => {
    if (backToTopBtn) backToTopBtn.classList.toggle('visible', window.scrollY > 400);
}, { passive: true });
</script>
@endpush
