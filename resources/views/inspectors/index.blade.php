@extends('layouts.dashboard')
@section('page-title', 'Akun Inspektur')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 gap-3">
    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
        <form action="{{ route('inspectors.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email / HP..." class="border border-gray-200 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none w-full sm:w-60">
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg text-[13px] transition flex-shrink-0"><i class="fas fa-search text-gray-500"></i></button>
        </form>
        @if(isset($companies) && $companies->isNotEmpty())
        <form action="{{ route('inspectors.index') }}" method="GET" class="flex gap-2">
            <select name="company_id" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2 text-[13px] bg-white outline-none">
                <option value="">Semua Company</option>
                @foreach($companies as $c)
                <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->city ?? '-' }})</option>
                @endforeach
            </select>
        </form>
        @endif
    </div>
    <a href="{{ route('inspectors.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-[13px] font-medium text-center"><i class="fas fa-plus mr-1.5"></i> Tambah Inspektur</a>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Nama / Akun</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Asal Company</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">No. HP</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inspectors as $i)
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30">
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-emerald-700 font-bold text-[11px]">{{ strtoupper(substr($i->name ?? '', 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-navy-800">{{ $i->name }}</p>
                                <p class="text-[11px] text-gray-400">{{ $i->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-5">
                        <span class="text-[12px] font-medium text-navy-700">{{ $i->merchant?->company?->name ?? 'Tanpa company' }}</span>
                        @if($i->merchant?->company?->city)
                        <p class="text-[11px] text-gray-400">{{ $i->merchant->company->city }}</p>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-[12px] text-navy-600">{{ $i->phone ?? '-' }}</td>
                    <td class="py-3 px-5 text-center">
                        @if($i->is_active) <span class="badge badge-green">Aktif</span>
                        @else <span class="badge badge-gray">Nonaktif</span>
                        @endif
                    </td>
                    <td class="py-3 px-5">
                        <a href="{{ route('inspectors.edit', $i) }}" class="text-blue-500 hover:text-blue-700 mr-2" title="Edit"><i class="fas fa-edit text-sm"></i></a>
                        <form method="POST" action="{{ route('inspectors.destroy', $i) }}" class="inline" onsubmit="return confirm('Hapus akun {{ $i->name }}? Tindakan ini tidak bisa dibatalkan.')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600" title="Hapus"><i class="fas fa-trash text-sm"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-10 text-center text-gray-300">Belum ada akun inspektur</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $inspectors->links() }}</div>
</div>
@endsection