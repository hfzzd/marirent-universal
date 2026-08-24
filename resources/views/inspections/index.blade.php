@extends('layouts.dashboard')
@section('page-title', 'Inspeksi')

@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('inspections.index') }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ !request('type') && !request('scope') ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Semua</a>
        <a href="{{ route('inspections.index', ['type' => 'pre_rental']) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('type') == 'pre_rental' ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Inspeksi Awal</a>
        <a href="{{ route('inspections.index', ['type' => 'post_rental']) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('type') == 'post_rental' ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Inspeksi Akhir</a>
        <span class="w-px bg-gray-200 mx-1"></span>
        <a href="{{ route('inspections.index', ['scope' => 'kendaraan']) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('scope') == 'kendaraan' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}"><i class="fas fa-car mr-1"></i> Kendaraan</a>
        <a href="{{ route('inspections.index', ['scope' => 'elektronik']) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('scope') == 'elektronik' ? 'bg-violet-500 text-white shadow-lg shadow-violet-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}"><i class="fas fa-mobile-alt mr-1"></i> HP / Kamera</a>
        <a href="{{ route('inspections.index', ['scope' => 'camping']) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ request('scope') == 'camping' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}"><i class="fas fa-campground mr-1"></i> Tenda</a>
    </div>
    <a href="{{ route('inspections.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-[13px] font-medium"><i class="fas fa-plus mr-1.5"></i> Inspeksi Baru</a>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Unit / Barang</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Jenis</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kondisi</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Lama Pakai</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kerusakan</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Foto</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Inspektur</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Tanggal</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inspections as $i)
                @php $totalDamages = count(array_merge($i->damage_items ?? [], is_array($i->damages) ? $i->damages : [])); @endphp
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30">
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 {{ $i->scope === 'elektronik' ? 'bg-violet-50 text-violet-500' : ($i->scope === 'camping' ? 'bg-emerald-50 text-emerald-500' : 'bg-sky-50 text-sky-500') }} rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas {{ $i->scope === 'elektronik' ? 'fa-camera' : ($i->scope === 'camping' ? 'fa-campground' : 'fa-car') }} text-[10px]"></i>
                            </div>
                            <div>
                                <p class="font-medium text-navy-800">{{ $i->getItemName() }}</p>
                                <span class="text-[10px] text-gray-400">{{ $i->getScopeLabel() }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-5">
                        @if($i->type == 'pre_rental') <span class="badge badge-blue">Awal</span>
                        @else <span class="badge badge-yellow">Akhir</span>
                        @endif
                    </td>
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-2">
                            <div class="w-16 bg-gray-100 rounded-full h-1.5"><div class="bg-sky-500 h-1.5 rounded-full" style="width:{{ $i->overall_condition * 10 }}%"></div></div>
                            <span class="text-[11px] font-medium text-navy-600">{{ $i->overall_condition }}/10</span>
                        </div>
                    </td>
                    <td class="py-3 px-5 text-[12px] text-navy-600">{{ $i->getUsageDurationLabel() }}</td>
                    <td class="py-3 px-5 text-center">
                        @if($totalDamages > 0) <span class="badge badge-red">{{ $totalDamages }} item</span>
                        @else <span class="badge badge-green">Aman</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-center">
                        @if($i->photos && count($i->photos)) <span class="badge badge-teal"><i class="fas fa-camera mr-1"></i>{{ count($i->photos) }}</span>
                        @else <span class="text-gray-300 text-[11px]">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-[12px] text-navy-600">{{ $i->inspector?->name ?? '-' }}</td>
                    <td class="py-3 px-5 text-[12px] text-navy-500">{{ $i->created_at->format('d M Y') }}</td>
                    <td class="py-3 px-5"><a href="{{ route('inspections.show', $i) }}" class="text-sky-600 text-[12px] font-medium">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="9" class="py-10 text-center text-gray-300">Belum ada inspeksi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $inspections->links() }}</div>
</div>
@endsection
