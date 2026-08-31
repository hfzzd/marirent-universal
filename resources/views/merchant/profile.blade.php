@extends('layouts.dashboard')
@section('title', 'Toko Saya - MariRent')
@section('page-title', 'Profil Toko (Merchant)')

@section('content')
@php $owner = auth()->user(); @endphp

@if(session('merchant_success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-[13px] flex items-center gap-2">
    <i class="fas fa-check-circle"></i> {{ session('merchant_success') }}
</div>
@endif

{{-- Store Overview --}}
<div class="relative overflow-hidden rounded-2xl mb-6" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0ea5e9 100%);">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-white rounded-full translate-y-1/2 -translate-x-1/4"></div>
    </div>
    <div class="relative px-7 py-6 flex items-center gap-5">
        <div class="w-16 h-16 rounded-2xl bg-white/15 backdrop-blur border border-white/20 flex items-center justify-center text-white text-2xl font-bold overflow-hidden flex-shrink-0">
            @if($merchant->logo)
                <img src="{{ asset('storage/' . $merchant->logo) }}" alt="" class="w-full h-full object-cover">
            @else
                {{ strtoupper(substr($merchant->name, 0, 1)) }}
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <h2 class="text-xl font-bold text-white">{{ $merchant->name }}</h2>
            <p class="text-sky-100/80 text-[12px] mt-0.5 flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1"><i class="fas fa-link text-sky-300"></i> {{ $merchant->slug }}</span>
                @if($merchant->city)<span class="inline-flex items-center gap-1"><i class="fas fa-map-marker-alt text-sky-300"></i> {{ $merchant->city }}</span>@endif
                @if($merchant->is_active)<span class="inline-flex items-center gap-1 text-emerald-300"><i class="fas fa-check-circle"></i> Aktif</span>@endif
            </p>
        </div>
        <a href="{{ route('public.store', $merchant->slug) }}" target="_blank" class="hidden md:inline-flex items-center gap-2 bg-white text-sky-700 hover:bg-sky-50 px-4 py-2.5 rounded-xl text-[12px] font-bold shadow-lg transition">
            <i class="fas fa-external-link-alt text-[11px]"></i> Lihat Toko
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="glass-card rounded-2xl p-5">
        <div class="w-10 h-10 bg-sky-100 rounded-xl flex items-center justify-center mb-3"><i class="fas fa-box-open text-sky-600"></i></div>
        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Total Unit</p>
        <p class="text-xl font-extrabold text-navy-800 mt-1">{{ number_format($stats['total_units']) }}</p>
    </div>
    <div class="glass-card rounded-2xl p-5">
        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center mb-3"><i class="fas fa-wallet text-emerald-600"></i></div>
        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Pendapatan Lunas</p>
        <p class="text-xl font-extrabold text-navy-800 mt-1">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
    </div>
    <div class="glass-card rounded-2xl p-5">
        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center mb-3"><i class="fas fa-percentage text-amber-600"></i></div>
        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Komisi Platform</p>
        <p class="text-xl font-extrabold text-navy-800 mt-1">Rp {{ number_format($stats['platform_fee'], 0, ',', '.') }}</p>
    </div>
    <div class="glass-card rounded-2xl p-5">
        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center mb-3"><i class="fas fa-hand-holding-usd text-indigo-600"></i></div>
        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Pendapatan Bersih</p>
        <p class="text-xl font-extrabold text-emerald-600 mt-1">Rp {{ number_format($stats['net_revenue'], 0, ',', '.') }}</p>
    </div>
</div>

{{-- Edit Form --}}
<div class="glass-card rounded-2xl p-6">
    <h3 class="text-[14px] font-bold text-navy-800 mb-1">Edit Profil Toko</h3>
    <p class="text-[11px] text-gray-400 mb-5">Profil toko akan tampil di halaman publik marketplace (/store/{{ $merchant->slug }}).</p>

    <form action="{{ route('merchant.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Nama Toko</label>
                <input type="text" name="name" value="{{ old('name', $merchant->name) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
                @error('name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">{{ old('description', $merchant->description) }}</textarea>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $merchant->phone) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Kota</label>
                <input type="text" name="city" value="{{ old('city', $merchant->city) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
            </div>

            <div class="md:col-span-2">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Alamat</label>
                <input type="text" name="address" value="{{ old('address', $merchant->address) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Alamat Penjemputan</label>
                <input type="text" name="pickup_address" value="{{ old('pickup_address', $merchant->pickup_address) }}" placeholder="Khusus untuk pengambilan unit" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Jam Operasional</label>
                <input type="text" name="operational_hours" value="{{ old('operational_hours', $merchant->operational_hours) }}" placeholder="cont: 08.00 - 20.00" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
            </div>
        </div>

        <div class="mt-5 flex items-center justify-end gap-2">
            <a href="{{ route('public.store', $merchant->slug) }}" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-[12px] font-semibold transition flex items-center gap-1.5">
                <i class="fas fa-external-link-alt text-[10px]"></i> Pratinjau
            </a>
            <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-1.5">
                <i class="fas fa-save text-[11px]"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
