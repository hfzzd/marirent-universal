@extends('layouts.dashboard')
@section('page-title', 'Subscription Merchant')

@section('content')
@php $money = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp

<div class="max-w-6xl mx-auto w-full">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 gap-3">
        <div class="min-w-0">
            <h2 class="text-base sm:text-lg font-bold text-navy-800 flex items-center gap-2">
                <i class="fas fa-credit-card text-sky-500"></i> Subscription Merchant
            </h2>
            <p class="text-[11px] sm:text-[12px] text-gray-400">Kelola plan billing (komisi per transaksi / subscription bulanan) semua toko</p>
        </div>
        <div class="flex flex-wrap gap-2 text-[11px] font-semibold">
            <span class="bg-sky-50 text-sky-700 border border-sky-200 px-3 py-1.5 rounded-lg whitespace-nowrap">Subscription: {{ $counts->subscription }}</span>
            <span class="bg-red-50 text-red-700 border border-red-200 px-3 py-1.5 rounded-lg whitespace-nowrap">Menunggak: {{ $counts->overdue }}</span>
            <span class="bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1.5 rounded-lg whitespace-nowrap">Perlu Verifikasi: {{ $counts->pending_verify }}</span>
        </div>
    </div>

    <div class="flex flex-col gap-3 mb-4">
        <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1">
            @php $tabs = ['all' => 'Semua', 'commission' => 'Komisi', 'subscription' => 'Subscription', 'active' => 'Aktif', 'overdue' => 'Menunggak']; @endphp
            @foreach($tabs as $key => $label)
            <a href="{{ route('superadmin.subscriptions', array_merge(request()->query(), ['status' => $key])) }}"
               class="px-4 py-2 rounded-xl text-[12px] font-semibold border transition whitespace-nowrap flex-shrink-0 {{ ($status ?? 'all') === $key ? 'bg-sky-600 text-white border-sky-600' : 'bg-white text-navy-600 border-gray-200 hover:border-sky-300' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
        <form action="{{ route('superadmin.subscriptions') }}" method="GET" class="flex items-center gap-2 w-full">
            @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari toko / email owner..."
                class="border border-gray-200 rounded-lg px-3 py-2 text-[12px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white w-full sm:max-w-xs">
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg text-[12px] transition flex-shrink-0" aria-label="Cari"><i class="fas fa-search text-gray-500"></i></button>
            @if(request('search'))
            <a href="{{ route('superadmin.subscriptions', request('status') ? ['status' => request('status')] : []) }}" class="bg-red-50 hover:bg-red-100 text-red-500 px-3 py-2 rounded-lg text-[12px] font-medium transition border border-red-100 flex-shrink-0" aria-label="Reset">
                <i class="fas fa-times text-[10px]"></i>
            </a>
            @endif
        </form>
    </div>

    {{-- Desktop / tablet landscape: tabel --}}
    <div class="glass-card rounded-2xl border border-gray-100 overflow-hidden hidden md:block">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-[12px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-[10px] text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 font-semibold">Toko / Owner</th>
                        <th class="px-4 py-3 font-semibold">Plan</th>
                        <th class="px-4 py-3 font-semibold">Fee Bulanan</th>
                        <th class="px-4 py-3 font-semibold">Tagihan Aktif</th>
                        <th class="px-4 py-3 font-semibold">Aktif s.d.</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                    <tr class="border-b border-gray-50 hover:bg-sky-50/40 transition-colors">
                        <td class="px-4 py-3">
                            <p class="font-bold text-navy-800">{{ $row->merchant->name }}</p>
                            <p class="text-[10px] text-gray-400 break-all">{{ $row->owner?->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($row->billing_plan === 'subscription')
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-sky-100 text-sky-700 whitespace-nowrap">SUBSCRIPTION</span>
                            @else
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 whitespace-nowrap">KOMISI</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-semibold text-navy-700 whitespace-nowrap">{{ $row->billing_plan === 'subscription' ? $money($row->fee) : '-' }}</td>
                        <td class="px-4 py-3">
                            @if($row->current_bill)
                            <p class="font-semibold text-navy-700 whitespace-nowrap">{{ $money($row->current_bill->amount) }}</p>
                            <p class="text-[10px] text-gray-400 whitespace-nowrap">s.d. {{ $row->current_bill->period_end->format('d M Y') }}</p>
                            @else
                            <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $row->subscription_until?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($row->overdue)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600 whitespace-nowrap">MENUNGGAK</span>
                            @elseif($row->billing_plan === 'subscription')
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-600 whitespace-nowrap">AKTIF</span>
                            @else
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 whitespace-nowrap">KOMPENSASI</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('superadmin.subscriptions.show', $row->merchant) }}" class="text-[11px] text-sky-600 hover:text-sky-700 font-semibold">Kelola <i class="fas fa-arrow-right text-[9px]"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">Tidak ada data merchant.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- HP / tablet portrait: kartu --}}
    <div class="grid grid-cols-1 gap-3 md:hidden">
        @forelse($rows as $row)
        <div class="glass-card rounded-2xl border border-gray-100 p-4 bg-white">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div class="min-w-0">
                    <p class="font-bold text-navy-800 text-[13px] truncate">{{ $row->merchant->name }}</p>
                    <p class="text-[10px] text-gray-400 truncate">{{ $row->owner?->email }}</p>
                </div>
                @if($row->overdue)
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600 flex-shrink-0">MENUNGGAK</span>
                @elseif($row->billing_plan === 'subscription')
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-600 flex-shrink-0">AKTIF</span>
                @else
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 flex-shrink-0">KOMISI</span>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-2 text-[11px] mb-3">
                <div class="bg-gray-50 rounded-xl p-2.5">
                    <p class="text-[10px] text-gray-400 font-semibold uppercase">Fee Bulanan</p>
                    <p class="font-bold text-navy-800 mt-0.5">{{ $row->billing_plan === 'subscription' ? $money($row->fee) : '-' }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-2.5">
                    <p class="text-[10px] text-gray-400 font-semibold uppercase">Aktif s.d.</p>
                    <p class="font-bold text-navy-800 mt-0.5">{{ $row->subscription_until?->format('d M Y') ?? '-' }}</p>
                </div>
            </div>
            @if($row->current_bill)
            <p class="text-[11px] text-gray-500 mb-3">Tagihan aktif: <strong class="text-navy-800">{{ $money($row->current_bill->amount) }}</strong> <span class="text-gray-400">s.d. {{ $row->current_bill->period_end->format('d M Y') }}</span></p>
            @endif
            <a href="{{ route('superadmin.subscriptions.show', $row->merchant) }}" class="block text-center bg-sky-600 hover:bg-sky-700 text-white text-[12px] font-bold py-2.5 rounded-xl transition">Kelola <i class="fas fa-arrow-right text-[10px] ml-1"></i></a>
        </div>
        @empty
        <div class="glass-card rounded-2xl border border-gray-100 p-10 text-center text-gray-400 text-[12px] bg-white">Tidak ada data merchant.</div>
        @endforelse
    </div>
</div>
@endsection
