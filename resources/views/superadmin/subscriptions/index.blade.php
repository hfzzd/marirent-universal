@extends('layouts.dashboard')
@section('page-title', 'Subscription Merchant')

@section('content')
@php $money = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp

<div class="max-w-6xl">
    <div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
        <div>
            <h2 class="text-lg font-bold text-navy-800 flex items-center gap-2">
                <i class="fas fa-credit-card text-sky-500"></i> Subscription Merchant
            </h2>
            <p class="text-[12px] text-gray-400">Kelola plan billing (komisi per transaksi / subscription bulanan) semua toko</p>
        </div>
        <div class="flex gap-2 text-[11px] font-semibold">
            <span class="bg-sky-50 text-sky-700 border border-sky-200 px-3 py-1.5 rounded-lg">Subscription: {{ $counts->subscription }}</span>
            <span class="bg-red-50 text-red-700 border border-red-200 px-3 py-1.5 rounded-lg">Menunggak: {{ $counts->overdue }}</span>
            <span class="bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1.5 rounded-lg">Perlu Verifikasi: {{ $counts->pending_verify }}</span>
        </div>
    </div>

    <div class="flex gap-2 mb-4 flex-wrap">
        @php $tabs = ['all' => 'Semua', 'commission' => 'Komisi', 'subscription' => 'Subscription', 'active' => 'Aktif', 'overdue' => 'Menunggak']; @endphp
        @foreach($tabs as $key => $label)
        <a href="{{ route('superadmin.subscriptions', ['status' => $key]) }}"
           class="px-4 py-2 rounded-xl text-[12px] font-semibold border transition {{ ($status ?? 'all') === $key ? 'bg-sky-600 text-white border-sky-600' : 'bg-white text-navy-600 border-gray-200 hover:border-sky-300' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    <div class="glass-card rounded-2xl border border-gray-100 overflow-hidden">
        <table class="w-full text-left text-[12px]">
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
                        <p class="text-[10px] text-gray-400">{{ $row->owner?->email }}</p>
                    </td>
                    <td class="px-4 py-3">
                        @if($row->billing_plan === 'subscription')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-sky-100 text-sky-700">SUBSCRIPTION</span>
                        @else
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">KOMISI</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-semibold text-navy-700">{{ $row->billing_plan === 'subscription' ? $money($row->fee) : '-' }}</td>
                    <td class="px-4 py-3">
                        @if($row->current_bill)
                        <p class="font-semibold text-navy-700">{{ $money($row->current_bill->amount) }}</p>
                        <p class="text-[10px] text-gray-400">s.d. {{ $row->current_bill->period_end->format('d M Y') }}</p>
                        @else
                        <span class="text-gray-300">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $row->subscription_until?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @if($row->overdue)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600">MENUNGGAK</span>
                        @elseif($row->billing_plan === 'subscription')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-600">AKTIF</span>
                        @else
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">KOMPENSASI</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
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
@endsection