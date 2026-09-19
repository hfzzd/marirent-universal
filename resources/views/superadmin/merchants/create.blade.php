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

        <form action="{{ route('superadmin.merchants.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5" x-data="{ logoPreview: '' }">
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
                    <x-searchable-select name="category_id" placeholder="-- Pilih Kategori --">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </x-searchable-select>
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

            <div>
                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Logo Toko <span class="text-gray-400 font-normal">(opsional, JPG/PNG/WebP maks 2MB)</span></label>
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" alt="Logo" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!logoPreview">
                            <i class="fas fa-image text-sky-300"></i>
                        </template>
                    </div>
                    <label class="flex-1 cursor-pointer border-2 border-dashed border-gray-200 hover:border-sky-300 rounded-xl px-4 py-2.5 text-center transition bg-gray-50/50 text-[12px] font-semibold text-sky-600">
                        <i class="fas fa-upload mr-1.5"></i>Pilih Logo
                        <input type="file" name="logo" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden"
                               x-on:change="if($event.target.files[0]) { const r = new FileReader(); r.onload = e => logoPreview = e.target.result; r.readAsDataURL($event.target.files[0]); }">
                    </label>
                </div>
                @error('logo') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
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

            <div>
                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Titik Lokasi di Peta <span class="text-gray-400 font-normal">(opsional — klik peta untuk menandai)</span></label>
                <div class="flex flex-col sm:flex-row gap-2 mb-2">
                    <input type="text" id="merchant-search-place" placeholder="Cari lokasi / nama tempat..." class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] outline-none focus:ring-sky-100">
                    <div class="flex gap-2">
                        <button type="button" id="merchant-search-btn" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-[12px] font-semibold transition"><i class="fas fa-search text-[11px] mr-1"></i>Cari</button>
                        <button type="button" id="merchant-geocode-address" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-4 py-2.5 rounded-xl text-[12px] font-semibold transition"><i class="fas fa-map-marker-alt text-[11px] mr-1"></i>Dari Alamat</button>
                    </div>
                </div>
                <div id="merchant-map" class="w-full rounded-xl overflow-hidden border border-gray-200" style="height: 260px;"></div>
                <input type="hidden" name="latitude" id="merchant-latitude" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="merchant-longitude" value="{{ old('longitude') }}">
            </div>

            <div>
                <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Rekening Bank Toko <span class="text-gray-400 font-normal">(opsional)</span></label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="Nama bank (cth: BCA)" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] outline-none focus:ring-sky-100">
                    </div>
                    <div>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="Nomor rekening" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] outline-none focus:ring-sky-100 font-mono">
                    </div>
                    <div>
                        <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder') }}" placeholder="Atas nama" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-[13px] outline-none focus:ring-sky-100">
                    </div>
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

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush
@push('scripts')
@vite(['resources/js/merchant-map.js'])
@endpush
@endsection
