@extends('layouts.public')

@section('title', $item->name . ' - MariRent')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- BREADCRUMBS --}}
    <nav class="flex items-center gap-2 text-[13px] text-gray-400 mb-6 reveal">
        <a href="{{ route('home') }}" class="hover:text-sky-600 transition"><i class="fas fa-home"></i></a>
        <i class="fas fa-chevron-right text-[9px]"></i>
        <a href="{{ route('products') }}" class="hover:text-sky-600 transition">Produk</a>
        <i class="fas fa-chevron-right text-[9px]"></i>
        <a href="{{ route('products', ['category' => $item->category->slug ?? '']) }}" class="hover:text-sky-600 transition">{{ $config['label'] }}</a>
        <i class="fas fa-chevron-right text-[9px]"></i>
        <span class="text-navy-700 font-medium">{{ $item->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="h-80 bg-gradient-to-br from-sky-50 to-sky-100 flex items-center justify-center">
                    @if($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas {{ $config['icon'] }} text-sky-300 text-8xl"></i>
                    @endif
                </div>
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <span class="bg-sky-50 text-sky-700 px-3 py-1 rounded-full text-xs font-medium">{{ $config['label'] }}</span>
                            <h1 class="text-2xl font-bold text-navy-900 mt-2">{{ $item->name }}</h1>
                            <p class="text-navy-500">{{ $item->brand }} {{ ($config['subtitle'])($item) }}</p>
                        </div>
                        <span class="{{ $item->status === 'available' ? 'status-available' : 'bg-gray-100 text-gray-500' }} px-3 py-1 rounded-full text-xs font-medium">
                            {{ $item->status === 'available' ? 'Tersedia' : ($item->status === 'rented' ? 'Sedang Disewa' : 'Dipesan') }}
                        </span>
                    </div>

                    <p class="text-navy-600 mb-6">{{ $item->description }}</p>

                    @php $specs = ($config['specs'])($item); @endphp
                    @if(!empty($specs))
                    <h3 class="font-bold text-navy-800 mb-3">Spesifikasi</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        @foreach($specs as $spec)
                        <div class="bg-gray-50 p-3 rounded-xl text-center">
                            <p class="text-sm font-medium text-navy-800">{{ $spec }}</p>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($item->condition)
                    <div class="bg-gray-50 p-4 rounded-xl">
                        <p class="text-sm text-navy-500">Kondisi Unit</p>
                        <p class="font-bold text-navy-800 capitalize">{{ ucfirst($item->condition) }}</p>
                    </div>
                    @endif
                </div>
            </div>

            @if($related->count())
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-navy-800 mb-4">Unit Serupa</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($related as $r)
                    <a href="{{ route('public.item', [$type, $r->slug]) }}" class="group border border-gray-100 rounded-xl p-3 hover:border-sky-300 transition">
                        <div class="h-20 bg-sky-50 rounded-lg flex items-center justify-center mb-2 overflow-hidden">
                            @if($r->image)
                                <img src="{{ asset('storage/' . $r->image) }}" class="w-full h-full object-cover" alt="{{ $r->name }}">
                            @else
                                <i class="fas {{ $config['icon'] }} text-sky-300 text-2xl"></i>
                            @endif
                        </div>
                        <p class="text-[12px] font-semibold text-navy-800 group-hover:text-sky-600 leading-tight">{{ Str::limit($r->name, 28) }}</p>
                        <p class="text-[11px] text-sky-600 font-medium">Rp {{ number_format($r->daily_price, 0, ',', '.') }}/hari</p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- BOOKING SIDEBAR --}}
        <div>
            <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-24">
                <div class="mb-6">
                    <p class="text-xs text-navy-500 mb-1">Harga Sewa</p>
                    <p class="text-3xl font-bold text-sky-600">Rp {{ number_format($item->daily_price, 0, ',', '.') }}</p>
                    <p class="text-sm text-navy-500">/hari</p>
                </div>

                <div class="space-y-3 mb-6">
                    @if($item->hourly_price)
                    <div class="flex justify-between text-sm">
                        <span class="text-navy-500">Per Jam</span>
                        <span class="font-medium text-navy-800">Rp {{ number_format($item->hourly_price, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-navy-500">Per Minggu</span>
                        <span class="font-medium text-navy-800">Rp {{ number_format($item->weekly_price ?? $item->daily_price * 7, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-navy-500">Per Bulan</span>
                        <span class="font-medium text-navy-800">Rp {{ number_format($item->monthly_price ?? $item->daily_price * 30, 0, ',', '.') }}</span>
                    </div>
                </div>

                @if(!empty($merchant))
                <div class="mb-5 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-2">Disewakan oleh</p>
                    <a href="{{ route('public.store', $merchant['slug']) }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center font-bold text-sky-700 flex-shrink-0 overflow-hidden">
                            @if(!empty($merchant['logo']))
                                <img src="{{ asset('storage/' . $merchant['logo']) }}" alt="" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($merchant['name'], 0, 1)) }}
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-navy-800 text-[13px] group-hover:text-sky-600 truncate">{{ $merchant['name'] }}</p>
                            <p class="text-[11px] text-gray-500 flex items-center gap-1">
                                @if($merchant['rating'] > 0)<i class="fas fa-star text-amber-400 text-[9px]"></i> {{ number_format($merchant['rating'], 1) }}@endif
                                @if($merchant['city'])<span>{{ $merchant['city'] }}</span>@endif
                            </p>
                            @if($item->company)
                            <p class="text-[10px] text-emerald-600 flex items-center gap-1 mt-0.5 truncate">
                                <i class="fas fa-store text-[8px]"></i> {{ $item->company->name }}
                            </p>
                            @endif
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 text-[10px] ml-auto group-hover:text-sky-500"></i>
                    </a>
                </div>
                @endif

                <div class="space-y-2 mb-5">
                    <div class="flex items-center text-[12px] text-gray-500"><i class="fas fa-shield-alt text-sky-400 mr-2"></i> Unit diperiksa sebelum & sesudah sewa</div>
                    <div class="flex items-center text-[12px] text-gray-500"><i class="fas fa-box-open text-sky-400 mr-2"></i> Kelengkapan tercatat saat serah terima</div>
                    <div class="flex items-center text-[12px] text-gray-500"><i class="fas fa-headset text-sky-400 mr-2"></i> Dukungan 24/7</div>
                </div>

                @auth
                    @if($item->status === 'available')
                    <a href="{{ route('bookings.create-item', [$type, $item->slug]) }}" class="btn-primary text-white w-full py-3 rounded-xl font-semibold text-center block">
                        <i class="fas fa-calendar-check mr-1.5"></i> Booking Sekarang
                    </a>
                    @else
                    <button disabled class="w-full py-3 rounded-xl font-semibold bg-gray-100 text-gray-400 cursor-not-allowed">Unit Sedang Tidak Tersedia</button>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn-primary text-white w-full py-3 rounded-xl font-semibold text-center block">
                        Login untuk Booking
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>

{{-- BACK TO TOP --}}
<button id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" class="back-to-top">
    <i class="fas fa-chevron-up"></i>
</button>
@endsection

@push('scripts')
<script>
const backToTopBtn = document.getElementById('backToTop');
window.addEventListener('scroll', () => {
    if (backToTopBtn) backToTopBtn.classList.toggle('visible', window.scrollY > 400);
}, { passive: true });
</script>
@endpush
