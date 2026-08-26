@extends('layouts.dashboard')
@section('page-title', 'Maintenance')

@section('content')
<div x-data="{ showForm: false }">
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-5 gap-3">
    <div>
        <h2 class="text-lg font-extrabold text-navy-800 flex items-center gap-2">
            <i class="fas fa-wrench text-amber-500"></i> Maintenance Unit
        </h2>
        <p class="text-xs text-gray-400 mt-0.5">Jadwalkan dan kelola maintenance kendaraan serta unit lainnya.</p>
    </div>
    <button @click="showForm = true" class="btn-primary text-white px-4 py-2 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-1.5">
        <i class="fas fa-plus text-[10px]"></i> Tambah Jadwal
    </button>
</div>

{{-- FILTERS --}}
<div class="glass-card rounded-2xl p-4 mb-5 border border-sky-100/50 shadow-sm">
    <form action="{{ route('maintenances.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
        <div class="flex-1 w-full">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cari</label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-[11px]"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Judul, kode, nama kendaraan..."
                    class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50">
            </div>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Tipe</label>
            <select name="type" class="border border-gray-200 rounded-xl px-3 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50 min-w-[130px]">
                <option value="">Semua</option>
                <option value="routine" {{ request('type') == 'routine' ? 'selected' : '' }}>Rutin</option>
                <option value="repair" {{ request('type') == 'repair' ? 'selected' : '' }}>Perbaikan</option>
                <option value="inspection" {{ request('type') == 'inspection' ? 'selected' : '' }}>Inspeksi</option>
                <option value="emergency" {{ request('type') == 'emergency' ? 'selected' : '' }}>Darurat</option>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Status</label>
            <select name="status" class="border border-gray-200 rounded-xl px-3 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50 min-w-[130px]">
                <option value="">Semua</option>
                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Proses</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Prioritas</label>
            <select name="priority" class="border border-gray-200 rounded-xl px-3 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50 min-w-[130px]">
                <option value="">Semua</option>
                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Rendah</option>
                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Sedang</option>
                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Tinggi</option>
                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Mendesak</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2.5 rounded-xl text-[12px] font-semibold shadow-sm">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['search','type','status','priority']))
            <a href="{{ route('maintenances.index') }}" class="bg-red-50 hover:bg-red-100 text-red-500 px-3 py-2.5 rounded-xl text-[12px] font-medium transition border border-red-100">
                <i class="fas fa-times text-[10px]"></i> Reset
            </a>
            @endif
        </div>
    </form>
</div>

{{-- TABLE --}}
<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-sky-50/50 border-b border-sky-100/50">
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kode</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Judul</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Kendaraan</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Tipe</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Prioritas</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Jadwal</th>
                    <th class="text-center py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                    <th class="text-left py-3 px-5 text-gray-400 font-semibold text-[11px] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenances as $m)
                @php
                    $priorityColors = ['low' => 'badge-green', 'medium' => 'badge-yellow', 'high' => 'bg-orange-100 text-orange-700', 'urgent' => 'badge-red'];
                    $statusColors = ['scheduled' => 'badge-blue', 'in_progress' => 'badge-yellow', 'completed' => 'badge-green', 'cancelled' => 'badge-gray'];
                    $typeLabels = ['routine' => 'Rutin', 'repair' => 'Perbaikan', 'inspection' => 'Inspeksi', 'emergency' => 'Darurat'];
                    $priorityLabels = ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'urgent' => 'Mendesak'];
                    $statusLabels = ['scheduled' => 'Terjadwal', 'in_progress' => 'Proses', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
                @endphp
                <tr class="border-b border-gray-50 last:border-0 hover:bg-sky-50/30 transition-colors">
                    <td class="py-3 px-5 font-mono font-medium text-sky-600 text-[12px]">{{ $m->maintenance_code }}</td>
                    <td class="py-3 px-5 text-navy-700 font-medium">{{ $m->title }}</td>
                    <td class="py-3 px-5 text-navy-600">{{ $m->vehicle?->name ?? '-' }}</td>
                    <td class="py-3 px-5 text-[12px] text-navy-600">{{ $typeLabels[$m->type] ?? $m->type }}</td>
                    <td class="py-3 px-5 text-center">
                        <span class="{{ $priorityColors[$m->priority] ?? 'badge-gray' }}" style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;display:inline-block;">{{ $priorityLabels[$m->priority] ?? $m->priority }}</span>
                    </td>
                    <td class="py-3 px-5 text-[12px] text-navy-600">
                        {{ $m->scheduled_date->format('d M Y') }}
                        @if($m->completed_date)<br><span class="text-emerald-500 text-[11px]">Selesai: {{ $m->completed_date->format('d M Y') }}</span>@endif
                    </td>
                    <td class="py-3 px-5 text-center">
                        <span class="{{ $statusColors[$m->status] ?? 'badge-gray' }}" style="padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;display:inline-block;">{{ $statusLabels[$m->status] ?? $m->status }}</span>
                    </td>
                    <td class="py-3 px-5">
                        @if($m->status !== 'completed' && $m->status !== 'cancelled')
                        <div class="flex items-center gap-2">
                            @if($m->status === 'scheduled')
                            <form method="POST" action="{{ route('maintenances.update', $m) }}" class="inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit" class="text-amber-600 text-[11px] font-semibold hover:text-amber-700"><i class="fas fa-play mr-1"></i>Proses</button>
                            </form>
                            @elseif($m->status === 'in_progress')
                            <form method="POST" action="{{ route('maintenances.update', $m) }}" class="inline" x-data>
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="completed">
                                <input type="hidden" name="actual_cost" value="{{ $m->estimated_cost }}">
                                <button type="submit" class="text-emerald-600 text-[11px] font-semibold hover:text-emerald-700"><i class="fas fa-check mr-1"></i>Selesai</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('maintenances.update', $m) }}" class="inline" x-data>
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="text-red-500 text-[11px] font-semibold hover:text-red-600" onclick="return confirm('Batalkan jadwal ini?')"><i class="fas fa-times mr-1"></i>Batal</button>
                            </form>
                        </div>
                        @else
                        <span class="text-gray-400 text-[11px]">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-14 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-wrench text-amber-300 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-[13px] font-medium text-navy-700">Belum ada jadwal maintenance</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Klik "Tambah Jadwal" untuk membuat jadwal baru</p>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">{{ $maintenances->withQueryString()->links() }}</div>
</div>

{{-- ADD FORM MODAL --}}
<div x-show="showForm" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @click.self="showForm=false">
    <div class="fixed inset-0 bg-navy-900/40 backdrop-blur-sm"></div>
    <div class="relative glass-card rounded-2xl p-6 w-full max-w-lg shadow-2xl border border-sky-100/50 animate-slide-up" @click.stop>
        <button @click="showForm=false" class="absolute top-3 right-3 w-7 h-7 rounded-lg bg-gray-100 hover:bg-red-100 flex items-center justify-center text-gray-400 hover:text-red-500 transition">
            <i class="fas fa-times text-xs"></i>
        </button>
        <h3 class="text-[15px] font-bold text-navy-800 mb-5 flex items-center gap-2">
            <i class="fas fa-plus-circle text-amber-500"></i> Jadwal Maintenance Baru
        </h3>
        <form method="POST" action="{{ route('maintenances.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Judul *</label>
                    <input type="text" name="title" required placeholder="Contoh: Ganti Oli Rutin" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Kendaraan *</label>
                    <select name="vehicle_id" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <option value="">Pilih Kendaraan</option>
                        @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->brand }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tipe *</label>
                        <select name="type" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            <option value="routine">Rutin</option>
                            <option value="repair">Perbaikan</option>
                            <option value="inspection">Inspeksi</option>
                            <option value="emergency">Darurat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Prioritas *</label>
                        <select name="priority" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            <option value="low">Rendah</option>
                            <option value="medium" selected>Sedang</option>
                            <option value="high">Tinggi</option>
                            <option value="urgent">Mendesak</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tanggal Jadwal *</label>
                        <input type="date" name="scheduled_date" required value="{{ now()->format('Y-m-d') }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Estimasi Biaya (Rp)</label>
                        <input type="number" name="estimated_cost" value="0" min="0" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Teknisi</label>
                    <input type="text" name="technician" placeholder="Nama teknisi / bengkel" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" placeholder="Detail pekerjaan..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500"></textarea>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan tambahan..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-xl text-[13px] font-semibold shadow-lg shadow-sky-500/25"><i class="fas fa-save mr-1.5"></i> Simpan</button>
                <button type="button" @click="showForm=false" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-xl text-[13px] font-medium text-navy-700">Batal</button>
            </div>
        </form>
    </div>
</div>

</div>
@endsection
