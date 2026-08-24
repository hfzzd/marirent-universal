@extends('layouts.dashboard')
@section('page-title', 'Live Chat Messenger')

@section('content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-5">
    <div>
        <h2 class="text-xl font-bold text-navy-800 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm shadow-sm"><i class="fas fa-comments"></i></span>
            Live Chat & Messenger
        </h2>
        <p class="text-xs text-gray-400 mt-0.5">Obrolan langsung dengan Pelanggan, Driver, Owner, dan Admin.</p>
    </div>
</div>

<div class="glass-card rounded-2xl overflow-hidden shadow-sm border border-sky-100/50 flex flex-col md:flex-row" style="height: calc(100vh - 200px); min-height: 550px;">
    {{-- Left Panel: Conversations List --}}
    <div class="w-full md:w-80 border-r border-gray-100 flex flex-col bg-white/50 backdrop-blur-sm flex-shrink-0">
        {{-- Search & New Chat Header --}}
        <div class="p-3.5 border-b border-gray-100 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-navy-800">Percakapan</span>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="w-7 h-7 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-600 flex items-center justify-center text-xs font-bold transition" title="Mulai Chat Baru">
                        <i class="fas fa-plus"></i>
                    </button>
                    {{-- Contacts Dropdown --}}
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 p-2 z-50 max-h-72 overflow-y-auto">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 px-3 py-1.5">Pilih Kontak</div>
                        @foreach($contacts as $c)
                        <a href="{{ route('chat.index', ['user_id' => $c->id]) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-sky-50 transition text-xs">
                            <div class="w-7 h-7 rounded-full bg-sky-100 text-sky-700 font-bold flex items-center justify-center text-[10px] flex-shrink-0">
                                {{ strtoupper(substr($c->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-navy-800 truncate">{{ $c->name }}</p>
                                <span class="text-[10px] text-gray-400 capitalize">{{ $c->role }}</span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Conversations Scroll Area --}}
        <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
            @forelse($conversations as $conv)
            @php
                $other = $conv->otherUser(auth()->id());
                $isActive = $activeConversation && $activeConversation->id === $conv->id;
                $lastMsg = $conv->latestMessage;
                $unread = $conv->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->count();
            @endphp
            <a href="{{ route('chat.index', ['conversation_id' => $conv->id]) }}" class="flex items-center gap-3 p-3.5 transition {{ $isActive ? 'bg-sky-500 text-white' : 'hover:bg-sky-50/50 text-navy-800' }}">
                <div class="relative flex-shrink-0">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold {{ $isActive ? 'bg-white text-sky-600' : 'bg-sky-100 text-sky-700' }}">
                        {{ strtoupper(substr($other->name ?? 'U', 0, 2)) }}
                    </div>
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1 mb-0.5">
                        <p class="font-bold text-xs truncate {{ $isActive ? 'text-white' : 'text-navy-800' }}">{{ $other->name ?? 'Pengguna' }}</p>
                        <span class="text-[10px] {{ $isActive ? 'text-sky-100' : 'text-gray-400' }} whitespace-nowrap">{{ $conv->last_message_at ? $conv->last_message_at->format('H:i') : '' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-1">
                        <p class="text-[11px] truncate {{ $isActive ? 'text-sky-100' : 'text-gray-400' }}">
                            {{ $lastMsg ? $lastMsg->message : 'Mulai obrolan...' }}
                        </p>
                        @if($unread > 0 && !$isActive)
                        <span class="w-4 h-4 rounded-full bg-sky-500 text-white font-bold text-[9px] flex items-center justify-center flex-shrink-0">{{ $unread }}</span>
                        @endif
                    </div>
                </div>
            </a>
            @empty
            <div class="p-8 text-center text-gray-400 text-xs">
                <i class="fas fa-comments text-2xl text-gray-300 mb-2"></i>
                <p class="font-semibold text-navy-700">Belum ada obrolan</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Klik tanda (+) di atas untuk memulai chat dengan pengguna.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Right Panel: Active Chat Area --}}
    <div class="flex-1 flex flex-col bg-white">
        @if($activeConversation)
        @php
            $activeOther = $activeConversation->otherUser(auth()->id());
            $activePhone = preg_replace('/[^0-9]/', '', $activeOther->phone ?? '');
            if(str_starts_with($activePhone, '0')) $activePhone = '62'.substr($activePhone, 1);
        @endphp
        {{-- Chat Header --}}
        <div class="p-4 border-b border-gray-100 flex items-center justify-between gap-3 bg-white/80 backdrop-blur-sm flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center text-xs font-bold shadow-sm">
                    {{ strtoupper(substr($activeOther->name ?? 'U', 0, 2)) }}
                </div>
                <div>
                    <h3 class="font-bold text-navy-800 text-sm leading-tight">{{ $activeOther->name ?? 'Pengguna' }}</h3>
                    <p class="text-[11px] text-gray-400 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span class="capitalize font-semibold text-sky-600">{{ $activeOther->role ?? '-' }}</span> &bull; 
                        <span>{{ $activeOther->email }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($activeOther->phone)
                <a href="https://wa.me/{{ $activePhone }}" target="_blank" class="w-8 h-8 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition" title="Buka WhatsApp">
                    <i class="fab fa-whatsapp text-sm"></i>
                </a>
                <a href="tel:{{ $activeOther->phone }}" class="w-8 h-8 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 flex items-center justify-center transition" title="Panggil Telepon">
                    <i class="fas fa-phone text-xs"></i>
                </a>
                @endif
                <a href="{{ route('mail.compose', ['to' => $activeOther->id]) }}" class="w-8 h-8 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-600 flex items-center justify-center transition" title="Kirim Surat Mail">
                    <i class="fas fa-envelope text-xs"></i>
                </a>
            </div>
        </div>

        {{-- Messages Container --}}
        <div id="chatMessagesContainer" class="flex-1 p-4 md:p-6 overflow-y-auto space-y-4 bg-gradient-to-b from-sky-50/20 to-slate-50/20">
            @foreach($activeConversation->messages as $msg)
            @php $isMe = $msg->sender_id === auth()->id(); @endphp
            <div class="flex items-end gap-2 {{ $isMe ? 'justify-end' : 'justify-start' }}">
                @if(!$isMe)
                <div class="w-7 h-7 rounded-full bg-gray-200 text-navy-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0 mb-1">
                    {{ strtoupper(substr($msg->sender->name ?? 'U', 0, 1)) }}
                </div>
                @endif
                <div class="max-w-md">
                    <div class="px-4 py-2.5 rounded-2xl text-xs leading-relaxed {{ $isMe ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white rounded-br-none shadow-md shadow-sky-500/15' : 'bg-white text-navy-800 border border-gray-100 rounded-bl-none shadow-sm' }}">
                        {{ $msg->message }}
                    </div>
                    <span class="text-[9px] text-gray-400 mt-1 block {{ $isMe ? 'text-right' : 'text-left' }}">
                        {{ $msg->created_at->format('H:i') }}
                        @if($isMe)
                        <i class="fas fa-check-double text-[8px] ml-0.5 text-sky-400"></i>
                        @endif
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Input Toolbar --}}
        <div class="p-3.5 border-t border-gray-100 bg-white flex-shrink-0">
            <form id="chatSendForm" method="POST" action="{{ route('chat.send', $activeConversation) }}" class="flex items-center gap-2">
                @csrf
                <input type="text" id="chatInputMessage" name="message" required autocomplete="off" placeholder="Ketik pesan untuk {{ $activeOther->name }}..." class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-slate-50/50">
                <button type="submit" class="btn-primary text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-sky-500/20 flex items-center gap-1.5 transition flex-shrink-0">
                    <i class="fas fa-paper-plane"></i> Kirim
                </button>
            </form>
        </div>

        @else
        <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-gray-400">
            <div class="w-16 h-16 rounded-full bg-sky-50 text-sky-500 flex items-center justify-center text-2xl mb-3 shadow-sm">
                <i class="fas fa-comments"></i>
            </div>
            <h3 class="font-bold text-navy-800 text-base mb-1">Pilih Obrolan untuk Memulai</h3>
            <p class="text-xs text-gray-400 max-w-sm">Pilih salah satu percakapan di sebelah kiri atau klik tombol (+) untuk memulai obrolan baru.</p>
        </div>
        @endif
    </div>
</div>

@if($activeConversation)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('chatMessagesContainer');
    const form = document.getElementById('chatSendForm');
    const input = document.getElementById('chatInputMessage');

    // Scroll to bottom
    if (container) {
        container.scrollTop = container.scrollHeight;
    }

    // AJAX submit form
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;

            const url = form.getAttribute('action');
            const token = form.querySelector('input[name="_token"]').value;

            // Optimistic append
            const tempBubble = `
                <div class="flex items-end gap-2 justify-end">
                    <div class="max-w-md">
                        <div class="px-4 py-2.5 rounded-2xl text-xs leading-relaxed bg-gradient-to-r from-sky-500 to-sky-600 text-white rounded-br-none shadow-md shadow-sky-500/15">
                            ${escapeHtml(text)}
                        </div>
                        <span class="text-[9px] text-gray-400 mt-1 block text-right">Baru saja</span>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', tempBubble);
            container.scrollTop = container.scrollHeight;
            input.value = '';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: text })
            }).then(res => res.json()).then(data => {
                // Success
            }).catch(err => {
                console.error('Error sending chat message:', err);
            });
        });
    }

    // Periodic poll for new messages (every 5 seconds)
    const convId = {{ $activeConversation->id }};
    setInterval(function() {
        fetch(`/chat/${convId}/messages`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.messages && data.messages.length > 0) {
                // Render fresh message list
                let html = '';
                data.messages.forEach(msg => {
                    if (msg.is_me) {
                        html += `
                            <div class="flex items-end gap-2 justify-end">
                                <div class="max-w-md">
                                    <div class="px-4 py-2.5 rounded-2xl text-xs leading-relaxed bg-gradient-to-r from-sky-500 to-sky-600 text-white rounded-br-none shadow-md shadow-sky-500/15">
                                        ${escapeHtml(msg.message)}
                                    </div>
                                    <span class="text-[9px] text-gray-400 mt-1 block text-right">
                                        ${msg.time} <i class="fas fa-check-double text-[8px] ml-0.5 text-sky-400"></i>
                                    </span>
                                </div>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="flex items-end gap-2 justify-start">
                                <div class="w-7 h-7 rounded-full bg-gray-200 text-navy-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0 mb-1">
                                    ${escapeHtml(msg.sender_name.substring(0, 1).toUpperCase())}
                                </div>
                                <div class="max-w-md">
                                    <div class="px-4 py-2.5 rounded-2xl text-xs leading-relaxed bg-white text-navy-800 border border-gray-100 rounded-bl-none shadow-sm">
                                        ${escapeHtml(msg.message)}
                                    </div>
                                    <span class="text-[9px] text-gray-400 mt-1 block text-left">
                                        ${msg.time}
                                    </span>
                                </div>
                            </div>
                        `;
                    }
                });
                const wasNearBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;
                container.innerHTML = html;
                if (wasNearBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            }
        });
    }, 5000);

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.innerText = text;
        return div.innerHTML;
    }
});
</script>
@endpush
@endif
@endsection
