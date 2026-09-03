@extends('layouts.dashboard')
@section('page-title', 'Notifikasi')

@section('content')
<div class="max-w-3xl">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
        <div>
            <h2 class="text-lg font-bold text-navy-800 flex items-center gap-2">
                <i class="fas fa-bell text-sky-500"></i> Notifikasi
            </h2>
            <p class="text-[12px] text-gray-400">Riwayat notifikasi dan pembaruan booking Anda</p>
        </div>
        @if(auth()->user()->unreadNotifications()->count() > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="text-[12px] text-sky-600 hover:text-sky-700 font-semibold flex items-center gap-1 transition">
                <i class="fas fa-check-double text-[10px]"></i> Tandai semua dibaca
            </button>
        </form>
        @endif
    </div>

    {{-- Notification List --}}
    <div class="space-y-2">
        @forelse($notifications as $n)
        @php
            $data = $n->data;
            $typeConfig = match($data['type'] ?? '') {
                'booking_created' => ['icon' => 'fa-plus-circle', 'color' => 'sky', 'bg' => 'bg-sky-50', 'border' => 'border-sky-200'],
                'booking_status_changed' => ['icon' => 'fa-sync-alt', 'color' => 'blue', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                'booking_cancelled' => ['icon' => 'fa-times-circle', 'color' => 'red', 'bg' => 'bg-red-50', 'border' => 'border-red-200'],
                'vehicle_replaced' => ['icon' => 'fa-exchange-alt', 'color' => 'amber', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200'],
                default => ['icon' => 'fa-bell', 'color' => 'gray', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200'],
            };
        @endphp
        <div class="glass-card rounded-xl p-4 border {{ $n->read_at ? 'border-gray-100 bg-white' : $typeConfig['border'] . ' ' . $typeConfig['bg'] }} transition-all duration-200 hover:shadow-sm">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl {{ $typeConfig['bg'] }} flex items-center justify-center flex-shrink-0 border {{ $typeConfig['border'] }}">
                    <i class="fas {{ $typeConfig['icon'] }} text-{{ $typeConfig['color'] }}-500 text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <h4 class="text-[13px] font-bold text-navy-800">{{ $data['title'] ?? 'Notifikasi' }}</h4>
                        @if(!$n->read_at)
                        <span class="w-2 h-2 rounded-full bg-sky-500 flex-shrink-0"></span>
                        @endif
                    </div>
                    <p class="text-[12px] text-gray-600 leading-relaxed">{{ $data['message'] ?? '-' }}</p>
                    <div class="flex items-center gap-3 mt-2">
                        @if(!empty($data['booking_code']))
                        <span class="text-[10px] font-mono bg-gray-100 text-gray-500 px-2 py-0.5 rounded">{{ $data['booking_code'] }}</span>
                        @endif
                        @if(!empty($data['url']))
                        <a href="{{ $data['url'] }}" class="text-[11px] text-sky-600 hover:text-sky-700 font-semibold flex items-center gap-1">
                            Lihat Detail <i class="fas fa-arrow-right text-[9px]"></i>
                        </a>
                        @endif
                        @if(!$n->read_at)
                        <form method="POST" action="{{ route('notifications.mark-read', $n->id) }}" class="ml-auto">
                            @csrf
                            <button type="submit" class="text-[10px] text-gray-400 hover:text-sky-600 transition" title="Tandai sudah dibaca">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                <span class="text-[10px] text-gray-400 whitespace-nowrap flex-shrink-0">{{ $n->created_at->diffForHumans() }}</span>
            </div>
        </div>
        @empty
        <div class="glass-card rounded-2xl p-12 text-center border border-sky-100/50">
            <div class="w-16 h-16 bg-gradient-to-br from-sky-50 to-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-sky-100">
                <i class="fas fa-bell-slash text-sky-300 text-2xl"></i>
            </div>
            <h3 class="text-base font-bold text-navy-800 mb-1">Belum ada notifikasi</h3>
            <p class="text-gray-400 text-[13px]">Notifikasi akan muncul di sini ketika ada pembaruan booking</p>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="mt-8 flex justify-center">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
