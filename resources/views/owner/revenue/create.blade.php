@extends('layouts.dashboard')
@section('title', 'Catat Pendapatan - MariRent')
@section('page-title', 'Catat Pendapatan Manual')

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('owner.revenue.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>

    <form method="POST" action="{{ route('owner.revenue.store') }}">
        @csrf
        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-tags text-sky-500"></i> Kategori Produk</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Kategori *</label>
                    <select name="category_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-receipt text-emerald-500"></i> Detail Pendapatan</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Deskripsi Pendapatan *</label>
                    <input type="text" name="description" value="{{ old('description') }}" required
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                           placeholder="Contoh: Sewa mobil Avanza 3 hari">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-700 mb-1">Jumlah (Rp) *</label>
                        <input type="number" name="amount" value="{{ old('amount') }}" min="1" required
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-700 mb-1">Pajak (%)</label>
                        <input type="number" name="tax_percent" value="{{ old('tax_percent', 0) }}" min="0" max="100" step="0.01"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-700 mb-1">Diskon (Rp)</label>
                        <input type="number" name="discount_amount" value="{{ old('discount_amount', 0) }}" min="0" step="1000"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-700 mb-1">Jatuh Tempo *</label>
                        <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(7)->format('Y-m-d')) }}" required
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-700 mb-1">Catatan</label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                               placeholder="Catatan opsional">
                    </div>
                </div>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-[13px]">
            @foreach($errors->all() as $err) <p class="flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> {{ $err }}</p> @endforeach
        </div>
        @endif

        <div class="flex gap-3 pb-2">
            <button type="submit" class="btn-primary text-white px-8 py-3 rounded-xl text-sm font-bold shadow-lg shadow-sky-500/25 transition"><i class="fas fa-save mr-2"></i> Simpan Pendapatan</button>
            <a href="{{ route('owner.revenue.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-3 rounded-xl text-sm font-semibold text-navy-700 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
