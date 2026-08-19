@extends('layouts.dashboard')
@section('page-title', 'Inspeksi Kendaraan')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div class="flex gap-2">
        <a href="{{ route('inspections.index') }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ !request('type') ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Semua</a>
        <a href="{{ route('inspections.index', ['type' => 'pre_rental']) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('type') == 'pre_rental' ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Pre-Rental</a>
        <a href="{{ route('inspections.index', ['type' => 'post_rental']) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('type') == 'post_rental' ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Post-Rental</a>
    </div>
    <a href="{{ route('inspections.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-[13px] font-medium"><i class="fas fa-plus mr-1.5"></i> Inspeksi Baru</a>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kendaraan</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Tipe</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kondisi</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Bakar</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Inspektur</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Tanggal</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inspections as $i)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30">
                    <td class="py-3 px-5 font-medium text-navy-800">{{ $i->vehicle->name }}</td>
                    <td class="py-3 px-5">
                        @if($i->type == 'pre_rental') <span class="badge badge-blue">Sebelum</span>
                        @else <span class="badge badge-yellow">Sesudah</span>
                        @endif
                    </td>
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-2">
                            <div class="w-16 bg-gray-100 rounded-full h-1.5"><div class="bg-sky-500 h-1.5 rounded-full" style="width:{{ $i->overall_condition * 10 }}%"></div></div>
                            <span class="text-[11px] font-medium text-navy-600">{{ $i->overall_condition }}/10</span>
                        </div>
                    </td>
                    <td class="py-3 px-5 text-[12px] text-navy-600">{{ $i->fuel_level }}%</td>
                    <td class="py-3 px-5 text-[12px] text-navy-600">{{ $i->inspector->name }}</td>
                    <td class="py-3 px-5 text-[12px] text-navy-500">{{ $i->created_at->format('d M Y') }}</td>
                    <td class="py-3 px-5"><a href="{{ route('inspections.show', $i) }}" class="text-sky-600 text-[12px] font-medium">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-10 text-center text-gray-300">Belum ada inspeksi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $inspections->links() }}</div>
</div>
@endsection
