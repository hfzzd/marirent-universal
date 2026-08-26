@extends('layouts.dashboard')
@section('page-title', 'Penggajian Driver')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div class="flex gap-2">
        @foreach(['','draft','approved','paid'] as $s)
        <a href="{{ route('salaries.index', array_merge(request()->query(), ['status' => $s ?: null])) }}"
           class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('status', '') == $s ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">
            {{ $s == '' ? 'Semua' : ucfirst($s) }}
        </a>
        @endforeach
    </div>
    <a href="{{ route('salaries.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-[13px] font-medium"><i class="fas fa-plus mr-1.5"></i> Input Gaji</a>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Driver</th>
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
                <tr><td colspan="7" class="py-10 text-center text-gray-300">Belum ada data gaji</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $salaries->links() }}</div>
</div>
@endsection
