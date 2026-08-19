@extends('layouts.dashboard')
@section('page-title', 'Kendaraan')

@section('content')
<div class="flex items-center justify-between mb-5">
    <form action="{{ route('vehicles.index') }}" method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kendaraan..." class="border border-gray-200 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none w-60">
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg text-[13px] transition"><i class="fas fa-search text-gray-500"></i></button>
    </form>
    <a href="{{ route('vehicles.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-[13px] font-medium"><i class="fas fa-plus mr-1.5"></i> Tambah</a>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kendaraan</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kategori</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Plat</th>
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Harga/Hari</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $v)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30">
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-3">
                            @php $cat = $v->category->slug ?? ''; @endphp
                            <div class="w-9 h-9 bg-gray-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                @if($cat == 'mobil') <i class="fas fa-car text-blue-500 text-sm"></i>
                                @elseif($cat == 'motor') <i class="fas fa-motorcycle text-amber-500 text-sm"></i>
                                @elseif($cat == 'sewa-kamera') <i class="fas fa-camera text-violet-500 text-sm"></i>
                                @else <i class="fas fa-campground text-emerald-500 text-sm"></i>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-navy-800">{{ $v->name }}</p>
                                <p class="text-[11px] text-gray-400">{{ $v->brand }} {{ $v->model }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-5 text-navy-600">{{ $v->category->name }}</td>
                    <td class="py-3 px-5 font-mono text-[12px] text-navy-600">{{ $v->license_plate }}</td>
                    <td class="py-3 px-5 text-right font-medium text-navy-700">Rp {{ number_format($v->daily_price,0,',','.') }}</td>
                    <td class="py-3 px-5 text-center">
                        @if($v->status == 'available') <span class="badge badge-green">Tersedia</span>
                        @elseif($v->status == 'rented') <span class="badge badge-yellow">Disewa</span>
                        @elseif($v->status == 'maintenance') <span class="badge badge-red">Maintenance</span>
                        @else <span class="badge badge-blue">Reservasi</span>
                        @endif
                    </td>
                    <td class="py-3 px-5">
                        <a href="{{ route('vehicles.edit', $v) }}" class="text-blue-500 hover:text-blue-700 mr-2"><i class="fas fa-edit text-sm"></i></a>
                        <form method="POST" action="{{ route('vehicles.destroy', $v) }}" class="inline" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600"><i class="fas fa-trash text-sm"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-10 text-center text-gray-300">Belum ada kendaraan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $vehicles->links() }}</div>
</div>
@endsection
