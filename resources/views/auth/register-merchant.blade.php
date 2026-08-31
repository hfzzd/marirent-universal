@extends('layouts.public')
@section('title', 'Daftar Merchant - MariRent')
@section('content')

<style>
    @keyframes gradientShift { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
    .gradient-bg { background: linear-gradient(-45deg,#f0f9ff,#e0f2fe,#bae6fd,#7dd3fc); background-size:400% 400%; animation: gradientShift 8s ease infinite; }
    .glass-card { background: rgba(255,255,255,0.75); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.6); }
    .input-focus { transition: all 0.3s ease; }
    .input-focus:focus { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(14,165,233,0.15); }
</style>

<div class="gradient-bg min-h-[85vh] flex items-center justify-center py-12 px-4 relative overflow-hidden">
    <div class="w-full max-w-md">
        <div class="glass-card rounded-3xl p-8 shadow-xl shadow-sky-500/5">
            <div class="text-center mb-7">
                <div class="w-14 h-14 bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-sky-500/20">
                    <i class="fas fa-store text-white text-xl"></i>
                </div>
                <h2 class="text-xl font-bold text-navy-900">Daftar sebagai Merchant</h2>
                <p class="text-[13px] text-gray-400 mt-1">Buka toko Anda di marketplace MariRent</p>
                <p class="text-[11px] text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mt-3"><i class="fas fa-clock mr-1"></i> Pendaftaran menunggu verifikasi admin, toko aktif setelah disetujui.</p>
            </div>

            @if($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-[13px]">
                @foreach($errors->all() as $e)
                <p class="flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> {{ $e }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('register.merchant.store') }}">
                @csrf
                <div class="mb-3.5">
                    <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Nama Pemilik</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-user text-sm"></i></span>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white"
                               placeholder="Nama lengkap pemilik">
                    </div>
                </div>

                <div class="mb-3.5">
                    <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Nama Toko</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-store text-sm"></i></span>
                        <input type="text" name="store_name" value="{{ old('store_name') }}" required
                               class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white"
                               placeholder="Nama yang tampil di marketplace">
                    </div>
                </div>

                <div class="mb-3.5">
                    <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Email</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-envelope text-sm"></i></span>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white"
                               placeholder="email@contoh.com">
                    </div>
                </div>

                <div class="mb-3.5">
                    <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">No. Telepon <span class="text-gray-300 font-normal">(opsional)</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-phone text-sm"></i></span>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white"
                               placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <div class="mb-3.5">
                    <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Kategori Produk <span class="text-gray-300 font-normal">(opsional)</span></label>
                    <select name="category_id" class="w-full border border-gray-200 bg-gray-50/50 rounded-xl px-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3.5">
                    <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-lock text-sm"></i></span>
                        <input type="password" name="password" required minlength="8"
                               class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white"
                               placeholder="Minimal 8 karakter">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-[13px] font-semibold text-navy-700 mb-1.5">Konfirmasi Password</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300"><i class="fas fa-lock text-sm"></i></span>
                        <input type="password" name="password_confirmation" required minlength="8"
                               class="input-focus w-full border border-gray-200 bg-gray-50/50 rounded-xl pl-10 pr-4 py-3 text-[13px] outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white"
                               placeholder="Ulangi password">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-sky-500 to-sky-600 text-white py-3.5 rounded-xl font-bold text-[14px] shadow-lg shadow-sky-500/25 transition-all duration-300 hover:shadow-xl hover:shadow-sky-500/30 flex items-center justify-center gap-2">
                    <i class="fas fa-store"></i> Daftar Sebagai Merchant
                </button>
            </form>

            <div class="mt-6 text-center space-y-1.5">
                <p class="text-[13px] text-gray-400">Mau daftar sebagai pelanggan?
                    <a href="{{ route('register') }}" class="text-sky-600 font-semibold hover:underline transition">Daftar Akun</a>
                </p>
                <p class="text-[13px] text-gray-400">Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-sky-600 font-semibold hover:underline transition">Masuk</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
