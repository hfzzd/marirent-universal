@extends('layouts.dashboard')
@section('page-title', 'Laporan Perjalanan')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div class="flex gap-2">
        <a href="{{ route('reports.index') }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ !request('status') ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Semua</a>
        <a href="{{ route('reports.index', ['status' => 'completed']) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('status') == 'completed' ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Selesai</a>
        <a href="{{ route('reports.index', ['status' => 'has_issues']) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('status') == 'has_issues' ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Bermasalah</a>
    </div>
    <a href="{{ route('reports.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-[13px] font-medium"><i class="fas fa-plus mr-1.5"></i> Buat Laporan</a>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Booking</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kendaraan</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Jarak</th>
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Biaya Operasional</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $r)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30">
                    <td class="py-3 px-5 font-medium text-sky-600">{{ $r->booking->booking_code ?? '-' }}</td>
                    <td class="py-3 px-5 text-navy-700">{{ $r->vehicle->name }}</td>
                    <td class="py-3 px-5 text-navy-600">{{ $r->total_distance ? number_format($r->total_distance, 1) . ' km' : '-' }}</td>
                    <td class="py-3 px-5 text-right font-medium text-navy-700">Rp {{ number_format($r->total_operational_cost,0,',','.') }}</td>
                    <td class="py-3 px-5 text-center">
                        @if($r->status == 'completed') <span class="badge badge-green">Selesai</span>
                        @elseif($r->status == 'has_issues') <span class="badge badge-red">Bermasalah</span>
                        @else <span class="badge badge-yellow">Berlangsung</span>
                        @endif
                    </td>
                    <td class="py-3 px-5"><a href="{{ route('reports.show', $r) }}" class="text-sky-600 text-[12px] font-medium">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-10 text-center text-gray-300">Belum ada laporan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $reports->links() }}</div>
</div>
@endsection
