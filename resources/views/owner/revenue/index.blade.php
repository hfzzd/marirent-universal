@extends('layouts.dashboard')
@section('title', 'Pendapatan Saya - MariRent')
@section('page-title', 'Pendapatan Manual')

@section('content')
@php $owner = auth()->user(); @endphp

<div class="mb-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-[14px] font-semibold text-navy-800">Pendapatan Manual Owner</h2>
            <p class="text-[11px] text-gray-400 mt-0.5">Catat pendapatan per kategori produk secara manual</p>
        </div>
        <a href="{{ route('owner.revenue.create') }}" class="btn-primary text-white px-4 py-2 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-1.5">
            <i class="fas fa-plus text-[10px]"></i> Catat Pendapatan
        </a>
    </div>
</div>

{{-- Category Revenue Summary --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
    @foreach($revenuePerCategory as $catId => $cat)
    <a href="{{ route('owner.revenue.index', ['category_id' => $catId]) }}" class="glass-card rounded-2xl p-4 border border-sky-100/50 shadow-sm hover:shadow-md transition-all group {{ request('category_id') == $catId ? 'ring-2 ring-sky-400' : '' }}">
        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">{{ $cat['name'] }}</p>
        <p class="text-lg font-black text-navy-800">Rp {{ number_format($cat['total'], 0, ',', '.') }}</p>
        @if($cat['pending'] > 0)
        <p class="text-[10px] text-amber-500 font-medium mt-1">Belum dibayar: Rp {{ number_format($cat['pending'], 0, ',', '.') }}</p>
        @else
        <p class="text-[10px] text-emerald-500 font-medium mt-1">Lunas semua</p>
        @endif
    </a>
    @endforeach
</div>

{{-- Total Summary --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="glass-card rounded-2xl p-4 border border-sky-100/50">
        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Pendapatan Lunas</p>
        <p class="text-xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
    </div>
    <div class="glass-card rounded-2xl p-4 border border-sky-100/50">
        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Belum Dibayar</p>
        <p class="text-xl font-black text-amber-500 mt-1">Rp {{ number_format($totalPending, 0, ',', '.') }}</p>
    </div>
    <div class="glass-card rounded-2xl p-4 border border-sky-100/50">
        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Seluruh</p>
        <p class="text-xl font-black text-navy-800 mt-1">Rp {{ number_format($totalRevenue + $totalPending, 0, ',', '.') }}</p>
    </div>
</div>

{{-- Komisi Platform (Marketplace) --}}
<div class="glass-card rounded-2xl p-5 mb-6 border border-sky-100/50" style="border-left: 4px solid #0ea5e9;">
    <div class="flex items-center gap-2 mb-3">
        <i class="fas fa-store text-sky-500"></i>
        <h3 class="text-[13px] font-bold text-navy-800">Revenue Marketplace & Komisi Platform</h3>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-sky-50 rounded-2xl p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Pendapatan Lunas</p>
            <p class="text-lg font-black text-navy-800 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-amber-50 rounded-2xl p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600/70">Komisi Platform</p>
            <p class="text-lg font-black text-amber-600 mt-1">Rp {{ number_format($platformFee, 0, ',', '.') }}</p>
        </div>
        <div class="bg-emerald-50 rounded-2xl p-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600/70">Pendapatan Bersih Anda</p>
            <p class="text-lg font-black text-emerald-600 mt-1">Rp {{ number_format($netRevenue, 0, ',', '.') }}</p>
        </div>
    </div>
    <p class="text-[10px] text-gray-400 mt-2.5"><i class="fas fa-info-circle mr-1"></i> Komisi platform dipotong otomatis dari invoice lunas berdasarkan tarif toko Anda.</p>
</div>

{{-- Filters --}}
<div class="glass-card rounded-2xl p-4 mb-5 border border-sky-100/50 shadow-sm">
    <form action="{{ route('owner.revenue.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
        <div class="flex-1 w-full">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Kategori</label>
            <select name="category_id" class="border border-gray-200 rounded-xl px-3 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50 min-w-[140px]">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Status</label>
            <select name="status" class="border border-gray-200 rounded-xl px-3 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Terkirim</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Sebagian</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2.5 rounded-xl text-[12px] font-semibold shadow-sm">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['category_id','status']))
            <a href="{{ route('owner.revenue.index') }}" class="bg-red-50 hover:bg-red-100 text-red-500 px-3 py-2.5 rounded-xl text-[12px] font-medium transition border border-red-100">
                <i class="fas fa-times text-[10px]"></i> Reset
            </a>
            @endif
        </div>
    </form>
</div>

{{-- Invoice Table --}}
<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Nomor Invoice</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kategori</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Deskripsi</th>
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Total</th>
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Dibayar</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30 transition-colors">
                    <td class="py-3 px-5 font-medium text-sky-600">{{ $inv->invoice_number }}</td>
                    <td class="py-3 px-5">
                        <span class="bg-sky-50 text-sky-600 px-2.5 py-1 rounded-lg text-[11px] font-semibold">{{ $inv->category->name ?? '-' }}</span>
                    </td>
                    <td class="py-3 px-5 text-navy-700">{{ Str::limit($inv->items->first()->description ?? '-', 50) }}</td>
                    <td class="py-3 px-5 text-right font-bold text-navy-800">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                    <td class="py-3 px-5 text-right text-navy-600">Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</td>
                    <td class="py-3 px-5 text-center">
                        @if($inv->status == 'paid') <span class="badge badge-green">Lunas</span>
                        @elseif($inv->status == 'partial') <span class="badge badge-yellow">Sebagian</span>
                        @elseif($inv->status == 'sent') <span class="badge badge-blue">Terkirim</span>
                        @else <span class="badge badge-gray">Draft</span>
                        @endif
                    </td>
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('owner.revenue.show', $inv) }}" class="text-sky-600 text-[12px] font-medium hover:text-sky-700 transition inline-flex items-center gap-1">
                                <i class="fas fa-eye text-[10px]"></i> Detail
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-14 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 bg-sky-50 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-hand-holding-dollar text-sky-300 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-[13px] font-medium text-navy-700">Belum ada pendapatan manual</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Mulai catat pendapatan dari katalog produk</p>
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
