@extends('layouts.dashboard')
@section('page-title', 'Kotak Masuk (Mail Inbox)')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-navy-800 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm shadow-sm"><i class="fas fa-envelope"></i></span>
            Mail & Pesan Masuk
        </h2>
        <p class="text-xs text-gray-400 mt-0.5">Sistem komunikasi dan perpesanan internal MariRent.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('mail.compose') }}" class="btn-primary text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-sky-500/20 flex items-center gap-1.5 transition">
            <i class="fas fa-pen-to-square"></i> Tulis Pesan Baru
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    {{-- Folder Sidebar --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="glass-card rounded-2xl p-4 border border-sky-100/50 shadow-sm">
            <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 px-3 py-2">Folder Pesan</div>
            <nav class="space-y-1 text-xs font-semibold">
                <a href="{{ route('mail.index', ['folder' => 'inbox']) }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ $folder === 'inbox' ? 'bg-sky-500 text-white shadow-md shadow-sky-500/25' : 'text-navy-700 hover:bg-sky-50' }}">
                    <span class="flex items-center gap-2.5">
                        <i class="fas fa-inbox text-sm"></i> Kotak Masuk
                    </span>
                    @if($unreadInboxCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $folder === 'inbox' ? 'bg-white text-sky-600' : 'bg-sky-500 text-white' }}">{{ $unreadInboxCount }}</span>
                    @endif
                </a>

                <a href="{{ route('mail.index', ['folder' => 'sent']) }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ $folder === 'sent' ? 'bg-sky-500 text-white shadow-md shadow-sky-500/25' : 'text-navy-700 hover:bg-sky-50' }}">
                    <span class="flex items-center gap-2.5">
                        <i class="fas fa-paper-plane text-sm"></i> Terkirim
                    </span>
                </a>

                <a href="{{ route('mail.index', ['folder' => 'starred']) }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ $folder === 'starred' ? 'bg-sky-500 text-white shadow-md shadow-sky-500/25' : 'text-navy-700 hover:bg-sky-50' }}">
                    <span class="flex items-center gap-2.5">
                        <i class="fas fa-star text-sm text-amber-400"></i> Berbintang
                    </span>
                </a>

                <a href="{{ route('mail.index', ['folder' => 'trash']) }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ $folder === 'trash' ? 'bg-sky-500 text-white shadow-md shadow-sky-500/25' : 'text-navy-700 hover:bg-sky-50' }}">
                    <span class="flex items-center gap-2.5">
                        <i class="fas fa-trash text-sm"></i> Sampah
                    </span>
                </a>
            </nav>

            @if(auth()->user()->role === 'superadmin')
            <div class="mt-5 pt-4 border-t border-gray-100">
                <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 px-3 py-1">Inquiries Publik</div>
                <a href="{{ route('mail.contacts') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold text-navy-700 hover:bg-amber-50 mt-1 transition">
                    <span class="flex items-center gap-2.5 text-amber-700">
                        <i class="fas fa-headset text-sm"></i> Pesan Kontak Web
                    </span>
                    @if($unreadContactCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white">{{ $unreadContactCount }}</span>
                    @endif
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Message List --}}
    <div class="lg:col-span-3">
        <div class="glass-card rounded-2xl overflow-hidden shadow-sm border border-sky-100/50">
            {{-- Toolbar & Search --}}
            <div class="p-4 border-b border-sky-100/50 bg-white/40 flex flex-col sm:flex-row items-center justify-between gap-3">
                <form action="{{ route('mail.index') }}" method="GET" class="relative w-full sm:w-72">
                    <input type="hidden" name="folder" value="{{ $folder }}">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari subjek atau isi pesan..." class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                </form>
                <div class="text-xs text-gray-400 font-medium">
                    Menampilkan folder: <strong class="text-navy-700 capitalize">{{ $folder }}</strong>
                </div>
            </div>

            {{-- Mail Items --}}
            <div class="divide-y divide-gray-100">
                @forelse($messages as $m)
                @php
                    $isReceiver = $m->receiver_id === auth()->id();
                    $isStarred = $isReceiver ? $m->is_starred_receiver : $m->is_starred_sender;
                    $otherUser = $isReceiver ? $m->sender : $m->receiver;
                @endphp
                <div class="p-4 hover:bg-sky-50/40 transition flex items-center justify-between gap-3 {{ (!$m->is_read && $isReceiver) ? 'bg-sky-50/60 font-semibold' : '' }}">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        {{-- Star Toggle --}}
                        <form method="POST" action="{{ route('mail.star', $m) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm focus:outline-none transition {{ $isStarred ? 'text-amber-400' : 'text-gray-300 hover:text-amber-400' }}">
                                <i class="fas fa-star"></i>
                            </button>
                        </form>

                        {{-- User Avatar --}}
                        <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($otherUser->name ?? 'U', 0, 2)) }}
                        </div>

                        {{-- Subject & Preview --}}
                        <a href="{{ route('mail.show', $m) }}" class="flex-1 min-w-0 block">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-navy-800 truncate">{{ $otherUser->name ?? 'Pengguna' }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 font-normal uppercase">{{ $otherUser->role ?? '-' }}</span>
                            </div>
                            <p class="text-xs text-navy-700 truncate mt-0.5">
                                {{ $m->subject }} <span class="text-gray-400 font-normal">&mdash; {{ Str::limit(strip_tags($m->body), 60) }}</span>
                            </p>
                        </a>
                    </div>

                    <div class="flex items-center gap-3 flex-shrink-0">
                        <span class="text-[11px] text-gray-400 whitespace-nowrap">{{ $m->created_at->translatedFormat('d M H:i') }}</span>
                        
                        {{-- Trash Action --}}
                        <form method="POST" action="{{ route('mail.trash', $m) }}" class="inline">
                            @csrf
                            <button type="submit" class="w-7 h-7 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 flex items-center justify-center transition" title="{{ $folder === 'trash' ? 'Pulihkan' : 'Pindahkan ke Sampah' }}">
                                <i class="fas fa-{{ $folder === 'trash' ? 'rotate-left' : 'trash' }} text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="p-12 text-center text-gray-400">
                    <div class="w-12 h-12 rounded-full bg-sky-50 flex items-center justify-center text-sky-400 mx-auto mb-2 text-xl">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <p class="font-semibold text-navy-700">Tidak ada pesan di folder ini</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Pesan baru akan otomatis muncul di sini.</p>
                </div>
                @endforelse
            </div>

            @if($messages->hasPages())
            <div class="p-4 border-t border-gray-100 bg-white/50">
                {{ $messages->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
