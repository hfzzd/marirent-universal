@extends('layouts.dashboard')
@section('page-title', 'Beranda Karyawan')
@section('content')
<div class="max-w-3xl">
    <div class="relative overflow-hidden rounded-3xl shadow-xl shadow-sky-900/10" style="background: linear-gradient(135deg, #09203f 0%, #1e3a8a 55%, #0284c7 100%);">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-sky-400/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative px-6 py-8 md:px-8">
            <p class="text-sky-200 text-xs font-semibold uppercase tracking-wider">Akun Karyawan</p>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight mt-2">Halo, {{ auth()->user()->name }}!</h1>
            <p class="text-sky-100/80 text-sm mt-2">{{ $staff?->company?->name ?? 'Tim MariRent' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
        <a href="{{ route('attendance.index') }}" class="glass-card rounded-2xl p-5 border border-sky-100/50 hover:border-sky-300 transition">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3"><i class="fas fa-fingerprint"></i></div>
            <h2 class="font-bold text-navy-800">Absensi</h2>
            <p class="text-xs text-gray-400 mt-1">Catat kehadiran kerja Anda.</p>
        </a>
        <a href="{{ route('dashboard.profile') }}" class="glass-card rounded-2xl p-5 border border-sky-100/50 hover:border-sky-300 transition">
            <div class="w-11 h-11 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center mb-3"><i class="fas fa-user-circle"></i></div>
            <h2 class="font-bold text-navy-800">Profil Saya</h2>
            <p class="text-xs text-gray-400 mt-1">Perbarui data akun karyawan.</p>
        </a>
    </div>
</div>
@endsection
