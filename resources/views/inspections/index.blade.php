@extends('layouts.dashboard')
@section('page-title', 'Riwayat Inspeksi')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('inspections.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !request('type') ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-emerald-300 hover:text-emerald-600' }}">Semua</a>
        <a href="{{ route('inspections.index', ['type' => 'pre_rental']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('type') == 'pre_rental' ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Pre-Rental</a>
        <a href="{{ route('inspections.index', ['type' => 'post_rental']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('type') == 'post_rental' ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-amber-300 hover:text-amber-600' }}">Post-Rental</a>
    </div>
    <a href="{{ route('inspections.create') }}" class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-4 py-2 rounded-lg text-sm font-medium"><i class="fas fa-plus mr-1"></i> Inspeksi Baru</a>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-emerald-50/50 border-b border-emerald-100/50">
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Item</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Booking</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Tipe</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Scope</th>
                <th class="text-center py-3 px-4 text-navy-500 font-medium">Kondisi</th>
                <th class="text-center py-3 px-4 text-navy-500 font-medium">Kerusakan</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Inspektur</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Tanggal</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($inspections as $i)
                <tr class="border-b hover:bg-emerald-50/30">
                    <td class="py-3 px-4 font-medium text-navy-800">{{ $i->getItemName() }}</td>
                    <td class="py-3 px-4 text-sky-600 font-medium text-xs">{{ $i->booking->booking_code ?? '-' }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $i->type == 'pre_rental' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700' }}">{{ $i->getTypeLabel() }}</span>
                    </td>
                    <td class="py-3 px-4">
                        @php
                            $sColors = ['kendaraan' => 'bg-blue-100 text-blue-700', 'elektronik' => 'bg-purple-100 text-purple-700', 'camping' => 'bg-green-100 text-green-700'];
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $sColors[$i->scope] ?? 'bg-gray-100 text-gray-700' }}">{{ $i->getScopeLabel() }}</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="font-bold">{{ $i->overall_condition ?? '-' }}/10</span>
                        <span class="text-[10px] text-gray-400 block">{{ $i->getConditionLabel() }}</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        @if(!empty($i->damage_items) && count($i->damage_items) > 0)
                            <span class="badge badge-red text-[10px]">{{ count($i->damage_items) }} Temuan</span>
                        @else
                            <span class="badge badge-green text-[10px]">Aman</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-xs text-navy-600">{{ $i->inspector->name ?? '-' }}</td>
                    <td class="py-3 px-4 text-xs text-navy-500">{{ $i->created_at->format('d M Y') }}</td>
                    <td class="py-3 px-4">
                        <a href="{{ route('inspections.show', $i) }}" class="text-emerald-600 text-xs font-medium"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="py-8 text-center text-navy-400">Belum ada data inspeksi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $inspections->links() }}</div>
</div>
@endsection
