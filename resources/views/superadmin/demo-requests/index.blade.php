@extends('layouts.dashboard')
@section('title', 'Jadwal Demo - MariRent')
@section('page-title', 'Permintaan Jadwal Demo')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
    <div>
        <h2 class="text-lg font-extrabold text-navy-800 flex items-center gap-2">
            <i class="fas fa-calendar-check text-sky-500"></i> Permintaan Jadwal Demo
            @if($pendingCount > 0)<span class="bg-red-500 text-white text-[11px] font-bold px-2.5 py-1 rounded-full">{{ $pendingCount }} menunggu</span>@endif
        </h2>
        <p class="text-xs text-gray-400 mt-0.5">Tindak lanjuti via WhatsApp, lalu ubah statusnya.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @foreach(['all' => 'Semua', 'pending' => 'Menunggu', 'contacted' => 'Dihubungi', 'scheduled' => 'Terjadwal', 'done' => 'Selesai', 'cancelled' => 'Batal'] as $key => $label)
        <a href="{{ route('superadmin.demo-requests', array_merge(request()->query(), ['status' => $key])) }}" class="px-3.5 py-2 rounded-xl text-[12px] font-bold transition {{ $status === $key ? 'bg-sky-600 text-white shadow-md shadow-sky-500/20' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

{{-- Pencarian --}}
<div class="glass-card rounded-2xl p-4 mb-5 border border-sky-100/50 shadow-sm">
    <form action="{{ route('superadmin.demo-requests') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
        <div class="flex-1 w-full">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cari</label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-[11px]"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama pemohon, bisnis, telepon, email..."
                    class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50">
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2.5 rounded-xl text-[12px] font-semibold shadow-sm">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request('search'))
            <a href="{{ route('superadmin.demo-requests') }}" class="bg-red-50 hover:bg-red-100 text-red-500 px-3 py-2.5 rounded-xl text-[12px] font-medium transition border border-red-100">
                <i class="fas fa-times text-[10px]"></i> Reset
            </a>
            @endif
        </div>
    </form>
</div>

<div class="glass-card rounded-2xl overflow-hidden border border-sky-100/50 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px] min-w-[820px]">
            <thead>
                <tr class="bg-sky-50/60 border-b border-sky-100 text-[10px] uppercase tracking-wider font-bold text-gray-500">
                    <th class="py-3 px-5 text-left">Pemohon</th>
                    <th class="py-3 px-5 text-left">Kontak</th>
                    <th class="py-3 px-5 text-left">Jadwal Diminta</th>
                    <th class="py-3 px-5 text-center">Status</th>
                    <th class="py-3 px-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($demos as $demo)
                @php
                    $wa = preg_replace('/[^0-9]/', '', $demo->phone ?? '');
                    if (str_starts_with($wa, '0')) $wa = '62' . substr($wa, 1);
                    $badge = match($demo->status) {
                        'pending' => 'badge-yellow', 'contacted' => 'badge-blue', 'scheduled' => 'badge-teal',
                        'done' => 'badge-green', 'cancelled' => 'badge-red', default => 'badge-gray',
                    };
                @endphp
                <tr class="hover:bg-sky-50/40 transition">
                    <td class="py-3.5 px-5">
                        <p class="font-bold text-navy-800">{{ $demo->name }}</p>
                        <p class="text-[11px] text-gray-400">{{ $demo->business_name ?? '—' }} • {{ $demo->created_at->format('d M Y H:i') }}</p>
                        @if($demo->notes)<p class="text-[11px] text-gray-500 mt-1 italic">“{{ \Illuminate\Support\Str::limit($demo->notes, 90) }}”</p>@endif
                    </td>
                    <td class="py-3.5 px-5">
                        <p class="font-mono text-[12px] text-navy-700">{{ $demo->phone }}</p>
                        <p class="text-[11px] text-gray-400 truncate max-w-[180px]">{{ $demo->email }}</p>
                    </td>
                    <td class="py-3.5 px-5">
                        <p class="font-bold text-navy-800">{{ $demo->preferred_date?->format('d M Y') }}</p>
                        <p class="text-[11px] text-gray-400">{{ $demo->preferred_time }} WIB</p>
                    </td>
                    <td class="py-3.5 px-5 text-center"><span class="badge {{ $badge }}">{{ ucfirst($demo->status) }}</span></td>
                    <td class="py-3.5 px-5">
                        <div class="flex items-center justify-end gap-2">
                            @if($wa)
                            <a href="https://wa.me/{{ $wa }}?text={{ urlencode('Halo ' . $demo->name . ', kami dari MariRent menindaklanjuti permintaan demo Anda.') }}" target="_blank" class="w-8 h-8 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition" title="Hubungi via WhatsApp"><i class="fab fa-whatsapp text-sm"></i></a>
                            @endif
                            <form method="POST" action="{{ route('superadmin.demo-requests.update', $demo) }}" class="flex items-center gap-1.5">
                                @csrf
                                @method('PUT')
                                <x-searchable-select name="status" placeholder="Pilih status" size="sm">
                                    @foreach(['pending' => 'Menunggu', 'contacted' => 'Dihubungi', 'scheduled' => 'Terjadwal', 'done' => 'Selesai', 'cancelled' => 'Batal'] as $key => $label)
                                    <option value="{{ $key }}" {{ $demo->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </x-searchable-select>
                                <button type="submit" class="btn-primary text-white w-8 h-8 rounded-xl text-xs font-bold shadow-sm flex items-center justify-center" title="Simpan status"><i class="fas fa-check"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-12 text-center text-gray-400">
                    <i class="fas fa-calendar-check text-3xl text-gray-200 mb-2"></i>
                    <p class="font-semibold text-navy-700 text-sm">Belum ada permintaan demo</p>
                    <p class="text-xs">Form publik: <a href="{{ route('demo') }}" class="text-sky-600 font-semibold hover:underline">{{ route('demo') }}</a></p>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($demos->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $demos->links() }}</div>
    @endif
</div>
@endsection
