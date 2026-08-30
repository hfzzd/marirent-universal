@extends('layouts.dashboard')
@section('page-title', 'Beranda Admin')

@section('content')
{{-- Hero Banner --}}
<div class="relative overflow-hidden rounded-3xl mb-6 shadow-xl shadow-sky-900/10" style="background: linear-gradient(135deg, #09203f 0%, #173b6c 50%, #0284c7 100%);">
    <div class="absolute -right-8 -bottom-8 w-72 h-72 bg-sky-400/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="relative px-6 py-7 md:px-8 md:py-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-sky-200 text-xs font-semibold mb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Admin @if($merchant) &mdash; {{ $merchant->name }} @endif</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">
                Halo, {{ auth()->user()->name }} 👋
            </h1>
            <p class="text-sky-100/80 text-xs md:text-sm mt-1 max-w-lg">
                Kelola booking, pembayaran, maintenance, dan inspeksi untuk merchant Anda.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('bookings.create') }}" class="bg-white text-navy-900 hover:bg-sky-50 px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-lg flex items-center gap-1.5">
                <i class="fas fa-plus text-sky-600"></i> Buat Booking
            </a>
            <a href="{{ route('maintenances.index') }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-lg flex items-center gap-1.5">
                <i class="fas fa-wrench"></i> Maintenance
            </a>
        </div>
    </div>
</div>

{{-- Stat Widgets --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Armada</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $vehicleCount }} Unit</h3>
                <p class="text-[11px] text-gray-500 mt-2 font-medium">Mobil & motor merchant</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-600 flex items-center justify-center text-white text-lg shadow-lg shadow-sky-500/25">
                <i class="fas fa-car-side"></i>
            </div>
        </div>
    </div>

    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Payment Pending</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $pendingPayments->count() }}</h3>
                <p class="text-[11px] text-gray-500 mt-2 font-medium">Menunggu verifikasi</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-lg shadow-lg shadow-amber-500/25">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
    </div>

    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Pendapatan Lunas</p>
                <h3 class="text-xl font-black text-navy-800 mt-1">Rp {{ number_format($revenue, 0, ',', '.') }}</h3>
                <p class="text-[11px] text-gray-500 mt-2 font-medium">Total invoice terbayar</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-lg shadow-lg shadow-emerald-500/25">
                <i class="fas fa-hand-holding-dollar"></i>
            </div>
        </div>
    </div>

    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Tim Driver</p>
                <h3 class="text-2xl font-black text-navy-800 mt-1">{{ $driverCount }} Personil</h3>
                <p class="text-[11px] text-gray-500 mt-2 font-medium">Driver terdaftar</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white text-lg shadow-lg shadow-purple-500/25">
                <i class="fas fa-id-card"></i>
            </div>
        </div>
    </div>
</div>

{{-- Pending Payments + Maintenance --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="glass-card rounded-2xl overflow-hidden border border-sky-100/50 shadow-sm">
        <div class="px-6 py-4 border-b border-sky-100/60 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-hourglass-half text-amber-500"></i> Payment Menunggu Verifikasi
            </h3>
            <a href="{{ route('invoices.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700">Verifikasi &rarr;</a>
        </div>
        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
            @forelse($pendingPayments as $p)
            <div class="px-5 py-3 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-navy-800 text-[13px] truncate">Rp {{ number_format($p->amount, 0, ',', '.') }} &middot; {{ $p->payment_method }}</p>
                    <p class="text-[11px] text-gray-400 truncate">{{ $p->invoice?->invoice_number }} &bull; {{ $p->user?->name }}</p>
                </div>
                <span class="badge badge-yellow text-[10px] whitespace-nowrap flex-shrink-0">{{ $p->status }}</span>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400 text-xs">Tidak ada pembayaran menunggu.</div>
            @endforelse
        </div>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden border border-sky-100/50 shadow-sm">
        <div class="px-6 py-4 border-b border-sky-100/60 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-wrench text-amber-600"></i> Jadwal Maintenance Aktif
            </h3>
            <a href="{{ route('maintenances.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700">Kelola &rarr;</a>
        </div>
        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
            @forelse($maintenances as $m)
            <div class="px-5 py-3 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-navy-800 text-[13px] truncate">{{ $m->title }}</p>
                    <p class="text-[11px] text-gray-400 truncate">{{ $m->vehicle?->name }} &bull; {{ \Carbon\Carbon::parse($m->scheduled_date)->translatedFormat('d M Y') }}</p>
                </div>
                <span class="badge {{ $m->priority === 'urgent' ? 'badge-red' : ($m->priority === 'high' ? 'badge-yellow' : 'badge-blue') }} text-[10px] whitespace-nowrap flex-shrink-0">{{ $m->status }}</span>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400 text-xs">Tidak ada jadwal maintenance aktif.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Laporan Inspeksi Driver + Booking Terbaru --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="glass-card rounded-2xl overflow-hidden border border-sky-100/50 shadow-sm">
        <div class="px-6 py-4 border-b border-sky-100/60 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-triangle-exclamation text-red-500"></i> Laporan Inspeksi Driver
            </h3>
            <a href="{{ route('inspections.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700">Lihat &rarr;</a>
        </div>
        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
            @forelse($inspectorReports as $r)
            <div class="px-5 py-3 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-navy-800 text-[13px] truncate">{{ $r->vehicle?->name ?? 'Unit' }}</p>
                    <p class="text-[11px] text-gray-400 truncate">Dilaporkan oleh {{ $r->reportedBy?->name ?? '-' }} &bull; {{ \Carbon\Carbon::parse($r->created_at)->diffForHumans() }}</p>
                </div>
                <span class="badge badge-red text-[10px] whitespace-nowrap flex-shrink-0">Menunggu Inspector</span>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400 text-xs">Tidak ada laporan inspeksi masuk.</div>
            @endforelse
        </div>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden border border-sky-100/50 shadow-sm">
        <div class="px-6 py-4 border-b border-sky-100/60 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                <i class="fas fa-calendar-check text-sky-500"></i> Booking Terbaru
            </h3>
            <a href="{{ route('bookings.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700">Lihat Semua &rarr;</a>
        </div>
        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
            @forelse($recentBookings as $b)
            <div class="px-5 py-3 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-navy-800 text-[13px] truncate">{{ $b->vehicle?->name ?? $b->category?->name ?? 'Unit' }}</p>
                    <p class="text-[11px] text-gray-400 truncate">{{ $b->booking_code }} &bull; {{ $b->user?->name }}</p>
                </div>
                @if($b->status == 'pending') <span class="badge badge-yellow">Pending</span>
                @elseif($b->status == 'confirmed') <span class="badge badge-teal">Confirmed</span>
                @elseif($b->status == 'ongoing') <span class="badge badge-blue">Berjalan</span>
                @elseif($b->status == 'completed') <span class="badge badge-green">Selesai</span>
                @else <span class="badge badge-red">Batal</span>
                @endif
            </div>
            @empty
            <div class="p-8 text-center text-gray-400 text-xs">Belum ada booking.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection