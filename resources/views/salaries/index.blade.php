@extends('layouts.dashboard')
@section('page-title', 'Penggajian Driver')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 gap-3">
    <div class="flex flex-col sm:flex-row gap-2 flex-wrap">
        <form action="{{ route('salaries.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama driver..." class="border border-gray-200 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none w-52">
            @if(request('company_id'))<input type="hidden" name="company_id" value="{{ request('company_id') }}">@endif
            @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
            @if(request('period_month'))<input type="hidden" name="period_month" value="{{ request('period_month') }}">@endif
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg text-[13px] transition flex-shrink-0"><i class="fas fa-search text-gray-500"></i></button>
        </form>
        @if(isset($companies) && $companies->isNotEmpty())
        <form action="{{ route('salaries.index') }}" method="GET" class="flex gap-2">
            <select name="company_id" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2 text-[13px] bg-white outline-none">
                <option value="">Semua Company</option>
                @foreach($companies as $c)
                <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->city ?? '-' }})</option>
                @endforeach
            </select>
        </form>
        @endif
        <input type="month" name="period_month" value="{{ request('period_month') }}" onchange="const url=new URL(window.location.href); url.searchParams.set('period_month', this.value); window.location=url;" class="border border-gray-200 rounded-lg px-3 py-2 text-[13px] bg-white outline-none">
    </div>
    <a href="{{ route('salaries.create', request()->has('company_id') ? ['company_id' => request('company_id')] : []) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-[13px] font-medium text-center"><i class="fas fa-plus mr-1.5"></i> Input Gaji</a>
</div>

<div class="flex gap-2 flex-wrap mb-4">
    @foreach(['','draft','approved','paid'] as $s)
    <a href="{{ route('salaries.index', array_merge(request()->query(), ['status' => $s ?: null])) }}"
       class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('status', '') == $s ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">
        {{ $s == '' ? 'Semua' : ucfirst($s) }}
    </a>
    @endforeach
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Driver</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Asal Company</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Periode</th>
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Gaji Pokok</th>
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Bonus</th>
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Total</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salaries as $s)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30">
                    <td class="py-3 px-5 font-medium text-navy-800">{{ $s->driver?->user?->name }}</td>
                    <td class="py-3 px-5 text-[12px] text-navy-600">{{ $s->driver?->company?->name ?? ($s->owner?->company?->name ?? '-') }}</td>
                    <td class="py-3 px-5 text-[12px] text-navy-600">{{ $s->period_month }}</td>
                    <td class="py-3 px-5 text-right text-navy-600">Rp {{ number_format($s->base_salary,0,',','.') }}</td>
                    <td class="py-3 px-5 text-right text-navy-600">Rp {{ number_format($s->trip_bonus + $s->overtime_pay,0,',','.') }}</td>
                    <td class="py-3 px-5 text-right font-bold text-sky-600">Rp {{ number_format($s->total_salary,0,',','.') }}</td>
                    <td class="py-3 px-5 text-center">
                        @if($s->status == 'draft') <span class="badge badge-gray">Draft</span>
                        @elseif($s->status == 'approved') <span class="badge badge-teal">Disetujui</span>
                        @else <span class="badge badge-green">Dibayar</span>
                        @endif
                    </td>
                    <td class="py-3 px-5">
                        <a href="{{ route('salaries.show', $s) }}" class="text-sky-600 text-[12px] font-medium mr-2">Detail</a>
                        @if($s->status == 'draft')
                        <form method="POST" action="{{ route('salaries.approve', $s) }}" class="inline">@csrf
                            <button class="text-emerald-600 text-[12px] font-medium mr-2">Setuju</button>
                        </form>
                        @endif
                        @if($s->status == 'approved')
                        <form method="POST" action="{{ route('salaries.pay', $s) }}" class="inline">@csrf
                            <button class="text-blue-600 text-[12px] font-medium">Bayar</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-10 text-center text-gray-300">Belum ada data gaji</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $salaries->links() }}</div>
</div>
@endsection
