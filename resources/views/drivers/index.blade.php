@extends('layouts.dashboard')
@section('page-title', 'Driver & Karyawan')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 gap-3">
    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
        <form action="{{ route('drivers.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email / SIM..." class="border border-gray-200 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none w-full sm:w-60">
            <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2 text-[13px] bg-white outline-none">
                <option value="">Semua Status</option>
                @foreach(['off_duty','on_duty','on_trip','active','inactive'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg text-[13px] transition flex-shrink-0"><i class="fas fa-search text-gray-500"></i></button>
        </form>
        @if(isset($companies) && $companies->isNotEmpty())
        <form action="{{ route('drivers.index') }}" method="GET" class="flex gap-2">
            <select name="company_id" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2 text-[13px] bg-white outline-none">
                <option value="">Semua Company</option>
                @foreach($companies as $c)
                <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->city ?? '-' }})</option>
                @endforeach
            </select>
        </form>
        @endif
    </div>
    <a href="{{ route('drivers.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-[13px] font-medium text-center"><i class="fas fa-plus mr-1.5"></i> Tambah Driver / Karyawan</a>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Nama / Akun</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Asal Company</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Posisi</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">SIM</th>
                    <th class="text-right py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Gaji/Hari</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $d)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30">
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-sky-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-sky-700 font-bold text-[11px]">{{ strtoupper(substr($d->user?->name ?? '', 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-navy-800">{{ $d->user?->name }}</p>
                                <p class="text-[11px] text-gray-400">{{ $d->user?->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-5">
                        <span class="text-[12px] font-medium text-navy-700">{{ $d->company?->name ?? 'Tanpa company' }}</span>
                        @if($d->company?->city)
                        <p class="text-[11px] text-gray-400">{{ $d->company->city }}</p>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-[12px] text-navy-600">{{ $d->position ?? '-' }}</td>
                    <td class="py-3 px-5 text-[12px] text-navy-600">{{ $d->license_number ?? '-' }}</td>
                    <td class="py-3 px-5 text-right font-medium text-navy-700">Rp {{ number_format($d->daily_salary,0,',','.') }}</td>
                    <td class="py-3 px-5 text-center">
                        @if(!$d->is_active || $d->status == 'inactive') <span class="badge badge-gray">Nonaktif</span>
                        @elseif($d->status == 'on_trip') <span class="badge badge-yellow">Bertugas</span>
                        @elseif($d->status == 'on_duty') <span class="badge badge-blue">Di Tugaskan</span>
                        @else <span class="badge badge-green">Aktif</span>
                        @endif
                    </td>
                    <td class="py-3 px-5">
                        <a href="{{ route('drivers.show', $d) }}" class="text-sky-500 hover:text-sky-700 mr-2" title="Lihat"><i class="fas fa-eye text-sm"></i></a>
                        <a href="{{ route('drivers.edit', $d) }}" class="text-blue-500 hover:text-blue-700 mr-2" title="Edit"><i class="fas fa-edit text-sm"></i></a>
                        <form method="POST" action="{{ route('drivers.destroy', $d) }}" class="inline" onsubmit="return confirm('Hapus akun {{ $d->user?->name }}? Tindakan ini tidak bisa dibatalkan.')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600" title="Hapus"><i class="fas fa-trash text-sm"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-10 text-center text-gray-300">Belum ada driver / karyawan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $drivers->links() }}</div>
</div>
@endsection