@extends('layouts.dashboard')
@section('page-title', 'Riwayat Inspeksi')
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('inspections.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !request('type') ? 'bg-gradient-to-r from-sky-500 to-blue-600 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Semua</a>
        <a href="{{ route('inspections.index', ['type' => 'pre_rental']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('type') == 'pre_rental' ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300 hover:text-sky-600' }}">Pre-Rental</a>
        <a href="{{ route('inspections.index', ['type' => 'post_rental']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('type') == 'post_rental' ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:border-amber-300 hover:text-amber-600' }}">Post-Rental</a>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        @if(in_array(auth()->user()->role, ['admin', 'owner'], true))
        <button type="button" x-data @click="$dispatch('open-inspection-modal')" class="bg-gradient-to-r from-sky-500 to-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium text-center inline-flex items-center gap-1.5 shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105">
            <i class="fas fa-bolt mr-1"></i> Inspeksi Cepat
        </button>
        @endif
        <a href="{{ route('inspections.create') }}" class="bg-white border border-gray-200 hover:border-sky-300 hover:text-sky-600 text-gray-500 px-4 py-2 rounded-lg text-sm font-medium text-center inline-flex items-center gap-1.5 transition"><i class="fas fa-plus mr-1"></i> Form Lengkap</a>
    </div>
</div>

{{-- MODAL INSPEKSI CEPAT (khusus akun admin & owner) --}}
@if(in_array(auth()->user()->role, ['admin', 'owner'], true))
<div x-data="{ open: false, loading: false }" @open-inspection-modal.window="open = true" x-cloak>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50" @click="open = false"></div>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 rounded-t-2xl p-5 relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-clipboard-check text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Inspeksi Cepat</h3>
                            <p class="text-sky-200 text-[11px]">Catat kondisi unit tanpa buka halaman baru</p>
                        </div>
                    </div>
                    <button @click="open = false" class="text-white/70 hover:text-white transition"><i class="fas fa-times text-lg"></i></button>
                </div>
            </div>
            <form method="POST" action="{{ route('inspections.store') }}" @submit="loading = true" class="p-5 space-y-4">
                @csrf
                <input type="hidden" name="scope" value="kendaraan">
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Booking *</label>
                    <select name="booking_id" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50">
                        <option value="">Pilih Booking</option>
                        @foreach($modalBookings as $b)
                        <option value="{{ $b->id }}">{{ $b->booking_code }} - {{ $b->vehicle?->name ?? ($b->category?->name ?? '-') }} ({{ $b->user?->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Tipe *</label>
                        <select name="type" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50">
                            <option value="pre_rental">Pre-Rental (awal)</option>
                            <option value="post_rental">Post-Rental (akhir)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Unit Kendaraan *</label>
                        <select name="inspection_item_id" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50">
                            <option value="">Pilih Unit</option>
                            @foreach($modalVehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->license_plate ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Kondisi Keseluruhan (1-10)</label>
                        <input type="number" name="overall_condition" min="1" max="10" value="7" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] text-center font-bold focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Level BBM (%)</label>
                        <input type="number" name="fuel_level" min="0" max="100" value="100" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] text-center font-bold focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50 resize-none" placeholder="Catatan kondisi unit..."></textarea>
                </div>
                <div class="flex gap-3 pt-2 border-t border-gray-100">
                    <button type="submit" :disabled="loading" class="bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white px-6 py-2.5 rounded-xl text-[13px] font-bold shadow-lg shadow-sky-500/25 transition flex items-center gap-2 disabled:opacity-50">
                        <i class="fas fa-paper-plane" x-show="!loading"></i>
                        <i class="fas fa-spinner fa-spin" x-show="loading" x-cloak></i>
                        <span x-text="loading ? 'Menyimpan...' : 'Simpan Inspeksi'"></span>
                    </button>
                    <button type="button" @click="open = false" class="bg-gray-100 hover:bg-gray-200 px-5 py-2.5 rounded-xl text-[13px] font-medium text-navy-700 transition">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-sky-50/50 border-b border-sky-100/50">
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Item</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Booking</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Tipe Sewa</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Tipe</th>
                <th class="text-center py-3 px-4 text-navy-500 font-medium">Kondisi</th>
                <th class="text-center py-3 px-4 text-navy-500 font-medium">Kerusakan</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Status</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Inspektur</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Tanggal</th>
                <th class="text-left py-3 px-4 text-navy-500 font-medium">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($inspections as $i)
                <tr class="border-b hover:bg-sky-50/30">
                    <td class="py-3 px-4 font-medium text-navy-800">{{ $i->getItemName() }}</td>
                    <td class="py-3 px-4 text-sky-600 font-medium text-xs">{{ $i->booking?->booking_code ?? '-' }}</td>
                    <td class="py-3 px-4">
                        @if($i->booking && $i->booking->with_driver !== null)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $i->booking->with_driver ? 'bg-sky-100 text-sky-700' : 'bg-gray-100 text-gray-700' }}">{{ $i->getRentalTypeLabel() }}</span>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $i->type == 'pre_rental' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700' }}">{{ $i->getTypeLabel() }}</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="font-bold">{{ $i->overall_condition ?? '-' }}/10</span>
                        <span class="text-[10px] text-gray-400 block">{{ $i->getConditionLabel() }}</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        @if(!empty($i->damage_items) && count($i->damage_items) > 0)
                            <span class="badge badge-red text-[10px]">{{ count($i->damage_items) }} Temuan</span>
                        @else
                            <span class="badge badge-green text-[10px]">Aman</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        @if($i->status == 'reported')
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Laporan Driver</span>
                        @elseif($i->status == 'processing')
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Dikerjakan</span>
                        @elseif($i->status == 'completed')
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Selesai</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">{{ $i->getStatusLabel() }}</span>
                        @endif
                        @if($i->reported_by && $i->reportedBy)
                            <span class="text-[10px] text-gray-400 block mt-0.5">oleh {{ $i->reportedBy->name }}</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-xs text-navy-600">{{ $i->inspector?->name ?? ($i->assignedTo?->name ?? '-') }}</td>
                    <td class="py-3 px-4 text-xs text-navy-500">{{ $i->created_at->format('d M Y') }}</td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('inspections.show', $i) }}" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-600 transition" title="Detail">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            @if($i->status == 'reported' && (auth()->user()->isSuperAdmin() || auth()->user()->isMerchantStaff() || auth()->user()->isInspector() || auth()->user()->isDriver()))
                            <form method="POST" action="{{ route('inspections.start', $i) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 transition" title="Kerjakan"><i class="fas fa-tasks text-xs"></i></button>
                            </form>
                            @endif
                            @if($i->status == 'processing' && (auth()->user()->isSuperAdmin() || auth()->user()->isMerchantStaff() || auth()->user()->isInspector() || auth()->user()->isDriver()))
                            <form method="POST" action="{{ route('inspections.complete', $i) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-green-50 hover:bg-green-100 text-green-600 transition" title="Tandai Selesai"><i class="fas fa-check text-xs"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="py-8 text-center text-navy-400">Belum ada data inspeksi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $inspections->links() }}</div>
</div>
@endsection
