@extends('layouts.public')

@section('title', $item->name . ' - MariRent')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-6">
        <a href="{{ route('products', ['category' => $item->category->slug ?? '']) }}" class="text-sky-600 hover:text-sky-700 text-sm"><i class="fas fa-arrow-left mr-1"></i> Kembali ke {{ $config['label'] }}</a>
    </div>

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
@endsection
