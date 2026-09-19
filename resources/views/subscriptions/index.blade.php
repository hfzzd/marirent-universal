@extends('layouts.dashboard')
@section('page-title', 'Riwayat Subscription')

@section('content')
@php $money = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp

<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
        <div>
            <h2 class="text-lg font-bold text-navy-800 flex items-center gap-2">
                <i class="fas fa-credit-card text-sky-500"></i> Subscription
            </h2>
            <p class="text-[12px] text-gray-400">Riwayat tagihan dan status langganan {{ $merchant->name }}</p>
        </div>
        @if($merchant->subscription_until)
        <div class="bg-sky-50 border border-sky-200 text-sky-700 px-4 py-2 rounded-xl text-[12px] font-semibold">
            <i class="fas fa-shield-halved mr-1.5"></i> Aktif s.d. {{ $merchant->subscription_until->format('d M Y') }}
        </div>
        @endif
    </div>

    <div class="mb-4">
        <form action="{{ route('subscriptions.index') }}" method="GET" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari #id, metode, atau status..."
                class="border border-gray-200 rounded-lg px-3 py-2 text-[12px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white w-full sm:w-64">
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg text-[12px] transition flex-shrink-0"><i class="fas fa-search text-gray-500"></i></button>
            @if(request('search'))
            <a href="{{ route('subscriptions.index') }}" class="bg-red-50 hover:bg-red-100 text-red-500 px-3 py-2 rounded-lg text-[12px] font-medium transition border border-red-100 flex-shrink-0">
                <i class="fas fa-times text-[10px]"></i>
            </a>
            @endif
        </form>
    </div>

    <div class="glass-card rounded-2xl border border-gray-100 overflow-hidden">
        @if($subscriptions->isEmpty())
        <div class="p-10 text-center">
            <i class="fas fa-receipt text-gray-300 text-3xl mb-2"></i>
            <p class="text-[13px] font-semibold text-navy-800">Belum ada riwayat tagihan</p>
            <p class="text-[12px] text-gray-400">Toko Anda masih menggunakan skema komisi per transaksi.</p>
        </div>
        @else
        <table class="w-full text-left text-[12px]">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-[10px] text-gray-400 uppercase tracking-wide">
                    <th class="px-4 py-3 font-semibold">Periode</th>
                    <th class="px-4 py-3 font-semibold">Jumlah</th>
                    <th class="px-4 py-3 font-semibold">Metode</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3 font-semibold text-right">Dibayar / Diverifikasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subscriptions as $sub)
                <tr class="border-b border-gray-50 hover:bg-sky-50/40 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-navy-700">{{ $sub->period_start->format('d M Y') }} – {{ $sub->period_end->format('d M Y') }}</p>
                        <p class="text-[10px] text-gray-400">#{{ $sub->id }}</p>
                    </td>
                    <td class="px-4 py-3 font-bold text-navy-800">{{ $money($sub->amount) }}</td>
                    <td class="px-4 py-3 uppercase text-gray-500 text-[11px]">{{ $sub->method ?? '-'; }}</td>
                    <td class="px-4 py-3">
                        @if($sub->isPaid())
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-600">LUNAS</span>
                        @elseif($sub->status === 'overdue')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600">JATUH TEMPO</span>
                        @else
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-600">MENUNGGU</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-500">
                        @if($sub->isPaid())
                        {{ $sub->paid_at?->format('d M Y H:i') }}
                        @else
                        <span class="text-gray-300">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <p class="text-[11px] text-gray-400 mt-4">
        <i class="fas fa-info-circle mr-1"></i> Terapkan skema <strong>Subscription</strong> atau <strong>Komisi per transaksi</strong> dapat diatur oleh admin melalui halaman Subscription di Superadmin.
    </p>
</div>
@endsection