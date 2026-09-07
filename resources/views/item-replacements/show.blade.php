@extends('layouts.dashboard')
@section('page-title', 'Detail Penggantian Unit')
@section('content')
@php
    $typeLabel = match($replacement->item_type) {
        'hp' => 'HP',
        'camera' => 'Kamera',
        'tenda' => 'Alat Camping',
        'ps' => 'Playstation',
        'drone' => 'Drone',
        'musik' => 'Alat Musik',
        default => ucfirst($replacement->item_type),
    };
    $statusLabel = match($replacement->status) {
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        default => 'Pending',
    };
@endphp

<div class="max-w-5xl">
    <a href="{{ route('item-replacements.index') }}" class="text-sky-600 text-[13px] mb-5 inline-flex items-center hover:text-sky-700 transition">
        <i class="fas fa-arrow-left mr-1.5"></i> Kembali ke Penggantian Unit
    </a>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-5 text-[13px]">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-5 text-[13px]">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <p class="text-[11px] text-gray-400 uppercase tracking-widest font-semibold">Penggantian {{ $typeLabel }}</p>
                        <h2 class="text-xl font-bold text-navy-900 mt-1">{{ $replacement->booking?->booking_code ?? '-' }}</h2>
                        <p class="text-[12px] text-gray-400 mt-1">Diajukan {{ $replacement->created_at?->format('d M Y H:i') }}</p>
                    </div>
                    <span class="{{ $replacement->status === 'approved' ? 'badge-green' : ($replacement->status === 'rejected' ? 'badge-red' : 'badge-blue') }} px-3 py-1.5 rounded-full text-[11px] font-semibold">{{ $statusLabel }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-sky-50/60 border border-sky-100 rounded-xl p-4">
                        <p class="text-[10px] text-gray-400 uppercase tracking-widest font-semibold">Pemohon</p>
                        <p class="text-[14px] font-bold text-navy-800 mt-1">{{ $replacement->requestedBy?->name ?? '-' }}</p>
                        <p class="text-[11px] text-gray-400 capitalize">{{ $replacement->requestedBy?->role ?? '-' }}</p>
                    </div>
                    <div class="bg-sky-50/60 border border-sky-100 rounded-xl p-4">
                        <p class="text-[10px] text-gray-400 uppercase tracking-widest font-semibold">Booking</p>
                        @if($replacement->booking)
                        <a href="{{ route('bookings.show', $replacement->booking) }}" class="text-[14px] font-bold text-sky-600 hover:text-sky-700 mt-1 inline-block">{{ $replacement->booking->booking_code }}</a>
                        <p class="text-[11px] text-gray-400 capitalize">{{ $replacement->booking->status }}</p>
                        @else
                        <p class="text-[14px] font-bold text-navy-800 mt-1">-</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-right-left text-sky-500 mr-2"></i>Perbandingan Unit</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach([
                        ['label' => 'Unit Asal', 'item' => $replacement->originalItem, 'class' => 'red'],
                        ['label' => 'Unit Pengganti', 'item' => $replacement->replacementItem, 'class' => 'emerald'],
                    ] as $unit)
                    <div class="border border-{{ $unit['class'] }}-200 bg-{{ $unit['class'] }}-50/40 rounded-xl p-4">
                        <p class="text-[10px] uppercase tracking-widest font-semibold text-{{ $unit['class'] }}-600">{{ $unit['label'] }}</p>
                        <p class="text-[15px] font-bold text-navy-800 mt-2">{{ $unit['item']?->name ?? '-' }}</p>
                        <p class="text-[12px] text-gray-500 mt-1">{{ $unit['item']?->brand ?? '' }} {{ $unit['item']?->model ?? '' }}</p>
                        @if($unit['item']?->daily_price)
                        <p class="text-[12px] text-sky-600 font-semibold mt-2">Rp {{ number_format($unit['item']->daily_price, 0, ',', '.') }}/hari</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-3"><i class="fas fa-comment-dots text-amber-500 mr-2"></i>Alasan Penggantian</h3>
                <p class="text-[13px] text-gray-600 leading-relaxed">{{ $replacement->reason ?: '-' }}</p>
                @if($replacement->mark_maintenance || $replacement->damage_notes)
                <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <p class="text-[12px] font-bold text-amber-800"><i class="fas fa-triangle-exclamation mr-1"></i> Unit asal ditandai maintenance</p>
                    @if($replacement->damage_notes)
                    <p class="text-[12px] text-amber-700 mt-1">{{ $replacement->damage_notes }}</p>
                    @endif
                </div>
                @endif
                @if($replacement->admin_notes)
                <div class="mt-4 bg-sky-50 border border-sky-100 rounded-xl p-4">
                    <p class="text-[12px] font-bold text-sky-800">Catatan admin</p>
                    <p class="text-[12px] text-sky-700 mt-1">{{ $replacement->admin_notes }}</p>
                </div>
                @endif
            </div>

            @if($replacement->initial_item_photo || $replacement->final_item_photo)
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-camera text-sky-500 mr-2"></i>Bukti Foto</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if($replacement->initial_item_photo)
                    <img src="{{ asset('storage/' . $replacement->initial_item_photo) }}" alt="Foto awal unit" class="w-full rounded-xl border border-gray-200 object-cover max-h-64">
                    @endif
                    @if($replacement->final_item_photo)
                    <img src="{{ asset('storage/' . $replacement->final_item_photo) }}" alt="Foto akhir unit" class="w-full rounded-xl border border-gray-200 object-cover max-h-64">
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="space-y-5">
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-coins text-sky-500 mr-2"></i>Selisih Harga</h3>
                <p class="text-2xl font-bold {{ $replacement->price_difference > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                    {{ $replacement->price_difference > 0 ? '+' : '' }} Rp {{ number_format((float) $replacement->price_difference, 0, ',', '.') }}
                </p>
                @if($replacement->approvedBy)
                <p class="text-[11px] text-gray-400 mt-3">Disetujui oleh {{ $replacement->approvedBy->name }}</p>
                @endif
            </div>

            @if(in_array(auth()->user()->role, ['superadmin','owner','admin']) && $replacement->status === 'pending')
            <div class="glass-card rounded-2xl p-6 border border-sky-100">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4">Persetujuan</h3>
                <form method="POST" action="{{ route('item-replacements.approve', $replacement) }}" onsubmit="return confirm('Setujui penggantian unit ini?')">
                    @csrf
                    <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-[12px] font-bold"><i class="fas fa-check mr-1"></i> ACC Penggantian</button>
                </form>
                @if(in_array(auth()->user()->role, ['superadmin','owner']))
                <form method="POST" action="{{ route('item-replacements.reject', $replacement) }}" class="mt-2" onsubmit="return confirm('Tolak penggantian unit ini?')">
                    @csrf
                    <button class="w-full bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2.5 rounded-xl text-[12px] font-bold">Tolak</button>
                </form>
                @endif
            </div>
            @endif

            @if(in_array(auth()->user()->role, ['superadmin','owner']) && $replacement->status === 'approved' && !$replacement->is_returned)
            <div class="glass-card rounded-2xl p-6 border border-amber-100" x-data="{ damaged: false, rating: 5 }">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-rotate-left text-amber-500 mr-2"></i>Catat Pengembalian</h3>
                <form method="POST" action="{{ route('item-replacements.return', $replacement) }}" class="space-y-3">
                    @csrf
                    <textarea name="condition_notes" required maxlength="2000" rows="3" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-[12px]" placeholder="Catatan kondisi unit saat dikembalikan"></textarea>
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500">Kondisi: <span x-text="rating">5</span>/10</label>
                        <input type="range" name="condition_rating" min="1" max="10" value="5" x-model="rating" class="w-full accent-amber-500">
                    </div>
                    <label class="flex items-center gap-2 text-[12px] text-gray-600"><input type="checkbox" name="return_is_damaged" value="1" x-model="damaged" class="rounded text-amber-500"> Unit pengganti rusak</label>
                    <textarea name="return_damage_notes" x-show="damaged" maxlength="2000" rows="2" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-[12px]" placeholder="Detail kerusakan unit pengganti"></textarea>
                    <button class="w-full bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-xl text-[12px] font-bold">Simpan Pengembalian</button>
                </form>
            </div>
            @endif

            @if($replacement->is_returned)
            <div class="glass-card rounded-2xl p-6 border border-emerald-100">
                <h3 class="text-[14px] font-bold text-emerald-700 mb-3"><i class="fas fa-check-circle mr-1"></i>Unit Sudah Dikembalikan</h3>
                <p class="text-[12px] text-gray-500">{{ $replacement->returned_at?->format('d M Y H:i') }} oleh {{ $replacement->returnedBy?->name ?? '-' }}</p>
                <p class="text-[12px] text-gray-600 mt-2">{{ $replacement->return_notes }}</p>
                @if($replacement->return_is_damaged && $replacement->return_damage_notes)
                <p class="text-[12px] text-red-600 mt-2">Kerusakan: {{ $replacement->return_damage_notes }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
