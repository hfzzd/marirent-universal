@extends('layouts.dashboard')
@section('page-title', 'Monitoring')

@section('content')
@php
    $activeStatus = request('status', 'all');
@endphp

{{-- Filter Status --}}
<div class="flex items-center gap-2 mb-5 flex-wrap">
    @php $statuses = ['all' => 'Semua', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'ongoing' => 'Ongoing', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan']; @endphp
    @foreach($statuses as $val => $label)
    <a href="{{ route('superadmin.monitoring', ['status' => $val]) }}"
       class="px-4 py-2 rounded-xl text-[12px] font-semibold transition {{ $activeStatus == $val ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- Datasheet --}}
<div class="glass-card rounded-2xl overflow-hidden">
    <div class="px-5 py-4 border-b border-sky-100/50 flex items-center justify-between">
        <h3 class="text-[14px] font-bold text-navy-800">Datasheet Monitoring Aktivitas</h3>
        <div class="flex items-center gap-3">
            <span class="text-[11px] text-gray-400 bg-sky-50 px-3 py-1 rounded-full">{{ now()->format('d M Y') }}</span>
            <button onclick="window.print()" class="text-[11px] text-sky-600 hover:text-sky-700 font-semibold bg-sky-50 hover:bg-sky-100 px-3 py-1.5 rounded-lg transition"><i class="fas fa-print mr-1"></i> Print</button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-[12px]">
            <thead>
                <tr class="bg-sky-50/50">
                    <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider sticky left-0 bg-sky-50/80 backdrop-blur-sm">No</th>
                    <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">ID Booking</th>
                    <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Kategori</th>
                    <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Barang</th>
                    <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Pengguna</th>
                    <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Tgl Mulai</th>
                    <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Tgl Selesai</th>
                    <th class="text-center py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Status</th>
                    <th class="text-center py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Pembayaran</th>
                    <th class="text-right py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $query = \App\Models\Booking::with(['user', 'vehicle', 'category']);
                    if ($activeStatus !== 'all') {
                        $query->where('status', $activeStatus);
                    }
                    $bookings = $query->latest()->paginate(25);
                @endphp
                @forelse($bookings as $i => $b)
                <tr class="border-b border-sky-50/50 last:border-0 hover:bg-sky-50/30 transition cursor-pointer" onclick="window.location='{{ route('bookings.show', $b) }}'">
                    <td class="py-2.5 px-3 text-gray-400 sticky left-0 bg-white/80 backdrop-blur-sm">{{ $bookings->firstItem() + $i }}</td>
                    <td class="py-2.5 px-3 font-semibold text-sky-600">{{ $b->booking_code }}</td>
                    <td class="py-2.5 px-3">
                        @php $catSlug = $b->category->slug ?? ''; @endphp
                        @if($catSlug == 'mobil') <span class="badge badge-blue">Mobil</span>
                        @elseif($catSlug == 'motor') <span class="badge badge-yellow">Motor</span>
                        @elseif($catSlug == 'sewa-kamera') <span class="badge badge-teal">Kamera</span>
                        @elseif($catSlug == 'sewa-tenda') <span class="badge badge-green">Tenda</span>
                        @elseif($catSlug == 'sewa-hp') <span class="badge badge-gray">HP</span>
                        @else <span class="badge badge-gray">{{ $catSlug ?: '-' }}</span>
                        @endif
                    </td>
                    <td class="py-2.5 px-3 text-navy-700 font-medium">
                        @if($b->vehicle) {{ $b->vehicle->name }}
                        @else {{ $b->category->name ?? '-' }}
                        @endif
                    </td>
                    <td class="py-2.5 px-3 text-navy-600">{{ $b->user->name }}</td>
                    <td class="py-2.5 px-3 text-navy-500">{{ $b->start_date->format('d/m/Y') }}</td>
                    <td class="py-2.5 px-3 text-navy-500">{{ $b->end_date->format('d/m/Y') }}</td>
                    <td class="py-2.5 px-3 text-center">
                        @if($b->status == 'pending') <span class="badge badge-blue">Pending</span>
                        @elseif($b->status == 'confirmed') <span class="badge badge-teal">Confirmed</span>
                        @elseif($b->status == 'ongoing') <span class="badge badge-yellow">Ongoing</span>
                        @elseif($b->status == 'completed') <span class="badge badge-green">Selesai</span>
                        @elseif($b->status == 'cancelled') <span class="badge badge-red">Dibatalkan</span>
                        @else <span class="badge badge-gray">{{ ucfirst($b->status) }}</span>
                        @endif
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        @if($b->payment_status == 'paid') <span class="badge badge-green">Lunas</span>
                        @elseif($b->payment_status == 'partial') <span class="badge badge-yellow">Sebagian</span>
                        @elseif($b->payment_status == 'refunded') <span class="badge badge-gray">Refund</span>
                        @else <span class="badge badge-red">Belum Bayar</span>
                        @endif
                    </td>
                    <td class="py-2.5 px-3 text-right font-bold text-navy-800">Rp {{ number_format($b->final_price, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="10" class="py-12 text-center text-gray-300">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-sky-100/50">{{ $bookings->withQueryString()->links() }}</div>
</div>
@endsection
