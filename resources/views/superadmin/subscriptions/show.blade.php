@extends('layouts.dashboard')
@section('page-title', 'Kelola Subscription — ' . $merchant->name)

@section('content')
@php $money = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp

<div class="max-w-5xl">
    <div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
        <div>
            <a href="{{ route('superadmin.subscriptions') }}" class="text-[12px] text-sky-600 hover:text-sky-700 font-semibold mb-1 inline-flex items-center gap-1">
                <i class="fas fa-arrow-left text-[10px]"></i> Kembali
            </a>
            <h2 class="text-lg font-bold text-navy-800 flex items-center gap-2">
                <i class="fas fa-store text-sky-500"></i> {{ $merchant->name }}
            </h2>
            <p class="text-[12px] text-gray-400">{{ $merchant->owner?->email }} — {{ $merchant->city ?? '-' }}</p>
        </div>
        @if($merchant->billing_plan === 'subscription')
        <div class="{{ $svc->isOverdue($merchant) ? 'bg-red-50 border-red-200 text-red-600' : 'bg-emerald-50 border-emerald-200 text-emerald-600' }} border px-4 py-2 rounded-xl text-[12px] font-semibold">
            @if($svc->isOverdue($merchant))
            <i class="fas fa-exclamation-triangle mr-1.5"></i> MENUNGGAK — diblokir
            @else
            <i class="fas fa-shield-halved mr-1.5"></i> Aktif s.d. {{ $merchant->subscription_until?->format('d M Y') }}
            @endif
        </div>
        @endif
    </div>

    {{-- Plan form --}}
    <div class="glass-card rounded-2xl p-5 border border-gray-100 mb-5">
        <h4 class="text-[13px] font-bold text-navy-800 mb-3 flex items-center gap-1.5"><i class="fas fa-sliders-h text-sky-500"></i> Ubah Plan Billing</h4>
        <form method="POST" action="{{ route('superadmin.subscriptions.plan', $merchant) }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label class="block text-[11px] font-semibold text-navy-600 mb-1">Plan</label>
                <select name="billing_plan" class="border border-gray-200 bg-gray-50/50 rounded-xl px-3 py-2 text-[12px] focus:ring-2 focus:ring-sky-500/30 outline-none">
                    <option value="subscription" {{ $merchant->billing_plan === 'subscription' ? 'selected' : '' }}>Subscription (flat bulanan)</option>
                    <option value="commission" {{ $merchant->billing_plan === 'commission' ? 'selected' : '' }}>Komisi per transaksi (10%)</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-navy-600 mb-1">Fee Bulanan (Rp) — subscription</label>
                <input type="number" name="subscription_fee" value="{{ $merchant->subscription_fee }}" min="0" step="10000"
                       class="border border-gray-200 bg-gray-50/50 rounded-xl px-3 py-2 text-[12px] w-44 focus:ring-2 focus:ring-sky-500/30 outline-none">
            </div>
            <button type="submit" class="bg-sky-600 text-white px-5 py-2 rounded-xl text-[12px] font-bold hover:bg-sky-700 transition">Simpan Plan</button>
            <p class="w-full text-[10px] text-gray-400">Catatan: beralih ke SUBSCRIPTION akan langsung membuat tagihan pertama (jatuh tempo +1 bulan). Beralih ke KOMISI menghentikan tagihan berlangganan.</p>
        </form>
    </div>

    {{-- Manual bill --}}
    <div class="glass-card rounded-2xl p-5 border border-gray-100 mb-5">
        <h4 class="text-[13px] font-bold text-navy-800 mb-3 flex items-center gap-1.5"><i class="fas fa-plus-circle text-sky-500"></i> Buat Tagihan Manual</h4>
        <form method="POST" action="{{ route('superadmin.subscriptions.bills', $merchant) }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label class="block text-[11px] font-semibold text-navy-600 mb-1">Periode Mulai</label>
                <input type="date" name="period_start" required class="border border-gray-200 bg-gray-50/50 rounded-xl px-3 py-2 text-[12px] focus:ring-2 focus:ring-sky-500/30 outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-navy-600 mb-1">Periode Selesai (jatuh tempo)</label>
                <input type="date" name="period_end" required class="border border-gray-200 bg-gray-50/50 rounded-xl px-3 py-2 text-[12px] focus:ring-2 focus:ring-sky-500/30 outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-navy-600 mb-1">Jumlah (Rp)</label>
                <input type="number" name="amount" required min="1"
                       class="border border-gray-200 bg-gray-50/50 rounded-xl px-3 py-2 text-[12px] w-40 focus:ring-2 focus:ring-sky-500/30 outline-none">
            </div>
            <button type="submit" class="bg-emerald-600 text-white px-5 py-2 rounded-xl text-[12px] font-bold hover:bg-emerald-700 transition">Buat Tagihan</button>
        </form>
    </div>

    {{-- Billing history --}}
    <div class="glass-card rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h4 class="text-[13px] font-bold text-navy-800 flex items-center gap-1.5"><i class="fas fa-receipt text-sky-500"></i> Riwayat Tagihan</h4>
        </div>
        <table class="w-full text-left text-[12px]">
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
                        <p class="font-semibold text-navy-700">{{ $sub->period_start->format('d M Y') }} – {{ $sub->period_end->format('d M Y') }}</p>
                        <p class="text-[10px] text-gray-400">#{{ $sub->id }}</p>
                    </td>
                    <td class="px-4 py-3 font-bold text-navy-800">{{ $money($sub->amount) }}</td>
                    <td class="px-4 py-3">
                        @if($sub->isPaid())
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-600">LUNAS</span>
                        @elseif($sub->status === 'overdue')
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600">JATUH TEMPO</span>
                        @elseif($sub->proof_photo)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-600">MENUNGGU VERIFIKASI</span>
                        @else
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">PENDING</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-[11px] text-gray-500">
                        @if($sub->proof_photo)
                        <a href="{{ asset('storage/' . $sub->proof_photo) }}" target="_blank" class="text-sky-600 underline">Lihat Bukti</a>
                        @else
                        <span class="text-gray-300">-</span>
                        @endif
                        @if($sub->reference_number)
                        <span class="block text-gray-400 mt-0.5">Ref: {{ $sub->reference_number }}</span>
                        @endif
                        @if($sub->rejection_reason)
                        <span class="block text-red-500 mt-0.5">Ditolak: {{ $sub->rejection_reason }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        @if(!$sub->isPaid())
                        @if($sub->proof_photo)
                        <form method="POST" action="{{ route('superadmin.subscriptions.verify', $sub) }}" class="inline">
                            @csrf
                            <button class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold hover:bg-emerald-700 transition">Verifikasi</button>
                        </form>
                        <button type="button" onclick="document.getElementById('reject-{{ $sub->id }}').classList.toggle('hidden')"
                                class="bg-red-50 text-red-600 border border-red-200 px-3 py-1.5 rounded-lg text-[11px] font-bold ml-1 hover:bg-red-100 transition">Tolak</button>
                        @endif
                        @endif
                    </td>
                </tr>
                @if(!$sub->isPaid() && $sub->proof_photo)
                <tr id="reject-{{ $sub->id }}" class="hidden">
                    <td colspan="5" class="px-4 py-2 bg-red-50/50">
                        <form method="POST" action="{{ route('superadmin.subscriptions.reject', $sub) }}" class="flex gap-2 items-center">
                            @csrf
                            <input type="text" name="rejection_reason" required placeholder="Alasan penolakan..." class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-[12px] outline-none">
                            <button class="bg-red-600 text-white px-4 py-2 rounded-xl text-[11px] font-bold">Konfirmasi Tolak</button>
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
</div>
@endsection