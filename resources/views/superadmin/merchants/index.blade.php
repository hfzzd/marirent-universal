@extends('layouts.dashboard')
@section('title', 'Merchant / Toko - MariRent')
@section('page-title', 'Kelola Merchant & Toko')

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-[13px] flex items-center gap-2">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-[13px] flex items-center gap-2">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-2">
        <span class="text-[13px] font-semibold text-gray-500">Filter status:</span>
        @foreach(['all' => 'Semua', 'pending' => 'Menunggu', 'active' => 'Aktif', 'suspended' => 'Ditangguhkan'] as $val => $label)
        <a href="{{ route('superadmin.merchants', ['status' => $val]) }}"
           class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition
           {{ $status === $val ? 'bg-sky-600 text-white shadow' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
    <a href="{{ route('superadmin.merchants.create') }}" class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2.5 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition">
        <i class="fas fa-plus text-[11px]"></i> Buat Toko Baru
    </a>
</div>

@forelse($merchants as $m)
<div class="glass-card rounded-2xl p-5 mb-4 border border-sky-100/50">
    <div class="flex flex-wrap items-start gap-4">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center text-white text-xl font-bold overflow-hidden flex-shrink-0">
            @if($m->logo)
                <img src="{{ asset('storage/' . $m->logo) }}" class="w-full h-full object-cover" alt="">
            @else
                {{ strtoupper(substr($m->name, 0, 1)) }}
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="text-[15px] font-bold text-navy-800">{{ $m->name }}</h3>
                @if($m->status === 'pending')
                    <span class="badge bg-amber-50 text-amber-600 border border-amber-200 px-2.5 py-1 rounded-full text-[10px] font-bold">Menunggu Verifikasi</span>
                @elseif($m->status === 'active')
                    <span class="badge bg-emerald-50 text-emerald-600 border border-emerald-200 px-2.5 py-1 rounded-full text-[10px] font-bold">Aktif</span>
                @else
                    <span class="badge bg-red-50 text-red-600 border border-red-200 px-2.5 py-1 rounded-full text-[10px] font-bold">Ditangguhkan</span>
                @endif
                @if($m->status === 'active' && $m->is_active)
                    <span class="badge bg-sky-50 text-sky-600 border border-sky-200 px-2.5 py-1 rounded-full text-[10px] font-bold"><i class="fas fa-check-circle mr-1"></i>Terverifikasi {{ $m->verified_at?->format('d M Y') }}</span>
                @endif
            </div>
            <p class="text-[11px] text-gray-400 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                <span class="inline-flex items-center gap-1"><i class="fas fa-user"></i> {{ $m->owner?->name ?? '-' }}</span>
                <span class="inline-flex items-center gap-1"><i class="fas fa-envelope"></i> {{ $m->owner?->email ?? '-' }}</span>
                <a href="{{ route('public.store', $m->slug) }}" target="_blank" class="inline-flex items-center gap-1 text-sky-600 hover:underline"><i class="fas fa-store"></i> /store/{{ $m->slug }}</a>
            </p>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-[11px] text-gray-500">
                <span class="inline-flex items-center gap-1"><i class="fas fa-box-open text-sky-400"></i> {{ $m->vehicles_count }} unit</span>
                <span class="inline-flex items-center gap-1"><i class="fas fa-percentage text-amber-500"></i> Komisi {{ number_format($m->commission_rate, 0) }}%</span>
                <span class="inline-flex items-center gap-1"><i class="fas fa-map-marker-alt text-gray-300"></i> {{ $m->city ?? $m->address ?? '-' }}</span>
            </div>
        </div>

        <div class="flex flex-col items-end gap-2 flex-shrink-0">
            <div class="flex items-center gap-1.5">
                @if($m->status === 'pending')
                <form action="{{ route('superadmin.merchants.verify', $m->id) }}" method="POST" class="inline">
                    @csrf
                    <button class="px-3 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-[11px] font-semibold transition inline-flex items-center gap-1">
                        <i class="fas fa-check"></i> Verifikasi
                    </button>
                </form>
                @endif
                @if($m->status === 'active')
                <form action="{{ route('superadmin.merchants.suspend', $m->id) }}" method="POST" class="inline">
                    @csrf
                    <button class="px-3 py-2 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-[11px] font-semibold transition inline-flex items-center gap-1">
                        <i class="fas fa-ban"></i> Nonaktifkan
                    </button>
                </form>
                @elseif($m->status === 'suspended')
                <form action="{{ route('superadmin.merchants.activate', $m->id) }}" method="POST" class="inline">
                    @csrf
                    <button class="px-3 py-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200 text-[11px] font-semibold transition inline-flex items-center gap-1">
                        <i class="fas fa-play"></i> Aktifkan
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Inline Edit --}}
    <details class="mt-4 group">
        <summary class="cursor-pointer text-[12px] font-semibold text-sky-600 hover:text-sky-700 inline-flex items-center gap-1.5 select-none">
            <i class="fas fa-edit text-[11px]"></i> Edit Toko
        </summary>
        <form action="{{ route('superadmin.merchants.update', $m->id) }}" method="POST" class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3 bg-gray-50/70 p-4 rounded-xl border border-gray-100">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nama Toko</label>
                <input name="name" value="{{ $m->name }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px]">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Komisi (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="commission_rate" value="{{ $m->commission_rate }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px]">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Kota</label>
                <input name="city" value="{{ $m->city }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px]">
            </div>
            <div class="md:col-span-3">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px]">{{ $m->description }}</textarea>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Telepon</label>
                <input name="phone" value="{{ $m->phone }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px]">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Alamat</label>
                <input name="address" value="{{ $m->address }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px]">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Jam Operasional</label>
                <input name="operational_hours" value="{{ $m->operational_hours }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px]">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Email Toko</label>
                <input name="company_email" value="{{ $m->company_email }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px]">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Website</label>
                <input name="website" value="{{ $m->website }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px]">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Instagram</label>
                <input name="instagram" value="{{ $m->instagram }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px]">
            </div>
            <div class="md:col-span-3 text-right">
                <button class="px-4 py-2 rounded-lg bg-navy-800 hover:bg-navy-900 text-white text-[12px] font-semibold transition inline-flex items-center gap-1.5">
                    <i class="fas fa-save text-[11px]"></i> Simpan
                </button>
            </div>
        </form>
    </details>
</div>
@empty
<div class="glass-card rounded-2xl p-12 text-center">
    <i class="fas fa-store text-4xl text-gray-300 mb-3"></i>
    <p class="text-gray-400 text-[13px]">Belum ada toko pada status ini.</p>
</div>
@endforelse
@endsection
