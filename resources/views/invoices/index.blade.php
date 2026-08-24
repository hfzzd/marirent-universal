@extends(auth()->user()->role === 'user' ? 'layouts.user' : 'layouts.dashboard')
@section('title', 'Invoice Saya - MariRent')
@section('page-title', 'Invoice Saya')

@section('content')
@php $isUser = auth()->user()->role === 'user'; @endphp

<div class="mb-5">
    <h2 class="text-[14px] font-semibold text-navy-800">{{ $isUser ? 'Riwayat Invoice Saya' : 'Semua Invoice' }}</h2>
    <p class="text-[11px] text-gray-400 mt-0.5">{{ $isUser ? 'Lihat detail tagihan dan status pembayaran Anda' : 'Kelola seluruh invoice' }}</p>
</div>

<div class="flex items-center justify-between gap-2 mb-5 flex-wrap">
    <div class="flex gap-2 flex-wrap">
    @php
        $statusLabels = [
            '' => 'Semua',
            'draft' => 'Draft',
            'sent' => 'Terkirim',
            'paid' => 'Lunas',
            'partial' => 'Sebagian',
            'overdue' => 'Terlambat'
        ];
    @endphp
    @foreach($statusLabels as $val => $label)
    <a href="{{ route('invoices.index', array_merge(request()->query(), ['status' => $val ?: null])) }}"
       class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('status', '') == $val ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">
        {{ $label }}
    </a>
    @endforeach
    </div>
    @if(in_array(auth()->user()->role, ['superadmin', 'owner']))
    <a href="{{ route('invoices.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-[13px] font-medium"><i class="fas fa-layer-group mr-1.5"></i> Invoice Gabungan</a>
    @endif
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Nomor Invoice</th>
                    @if(!$isUser)
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Tipe</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Pengguna</th>
                    @else
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Booking</th>
                    @endif
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Total</th>
                    @if(!$isUser)
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Dibayar</th>
                    @else
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Sisa Bayar</th>
                    @endif
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Jatuh Tempo</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30 transition-colors">
                    <td class="py-3 px-5 font-medium text-sky-600">{{ $inv->invoice_number }}</td>
                    @if(!$isUser)
                    <td class="py-3 px-5 text-[12px] text-navy-600">
                        @if($inv->type == 'rental') <span class="inline-flex items-center gap-1"><i class="fas fa-car text-sky-400"></i> Sewa</span>
                        @elseif($inv->type == 'driver_salary') <span class="inline-flex items-center gap-1"><i class="fas fa-id-card text-emerald-400"></i> Gaji</span>
                        @elseif($inv->type == 'replacement') <span class="inline-flex items-center gap-1"><i class="fas fa-exchange-alt text-amber-400"></i> Penggantian</span>
                        @elseif($inv->type == 'damage') <span class="inline-flex items-center gap-1"><i class="fas fa-tools text-red-400"></i> Kerusakan</span>
                        @else <span class="inline-flex items-center gap-1"><i class="fas fa-file text-gray-400"></i> Lainnya</span>
                        @endif
                        @if($inv->bookings->count() > 1)
                        <span class="ml-1 inline-flex items-center bg-violet-50 text-violet-600 border border-violet-200 rounded px-1.5 py-0.5 text-[10px] font-bold" title="Invoice gabungan {{ $inv->bookings->count() }} sewa"><i class="fas fa-layer-group mr-0.5"></i> {{ $inv->bookings->count() }}x</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-navy-700">{{ $inv->user->name }}</td>
                    @else
                    <td class="py-3 px-5">
                        @php $bs = $inv->bookings->isNotEmpty() ? $inv->bookings : collect([$inv->booking])->filter(); @endphp
                        <div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-navy-700 font-medium">{{ $bs->first()?->booking_code ?? '-' }}</span>
                                @if($bs->count() > 1)
                                <span class="inline-flex items-center bg-violet-50 text-violet-600 border border-violet-200 rounded px-1.5 py-0.5 text-[10px] font-bold"><i class="fas fa-layer-group mr-0.5"></i> +{{ $bs->count() - 1 }}</span>
                                @endif
                            </div>
                            @if($bs->isNotEmpty())
                            <p class="text-[11px] text-gray-400">{{ $bs->first()->vehicle->name ?? $bs->first()->category->name ?? '-' }}</p>
                            @endif
                            @if($bs->count() > 1)
                            <p class="text-[10px] font-semibold text-violet-500 mt-0.5">Gabungan {{ $bs->count() }} sewa</p>
                            @endif
                        </div>
                    </td>
                    @endif
                    <td class="py-3 px-5 text-right font-medium text-navy-700">Rp {{ number_format($inv->total_amount,0,',','.') }}</td>
                    @if(!$isUser)
                    <td class="py-3 px-5 text-right text-navy-600">Rp {{ number_format($inv->paid_amount,0,',','.') }}</td>
                    @else
                    <td class="py-3 px-5 text-right">
                        @if($inv->getRemainingAmount() > 0)
                        <span class="text-amber-600 font-medium">Rp {{ number_format($inv->getRemainingAmount(),0,',','.') }}</span>
                        @else
                        <span class="text-emerald-600 font-medium">Rp 0</span>
                        @endif
                    </td>
                    @endif
                    <td class="py-3 px-5 text-center">
                        @if($inv->status == 'paid') <span class="badge badge-green">Lunas</span>
                        @elseif($inv->status == 'partial') <span class="badge badge-yellow">Sebagian</span>
                        @elseif($inv->status == 'overdue') <span class="badge badge-red">Terlambat</span>
                        @elseif($inv->status == 'sent') <span class="badge badge-blue">Terkirim</span>
                        @else <span class="badge badge-gray">Draft</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-[12px] {{ $inv->isOverdue() ? 'text-red-500 font-semibold' : 'text-navy-500' }}">
                        <div class="flex items-center gap-1.5">
                            @if($inv->isOverdue()) <i class="fas fa-exclamation-circle text-[10px]"></i> @endif
                            {{ $inv->due_date->format('d M Y') }}
                        </div>
                    </td>
                    <td class="py-3 px-5">
                        <a href="{{ route('invoices.show', $inv) }}" class="text-sky-600 text-[12px] font-medium hover:text-sky-700 transition inline-flex items-center gap-1">
                            <i class="fas fa-eye text-[10px]"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $isUser ? '7' : '8' }}" class="py-14 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 bg-sky-50 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-file-invoice text-sky-300 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-[13px] font-medium text-navy-700">{{ $isUser ? 'Belum ada invoice' : 'Tidak ada data invoice' }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $isUser ? 'Invoice akan muncul setelah pemesanan dikonfirmasi' : '' }}</p>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $invoices->links() }}</div>
</div>
@endsection
