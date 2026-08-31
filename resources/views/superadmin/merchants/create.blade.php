@extends('layouts.dashboard')
@section('title', 'Buat Toko Baru - MariRent')
@section('page-title', 'Buat Merchant / Toko Baru')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('superadmin.merchants') }}" class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-gray-500 hover:text-sky-600 mb-4 transition">
        <i class="fas fa-arrow-left text-[11px]"></i> Kembali
    </a>

    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-sky-100/50 bg-sky-50/40">
            <h3 class="text-[14px] font-semibold text-navy-800">
                <i class="fas fa-store text-sky-500 mr-2"></i>Toko Baru (Owner + Merchant)
            </h3>
            <p class="text-[11px] text-gray-400 mt-0.5">Buat akun owner beserta profil tokonya. Toko dibuat dengan status menunggu verifikasi.</p>
        </div>

        <form action="{{ route('superadmin.merchants.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Nama Pemilik (Owner) <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] font-sky-400 focus:ring-sky-100 outline-none">
                @error('name') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Email Owner <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] outline-none focus:ring-sky-100">
                    @error('email') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">No. Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] outline-none focus:ring-sky-100">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Kategori Produk</label>
                    <select name="category_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] outline-none bg-white">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Rate Komisi (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="commission_rate" value="{{ old('commission_rate', 10) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] outline-none focus:ring-sky-100">
                </div>
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Nama Toko <span class="text-red-500">*</span></label>
                <input type="text" name="store_name" value="{{ old('store_name') }}" required placeholder="Nama yang tampil di marketplace" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] outline-none focus:ring-sky-100">
                @error('store_name') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Kota</label>
                    <input type="text" name="city" value="{{ old('city') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] outline-none focus:ring-sky-100">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Alamat</label>
                    <input type="text" name="address" value="{{ old('address') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] outline-none focus:ring-sky-100">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required minlength="8" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] outline-none focus:ring-sky-100">
                    @error('password') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required minlength="8" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] outline-none focus:ring-sky-100">
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-xl text-[13px] font-semibold shadow-lg shadow-sky-500/25 inline-flex items-center gap-2">
                    <i class="fas fa-plus text-xs"></i> Buat Toko
                </button>
                <a href="{{ route('superadmin.merchants') }}" class="px-6 py-2.5 rounded-xl text-[13px] font-semibold text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600 transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
