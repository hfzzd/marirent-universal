@extends(auth()->user()->role === 'user' ? 'layouts.user' : 'layouts.dashboard')
@section('title', 'Booking Saya - MariRent')
@section('page-title', 'Booking Saya')

@section('content')
@php $isUser = auth()->user()->role === 'user'; @endphp

<div class="mb-5 flex items-center justify-between flex-wrap gap-3">
    <div>
        <h2 class="text-[14px] font-semibold text-navy-800">{{ $isUser ? 'Riwayat Pemesanan Saya' : 'Semua Booking' }}</h2>
        <p class="text-[11px] text-gray-400 mt-0.5">{{ $isUser ? 'Lacak status pemesanan kendaraan dan barang Anda' : 'Kelola seluruh pemesanan' }}</p>
    </div>
    @if($isUser)
    <a href="{{ route('home') }}" class="btn-primary text-white px-4 py-2 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 inline-flex items-center gap-1.5">
        <i class="fas fa-plus text-[10px]"></i> Booking Baru
    </a>
    @elseif(in_array(auth()->user()->role, ['superadmin', 'owner']))
    <div class="flex items-center gap-2">
        <a href="{{ route('bookings.manual-create') }}" class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white px-4 py-2 rounded-xl text-[12px] font-semibold shadow-lg shadow-emerald-500/25 inline-flex items-center gap-1.5 transition">
            <i class="fas fa-user-pen text-[10px]"></i> Booking Manual
        </a>
        <a href="{{ route('superadmin.monitoring') }}" class="bg-white border border-gray-200 hover:border-sky-300 hover:text-sky-600 text-gray-500 px-4 py-2 rounded-xl text-[12px] font-semibold inline-flex items-center gap-1.5 transition">
            <i class="fas fa-calendar-days text-[10px]"></i> Scheduler
        </a>
    </div>
    @endif
</div>

<div class="flex items-center gap-2 mb-5 flex-wrap">
    @php
        $statusLabels = [
            '' => 'Semua',
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'ongoing' => 'Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];
    @endphp
    @foreach($statusLabels as $val => $label)
    <a href="{{ route('bookings.index', array_merge(request()->query(), ['status' => $val ?: null])) }}"
       class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('status', '') == $val ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kode Booking</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Barang / Kendaraan</th>
                    @if(!$isUser)
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Pengguna</th>
                    @endif
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Tanggal</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    @if(!$isUser)
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Pembayaran</th>
                    @endif
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Total</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30 transition-colors">
                    <td class="py-3 px-5 font-medium text-sky-600">
                        {{ $b->booking_code }}
                        @if(($b->source ?? 'online') == 'manual')<span class="badge badge-teal text-[9px] ml-1">Manual</span>@endif
                    </td>
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-2">
                            @php $cat = $b->category->slug ?? ''; @endphp
                            <div class="w-8 h-8 {{ $cat == 'sewa-kamera' ? 'bg-violet-50' : ($cat == 'sewa-tenda' ? 'bg-emerald-50' : 'bg-sky-50') }} rounded-lg flex items-center justify-center flex-shrink-0">
                                @if($cat == 'sewa-kamera') <i class="fas fa-camera text-violet-400 text-[10px]"></i>
                                @elseif($cat == 'sewa-tenda') <i class="fas fa-campground text-emerald-400 text-[10px]"></i>
                                @elseif($cat == 'sewa-hp') <i class="fas fa-mobile-alt text-blue-400 text-[10px]"></i>
                                @else <i class="fas fa-car text-sky-400 text-[10px]"></i>
                                @endif
                            </div>
                            <div>
                                <p class="text-navy-700 font-medium">{{ $b->vehicle?->name ?? ($b->category?->name ?? '-') }}</p>
                                @if($isUser)
                                <p class="text-[11px] text-gray-400">{{ ucfirst($b->rental_type ?? '-') }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    @if(!$isUser)
                    <td class="py-3 px-5 text-navy-700">{{ $b->user->name }}</td>
                    @endif
                    <td class="py-3 px-5 text-[12px] text-navy-500">
                        <div>{{ $b->start_date->format('d M Y') }}</div>
                        <div class="text-[11px] text-gray-400">s/d {{ $b->end_date->format('d M Y') }}</div>
                    </td>
                    <td class="py-3 px-5 text-center">
                        @if($b->status == 'pending') <span class="badge badge-blue">Menunggu</span>
                        @elseif($b->status == 'confirmed') <span class="badge badge-teal">Dikonfirmasi</span>
                        @elseif($b->status == 'ongoing') <span class="badge badge-yellow">Berlangsung</span>
                        @elseif($b->status == 'completed') <span class="badge badge-green">Selesai</span>
                        @elseif($b->status == 'cancelled') <span class="badge badge-red">Dibatalkan</span>
                        @else <span class="badge badge-gray">{{ ucfirst($b->status) }}</span>
                        @endif
                    </td>
                    @if(!$isUser)
                    <td class="py-3 px-5 text-center">
                        @if($b->payment_status == 'paid') <span class="badge badge-green">Lunas</span>
                        @elseif($b->payment_status == 'partial') <span class="badge badge-yellow">Sebagian</span>
                        @else <span class="badge badge-red">Belum Bayar</span>
                        @endif
                    </td>
                    @endif
                    <td class="py-3 px-5 text-right font-medium text-navy-700">Rp {{ number_format($b->final_price,0,',','.') }}</td>
                    <td class="py-3 px-5">
                        <a href="{{ route('bookings.show', $b) }}" class="text-sky-600 text-[12px] font-medium hover:text-sky-700 transition inline-flex items-center gap-1">
                            <i class="fas fa-eye text-[10px]"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $isUser ? '6' : '8' }}" class="py-14 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 bg-sky-50 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-calendar-times text-sky-300 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-[13px] font-medium text-navy-700">{{ $isUser ? 'Belum ada pemesanan' : 'Belum ada booking' }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $isUser ? 'Mulai sewa kendaraan atau barang sekarang!' : 'Tidak ada data booking ditemukan' }}</p>
                            </div>
                            @if($isUser)
                            <a href="{{ route('home') }}" class="btn-primary text-white px-5 py-2 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition mt-1 inline-flex items-center gap-1.5">
                                <i class="fas fa-plus text-[10px]"></i> Booking Baru
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $bookings->links() }}</div>
</div>
@endsection
