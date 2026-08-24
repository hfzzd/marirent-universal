@extends('layouts.dashboard')
@section('page-title', 'Buku Kontak (Contacts Directory)')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-navy-800 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm shadow-sm"><i class="fas fa-address-book"></i></span>
            Direktori Buku Kontak
        </h2>
        <p class="text-xs text-gray-400 mt-0.5">Daftar kontak seluruh Pelanggan, Driver, Mitra Owner, dan Admin sistem.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('chat.index') }}" class="btn-primary text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md shadow-sky-500/20 flex items-center gap-1.5 transition">
            <i class="fas fa-comments"></i> Buka Messenger
        </a>
    </div>
</div>

{{-- Search & Filter Tabs --}}
<div class="glass-card rounded-2xl p-4 border border-sky-100/50 shadow-sm mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-2 w-full md:w-auto text-xs">
        <a href="{{ route('contacts.index') }}" class="px-3.5 py-2 rounded-xl font-bold transition {{ !$role ? 'bg-purple-600 text-white shadow-md shadow-purple-500/20' : 'text-gray-600 hover:bg-gray-100' }}">
            Semua ({{ $counts['all'] }})
        </a>
        <a href="{{ route('contacts.index', ['role' => 'user']) }}" class="px-3.5 py-2 rounded-xl font-bold transition {{ $role === 'user' ? 'bg-purple-600 text-white shadow-md shadow-purple-500/20' : 'text-gray-600 hover:bg-gray-100' }}">
            Pelanggan ({{ $counts['user'] }})
        </a>
        <a href="{{ route('contacts.index', ['role' => 'driver']) }}" class="px-3.5 py-2 rounded-xl font-bold transition {{ $role === 'driver' ? 'bg-purple-600 text-white shadow-md shadow-purple-500/20' : 'text-gray-600 hover:bg-gray-100' }}">
            Driver ({{ $counts['driver'] }})
        </a>
        <a href="{{ route('contacts.index', ['role' => 'owner']) }}" class="px-3.5 py-2 rounded-xl font-bold transition {{ $role === 'owner' ? 'bg-purple-600 text-white shadow-md shadow-purple-500/20' : 'text-gray-600 hover:bg-gray-100' }}">
            Mitra Owner ({{ $counts['owner'] }})
        </a>
        <a href="{{ route('contacts.index', ['role' => 'superadmin']) }}" class="px-3.5 py-2 rounded-xl font-bold transition {{ $role === 'superadmin' ? 'bg-purple-600 text-white shadow-md shadow-purple-500/20' : 'text-gray-600 hover:bg-gray-100' }}">
            Admin ({{ $counts['superadmin'] }})
        </a>
    </div>

    <form action="{{ route('contacts.index') }}" method="GET" class="relative w-full md:w-72">
        @if($role)
        <input type="hidden" name="role" value="{{ $role }}">
        @endif
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, nomor HP..." class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-xs focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none bg-white">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
    </form>
</div>

{{-- Contact Cards Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    @forelse($contacts as $c)
    @php
        $cleanPhone = preg_replace('/[^0-9]/', '', $c->phone ?? '');
        if(str_starts_with($cleanPhone, '0')) $cleanPhone = '62'.substr($cleanPhone, 1);
    @endphp
    <div class="stat-card glass-card rounded-2xl p-5 border border-sky-100/50 shadow-sm flex flex-col justify-between hover:shadow-md transition">
        <div>
            {{-- Top Row: Avatar & Role --}}
            <div class="flex items-start justify-between gap-3 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white font-black flex items-center justify-center text-sm shadow-md shadow-purple-500/20">
                    {{ strtoupper(substr($c->name, 0, 2)) }}
                </div>
                @if($c->role === 'superadmin')
                <span class="badge badge-blue text-[10px] uppercase">Admin</span>
                @elseif($c->role === 'owner')
                <span class="badge badge-yellow text-[10px] uppercase">Owner</span>
                @elseif($c->role === 'driver')
                <span class="badge badge-teal text-[10px] uppercase">Driver</span>
                @else
                <span class="badge badge-gray text-[10px] uppercase">Pelanggan</span>
                @endif
            </div>

            {{-- Name & Email --}}
            <h3 class="font-bold text-navy-800 text-sm truncate" title="{{ $c->name }}">{{ $c->name }}</h3>
            <p class="text-[11px] text-gray-400 truncate mt-0.5" title="{{ $c->email }}">{{ $c->email }}</p>

            {{-- Phone & Address Details --}}
            <div class="mt-3 pt-3 border-t border-gray-100 space-y-1.5 text-xs text-navy-700">
                <div class="flex items-center gap-2 text-[11px]">
                    <i class="fas fa-phone text-gray-400 w-3.5"></i>
                    <span class="font-mono text-gray-600">{{ $c->phone ?? 'Belum ada no. HP' }}</span>
                </div>
                <div class="flex items-center gap-2 text-[11px]">
                    <i class="fas fa-location-dot text-gray-400 w-3.5"></i>
                    <span class="text-gray-600 truncate">{{ $c->address ?? 'Indonesia' }}</span>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="mt-5 pt-3 border-t border-gray-100 flex items-center justify-between gap-1.5">
            {{-- WhatsApp --}}
            @if($c->phone)
            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="flex-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 py-1.5 px-2 rounded-xl text-center text-xs font-bold transition flex items-center justify-center gap-1" title="Chat WhatsApp">
                <i class="fab fa-whatsapp text-sm"></i> <span class="hidden sm:inline">WA</span>
            </a>
            <a href="tel:{{ $c->phone }}" class="w-8 h-8 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 flex items-center justify-center text-xs transition" title="Telepon">
                <i class="fas fa-phone"></i>
            </a>
            @endif

            {{-- Live Chat --}}
            <a href="{{ route('chat.index', ['user_id' => $c->id]) }}" class="flex-1 bg-sky-50 hover:bg-sky-100 text-sky-700 py-1.5 px-2 rounded-xl text-center text-xs font-bold transition flex items-center justify-center gap-1" title="Live Chat">
                <i class="fas fa-comments"></i> <span class="hidden sm:inline">Chat</span>
            </a>

            {{-- Mail Compose --}}
            <a href="{{ route('mail.compose', ['to' => $c->id]) }}" class="w-8 h-8 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-600 flex items-center justify-center text-xs transition" title="Kirim Surat / Email">
                <i class="fas fa-envelope"></i>
            </a>
        </div>
    </div>
    @empty
    <div class="col-span-full py-16 text-center text-gray-400">
        <div class="w-12 h-12 rounded-full bg-purple-50 text-purple-400 flex items-center justify-center mx-auto mb-2 text-xl">
            <i class="fas fa-address-book"></i>
        </div>
        <p class="font-semibold text-navy-700">Tidak ada kontak ditemukan</p>
        <p class="text-xs text-gray-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau tab filter.</p>
    </div>
    @endforelse
</div>

@if($contacts->hasPages())
<div class="p-4 glass-card rounded-2xl shadow-sm border border-sky-100/50">
    {{ $contacts->links() }}
</div>
@endif
@endsection
