@extends('layouts.dashboard')
@section('page-title', 'Inventaris Motor')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-navy-800 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm shadow-sm"><i class="fas fa-motorcycle"></i></span>
            Inventaris Motor
        </h2>
        <p class="text-xs text-gray-400 mt-0.5">Kelola armada motor rental yang tersedia di sistem.</p>
    </div>
    <div class="flex items-center gap-3 w-full sm:w-auto">
        <form action="{{ route('motors.index') }}" method="GET" class="relative flex-1 sm:flex-initial">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari motor, plat, merk..." class="border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none w-full sm:w-64 bg-white/80 backdrop-blur-sm">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
        </form>
        <a href="{{ route('vehicles.create', ['type' => 'motor']) }}" class="bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-md shadow-amber-500/20 flex items-center gap-1.5 whitespace-nowrap transition">
            <i class="fas fa-plus"></i> Tambah Motor
        </a>
    </div>
</div>

<div class="glass-card rounded-2xl overflow-hidden shadow-sm border border-amber-100/40">
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gradient-to-r from-amber-50/70 to-orange-50/70 border-b border-amber-100/60">
                    <th class="text-left py-3.5 px-5 text-gray-500 font-bold uppercase tracking-wider text-[10px]">Unit Motor</th>
                    <th class="text-left py-3.5 px-5 text-gray-500 font-bold uppercase tracking-wider text-[10px]">Plat Nomor</th>
                    <th class="text-left py-3.5 px-5 text-gray-500 font-bold uppercase tracking-wider text-[10px]">Tipe / Transmisi</th>
                    <th class="text-left py-3.5 px-5 text-gray-500 font-bold uppercase tracking-wider text-[10px]">Tarif / Hari</th>
                    <th class="text-center py-3.5 px-5 text-gray-500 font-bold uppercase tracking-wider text-[10px]">Kondisi</th>
                    <th class="text-center py-3.5 px-5 text-gray-500 font-bold uppercase tracking-wider text-[10px]">Status</th>
                    @if(auth()->user()->role === 'superadmin')
                    <th class="text-left py-3.5 px-5 text-gray-500 font-bold uppercase tracking-wider text-[10px]">Owner</th>
                    @endif
                    <th class="text-center py-3.5 px-5 text-gray-500 font-bold uppercase tracking-wider text-[10px]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($vehicles as $v)
                <tr class="hover:bg-amber-50/30 transition-colors">
                    <td class="py-3.5 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0 border border-amber-100/50 text-amber-600 shadow-sm">
                                <i class="fas fa-motorcycle text-sm"></i>
                            </div>
                            <div>
                                <p class="font-bold text-navy-800 text-[13px]">{{ $v->name }}</p>
                                <p class="text-[11px] text-gray-400 font-medium">{{ $v->brand ?? 'Honda' }} {{ $v->model }} {{ $v->year ? '• '.$v->year : '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-5 font-mono text-[12px] font-semibold text-navy-700">
                        <span class="bg-gray-100 px-2 py-0.5 rounded border border-gray-200/60">{{ $v->license_plate }}</span>
                    </td>
                    <td class="py-3.5 px-5 text-navy-600">
                        <span class="capitalize font-medium">{{ $v->transmission ?? 'Matic' }}</span>
                        <span class="text-gray-400 block text-[10px] uppercase">{{ $v->fuel_type ?? 'Bensin' }}</span>
                    </td>
                    <td class="py-3.5 px-5 font-bold text-navy-800 text-[13px]">
                        Rp {{ number_format($v->daily_price, 0, ',', '.') }}
                        @if($v->weekly_price)
                        <span class="text-[10px] text-gray-400 block font-normal">Mingguan: Rp {{ number_format($v->weekly_price, 0, ',', '.') }}</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-5 text-center">
                        @if($v->condition == 'excellent') <span class="badge badge-green">Sangat Baik</span>
                        @elseif($v->condition == 'good') <span class="badge badge-teal">Baik</span>
                        @elseif($v->condition == 'fair') <span class="badge badge-yellow">Cukup</span>
                        @else <span class="badge badge-red">Kurang</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-5 text-center">
                        @if($v->status == 'available') <span class="badge badge-green">Tersedia</span>
                        @elseif($v->status == 'rented') <span class="badge badge-yellow">Disewa</span>
                        @elseif($v->status == 'maintenance') <span class="badge badge-red">Maintenance</span>
                        @else <span class="badge badge-blue">Reservasi</span>
                        @endif
                    </td>
                    @if(auth()->user()->role === 'superadmin')
                    <td class="py-3.5 px-5 text-navy-600 font-medium">
                        {{ $v->owner->name ?? 'MariRent' }}
                    </td>
                    @endif
                    <td class="py-3.5 px-5 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('vehicles.edit', $v) }}" class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 flex items-center justify-center transition" title="Edit Motor">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            <form method="POST" action="{{ route('vehicles.destroy', $v) }}" class="inline" onsubmit="return confirm('Hapus motor {{ $v->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center transition" title="Hapus Motor">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->role === 'superadmin' ? '8' : '7' }}" class="py-12 text-center text-gray-400">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center text-amber-400 mb-2">
                                <i class="fas fa-motorcycle text-xl"></i>
                            </div>
                            <p class="font-medium text-navy-700">Belum ada data motor</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Klik tombol "Tambah Motor" di atas untuk menambahkan unit motor.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($vehicles->hasPages())
    <div class="px-5 py-3.5 border-t border-gray-100 bg-white/50">
        {{ $vehicles->links() }}
    </div>
    @endif
</div>
@endsection
