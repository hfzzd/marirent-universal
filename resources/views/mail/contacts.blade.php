@extends('layouts.dashboard')
@section('page-title', 'Pesan Kontak Masuk (Web Inquiries)')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold text-navy-800 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm shadow-sm"><i class="fas fa-headset"></i></span>
            Pesan Kontak Web (Inquiries)
        </h2>
        <p class="text-xs text-gray-400 mt-0.5">Pertanyaan, permohonan sewa, dan saran dari formulir publik website.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('mail.index') }}" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-navy-700 hover:bg-gray-50 flex items-center gap-1.5 transition">
            <i class="fas fa-inbox"></i> Kembali ke Mail
        </a>
    </div>
</div>

<div class="glass-card rounded-2xl overflow-hidden shadow-sm border border-amber-100/50">
    <div class="p-4 border-b border-gray-100 bg-white/40 flex flex-col sm:flex-row items-center justify-between gap-3">
        <form action="{{ route('mail.contacts') }}" method="GET" class="relative w-full sm:w-72">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, perihal..." class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none bg-white">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
        </form>
        <div class="flex items-center gap-2 text-xs">
            <a href="{{ route('mail.contacts') }}" class="px-3 py-1.5 rounded-lg {{ !request('filter') ? 'bg-amber-500 text-white font-bold' : 'text-gray-500 hover:bg-gray-100' }}">Semua</a>
            <a href="{{ route('mail.contacts', ['filter' => 'unread']) }}" class="px-3 py-1.5 rounded-lg {{ request('filter') == 'unread' ? 'bg-amber-500 text-white font-bold' : 'text-gray-500 hover:bg-gray-100' }}">Belum Dibaca ({{ $unreadCount }})</a>
            <a href="{{ route('mail.contacts', ['filter' => 'replied']) }}" class="px-3 py-1.5 rounded-lg {{ request('filter') == 'replied' ? 'bg-amber-500 text-white font-bold' : 'text-gray-500 hover:bg-gray-100' }}">Sudah Dibalas</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-amber-50/40 border-b border-amber-100/50 text-[10px] uppercase font-bold text-gray-500">
                    <th class="py-3 px-6 text-left">Pengirim</th>
                    <th class="py-3 px-6 text-left">Subjek & Pesan</th>
                    <th class="py-3 px-6 text-left">Kontak</th>
                    <th class="py-3 px-6 text-left">Waktu Masuk</th>
                    <th class="py-3 px-6 text-center">Status</th>
                    <th class="py-3 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($messages as $m)
                <tr class="hover:bg-amber-50/30 transition-colors {{ !$m->is_read ? 'bg-amber-50/50 font-semibold' : '' }}">
                    <td class="py-3.5 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                {{ strtoupper(substr($m->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-bold text-navy-800">{{ $m->name }}</p>
                                <p class="text-[10px] text-gray-400">{{ $m->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-6 max-w-xs">
                        <a href="{{ route('mail.contacts.show', $m) }}" class="block hover:text-amber-600">
                            <p class="font-bold text-navy-800 truncate">{{ $m->subject }}</p>
                            <p class="text-[11px] text-gray-500 truncate font-normal">{{ Str::limit($m->message, 50) }}</p>
                        </a>
                    </td>
                    <td class="py-3.5 px-6">
                        @if($m->phone)
                        @php $cleanPhone = preg_replace('/[^0-9]/', '', $m->phone); if(str_starts_with($cleanPhone, '0')) $cleanPhone = '62'.substr($cleanPhone, 1); @endphp
                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1.5 text-emerald-600 hover:text-emerald-700 font-medium">
                            <i class="fab fa-whatsapp"></i> {{ $m->phone }}
                        </a>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-6 text-gray-500">
                        {{ $m->created_at->translatedFormat('d M Y H:i') }}
                    </td>
                    <td class="py-3.5 px-6 text-center">
                        @if($m->replied_at)
                        <span class="badge badge-green">Sudah Dibalas</span>
                        @elseif($m->is_read)
                        <span class="badge badge-gray">Sudah Dibaca</span>
                        @else
                        <span class="badge badge-yellow">Baru Masuk</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-6 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('mail.contacts.show', $m) }}" class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 flex items-center justify-center transition" title="Buka Detail">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <form method="POST" action="{{ route('mail.contacts.destroy', $m) }}" class="inline" onsubmit="return confirm('Hapus pesan kontak ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center transition" title="Hapus">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-gray-400">Belum ada pesan kontak masuk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($messages->hasPages())
    <div class="p-4 border-t border-gray-100 bg-white/50">
        {{ $messages->links() }}
    </div>
    @endif
</div>
@endsection
