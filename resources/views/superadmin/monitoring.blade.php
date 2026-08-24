@extends('layouts.dashboard')
@section('page-title', 'Monitoring & Scheduler Pembayaran')

@section('content')
@php
    $activeStatus = request('status', 'all');
    $prevMonth = $month->copy()->subMonth()->format('Y-m');
    $nextMonth = $month->copy()->addMonth()->format('Y-m');
    // Kalender: Senin sebagai awal minggu
    $calendarStart = $month->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
    $calendarEnd = $month->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
    $today = today();
@endphp

<div x-data="{ tab: 'scheduler' }">
    {{-- Tab Switch --}}
    <div class="flex items-center justify-between gap-3 mb-5 flex-wrap">
        <div class="flex items-center gap-2 bg-white rounded-xl p-1 border border-gray-200 shadow-sm">
            <button @click="tab = 'scheduler'" :class="tab === 'scheduler' ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'text-gray-500 hover:text-sky-600'" class="px-4 py-2 rounded-lg text-[12px] font-semibold transition flex items-center gap-2">
                <i class="fas fa-calendar-days"></i> Scheduler Pembayaran
            </button>
            <button @click="tab = 'aktivitas'" :class="tab === 'aktivitas' ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'text-gray-500 hover:text-sky-600'" class="px-4 py-2 rounded-lg text-[12px] font-semibold transition flex items-center gap-2">
                <i class="fas fa-table-list"></i> Datasheet Aktivitas
            </button>
        </div>
        <span class="text-[11px] text-gray-400 bg-white border border-gray-200 px-3 py-2 rounded-xl"><i class="far fa-clock mr-1"></i> {{ now()->translatedFormat('d M Y H:i') }}</span>
    </div>

    {{-- ================= SCHEDULER ================= --}}
    <div x-show="tab === 'scheduler'" x-cloak>
        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <a href="{{ route('superadmin.monitoring') }}" class="stat-card glass-card rounded-2xl p-5 border border-orange-100/60 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Jatuh Tempo Bulan Ini</p>
                <h3 class="text-xl font-black text-navy-800 mt-1">{{ $summary['due_this_month'] }} <span class="text-sm font-bold text-gray-400">tagihan</span></h3>
                <p class="text-[12px] font-bold text-orange-600 mt-1.5">Rp {{ number_format($summary['due_this_month_amount'], 0, ',', '.') }}</p>
            </a>
            <a href="{{ route('superadmin.monitoring') }}" class="stat-card glass-card rounded-2xl p-5 border border-red-100/60 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Terlambat Bayar</p>
                <h3 class="text-xl font-black text-red-600 mt-1">{{ $summary['overdue_total'] }} <span class="text-sm font-bold text-gray-400">tagihan</span></h3>
                <p class="text-[12px] font-bold text-red-500 mt-1.5">Rp {{ number_format($summary['overdue_amount'], 0, ',', '.') }}</p>
            </a>
            <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/60 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">DP / Sebagian Bulan Ini</p>
                <h3 class="text-xl font-black text-sky-600 mt-1">{{ $summary['dp_this_month'] }} <span class="text-sm font-bold text-gray-400">booking</span></h3>
                <p class="text-[12px] font-bold text-sky-500 mt-1.5">Rp {{ number_format($summary['dp_this_month_amount'], 0, ',', '.') }}</p>
            </div>
            <div class="stat-card glass-card rounded-2xl p-5 border border-emerald-100/60 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Lunas Bulan Ini</p>
                <h3 class="text-xl font-black text-emerald-600 mt-1">{{ $summary['paid_this_month'] }} <span class="text-sm font-bold text-gray-400">booking</span></h3>
                <p class="text-[11px] text-gray-400 mt-1.5">Pembayaran tepat jadwal</p>
            </div>
            <div class="stat-card glass-card rounded-2xl p-5 border border-navy-100/60 shadow-sm col-span-2 lg:col-span-1">
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Piutang Berjalan</p>
                <h3 class="text-lg font-black text-navy-800 mt-1">Rp {{ number_format($summary['outstanding_all_amount'], 0, ',', '.') }}</h3>
                <p class="text-[11px] text-gray-400 mt-1.5">Seluruh tagihan belum lunas</p>
            </div>
        </div>

        {{-- Kalender --}}
        <div class="glass-card rounded-2xl overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-sky-100/50 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                    <a href="{{ route('superadmin.monitoring', ['bulan' => $prevMonth]) }}" class="w-9 h-9 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-600 flex items-center justify-center transition"><i class="fas fa-chevron-left text-xs"></i></a>
                    <h3 class="text-[15px] font-extrabold text-navy-800 capitalize">{{ $month->translatedFormat('F Y') }}</h3>
                    <a href="{{ route('superadmin.monitoring', ['bulan' => $nextMonth]) }}" class="w-9 h-9 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-600 flex items-center justify-center transition"><i class="fas fa-chevron-right text-xs"></i></a>
                </div>
                <div class="flex items-center gap-4 text-[10px] font-semibold text-gray-500 flex-wrap">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Terlambat</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span> DP / Sebagian</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Belum Bayar</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Lunas</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-sky-300 ring-2 ring-sky-400"></span> Hari Ini</span>
                </div>
            </div>

            <div class="overflow-x-auto p-4">
                <div class="min-w-[770px]">
                    {{-- Header hari --}}
                    <div class="grid grid-cols-7 gap-1.5 mb-1.5">
                        @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $d)
                        <div class="text-center text-[10px] font-extrabold uppercase tracking-wider text-gray-400 py-1">{{ $d }}</div>
                        @endforeach
                    </div>

                    {{-- Grid tanggal --}}
                    <div class="grid grid-cols-7 gap-1.5">
                        @for($date = $calendarStart->copy(); $date->lte($calendarEnd); $date->addDay())
                        @php
                            $inMonth = $date->isSameMonth($month);
                            $dayEvents = $eventsByDay[$date->day] ?? [];
                            $isToday = $date->isSameDay($today);
                            $hasOverdue = collect($dayEvents)->contains(fn($e) => $e['overdue']);
                            $hasPaid = collect($dayEvents)->contains(fn($e) => $e['status'] === 'paid');
                        @endphp
                        <div class="rounded-xl border {{ $isToday ? 'border-sky-400 ring-2 ring-sky-100' : ($inMonth ? 'border-gray-100' : 'border-gray-50 opacity-40') }} bg-white min-h-[92px] p-1.5 relative {{ $hasOverdue ? 'bg-red-50/40' : '' }}">
                            <span class="text-[11px] font-bold {{ $isToday ? 'bg-sky-500 text-white w-5 h-5 inline-flex items-center justify-center rounded-full' : ($inMonth ? 'text-navy-700' : 'text-gray-300') }}">{{ $date->day }}</span>
                            @if($inMonth)
                            <div class="mt-1 space-y-1">
                                @foreach(array_slice($dayEvents, 0, 2) as $e)
                                @php
                                    $chipLabel = match(true) {
                                        $e['overdue'] => 'Telat ' . $e['days_late'] . 'hr',
                                        $e['status'] === 'paid' => 'Lunas',
                                        $e['status'] === 'partial' => 'DP ' . $e['dp'] . '%',
                                        default => 'Bayar',
                                    };
                                    $chipClass = match(true) {
                                        $e['overdue'] => 'bg-red-100 text-red-700',
                                        $e['status'] === 'paid' => 'bg-emerald-100 text-emerald-700',
                                        $e['status'] === 'partial' => 'bg-sky-100 text-sky-700',
                                        default => 'bg-amber-50 text-amber-600',
                                    };
                                @endphp
                                <a href="{{ route('bookings.show', $e['id']) }}"
                                   title="{{ $e['item'] }} &bull; {{ $e['customer'] }} &bull; {{ $chipLabel }}{{ $e['remaining'] > 0 ? ' &bull; Sisa Rp ' . number_format($e['remaining'],0,',','.') : '' }}"
                                   class="block text-[9px] leading-tight px-1.5 py-1 rounded-md font-semibold transition hover:opacity-80 {{ $chipClass }}">
                                    <span class="block truncate">{{ $e['item'] }}</span>
                                    <span class="block font-bold opacity-75">{{ $chipLabel }}</span>
                                </a>
                                @endforeach
                                @if(count($dayEvents) > 2)
                                <div class="text-[9px] text-gray-400 font-semibold pl-1">+{{ count($dayEvents) - 2 }} lainnya</div>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar tagihan terlambat --}}
        <div class="glass-card rounded-2xl overflow-hidden mb-6 border border-red-100/60">
            <div class="px-6 py-4 border-b border-red-100/60 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-extrabold text-red-600 flex items-center gap-2">
                        <i class="fas fa-triangle-exclamation"></i> Tagihan Terlambat
                    </h3>
                    <p class="text-[11px] text-gray-400">Segera lakukan penagihan &amp; follow-up pelanggan.</p>
                </div>
                @if($overdueList->isNotEmpty())
                <span class="badge badge-red text-[10px]">{{ $summary['overdue_total'] }} tagihan &bull; Rp {{ number_format($summary['overdue_amount'], 0, ',', '.') }}</span>
                @endif
            </div>
            <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                @forelse($overdueList as $b)
                @php $daysLate = \Carbon\Carbon::parse($b->payment_due_date)->startOfDay()->diffInDays($today); @endphp
                <a href="{{ route('bookings.show', $b) }}" class="flex items-center justify-between gap-4 px-6 py-3.5 hover:bg-red-50/30 transition">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-[14px] font-black leading-none">{{ \Carbon\Carbon::parse($b->payment_due_date)->format('d') }}</span>
                            <span class="text-[8px] font-bold uppercase">{{ \Carbon\Carbon::parse($b->payment_due_date)->translatedFormat('M') }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-navy-800 text-[13px] truncate">{{ $b->vehicle?->name ?? ($b->item?->name ?? ($b->category?->name ?? 'Unit Sewa')) }}</p>
                            <p class="text-[11px] text-gray-400 truncate">{{ $b->booking_code }} &bull; {{ $b->user?->name ?? '-' }}{{ $b->payment_status === 'partial' ? ' &bull; DP ' . $b->dp_percent . '%' : '' }}</p>
                        </div>
                    </div>
                    <div class="text-right whitespace-nowrap">
                        <p class="font-black text-navy-800 text-[13px]">Rp {{ number_format($b->remaining > 0 && $b->payment_status === 'partial' ? $b->remaining : $b->final_price, 0, ',', '.') }}{{ $b->payment_status === 'partial' ? ' <span class="text-[10px] font-semibold text-gray-400">sisa</span>' : '' }}</p>
                        <span class="badge badge-red text-[10px]">{{ $daysLate }} hari terlambat</span>
                    </div>
                </a>
                @empty
                <div class="py-8 text-center text-gray-300 text-xs">
                    <i class="fas fa-circle-check text-emerald-300 text-2xl block mb-2"></i>
                    Tidak ada tagihan terlambat.
                </div>
                @endforelse
            </div>
        </div>

        {{-- Daftar jatuh tempo 7 hari ke depan --}}
        <div class="glass-card rounded-2xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-sky-100/60">
                <h3 class="text-sm font-extrabold text-navy-800 flex items-center gap-2">
                    <i class="fas fa-bell text-orange-500"></i> Jatuh Tempo 7 Hari Ke Depan
                </h3>
                <p class="text-[11px] text-gray-400">Tagihan yang harus ditindaklanjuti segera.</p>
            </div>
            <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                @forelse($upcomingDues as $b)
                @php $isOverdue = \Carbon\Carbon::parse($b->payment_due_date)->lt($today); @endphp
                <a href="{{ route('bookings.show', $b) }}" class="flex items-center justify-between gap-4 px-6 py-3.5 hover:bg-sky-50/30 transition">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-11 h-11 rounded-xl flex flex-col items-center justify-center flex-shrink-0 {{ $isOverdue ? 'bg-red-50 text-red-600' : 'bg-sky-50 text-sky-600' }}">
                            <span class="text-[14px] font-black leading-none">{{ \Carbon\Carbon::parse($b->payment_due_date)->format('d') }}</span>
                            <span class="text-[8px] font-bold uppercase">{{ \Carbon\Carbon::parse($b->payment_due_date)->translatedFormat('M') }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-navy-800 text-[13px] truncate">{{ $b->vehicle?->name ?? ($b->category?->name ?? 'Unit Sewa') }}</p>
                            <p class="text-[11px] text-gray-400 truncate">{{ $b->booking_code }} &bull; {{ $b->user?->name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="text-right whitespace-nowrap">
                        <p class="font-black text-navy-800 text-[13px]">Rp {{ number_format($b->final_price, 0, ',', '.') }}</p>
                        @if($b->payment_status === 'partial')
                        <p class="text-[10px] font-bold text-sky-600">DP {{ $b->dp_percent }}% &bull; Sisa Rp {{ number_format($b->remaining, 0, ',', '.') }}</p>
                        @endif
                        @if($isOverdue) <span class="badge badge-red text-[10px]">Terlambat</span>
                        @elseif($today->isSameDay(\Carbon\Carbon::parse($b->payment_due_date))) <span class="badge badge-yellow text-[10px]">Hari Ini</span>
                        @else <span class="badge badge-blue text-[10px]">{{ today()->diffInDays(\Carbon\Carbon::parse($b->payment_due_date)) }} hari lagi</span> @endif
                    </div>
                </a>
                @empty
                <div class="py-10 text-center text-gray-300 text-xs">
                    <i class="fas fa-circle-check text-emerald-300 text-2xl block mb-2"></i>
                    Tidak ada tagihan jatuh tempo dalam 7 hari ke depan.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ================= DATASHEET AKTIVITAS ================= --}}
    <div x-show="tab === 'aktivitas'" x-cloak>
        {{-- Filter Status --}}
        <div class="flex items-center gap-2 mb-5 flex-wrap">
            @php $statuses = ['all' => 'Semua', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'ongoing' => 'Ongoing', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan']; @endphp
            @foreach($statuses as $val => $label)
            <a href="{{ route('superadmin.monitoring', array_merge(request()->query(), ['status' => $val !== 'all' ? $val : null])) }}"
               class="px-4 py-2 rounded-xl text-[12px] font-semibold transition {{ $activeStatus == $val ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Datasheet --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-sky-100/50 flex items-center justify-between">
                <h3 class="text-[14px] font-bold text-navy-800">Datasheet Monitoring Aktivitas</h3>
                <button onclick="window.print()" class="text-[11px] text-sky-600 hover:text-sky-700 font-semibold bg-sky-50 hover:bg-sky-100 px-3 py-1.5 rounded-lg transition"><i class="fas fa-print mr-1"></i> Print</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-[12px]">
                    <thead>
                        <tr class="bg-sky-50/50">
                            <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider sticky left-0 bg-sky-50/80 backdrop-blur-sm">No</th>
                            <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">ID Booking</th>
                            <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Sumber</th>
                            <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Kategori</th>
                            <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Barang</th>
                            <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Pengguna</th>
                            <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Tgl Mulai</th>
                            <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Tgl Selesai</th>
                            <th class="text-left py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Tempo Bayar</th>
                            <th class="text-center py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Status</th>
                            <th class="text-center py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Pembayaran</th>
                            <th class="text-right py-3 px-3 text-gray-400 font-semibold text-[10px] uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $query = \App\Models\Booking::with(['user', 'vehicle', 'category']);
                            if ($activeStatus !== 'all') {
                                $query->where('status', $activeStatus);
                            }
                            $bookings = $query->latest()->paginate(25);
                        @endphp
                        @forelse($bookings as $i => $b)
                        <tr class="border-b border-sky-50/50 last:border-0 hover:bg-sky-50/30 transition cursor-pointer" onclick="window.location='{{ route('bookings.show', $b) }}'">
                            <td class="py-2.5 px-3 text-gray-400 sticky left-0 bg-white/80 backdrop-blur-sm">{{ $bookings->firstItem() + $i }}</td>
                            <td class="py-2.5 px-3 font-semibold text-sky-600">{{ $b->booking_code }}</td>
                            <td class="py-2.5 px-3">
                                @if(($b->source ?? 'online') == 'manual') <span class="badge badge-teal">Manual</span>
                                @else <span class="badge badge-gray">Online</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                @php $catSlug = $b->category->slug ?? ''; @endphp
                                @if($catSlug == 'mobil') <span class="badge badge-blue">Mobil</span>
                                @elseif($catSlug == 'motor') <span class="badge badge-yellow">Motor</span>
                                @elseif($catSlug == 'sewa-kamera') <span class="badge badge-teal">Kamera</span>
                                @elseif($catSlug == 'sewa-tenda') <span class="badge badge-green">Tenda</span>
                                @elseif($catSlug == 'sewa-hp') <span class="badge badge-gray">HP</span>
                                @else <span class="badge badge-gray">{{ $catSlug ?: '-' }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-navy-700 font-medium">
                                @if($b->vehicle) {{ $b->vehicle->name }}
                                @else {{ $b->item?->name ?? ($b->category->name ?? '-') }}
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-navy-600">{{ $b->user->name }}</td>
                            <td class="py-2.5 px-3 text-navy-500">{{ $b->start_date->format('d/m/Y') }}</td>
                            <td class="py-2.5 px-3 text-navy-500">{{ $b->end_date->format('d/m/Y') }}</td>
                            <td class="py-2.5 px-3 {{ $b->payment_status != 'paid' && $b->payment_due_date && \Carbon\Carbon::parse($b->payment_due_date)->isPast() ? 'text-red-600 font-semibold' : 'text-navy-500' }}">
                                {{ $b->payment_due_date ? \Carbon\Carbon::parse($b->payment_due_date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                @if($b->status == 'pending') <span class="badge badge-blue">Pending</span>
                                @elseif($b->status == 'confirmed') <span class="badge badge-teal">Confirmed</span>
                                @elseif($b->status == 'ongoing') <span class="badge badge-yellow">Ongoing</span>
                                @elseif($b->status == 'completed') <span class="badge badge-green">Selesai</span>
                                @elseif($b->status == 'cancelled') <span class="badge badge-red">Dibatalkan</span>
                                @else <span class="badge badge-gray">{{ ucfirst($b->status) }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                @if($b->payment_status == 'paid') <span class="badge badge-green">Lunas</span>
                                @elseif($b->payment_status == 'partial') <span class="badge badge-yellow">Sebagian</span>
                                @elseif($b->payment_status == 'refunded') <span class="badge badge-gray">Refund</span>
                                @else <span class="badge badge-red">Belum Bayar</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold text-navy-800">Rp {{ number_format($b->final_price, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="12" class="py-12 text-center text-gray-300">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-sky-100/50">{{ $bookings->withQueryString()->links() }}</div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>[x-cloak]{display:none!important;}</style>
@endpush
