@extends('layouts.dashboard')
@section('page-title', 'Detail Pesan - ' . $message->subject)

@section('content')
<div class="max-w-4xl">
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('mail.index') }}" class="text-sky-600 hover:text-sky-700 text-xs font-semibold flex items-center gap-1.5 transition">
            <i class="fas fa-arrow-left"></i> Kembali ke Kotak Masuk
        </a>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('mail.star', $message) }}" class="inline">
                @csrf
                <button type="submit" class="px-3 py-1.5 rounded-xl border border-gray-200 hover:bg-gray-50 text-xs font-semibold text-navy-700 flex items-center gap-1.5 transition">
                    <i class="fas fa-star text-amber-400"></i> Bintang
                </button>
            </form>
            <form method="POST" action="{{ route('mail.trash', $message) }}" class="inline">
                @csrf
                <button type="submit" class="px-3 py-1.5 rounded-xl border border-gray-200 hover:bg-red-50 hover:text-red-600 text-xs font-semibold text-navy-700 flex items-center gap-1.5 transition">
                    <i class="fas fa-trash text-xs"></i> Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="glass-card rounded-2xl shadow-sm border border-sky-100/50 p-6 md:p-8">
        {{-- Header --}}
        <div class="pb-5 mb-5 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <h2 class="text-lg md:text-xl font-extrabold text-navy-800">{{ $message->subject }}</h2>
                <span class="text-xs text-gray-400 font-medium">{{ $message->created_at->translatedFormat('l, d F Y H:i') }}</span>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center text-sm font-bold shadow-sm">
                    {{ strtoupper(substr($message->sender->name ?? 'U', 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-navy-800 text-sm">{{ $message->sender->name ?? 'Pengguna' }}</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 font-bold uppercase">{{ $message->sender->role ?? '-' }}</span>
                    </div>
                    <p class="text-xs text-gray-400">Kepada: <strong class="text-navy-700">{{ $message->receiver->name ?? 'Penerima' }}</strong> ({{ $message->receiver->email ?? '' }})</p>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="text-xs leading-relaxed text-navy-800 whitespace-pre-line py-4">
{{ $message->body }}
        </div>

        {{-- Reply Action --}}
        <div class="mt-8 pt-6 border-t border-gray-100 flex items-center gap-3">
            @php
                $replyRecipient = ($message->sender_id === auth()->id()) ? $message->receiver_id : $message->sender_id;
            @endphp
            <a href="{{ route('mail.compose', ['to' => $replyRecipient, 'reply_to' => $message->id]) }}" class="btn-primary text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-sky-500/20 flex items-center gap-2 transition">
                <i class="fas fa-reply"></i> Balas Pesan
            </a>
            <a href="{{ route('chat.index', ['user_id' => $replyRecipient]) }}" class="bg-amber-50 hover:bg-amber-100 text-amber-700 px-5 py-2.5 rounded-xl text-xs font-bold border border-amber-200/60 flex items-center gap-2 transition">
                <i class="fas fa-comments"></i> Buka Chat Langsung
            </a>
        </div>
    </div>
</div>
@endsection
