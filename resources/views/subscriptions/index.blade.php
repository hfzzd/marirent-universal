@extends('layouts.dashboard')
@section('page-title', 'Riwayat Subscription')

@section('content')
@php $money = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp

<div class="max-w-4xl mx-auto w-full">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 gap-3">
        <div class="min-w-0">
            <h2 class="text-base sm:text-lg font-bold text-navy-800 flex items-center gap-2">
                <i class="fas fa-credit-card text-sky-500"></i> Subscription
            </h2>
            <p class="text-[11px] sm:text-[12px] text-gray-400">Riwayat tagihan dan status langganan {{ $merchant->name }}</p>
        </div>
        @if($merchant->subscription_until)
        <div class="bg-sky-50 border border-sky-200 text-sky-700 px-4 py-2 rounded-xl text-[12px] font-semibold whitespace-nowrap flex-shrink-0">
            <i class="fas fa-shield-halved mr-1.5"></i> Aktif s.d. {{ $merchant->subscription_until->format('d M Y') }}
        </div>
        @endif
    </div>

    <div class="mb-4">
        <form action="{{ route('subscriptions.index') }}" method="GET" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari #id, metode, atau status..."
                class="border border-gray-200 rounded-lg px-3 py-2 text-[12px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white w-full sm:max-w-xs">
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg text-[12px] transition flex-shrink-0" aria-label="Cari"><i class="fas fa-search text-gray-500"></i></button>
            @if(request('search'))
            <a href="{{ route('subscriptions.index') }}" class="bg-red-50 hover:bg-red-100 text-red-500 px-3 py-2 rounded-lg text-[12px] font-medium transition border border-red-100 flex-shrink-0" aria-label="Reset">
                <i class="fas fa-times text-[10px]"></i>
            </a>
            @endif
        </form>
    </div>

    <div class="glass-card rounded-2xl border border-gray-100 overflow-hidden bg-white">
        @if($subscriptions->isEmpty())
        <div class="p-10 text-center">
            <i class="fas fa-receipt text-gray-300 text-3xl mb-2"></i>
            <p class="text-[13px] font-semibold text-navy-800">Belum ada riwayat tagihan</p>
            <p class="text-[12px] text-gray-400">Toko Anda masih menggunakan skema komisi per transaksi.</p>
        </div>
        @else
        {{-- Desktop / tablet: tabel --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full min-w-[620px] text-left text-[12px]">
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
                            <p class="font-semibold text-navy-700 whitespace-nowrap">{{ $sub->period_start->format('d M Y') }} – {{ $sub->period_end->format('d M Y') }}</p>
                            <p class="text-[10px] text-gray-400">#{{ $sub->id }}</p>
                            @if($sub->rejection_reason)<p class="text-[10px] text-red-500 mt-0.5">Ditolak: {{ $sub->rejection_reason }}</p>@endif
                        </td>
                        <td class="px-4 py-3 font-bold text-navy-800 whitespace-nowrap">{{ $money($sub->amount) }}</td>
                        <td class="px-4 py-3 uppercase text-gray-500 text-[11px]">{{ $sub->method ?? '-'; }}</td>
                        <td class="px-4 py-3">
                            @if($sub->isPaid())
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-600 whitespace-nowrap">LUNAS</span>
                            @elseif($sub->status === 'overdue')
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600 whitespace-nowrap">JATUH TEMPO</span>
                            @else
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-600 whitespace-nowrap">MENUNGGU</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500 whitespace-nowrap">
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
        </div>
        {{-- HP: kartu --}}
        <div class="grid grid-cols-1 gap-3 p-4 sm:hidden bg-gray-50/50">
            @foreach($subscriptions as $sub)
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <div class="flex items-start justify-between gap-2 mb-1.5">
                    <div class="min-w-0">
                        <p class="font-semibold text-navy-700 text-[12px]">{{ $sub->period_start->format('d M Y') }} – {{ $sub->period_end->format('d M Y') }}</p>
                        <p class="text-[10px] text-gray-400">#{{ $sub->id }} • {{ strtoupper($sub->method ?? '-') }}</p>
                    </div>
                    @if($sub->isPaid())
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-600 flex-shrink-0">LUNAS</span>
                    @elseif($sub->status === 'overdue')
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600 flex-shrink-0">JATUH TEMPO</span>
                    @else
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-600 flex-shrink-0">MENUNGGU</span>
                    @endif
                </div>
                <p class="text-[15px] font-extrabold text-navy-900">{{ $money($sub->amount) }}</p>
                @if($sub->rejection_reason)<p class="text-[11px] text-red-500 mt-1">Ditolak: {{ $sub->rejection_reason }}</p>@endif
                <p class="text-[10px] text-gray-400 mt-1">{{ $sub->isPaid() ? 'Dibayar: ' . $sub->paid_at?->format('d M Y H:i') : 'Belum dibayar' }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <p class="text-[11px] text-gray-400 mt-4">
        <i class="fas fa-info-circle mr-1"></i> Terapkan skema <strong>Subscription</strong> atau <strong>Komisi per transaksi</strong> dapat diatur oleh admin melalui halaman Subscription di Superadmin.
    </p>
</div>
@endsection
