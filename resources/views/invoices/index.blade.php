@extends(auth()->user()->role === 'user' ? 'layouts.user' : 'layouts.dashboard')
@section('title', 'Invoice - MariRent')
@section('page-title', 'Invoice')

@section('content')
@php $isUser = auth()->user()->role === 'user'; @endphp

<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-5 gap-3">
    <div>
        <h2 class="text-[14px] font-semibold text-navy-800">{{ $isUser ? 'Riwayat Invoice Saya' : 'Semua Invoice' }}</h2>
        <p class="text-[11px] text-gray-400 mt-0.5">{{ $isUser ? 'Lihat detail tagihan dan status pembayaran Anda' : 'Kelola seluruh invoice' }}</p>
    </div>
    @if(!$isUser)
    <div class="flex gap-2">
        <a href="{{ route('invoices.create') }}" class="btn-primary text-white px-4 py-2 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-1.5">
            <i class="fas fa-plus text-[10px]"></i> Invoice Gabungan
        </a>
    </div>
    @endif
</div>

{{-- FILTERS --}}
<div class="glass-card rounded-2xl p-4 mb-5 border border-sky-100/50 shadow-sm">
    <form action="{{ route('invoices.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
        <div class="flex-1 w-full">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cari</label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-[11px]"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nomor invoice, nama, kode booking..."
                    class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50">
            </div>
        </div>
        @if(!$isUser)
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Tipe</label>
            <select name="type" class="border border-gray-200 rounded-xl px-3 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50 min-w-[140px]">
                <option value="">Semua Tipe</option>
                <option value="rental" {{ request('type') == 'rental' ? 'selected' : '' }}>Sewa</option>
                <option value="driver_salary" {{ request('type') == 'driver_salary' ? 'selected' : '' }}>Gaji Driver</option>
                <option value="replacement" {{ request('type') == 'replacement' ? 'selected' : '' }}>Penggantian</option>
                <option value="damage" {{ request('type') == 'damage' ? 'selected' : '' }}>Kerusakan</option>
                <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Lainnya</option>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-200 rounded-xl px-3 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Sampai</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-200 rounded-xl px-3 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
        </div>
        @endif
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2.5 rounded-xl text-[12px] font-semibold shadow-sm">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['search','type','status','date_from','date_to','user_id']))
            <a href="{{ route('invoices.index') }}" class="bg-red-50 hover:bg-red-100 text-red-500 px-3 py-2.5 rounded-xl text-[12px] font-medium transition border border-red-100">
                <i class="fas fa-times text-[10px]"></i> Reset
            </a>
            @endif
        </div>
    </form>
</div>

{{-- STATUS TABS --}}
<div class="flex items-center gap-2 mb-5 flex-wrap">
    @php
        $statusLabels = [
            '' => 'Semua',
            'draft' => 'Draft',
            'sent' => 'Terkirim',
            'paid' => 'Lunas',
            'partial' => 'Sebagian',
            'overdue' => 'Terlambat'
        ];
    @endphp
    @foreach($statusLabels as $val => $label)
    <a href="{{ route('invoices.index', array_merge(request()->query(), ['status' => $val ?: null])) }}"
       class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('status', '') == $val ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- TABLE --}}
<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Nomor Invoice</th>
                    @if(!$isUser)
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Tipe</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Pengguna</th>
                    @else
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Booking</th>
                    @endif
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Total</th>
                    @if(!$isUser)
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Dibayar</th>
                    @else
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Sisa Bayar</th>
                    @endif
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Jatuh Tempo</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30 transition-colors">
                    <td class="py-3 px-5 font-medium text-sky-600">{{ $inv->invoice_number }}</td>
                    @if(!$isUser)
                    <td class="py-3 px-5 text-[12px] text-navy-600">
                        @php
                            $invCategory = $inv->booking?->vehicle?->category?->slug ?? $inv->booking?->category?->slug ?? null;
                            $invTypeIcon = match($invCategory) {
                                'mobil' => ['icon' => 'fa-car', 'color' => 'text-sky-500'],
                                'motor' => ['icon' => 'fa-motorcycle', 'color' => 'text-amber-500'],
                                'sewa-hp' => ['icon' => 'fa-mobile-alt', 'color' => 'text-blue-500'],
                                'sewa-kamera' => ['icon' => 'fa-camera', 'color' => 'text-violet-500'],
                                'sewa-tenda' => ['icon' => 'fa-campground', 'color' => 'text-emerald-500'],
                                default => null,
                            };
                        @endphp
                        <div class="flex items-center gap-1.5">
                            @if($invTypeIcon)
                            <span class="w-6 h-6 {{ str_replace('text-', 'bg-', $invTypeIcon['color']) }} bg-opacity-10 rounded flex items-center justify-center">
                                <i class="fas {{ $invTypeIcon['icon'] }} {{ $invTypeIcon['color'] }} text-[10px]"></i>
                            </span>
                            @endif
                            @if($inv->type == 'rental') <span>Sewa</span>
                            @elseif($inv->type == 'driver_salary') <span>Gaji Driver</span>
                            @elseif($inv->type == 'replacement') <span>Penggantian</span>
                            @elseif($inv->type == 'damage') <span>Kerusakan</span>
                            @else <span>Lainnya</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-5 text-navy-700">{{ $inv->user?->name }}</td>
                    @else
                    <td class="py-3 px-5">
                        <div>
                            @php
                                $uInvCategory = $inv->booking?->vehicle?->category?->slug ?? $inv->booking?->category?->slug ?? null;
                                $uInvTypeIcon = match($uInvCategory) {
                                    'mobil' => ['icon' => 'fa-car', 'color' => 'text-sky-500'],
                                    'motor' => ['icon' => 'fa-motorcycle', 'color' => 'text-amber-500'],
                                    'sewa-hp' => ['icon' => 'fa-mobile-alt', 'color' => 'text-blue-500'],
                                    'sewa-kamera' => ['icon' => 'fa-camera', 'color' => 'text-violet-500'],
                                    'sewa-tenda' => ['icon' => 'fa-campground', 'color' => 'text-emerald-500'],
                                    default => null,
                                };
                            @endphp
                            <div class="flex items-center gap-2">
                                @if($uInvTypeIcon)
                                <span class="w-6 h-6 {{ str_replace('text-', 'bg-', $uInvTypeIcon['color']) }} bg-opacity-10 rounded flex items-center justify-center flex-shrink-0">
                                    <i class="fas {{ $uInvTypeIcon['icon'] }} {{ $uInvTypeIcon['color'] }} text-[10px]"></i>
                                </span>
                                @endif
                                <div>
                                    <span class="text-navy-700 font-medium">{{ $inv->booking->booking_code ?? '-' }}</span>
                                    @if($inv->booking)
                                    <p class="text-[11px] text-gray-400">{{ $inv->booking->vehicle?->name ?? $inv->booking->category?->name ?? '-' }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    @endif
                    <td class="py-3 px-5 text-right font-medium text-navy-700">Rp {{ number_format($inv->total_amount,0,',','.') }}</td>
                    @if(!$isUser)
                    <td class="py-3 px-5 text-right text-navy-600">Rp {{ number_format($inv->paid_amount,0,',','.') }}</td>
                    @else
                    <td class="py-3 px-5 text-right">
                        @if($inv->getRemainingAmount() > 0)
                        <span class="text-amber-600 font-medium">Rp {{ number_format($inv->getRemainingAmount(),0,',','.') }}</span>
                        @else
                        <span class="text-emerald-600 font-medium">Rp 0</span>
                        @endif
                    </td>
                    @endif
                    <td class="py-3 px-5 text-center">
                        @if($inv->status == 'paid') <span class="badge badge-green">Lunas</span>
                        @elseif($inv->status == 'partial') <span class="badge badge-yellow">Sebagian</span>
                        @elseif($inv->status == 'overdue') <span class="badge badge-red">Terlambat</span>
                        @elseif($inv->status == 'sent') <span class="badge badge-blue">Terkirim</span>
                        @else <span class="badge badge-gray">Draft</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-[12px] {{ $inv->due_date && $inv->isOverdue() ? 'text-red-500 font-semibold' : 'text-navy-500' }}">
                        <div class="flex items-center gap-1.5">
                            @if($inv->due_date && $inv->isOverdue()) <i class="fas fa-exclamation-circle text-[10px]"></i> @endif
                            {{ $inv->due_date ? $inv->due_date->format('d M Y') : '-' }}
                        </div>
                    </td>
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('invoices.show', $inv) }}" class="text-sky-600 text-[12px] font-medium hover:text-sky-700 transition inline-flex items-center gap-1">
                                <i class="fas fa-eye text-[10px]"></i> Detail
                            </a>
                            @if(!$isUser && $inv->status === 'draft')
                            <form method="POST" action="{{ route('invoices.send', $inv) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-emerald-600 text-[12px] font-medium hover:text-emerald-700 transition inline-flex items-center gap-1">
                                    <i class="fas fa-paper-plane text-[10px]"></i> Kirim
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $isUser ? '7' : '8' }}" class="py-14 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 bg-sky-50 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-file-invoice text-sky-300 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-[13px] font-medium text-navy-700">{{ $isUser ? 'Belum ada invoice' : 'Tidak ada data invoice' }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $isUser ? 'Invoice akan muncul setelah pemesanan dikonfirmasi' : '' }}</p>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $invoices->withQueryString()->links() }}</div>
</div>
@endsection
