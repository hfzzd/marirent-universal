@extends('layouts.public')
@section('title', 'Akun Diblokir — Segera Bayar Subscription')
@section('page-title', 'Akun Diblokir — Segera Bayar Subscription')

@section('content')
@php $money = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
    {{-- Notice Banner --}}
    <div class="mb-6 bg-red-600 rounded-2xl p-6 shadow-2xl shadow-red-600/25 text-center animate-slide-up">
        <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-lock text-white text-xl"></i>
        </div>
        <h1 class="text-white text-xl sm:text-2xl font-extrabold tracking-tight">AKUN DIBLOKIR — SEGERA BAYAR SUBSCRIPTION</h1>
        <p class="text-white/85 text-[13px] mt-2 leading-relaxed">
            Tagihan berlangganan <strong>{{ $merchant->name }}</strong> telah melewati jatuh tempo.
            Semua akses operasional dinonaktifkan sampai pembayaran lunas dan diverifikasi admin.
        </p>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-[13px] flex items-center gap-2 animate-slide-up">
        <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-[13px] flex items-center gap-2 animate-slide-up">
        <i class="fas fa-exclamation-triangle text-red-500"></i> {{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-[13px] animate-slide-up shadow-sm">
        @foreach($errors->all() as $error)
            <div><i class="fas fa-exclamation-circle text-red-500 mr-1"></i>{{ $error }}</div>
        @endforeach
    </div>
    @endif

    {{-- Bill Card --}}
    @if($bill)
    <div class="glass-card rounded-2xl p-6 border border-red-200 shadow-sm mb-6 bg-white">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <div>
                <h3 class="text-[14px] font-bold text-navy-800">Tagihan Subscription</h3>
                <p class="text-[11px] text-gray-400">Periode berlangganan</p>
            </div>
            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-red-100 text-red-600">MENUNGGAK</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-5">
            <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide">Jumlah Tagihan</p>
                <p class="text-[18px] font-extrabold text-navy-900 mt-1">{{ $money($bill->amount) }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide">Periode</p>
                <p class="text-[13px] font-bold text-navy-800 mt-1">{{ $bill->period_start->format('d M Y') }} – {{ $bill->period_end->format('d M Y') }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3 col-span-2 sm:col-span-1">
                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide">Jatuh Tempo</p>
                <p class="text-[13px] font-bold text-red-500 mt-1">{{ $bill->period_end->format('d M Y') }}</p>
            </div>
        </div>

        <h4 class="text-[12px] font-bold text-navy-700 mb-3 flex items-center gap-1.5">
            <i class="fas fa-credit-card text-sky-500"></i> Kirim Bukti Pembayaran
        </h4>

        <form id="noticePayForm" method="POST" action="{{ route('subscriptions.pay') }}" enctype="multipart/form-data" x-data="{ method: 'transfer' }">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Metode Pembayaran</label>
                    <select name="method" x-model="method" class="w-full border border-gray-200 bg-gray-50/50 rounded-xl px-3 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 outline-none">
                        <option value="transfer">Transfer Bank</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="cash">Tunai</option>
                        <option value="credit_card">Kartu Kredit</option>
                        <option value="other">Lainnya</option>
                    </select>
                    @error('method') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Nomor Referensi</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="Kode transfer / VA"
                           class="w-full border border-gray-200 bg-gray-50/50 rounded-xl px-3 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 outline-none">
                </div>
            </div>

            <div class="mb-3">
                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Bukti Pembayaran (foto)</label>
                <input type="file" name="proof_photo" accept="image/*" required
                       class="w-full text-[12px] file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-sky-500 file:text-white file:font-semibold file:text-[12px] hover:file:bg-sky-600 border border-gray-200 rounded-xl px-3 py-2">
                @error('proof_photo') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Catatan (opsional)</label>
                <textarea name="notes" rows="2" class="w-full border border-gray-200 bg-gray-50/50 rounded-xl px-3 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 outline-none" placeholder="Contoh: sudah transfer dari bank BCA"></textarea>
            </div>

            <button id="noticePayBtn" type="submit" class="w-full bg-gradient-to-r from-sky-500 to-sky-600 text-white py-3 rounded-xl font-bold text-[13px] shadow-lg shadow-sky-500/25 hover:opacity-90 transition flex items-center justify-center gap-2">
                <i class="fas fa-paper-plane text-[12px]"></i> Kirim Bukti & Tunggu Verifikasi
            </button>
            <p class="text-[11px] text-gray-400 text-center mt-2.5">Akses akan aktif kembali secara otomatis setelah admin memverifikasi pembayaran.</p>
        </form>
    </div>
    @else
    <div class="glass-card rounded-2xl p-6 border border-gray-200 text-center bg-white">
        <i class="fas fa-circle-check text-emerald-500 text-3xl mb-2"></i>
        <p class="text-[13px] font-semibold text-navy-800">Tidak ada tagihan aktif</p>
        <p class="text-[12px] text-gray-400">Semua tagihan subscription Anda sudah lunas.</p>
        <a href="{{ route('subscriptions.index') }}" class="inline-block mt-3 text-[12px] text-sky-600 font-semibold">Lihat Riwayat <i class="fas fa-arrow-right text-[10px]"></i></a>
    </div>
    @endif

    {{-- Recent history --}}
    @if($subscriptions->isNotEmpty())
    <div class="glass-card rounded-2xl p-5 border border-gray-100 mb-6 bg-white">
        <h4 class="text-[13px] font-bold text-navy-800 mb-3 flex items-center gap-1.5"><i class="fas fa-history text-sky-500"></i> Riwayat Terakhir</h4>
        <div class="space-y-2">
            @foreach($subscriptions as $sub)
            <div class="flex items-center justify-between border-b border-gray-50 last:border-0 pb-2 last:pb-0">
                <div>
                    <p class="text-[12px] font-semibold text-navy-700">{{ $sub->period_start->format('d M Y') }} – {{ $sub->period_end->format('d M Y') }}</p>
                    <p class="text-[10px] text-gray-400">{{ $money($sub->amount) }}</p>
                </div>
                <div class="text-right">
                    @if($sub->isPaid())
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-600">LUNAS</span>
                    @elseif($sub->status === 'overdue')
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600">JATUH TEMPO</span>
                    @else
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-600">MENUNGGU</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="flex flex-wrap items-center justify-center gap-3">
        <a href="{{ route('subscriptions.index') }}" class="text-[12px] text-sky-600 font-semibold hover:underline">Lihat Riwayat <i class="fas fa-arrow-right text-[10px]"></i></a>
        <span class="text-[12px] text-gray-300">•</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-[12px] text-red-500 font-semibold hover:underline"><i class="fas fa-sign-out-alt mr-1"></i> Logout</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Once-guard submit (cegah double-submit yang memicu 419)
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('noticePayForm');
        const btn = document.getElementById('noticePayBtn');
        if (!form || !btn) return;
        let submitted = false;
        form.addEventListener('submit', function (e) {
            if (submitted) {
                e.preventDefault();
                return;
            }
            submitted = true;
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-not-allowed');
            btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
            btn.querySelector('.fa-paper-plane')?.remove();
            if (!btn.querySelector('.animate-spin')) {
                btn.innerHTML = '<span class="flex items-center justify-center gap-2"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Mengirim...</span>';
            }
        });
    });
</script>
@endpush