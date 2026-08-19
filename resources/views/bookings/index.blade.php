@extends('layouts.dashboard')
@section('page-title', 'Booking')

@section('content')
<div class="flex items-center gap-2 mb-5 flex-wrap">
    @foreach(['','pending','confirmed','ongoing','completed','cancelled'] as $s)
    <a href="{{ route('bookings.index', array_merge(request()->query(), ['status' => $s ?: null])) }}"
       class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('status', '') == $s ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">
        {{ $s == '' ? 'Semua' : ucfirst($s) }}
    </a>
    @endforeach
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kode</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Pengguna</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kendaraan</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Tanggal</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Pembayaran</th>
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Total</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30">
                    <td class="py-3 px-5 font-medium text-sky-600">{{ $b->booking_code }}</td>
                    <td class="py-3 px-5 text-navy-700">{{ $b->user->name }}</td>
                    <td class="py-3 px-5 text-navy-700">{{ $b->vehicle->name ?? ($b->category->name ?? '-') }}</td>
                    <td class="py-3 px-5 text-[12px] text-navy-500">{{ $b->start_date->format('d M') }} - {{ $b->end_date->format('d M Y') }}</td>
                    <td class="py-3 px-5 text-center">
                        @if($b->status == 'pending') <span class="badge badge-blue">{{ ucfirst($b->status) }}</span>
                        @elseif($b->status == 'confirmed') <span class="badge badge-teal">{{ ucfirst($b->status) }}</span>
                        @elseif($b->status == 'ongoing') <span class="badge badge-yellow">{{ ucfirst($b->status) }}</span>
                        @elseif($b->status == 'completed') <span class="badge badge-green">{{ ucfirst($b->status) }}</span>
                        @else <span class="badge badge-gray">{{ ucfirst($b->status) }}</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-center">
                        @if($b->payment_status == 'paid') <span class="badge badge-green">Lunas</span>
                        @elseif($b->payment_status == 'partial') <span class="badge badge-yellow">Sebagian</span>
                        @else <span class="badge badge-red">Belum Bayar</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-right font-medium text-navy-700">Rp {{ number_format($b->final_price,0,',','.') }}</td>
                    <td class="py-3 px-5"><a href="{{ route('bookings.show', $b) }}" class="text-sky-600 text-[12px] font-medium">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-10 text-center text-gray-300">Belum ada booking</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $bookings->links() }}</div>
</div>
@endsection
