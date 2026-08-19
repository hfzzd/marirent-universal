@extends('layouts.dashboard')
@section('page-title', 'Invoice')

@section('content')
<div class="flex items-center gap-2 mb-5 flex-wrap">
    <a href="{{ route('invoices.index') }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ !request('status') ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Semua</a>
    @foreach(['draft','sent','paid','partial','overdue'] as $s)
    <a href="{{ route('invoices.index', ['status' => $s]) }}"
       class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('status') == $s ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">
        {{ ucfirst($s) }}
    </a>
    @endforeach
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Nomor</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Tipe</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Pengguna</th>
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Total</th>
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Dibayar</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Jatuh Tempo</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30">
                    <td class="py-3 px-5 font-medium text-sky-600">{{ $inv->invoice_number }}</td>
                    <td class="py-3 px-5 text-[12px] text-navy-600">
                        @if($inv->type == 'rental') Sewa
                        @elseif($inv->type == 'driver_salary') Gaji
                        @elseif($inv->type == 'replacement') Penggantian
                        @elseif($inv->type == 'damage') Kerusakan
                        @else Lainnya @endif
                    </td>
                    <td class="py-3 px-5 text-navy-700">{{ $inv->user->name }}</td>
                    <td class="py-3 px-5 text-right font-medium text-navy-700">Rp {{ number_format($inv->total_amount,0,',','.') }}</td>
                    <td class="py-3 px-5 text-right text-navy-600">Rp {{ number_format($inv->paid_amount,0,',','.') }}</td>
                    <td class="py-3 px-5 text-center">
                        @if($inv->status == 'paid') <span class="badge badge-green">Lunas</span>
                        @elseif($inv->status == 'partial') <span class="badge badge-yellow">Sebagian</span>
                        @elseif($inv->status == 'overdue') <span class="badge badge-red">Terlambat</span>
                        @elseif($inv->status == 'sent') <span class="badge badge-blue">Terkirim</span>
                        @else <span class="badge badge-gray">Draft</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-[12px] {{ $inv->isOverdue() ? 'text-red-500 font-semibold' : 'text-navy-500' }}">{{ $inv->due_date->format('d M Y') }}</td>
                    <td class="py-3 px-5"><a href="{{ route('invoices.show', $inv) }}" class="text-sky-600 text-[12px] font-medium">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-10 text-center text-gray-300">Belum ada invoice</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $invoices->links() }}</div>
</div>
@endsection
