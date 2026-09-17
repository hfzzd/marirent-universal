@extends('layouts.dashboard')
@section('title', 'Rekening Bank - MariRent')
@section('page-title', 'Rekening Bank Toko')

@section('content')
@if(session('bank_success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-[13px] flex items-center gap-2">
    <i class="fas fa-check-circle"></i> {{ session('bank_success') }}
</div>
@endif

{{-- Ringkasan rekening aktif --}}
<div class="relative overflow-hidden rounded-2xl mb-6" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0ea5e9 100%);">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-white rounded-full translate-y-1/2 -translate-x-1/4"></div>
    </div>
    <div class="relative px-7 py-6 flex flex-col sm:flex-row sm:items-center gap-5">
        <div class="w-16 h-16 rounded-2xl bg-white/15 backdrop-blur border border-white/20 flex items-center justify-center text-white flex-shrink-0">
            <i class="fas fa-university text-2xl"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sky-200 text-[11px] font-semibold uppercase tracking-widest">Rekening tujuan transaksi</p>
            @if($merchant->bank_name && $merchant->bank_account_number)
            <h2 class="text-xl font-bold text-white mt-0.5">{{ $merchant->bank_name }} • <span class="font-mono">{{ $merchant->bank_account_number }}</span></h2>
            <p class="text-sky-100/80 text-[12px] mt-0.5">a.n. {{ $merchant->bank_account_holder ?? $merchant->name }}</p>
            @else
            <h2 class="text-xl font-bold text-white mt-0.5">Belum ada rekening</h2>
            <p class="text-sky-100/80 text-[12px] mt-0.5">Isi form di bawah agar penyewa bisa transfer ke toko Anda.</p>
            @endif
        </div>
        <div class="text-sky-100/90 text-[11px] bg-white/10 border border-white/15 rounded-xl px-4 py-3 backdrop-blur-sm max-w-xs">
            <i class="fas fa-info-circle mr-1"></i> Rekening ini otomatis tampil pada halaman invoice penyewa sebagai tujuan pembayaran transfer.
        </div>
    </div>
</div>

{{-- Form edit --}}
<div class="glass-card rounded-2xl p-6">
    <h3 class="text-[14px] font-bold text-navy-800 mb-1">Ubah Rekening Bank</h3>
    <p class="text-[11px] text-gray-400 mb-5">Kosongkan semua kolom bila belum ingin menampilkan rekening pada invoice.</p>

    <form action="{{ route('merchant.bank.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Nama Bank</label>
                <input type="text" name="bank_name" value="{{ old('bank_name', $merchant->bank_name) }}" placeholder="cth: BCA, Mandiri, BRI" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
                @error('bank_name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Nomor Rekening</label>
                <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $merchant->bank_account_number) }}" placeholder="cth: 1234567890" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50 font-mono">
                @error('bank_account_number')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Atas Nama</label>
                <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder', $merchant->bank_account_holder) }}" placeholder="Nama pemilik rekening" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
                @error('bank_account_holder')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-5 flex items-center justify-end">
            <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-1.5">
                <i class="fas fa-save text-[11px]"></i> Simpan Rekening
            </button>
        </div>
    </form>
</div>
@endsection
