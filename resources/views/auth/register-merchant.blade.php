@extends('layouts.public')
@section('title', 'Daftar Merchant - MariRent')
@section('content')

<style>
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
    @keyframes fadeInUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
    @keyframes slideIn { from{opacity:0;transform:translateX(-10px)} to{opacity:1;transform:translateX(0)} }
    @keyframes gradientShift { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
    .animate-float { animation: float 3s ease-in-out infinite; }
    .animate-float-delay { animation: float 3s ease-in-out 0.5s infinite; }
    .animate-fadeInUp { animation: fadeInUp 0.7s ease-out both; }
    .animate-fadeInUp-delay { animation: fadeInUp 0.7s ease-out 0.15s both; }
    .animate-fadeInUp-delay2 { animation: fadeInUp 0.7s ease-out 0.3s both; }
    .animate-slideIn { animation: slideIn 0.5s ease-out both; }
    .gradient-bg { background: linear-gradient(-45deg,#f0f9ff,#e0f2fe,#bae6fd,#7dd3fc); background-size:400% 400%; animation: gradientShift 8s ease infinite; }
    .glass-card { background: rgba(255,255,255,0.75); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.6); }
    .input-focus { transition: all 0.3s ease; }
    .input-focus:focus { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(14,165,233,0.15); }
    .btn-hover { transition: all 0.3s ease; position:relative; overflow:hidden; }
    .btn-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(14,165,233,0.35); }
    .btn-hover:active { transform: translateY(0); }
    .floating-shape { position:absolute; border-radius:50%; opacity:0.08; }
</style>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    #merchant-map { z-index: 1; }
    .leaflet-container { font-family: inherit; border-radius: 0.75rem; }
</style>
@endpush

<div class="gradient-bg min-h-[85vh] flex items-center justify-center py-12 px-4 relative overflow-hidden">
    {{-- Floating shapes --}}
    <div class="floating-shape w-64 h-64 bg-sky-400 -top-20 -right-20 animate-float" style="position:absolute;"></div>
    <div class="floating-shape w-40 h-40 bg-sky-600 bottom-10 -left-10 animate-float-delay" style="position:absolute;"></div>
    <div class="floating-shape w-20 h-20 bg-sky-300 top-1/4 left-10 animate-float" style="position:absolute;"></div>
    <div class="floating-shape w-16 h-16 bg-sky-500 bottom-1/4 right-20 animate-float-delay" style="position:absolute;"></div>

    <div class="max-w-5xl w-full flex flex-col lg:flex-row items-start gap-12 relative z-10">
        {{-- Left - Branding --}}
        <div class="hidden lg:flex flex-col flex-1 animate-fadeInUp lg:sticky lg:top-24">
            <div class="mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 mb-8">
                    <div class="w-14 h-14 bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl flex items-center justify-center shadow-lg shadow-sky-500/30">
                        <i class="fas fa-car-side text-white text-2xl"></i>
                    </div>
                    <span class="text-3xl font-extrabold text-navy-900">Mari<span class="text-sky-600">Rent</span></span>
                </a>
                <h1 class="text-4xl font-extrabold text-navy-900 leading-tight">Buka Toko<br>Anda Sendiri</h1>
                <p class="text-navy-500 mt-4 text-lg max-w-sm">Gabung marketplace MariRent dan jangkau ribuan penyewa setiap hari.</p>
            </div>

            <div class="space-y-4 mt-4">
                <div class="flex items-center gap-3 animate-slideIn" style="animation-delay:0.3s">
                    <div class="w-10 h-10 bg-sky-100 rounded-xl flex items-center justify-center"><i class="fas fa-users text-sky-500 text-sm"></i></div>
                    <div><p class="text-[13px] font-semibold text-navy-800">Jangkauan Luas</p><p class="text-[11px] text-gray-400">Ribuan calon penyewa aktif</p></div>
                </div>
                <div class="flex items-center gap-3 animate-slideIn" style="animation-delay:0.45s">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center"><i class="fas fa-percent text-emerald-500 text-sm"></i></div>
                    <div><p class="text-[13px] font-semibold text-navy-800">Komisi Transparan</p><p class="text-[11px] text-gray-400">Mulai 7% per transaksi lunas</p></div>
                </div>
                <div class="flex items-center gap-3 animate-slideIn" style="animation-delay:0.6s">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center"><i class="fas fa-chart-line text-amber-500 text-sm"></i></div>
                    <div><p class="text-[13px] font-semibold text-navy-800">Dashboard Lengkap</p><p class="text-[11px] text-gray-400">Booking, invoice & laporan otomatis</p></div>
                </div>
            </div>
        </div>

        {{-- Right - Form --}}
        <div class="w-full max-w-xl animate-fadeInUp-delay2 mx-auto lg:mx-0">
            <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-xl shadow-sky-500/5">
                {{-- Mobile Logo --}}
                <div class="lg:hidden text-center mb-6">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                        <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/30"><i class="fas fa-car-side text-white"></i></div>
                        <span class="text-xl font-extrabold text-navy-900">Mari<span class="text-sky-600">Rent</span></span>
                    </a>
                </div>

                <div class="text-center mb-7">
                    <div class="w-14 h-14 bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-sky-500/20">
                        <i class="fas fa-store text-white text-xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-navy-900">Daftar sebagai Merchant</h2>
                    <p class="text-[13px] text-gray-400 mt-1">Buka toko Anda di marketplace MariRent</p>
                    <p class="text-[11px] text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mt-3"><i class="fas fa-clock mr-1"></i> Pendaftaran menunggu verifikasi admin, toko aktif setelah disetujui.</p>
                </div>

                @if($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-[13px] animate-fadeInUp">
                    @foreach($errors->all() as $e)
                    <p class="flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> {{ $e }}</p>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('register.merchant.store') }}" enctype="multipart/form-data" id="merchantRegisterForm" x-data="{ logoPreview: '' }">
                    @csrf

                    {{-- Data pemilik --}}
                    <p class="text-[11px] font-bold uppercase tracking-widest text-sky-600 mb-3 flex items-center gap-2"><i class="fas fa-user"></i> Data Pemilik</p>
                    <div class="mb-3.5">
                        <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Nama Pemilik</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-user text-sm"></i></span>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white"
                                   placeholder="Nama lengkap pemilik">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mb-3.5">
                        <div>
                            <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Email</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-envelope text-sm"></i></span>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                       class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white"
                                       placeholder="email@contoh.com">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">No. Telepon <span class="text-gray-300 font-normal">(opsional)</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-phone text-sm"></i></span>
                                <input type="text" name="phone" value="{{ old('phone') }}"
                                       class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white"
                                       placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                    </div>

                    {{-- Data toko --}}
                    <p class="text-[11px] font-bold uppercase tracking-widest text-sky-600 mb-3 mt-6 flex items-center gap-2"><i class="fas fa-store"></i> Data Toko</p>
                    <div class="mb-3.5">
                        <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Nama Toko</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-store text-sm"></i></span>
                            <input type="text" name="store_name" value="{{ old('store_name') }}" required
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white"
                                   placeholder="Nama yang tampil di marketplace">
                        </div>
                    </div>

                    {{-- Logo toko --}}
                    <div class="mb-3.5">
                        <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Logo Toko <span class="text-gray-300 font-normal">(opsional, JPG/PNG/WebP maks 2MB)</span></label>
                        <div class="flex items-center gap-3">
                            <div class="w-16 h-16 rounded-2xl bg-sky-50 border border-sky-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                                <template x-if="logoPreview">
                                    <img :src="logoPreview" alt="Logo" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!logoPreview">
                                    <i class="fas fa-image text-sky-300 text-xl"></i>
                                </template>
                            </div>
                            <label class="flex-1 cursor-pointer border-2 border-dashed border-gray-200 hover:border-sky-300 rounded-xl px-4 py-3 text-center transition bg-gray-50/50 hover:bg-sky-50/50">
                                <span class="text-[12px] font-semibold text-sky-600"><i class="fas fa-upload mr-1.5"></i>Pilih Logo</span>
                                <input type="file" name="logo" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden"
                                       x-on:change="if($event.target.files[0]) { const r = new FileReader(); r.onload = e => logoPreview = e.target.result; r.readAsDataURL($event.target.files[0]); }">
                            </label>
                        </div>
                        @error('logo')<p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-3.5">
                        <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Kategori Produk <span class="text-gray-300 font-normal">(opsional)</span></label>
                        <x-searchable-select name="category_id" placeholder="-- Pilih Kategori --">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </x-searchable-select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mb-3.5">
                        <div>
                            <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Kota</label>
                            <input type="text" name="city" value="{{ old('city') }}" placeholder="cth: Jakarta"
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl px-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Alamat Toko</label>
                            <input type="text" name="address" value="{{ old('address') }}" placeholder="Jl. Contoh No. 12"
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl px-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white">
                        </div>
                    </div>

                    {{-- Rekening bank --}}
                    <div class="mb-3.5">
                        <label class="block text-[13px] font-semibold text-navy-700 mb-1.5"><i class="fas fa-university text-sky-500 mr-1"></i>Rekening Bank Toko <span class="text-gray-300 font-normal">(opsional — untuk pencairan dana)</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="Nama bank (cth: BCA)"
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl px-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white">
                            <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="Nomor rekening"
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl px-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white font-mono">
                            <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder') }}" placeholder="Atas nama"
                                   class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl px-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white">
                        </div>
                    </div>

                    {{-- Lokasi di peta --}}
                    <div class="mb-3.5">
                        <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Titik Lokasi di Peta <span class="text-gray-300 font-normal">(opsional — klik peta untuk menandai)</span></label>
                        <div class="flex flex-col sm:flex-row gap-2 mb-2">
                            <input type="text" id="merchant-search-place" placeholder="Cari lokasi / nama tempat..."
                                   class="flex-1 border border-gray-200 bg-gray-50/50 rounded-xl px-4 py-2.5 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white">
                            <div class="flex gap-2">
                                <button type="button" id="merchant-search-btn" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-[12px] font-semibold transition flex items-center gap-1.5">
                                    <i class="fas fa-search text-[11px]"></i> Cari
                                </button>
                                <button type="button" id="merchant-geocode-address" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-4 py-2.5 rounded-xl text-[12px] font-semibold transition flex items-center gap-1.5">
                                    <i class="fas fa-map-marker-alt text-[11px]"></i> Dari Alamat
                                </button>
                            </div>
                        </div>
                        <div id="merchant-map" class="w-full rounded-xl overflow-hidden border border-gray-200" style="height: 260px;"></div>
                        <input type="hidden" name="latitude" id="merchant-latitude" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="merchant-longitude" value="{{ old('longitude') }}">
                        @error('latitude')<p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Password --}}
                    <p class="text-[11px] font-bold uppercase tracking-widest text-sky-600 mb-3 mt-6 flex items-center gap-2"><i class="fas fa-lock"></i> Keamanan</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mb-6" x-data>
                        <div x-data="{ show: false }">
                            <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Password</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-lock text-sm"></i></span>
                                <input :type="show ? 'text' : 'password'" name="password" required minlength="8"
                                       class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-10 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white"
                                       placeholder="Minimal 8 karakter">
                                <button type="button" @click="show = !show" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-sky-500 transition">
                                    <i class="fas text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                        <div x-data="{ show: false }">
                            <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Konfirmasi Password</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-lock text-sm"></i></span>
                                <input :type="show ? 'text' : 'password'" name="password_confirmation" required minlength="8"
                                       class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-10 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white"
                                       placeholder="Ulangi password">
                                <button type="button" @click="show = !show" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-sky-500 transition">
                                    <i class="fas text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-hover w-full bg-gradient-to-r from-sky-500 to-sky-600 text-white py-3.5 rounded-xl font-bold text-[14px] shadow-lg shadow-sky-500/25 flex items-center justify-center gap-2">
                        <i class="fas fa-store"></i> Daftar Sebagai Merchant
                    </button>
                </form>

                <div class="mt-6 text-center space-y-1.5">
                    <p class="text-[13px] text-gray-400">Mau daftar sebagai pelanggan?
                        <a href="{{ route('register') }}" class="text-sky-600 font-semibold hover:text-sky-700 hover:underline transition">Daftar Akun</a>
                    </p>
                    <p class="text-[13px] text-gray-400">Sudah punya akun?
                        <a href="{{ route('login') }}" class="text-sky-600 font-semibold hover:text-sky-700 hover:underline transition">Masuk</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@vite(['resources/js/merchant-map.js'])
@endpush
@endsection
