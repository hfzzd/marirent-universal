@extends('layouts.dashboard')
@section('page-title', 'Absen Hari Ini')

@section('content')
@php
    $now = now();
    $isCheckedIn = $todayAttendance && $todayAttendance->isCheckedin();
    $isCheckedOut = $todayAttendance && $todayAttendance->isCheckedout();
@endphp

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-4 text-[13px] flex items-center gap-2">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-4 text-[13px] flex items-center gap-2">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

{{-- ATTENDANCE CARD --}}
<div class="glass-card rounded-2xl overflow-hidden mb-6 border border-sky-100/50" style="box-shadow: 0 4px 24px rgba(0,0,0,0.03);">
    <div class="bg-gradient-to-r from-sky-500 to-blue-600 p-6 text-white">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-sky-200 text-[12px] font-medium">{{ $now->format('l, d F Y') }}</p>
                <h2 class="text-2xl font-extrabold mt-1" id="clock">{{ $now->format('H:i') }} WIB</h2>
            </div>
            <div class="w-14 h-14 bg-white/15 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                <i class="fas fa-fingerprint text-2xl text-white"></i>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if($isCheckedOut)
                <span class="bg-white/20 text-white px-4 py-2 rounded-xl text-[12px] font-semibold flex items-center gap-2">
                    <i class="fas fa-check-double"></i> Sudah Absen Hari Ini
                </span>
            @elseif($isCheckedIn)
                <span class="bg-white/20 text-white px-4 py-2 rounded-xl text-[12px] font-semibold flex items-center gap-2">
                    <i class="fas fa-check"></i> Sudah Masuk
                </span>
            @else
                <span class="bg-white/20 text-white px-4 py-2 rounded-xl text-[12px] font-semibold flex items-center gap-2">
                    <i class="fas fa-clock"></i> Belum Absen
                </span>
            @endif
        </div>
    </div>

    <div class="p-6">
        @if(!$isCheckedIn && !$isCheckedOut)
        {{-- CHECK IN FORM --}}
        <div class="text-center py-4">
            <div class="w-20 h-20 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-emerald-100">
                <i class="fas fa-sign-in-alt text-emerald-500 text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-navy-800 mb-2">Absen Masuk</h3>
            <p class="text-[13px] text-gray-400 mb-5">Klik tombol di bawah untuk melakukan absen masuk</p>
            <form method="POST" action="{{ route('attendance.check-in') }}" x-data="{ confirm: false }">
                @csrf
                <div class="max-w-sm mx-auto mb-4">
                    <input type="text" name="notes" placeholder="Catatan (opsional)" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50">
                </div>
                <button type="submit" class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white px-8 py-3.5 rounded-2xl text-[14px] font-bold shadow-lg shadow-emerald-500/25 transition-all duration-300 hover:scale-105 hover:shadow-xl inline-flex items-center gap-2">
                    <i class="fas fa-fingerprint"></i> Absen Masuk Sekarang
                </button>
            </form>
        </div>

        @elseif($isCheckedIn && !$isCheckedOut)
        {{-- CHECK OUT FORM --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-emerald-50/60 border border-emerald-100 rounded-xl p-4">
                <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-widest mb-1">Waktu Masuk</p>
                <p class="text-xl font-extrabold text-emerald-700">{{ $todayAttendance->check_in->format('H:i') }}</p>
                <p class="text-[11px] text-emerald-600/70 mt-0.5">{{ $todayAttendance->check_in_location ?? 'Kantor' }}</p>
                @if($todayAttendance->check_in_notes)
                <p class="text-[11px] text-emerald-600/70 mt-1 italic">"{{ $todayAttendance->check_in_notes }}"</p>
                @endif
            </div>
            <div class="text-center flex flex-col items-center justify-center">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl flex items-center justify-center mb-3 border border-amber-100">
                    <i class="fas fa-sign-out-alt text-amber-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-navy-800 mb-1">Absen Keluar</h3>
                <p class="text-[12px] text-gray-400 mb-4">Waktu kerja: {{ $todayAttendance->work_duration ?? '-' }}</p>
                <form method="POST" action="{{ route('attendance.check-out') }}">
                    @csrf
                    <div class="mb-3">
                        <input type="text" name="notes" placeholder="Catatan (opsional)" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50">
                    </div>
                    <button type="submit" class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white px-6 py-3 rounded-2xl text-[13px] font-bold shadow-lg shadow-amber-500/25 transition-all duration-300 hover:scale-105 inline-flex items-center gap-2">
                        <i class="fas fa-sign-out-alt"></i> Absen Keluar
                    </button>
                </form>
            </div>
        </div>

        @else
        {{-- COMPLETED --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-emerald-50/60 border border-emerald-100 rounded-xl p-4 text-center">
                <i class="fas fa-sign-in-alt text-emerald-500 text-lg mb-2"></i>
                <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-widest mb-1">Masuk</p>
                <p class="text-xl font-extrabold text-emerald-700">{{ $todayAttendance->check_in->format('H:i') }}</p>
            </div>
            <div class="bg-amber-50/60 border border-amber-100 rounded-xl p-4 text-center">
                <i class="fas fa-sign-out-alt text-amber-500 text-lg mb-2"></i>
                <p class="text-[10px] text-amber-600 font-bold uppercase tracking-widest mb-1">Keluar</p>
                <p class="text-xl font-extrabold text-amber-700">{{ $todayAttendance->check_out->format('H:i') }}</p>
            </div>
            <div class="bg-sky-50/60 border border-sky-100 rounded-xl p-4 text-center">
                <i class="fas fa-clock text-sky-500 text-lg mb-2"></i>
                <p class="text-[10px] text-sky-600 font-bold uppercase tracking-widest mb-1">Durasi</p>
                <p class="text-xl font-extrabold text-sky-700">{{ $todayAttendance->work_duration ?? '-' }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- HISTORY --}}
<div class="flex items-center justify-between mb-4">
    <h3 class="text-[14px] font-bold text-navy-800"><i class="fas fa-history text-sky-500 mr-2"></i>Riwayat Absensi</h3>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Tanggal</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Masuk</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Keluar</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Durasi</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $a)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30">
                    <td class="py-3 px-5 font-medium text-navy-700">{{ $a->date->format('d M Y') }}</td>
                    <td class="py-3 px-5 text-center">
                        @if($a->check_in)
                            <span class="text-emerald-600 font-semibold">{{ $a->check_in->format('H:i') }}</span>
                            @if($a->check_in_notes)
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ Str::limit($a->check_in_notes, 30) }}</p>
                            @endif
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-center">
                        @if($a->check_out)
                            <span class="text-amber-600 font-semibold">{{ $a->check_out->format('H:i') }}</span>
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-center text-navy-600 font-medium">{{ $a->work_duration ?? '-' }}</td>
                    <td class="py-3 px-5 text-center">
                        @if($a->status === 'checked_out')
                            <span class="badge badge-green">Selesai</span>
                        @elseif($a->status === 'checked_in')
                            <span class="badge badge-blue">Sedang Bekerja</span>
                        @else
                            <span class="badge badge-gray">Absen</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-10 text-center text-gray-300">Belum ada riwayat absensi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $attendances->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
    setInterval(() => {
        const el = document.getElementById('clock');
        if (el) {
            const now = new Date();
            el.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':') + ' WIB';
        }
    }, 1000);
</script>
@endpush
