@extends('layouts.dashboard')
@section('page-title', 'Elektronik & Alat - ' . ucfirst($type))

@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('superadmin.elektronik.type', 'kamera') }}"
           class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ $type == 'kamera' ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">
            <i class="fas fa-camera mr-1"></i> Kamera ({{ $counts['kamera'] }})
        </a>
        <a href="{{ route('superadmin.elektronik.type', 'hp') }}"
           class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ $type == 'hp' ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">
            <i class="fas fa-mobile-alt mr-1"></i> HP ({{ $counts['hp'] }})
        </a>
        <a href="{{ route('superadmin.elektronik.type', 'tenda') }}"
           class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ $type == 'tenda' ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">
            <i class="fas fa-campground mr-1"></i> Tenda ({{ $counts['tenda'] }})
        </a>
    </div>
    <div class="flex gap-2">
        <form action="{{ route('superadmin.elektronik.type', $type) }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari barang..." class="border border-gray-200 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none w-52">
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg text-[13px] transition"><i class="fas fa-search text-gray-500"></i></button>
        </form>
        <a href="{{ route('superadmin.elektronik.create', $type) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-[13px] font-semibold shadow-lg shadow-sky-500/25 transition flex items-center gap-1.5">
            <i class="fas fa-plus text-[11px]"></i> Tambah
        </a>
    </div>
</div>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-4 text-[13px] flex items-center gap-2">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Barang</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kategori</th>
                    @if($type == 'kamera')
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Sensor</th>
                    @elseif($type == 'hp')
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Storage</th>
                    @else
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kapasitas</th>
                    @endif
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Harga/Hari</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kondisi</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aktif</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30">
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 {{ $type == 'kamera' ? 'bg-violet-50' : ($type == 'tenda' ? 'bg-emerald-50' : 'bg-blue-50') }} rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
                                @if($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                @else
                                    @if($type == 'kamera') <i class="fas fa-camera text-violet-500 text-sm"></i>
                                    @elseif($type == 'tenda') <i class="fas fa-campground text-emerald-500 text-sm"></i>
                                    @else <i class="fas fa-mobile-alt text-blue-500 text-sm"></i>
                                    @endif
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-navy-800">{{ $item->name }}</p>
                                <p class="text-[11px] text-gray-400">{{ $item->brand }} @if($type == 'kamera') {{ $item->camera_model ?? '' }} @elseif($type == 'hp') {{ $item->phone_model ?? '' }} @else {{ $item->equipment_model ?? '' }} @endif</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-5 text-navy-600">{{ $item->category->name ?? '-' }}</td>
                    @if($type == 'kamera')
                    <td class="py-3 px-5 text-navy-600 text-[12px]">{{ $item->sensor_size ?? '-' }}</td>
                    @elseif($type == 'hp')
                    <td class="py-3 px-5 text-navy-600 text-[12px]">{{ $item->storage_capacity ?? '-' }} / {{ $item->ram ?? '-' }}</td>
                    @else
                    <td class="py-3 px-5 text-navy-600 text-[12px]">{{ $item->capacity ?? '-' }} orang</td>
                    @endif
                    <td class="py-3 px-5 font-medium text-navy-700">Rp {{ number_format($item->daily_price, 0, ',', '.') }}</td>
                    <td class="py-3 px-5">
                        @if($item->condition == 'excellent') <span class="badge badge-green">Sangat Baik</span>
                        @elseif($item->condition == 'good') <span class="badge badge-teal">Baik</span>
                        @elseif($item->condition == 'fair') <span class="badge badge-yellow">Cukup</span>
                        @else <span class="badge badge-red">Kurang</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-center">
                        @if($item->status == 'available') <span class="badge badge-green">Tersedia</span>
                        @elseif($item->status == 'rented') <span class="badge badge-yellow">Disewa</span>
                        @elseif($item->status == 'maintenance') <span class="badge badge-red">Maintenance</span>
                        @else <span class="badge badge-blue">Reservasi</span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-center">
                        @if($item->is_active) <span class="badge badge-green">Aktif</span>
                        @else <span class="badge badge-red">Nonaktif</span>
                        @endif
                    </td>
                    <td class="py-3 px-5">
                        <a href="{{ route('superadmin.elektronik.edit', [$type, $item->id]) }}" class="text-blue-500 hover:text-blue-700 mr-2" title="Edit"><i class="fas fa-edit text-sm"></i></a>
                        <form method="POST" action="{{ route('superadmin.elektronik.destroy', [$type, $item->id]) }}" class="inline" onsubmit="return confirm('Hapus {{ ucfirst($type) }} ini?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600" title="Hapus"><i class="fas fa-trash text-sm"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-12 text-center text-gray-300">
                    <div class="flex flex-col items-center gap-2">
                        <i class="fas fa-box-open text-3xl text-gray-200"></i>
                        <p>Belum ada data {{ ucfirst($type) }}</p>
                        <a href="{{ route('superadmin.elektronik.create', $type) }}" class="text-sky-500 text-[12px] font-medium hover:underline">+ Tambah {{ ucfirst($type) }}</a>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $items->withQueryString()->links() }}</div>
</div>
@endsection
