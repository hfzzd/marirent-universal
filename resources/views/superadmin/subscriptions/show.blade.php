@extends('layouts.dashboard')
@section('page-title', 'Kelola Subscription — ' . $merchant->name)

@section('content')
@php $money = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp

<div class="max-w-5xl mx-auto w-full">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 gap-3">
        <div class="min-w-0">
            <a href="{{ route('superadmin.subscriptions') }}" class="text-[12px] text-sky-600 hover:text-sky-700 font-semibold mb-1 inline-flex items-center gap-1">
                <i class="fas fa-arrow-left text-[10px]"></i> Kembali
            </a>
            <h2 class="text-base sm:text-lg font-bold text-navy-800 flex items-center gap-2 break-words">
                <i class="fas fa-store text-sky-500 flex-shrink-0"></i> <span class="truncate">{{ $merchant->name }}</span>
            </h2>
            <p class="text-[11px] sm:text-[12px] text-gray-400 truncate">{{ $merchant->owner?->email }} — {{ $merchant->city ?? '-' }}</p>
        </div>
        @if($merchant->billing_plan === 'subscription')
        <div class="{{ $svc->isOverdue($merchant) ? 'bg-red-50 border-red-200 text-red-600' : 'bg-emerald-50 border-emerald-200 text-emerald-600' }} border px-4 py-2 rounded-xl text-[12px] font-semibold whitespace-nowrap flex-shrink-0">
            @if($svc->isOverdue($merchant))
            <i class="fas fa-exclamation-triangle mr-1.5"></i> MENUNGGAK — diblokir
            @else
            <i class="fas fa-shield-halved mr-1.5"></i> Aktif s.d. {{ $merchant->subscription_until?->format('d M Y') ?? '-' }}
            @endif
        </div>
        @endif
    </div>

    {{-- Plan form --}}
    <div class="glass-card rounded-2xl p-4 sm:p-5 border border-gray-100 mb-5 bg-white">
        <h4 class="text-[13px] font-bold text-navy-800 mb-3 flex items-center gap-1.5"><i class="fas fa-sliders-h text-sky-500"></i> Ubah Plan Billing</h4>
        <form method="POST" action="{{ route('superadmin.subscriptions.plan', $merchant) }}" class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-3 items-end">
            @csrf
            <div class="min-w-0">
                <label class="block text-[11px] font-semibold text-navy-600 mb-1">Plan</label>
                <x-searchable-select name="billing_plan" placeholder="Pilih plan" size="sm" wrapClass="w-full">
                    <option value="subscription" {{ $merchant->billing_plan === 'subscription' ? 'selected' : '' }}>Subscription (flat bulanan)</option>
                    <option value="commission" {{ $merchant->billing_plan === 'commission' ? 'selected' : '' }}>Komisi per transaksi (10%)</option>
                </x-searchable-select>
            </div>
            <div class="min-w-0">
                <label class="block text-[11px] font-semibold text-navy-600 mb-1">Fee Bulanan (Rp) — subscription</label>
                <input type="number" name="subscription_fee" value="{{ old('subscription_fee', $merchant->subscription_fee) }}" min="0" step="10000"
                       class="w-full border border-gray-200 bg-gray-50/50 rounded-xl px-3 py-2 text-[12px] focus:ring-2 focus:ring-sky-500/30 outline-none">
            </div>
            <button type="submit" class="bg-sky-600 text-white px-5 py-2 rounded-xl text-[12px] font-bold hover:bg-sky-700 transition w-full sm:w-auto">Simpan Plan</button>
            <p class="sm:col-span-3 text-[10px] text-gray-400">Catatan: beralih ke SUBSCRIPTION akan langsung membuat tagihan pertama (jatuh tempo +1 bulan). Beralih ke KOMISI menghentikan tagihan berlangganan.</p>
        </form>
    </div>

    {{-- Manual bill --}}
    <div class="glass-card rounded-2xl p-4 sm:p-5 border border-gray-100 mb-5 bg-white">
        <h4 class="text-[13px] font-bold text-navy-800 mb-3 flex items-center gap-1.5"><i class="fas fa-plus-circle text-sky-500"></i> Buat Tagihan Manual</h4>
        <form method="POST" action="{{ route('superadmin.subscriptions.bills', $merchant) }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto] gap-3 items-end">
            @csrf
            <div class="min-w-0">
                <label class="block text-[11px] font-semibold text-navy-600 mb-1">Periode Mulai</label>
                <input type="date" name="period_start" required class="w-full border border-gray-200 bg-gray-50/50 rounded-xl px-3 py-2 text-[12px] focus:ring-2 focus:ring-sky-500/30 outline-none">
            </div>
            <div class="min-w-0">
                <label class="block text-[11px] font-semibold text-navy-600 mb-1">Periode Selesai (jatuh tempo)</label>
                <input type="date" name="period_end" required class="w-full border border-gray-200 bg-gray-50/50 rounded-xl px-3 py-2 text-[12px] focus:ring-2 focus:ring-sky-500/30 outline-none">
            </div>
            <div class="min-w-0">
                <label class="block text-[11px] font-semibold text-navy-600 mb-1">Jumlah (Rp)</label>
                <input type="number" name="amount" required min="1" placeholder="500000"
                       class="w-full border border-gray-200 bg-gray-50/50 rounded-xl px-3 py-2 text-[12px] focus:ring-2 focus:ring-sky-500/30 outline-none">
            </div>
            <button type="submit" class="bg-emerald-600 text-white px-5 py-2 rounded-xl text-[12px] font-bold hover:bg-emerald-700 transition w-full sm:w-auto">Buat Tagihan</button>
            <div class="sm:col-span-2 lg:col-span-4">
                <label class="block text-[11px] font-semibold text-navy-600 mb-1">Catatan (opsional)</label>
                <input type="text" name="notes" maxlength="1000" placeholder="Keterangan tagihan..."
                       class="w-full border border-gray-200 bg-gray-50/50 rounded-xl px-3 py-2 text-[12px] focus:ring-2 focus:ring-sky-500/30 outline-none">
            </div>
        </form>
    </div>

    {{-- Billing history: tabel di desktop, kartu di HP --}}
    <div class="glass-card rounded-2xl border border-gray-100 overflow-hidden bg-white">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100">
            <h4 class="text-[13px] font-bold text-navy-800 flex items-center gap-1.5"><i class="fas fa-receipt text-sky-500"></i> Riwayat Tagihan</h4>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-[12px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-[10px] text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 font-semibold">Periode</th>
                        <th class="px-4 py-3 font-semibold">Jumlah</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold">Bukti / Referensi</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                    <tr class="border-b border-gray-50 hover:bg-sky-50/40 transition-colors align-top">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-navy-700 whitespace-nowrap">{{ $sub->period_start->format('d M Y') }} – {{ $sub->period_end->format('d M Y') }}</p>
                            <p class="text-[10px] text-gray-400">#{{ $sub->id }}</p>
                            @if($sub->notes)<p class="text-[10px] text-gray-400 mt-0.5">{{ $sub->notes }}</p>@endif
                        </td>
                        <td class="px-4 py-3 font-bold text-navy-800 whitespace-nowrap">{{ $money($sub->amount) }}</td>
                        <td class="px-4 py-3">
                            @if($sub->isPaid())
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-600 whitespace-nowrap">LUNAS</span>
                            @elseif($sub->status === 'overdue')
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600 whitespace-nowrap">JATUH TEMPO</span>
                            @elseif($sub->proof_photo)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-600 whitespace-nowrap">MENUNGGU VERIFIKASI</span>
                            @else
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 whitespace-nowrap">PENDING</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-[11px] text-gray-500">
                            @if($sub->proof_photo)
                            <a href="{{ asset('storage/' . $sub->proof_photo) }}" target="_blank" rel="noopener" class="text-sky-600 underline">Lihat Bukti</a>
                            @else
                            <span class="text-gray-300">-</span>
                            @endif
                            @if($sub->method)<span class="block text-gray-400 mt-0.5 uppercase">{{ $sub->method }}</span>@endif
                            @if($sub->reference_number)
                            <span class="block text-gray-400 mt-0.5 break-all">Ref: {{ $sub->reference_number }}</span>
                            @endif
                            @if($sub->rejection_reason)
                            <span class="block text-red-500 mt-0.5">Ditolak: {{ $sub->rejection_reason }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if(!$sub->isPaid() && $sub->proof_photo)
                            <form method="POST" action="{{ route('superadmin.subscriptions.verify', $sub) }}" class="inline">
                                @csrf
                                <button class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold hover:bg-emerald-700 transition">Verifikasi</button>
                            </form>
                            <button type="button" onclick="document.getElementById('reject-{{ $sub->id }}').classList.toggle('hidden')"
                                    class="bg-red-50 text-red-600 border border-red-200 px-3 py-1.5 rounded-lg text-[11px] font-bold ml-1 hover:bg-red-100 transition">Tolak</button>
                            @else
                            <span class="text-gray-300 text-[11px]">-</span>
                            @endif
                        </td>
                    </tr>
                    @if(!$sub->isPaid() && $sub->proof_photo)
                    <tr id="reject-{{ $sub->id }}" class="hidden">
                        <td colspan="5" class="px-4 py-2 bg-red-50/50">
                            <form method="POST" action="{{ route('superadmin.subscriptions.reject', $sub) }}" class="flex flex-col sm:flex-row gap-2 sm:items-center">
                                @csrf
                                <input type="text" name="rejection_reason" required placeholder="Alasan penolakan..." class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-[12px] outline-none">
                                <button class="bg-red-600 text-white px-4 py-2 rounded-xl text-[11px] font-bold flex-shrink-0">Konfirmasi Tolak</button>
                            </form>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada tagihan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 gap-3 p-4 md:hidden bg-gray-50/50">
            @forelse($subscriptions as $sub)
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-navy-700 text-[12px]">{{ $sub->period_start->format('d M Y') }} – {{ $sub->period_end->format('d M Y') }}</p>
                        <p class="text-[10px] text-gray-400">#{{ $sub->id }} • {{ $money($sub->amount) }}</p>
                    </div>
                    @if($sub->isPaid())
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-600 flex-shrink-0">LUNAS</span>
                    @elseif($sub->status === 'overdue')
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600 flex-shrink-0">JATUH TEMPO</span>
                    @elseif($sub->proof_photo)
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-600 flex-shrink-0">PERLU VERIFIKASI</span>
                    @else
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 flex-shrink-0">PENDING</span>
                    @endif
                </div>
                @if($sub->proof_photo)
                <a href="{{ asset('storage/' . $sub->proof_photo) }}" target="_blank" rel="noopener" class="text-[11px] text-sky-600 underline">Lihat Bukti Pembayaran</a>
                @endif
                @if($sub->reference_number)<p class="text-[11px] text-gray-400 mt-1 break-all">Ref: {{ $sub->reference_number }}</p>@endif
                @if($sub->notes)<p class="text-[11px] text-gray-400 mt-1">{{ $sub->notes }}</p>@endif
                @if($sub->rejection_reason)<p class="text-[11px] text-red-500 mt-1">Ditolak: {{ $sub->rejection_reason }}</p>@endif
                @if(!$sub->isPaid() && $sub->proof_photo)
                <div class="flex gap-2 mt-3">
                    <form method="POST" action="{{ route('superadmin.subscriptions.verify', $sub) }}" class="flex-1">
                        @csrf
                        <button class="w-full bg-emerald-600 text-white py-2.5 rounded-xl text-[12px] font-bold hover:bg-emerald-700 transition">Verifikasi</button>
                    </form>
                    <button type="button" onclick="document.getElementById('reject-m-{{ $sub->id }}').classList.toggle('hidden')"
                            class="flex-1 bg-red-50 text-red-600 border border-red-200 py-2.5 rounded-xl text-[12px] font-bold hover:bg-red-100 transition">Tolak</button>
                </div>
                <div id="reject-m-{{ $sub->id }}" class="hidden mt-2">
                    <form method="POST" action="{{ route('superadmin.subscriptions.reject', $sub) }}" class="flex flex-col gap-2">
                        @csrf
                        <input type="text" name="rejection_reason" required placeholder="Alasan penolakan..." class="border border-gray-200 rounded-xl px-3 py-2 text-[12px] outline-none">
                        <button class="bg-red-600 text-white py-2 rounded-xl text-[11px] font-bold">Konfirmasi Tolak</button>
                    </form>
                </div>
                @endif
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center text-gray-400 text-[12px]">Belum ada tagihan.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
