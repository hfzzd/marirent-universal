@extends('layouts.dashboard')
@section('page-title', 'Laporan Perjalanan')

@section('content')
@php
    $role = auth()->user()->role;
    $isDriver = $role === 'driver';
    $totalReports = $reports->total();
    // Hitung ringkasan dari query dasar (tanpa paginasi) agar akurat
    $summaryQuery = \App\Models\TripReport::query();
    if ($isDriver) {
        $dId = \App\Models\Driver::where('user_id', auth()->id())->value('id');
        // Sesuaikan dengan scope controller jika ada; fallback ke semua milik driver via booking
        if ($dId) {
            $summaryQuery->whereHas('booking', fn($q) => $q->where('driver_id', $dId));
        }
    }
    if (request('status')) {
        // ringkasan global, bukan filter aktif — tetap hitung semua agar konsisten
    }
    $completedCount = (clone $summaryQuery)->where('status', 'completed')->count();
    $issuesCount = (clone $summaryQuery)->where('status', 'has_issues')->count();
    $totalDistance = (clone $summaryQuery)->sum('total_distance');
    $totalCost = (clone $summaryQuery)->sum('total_operational_cost');
@endphp

{{-- SUMMARY CARDS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="glass-card rounded-2xl p-4 border border-sky-100/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-500/20">
                <i class="fas fa-route text-white text-sm"></i>
            </div>
            <div>
                <p class="text-[20px] font-extrabold text-navy-800">{{ $totalReports }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Total Laporan</p>
            </div>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-4 border border-emerald-100/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                <i class="fas fa-check-circle text-white text-sm"></i>
            </div>
            <div>
                <p class="text-[20px] font-extrabold text-emerald-600">{{ $completedCount }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Selesai</p>
            </div>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-4 border border-amber-100/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/20">
                <i class="fas fa-road text-white text-sm"></i>
            </div>
            <div>
                <p class="text-[20px] font-extrabold text-amber-600">{{ number_format($totalDistance, 1) }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Total KM</p>
            </div>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-4 border border-violet-100/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/20">
                <i class="fas fa-coins text-white text-sm"></i>
            </div>
            <div>
                <p class="text-[20px] font-extrabold text-violet-600">Rp {{ number_format($totalCost / 1000, 0, ',', '.') }}k</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Biaya Operasional</p>
            </div>
        </div>
    </div>
</div>

{{-- HEADER & ACTION --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-5 gap-3">
    <div>
        <h2 class="text-lg font-extrabold text-navy-800 flex items-center gap-2">
            <i class="fas fa-route text-sky-500"></i> Laporan Perjalanan
        </h2>
        <p class="text-xs text-gray-400 mt-0.5">Kelola laporan perjalanan dan kondisi kendaraan.</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        @if(in_array(auth()->user()->role, ['admin', 'owner'], true))
        <button type="button" x-data @click="$dispatch('open-report-modal')" class="bg-gradient-to-r from-sky-500 to-sky-600 text-white px-4 py-2 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-1.5">
            <i class="fas fa-bolt text-[10px]"></i> Laporan Cepat
        </button>
        @endif
        <a href="{{ route('reports.create') }}" class="bg-white border border-gray-200 hover:border-sky-300 hover:text-sky-600 text-gray-500 px-4 py-2 rounded-xl text-[12px] font-semibold transition flex items-center gap-1.5">
            <i class="fas fa-plus text-[10px]"></i> Form Lengkap
        </a>
    </div>
</div>

{{-- MODAL LAPORAN CEPAT (khusus akun admin & owner) --}}
@if(in_array(auth()->user()->role, ['admin', 'owner'], true))
<div x-data="{ open: false, loading: false }" @open-report-modal.window="open = true" x-cloak>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50" @click="open = false"></div>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="open = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 rounded-t-2xl p-5 relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-route text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Laporan Cepat</h3>
                            <p class="text-sky-200 text-[11px]">Catat perjalanan tanpa buka halaman baru</p>
                        </div>
                    </div>
                    <button @click="open = false" class="text-white/70 hover:text-white transition"><i class="fas fa-times text-lg"></i></button>
                </div>
            </div>
            <form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data" @submit="loading = true" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Booking (Perjalanan Aktif) *</label>
                    <x-searchable-select name="booking_id" placeholder="Pilih Booking" required>
                        <option value="">Pilih Booking</option>
                        @foreach($modalBookings as $b)
                        <option value="{{ $b->id }}">{{ $b->booking_code }} - {{ $b->vehicle?->name ?? ($b->category?->name ?? '-') }} ({{ $b->user?->name }})</option>
                        @endforeach
                    </x-searchable-select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Odometer Awal (km)</label>
                        <input type="number" name="start_odometer" min="0" placeholder="0" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] text-center font-bold focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Odometer Akhir (km)</label>
                        <input type="number" name="end_odometer" min="0" placeholder="0" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] text-center font-bold focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([['fuel_cost','BBM'],['toll_cost','Tol'],['parking_cost','Parkir'],['other_cost','Lainnya']] as [$field, $label])
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">{{ $label }} (Rp)</label>
                        <input type="number" name="{{ $field }}" min="0" value="0" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] text-right font-medium focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50">
                    </div>
                    @endforeach
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Catatan Perjalanan</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50 resize-none" placeholder="Catatan perjalanan..."></textarea>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Masalah yang Ditemukan</label>
                    <textarea name="issues_reported" rows="2" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 outline-none bg-gray-50/50 resize-none" placeholder="Kosongkan bila tidak ada masalah..."></textarea>
                </div>
                <div class="flex gap-3 pt-2 border-t border-gray-100">
                    <button type="submit" :disabled="loading" class="bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white px-6 py-2.5 rounded-xl text-[13px] font-bold shadow-lg shadow-sky-500/25 transition flex items-center gap-2 disabled:opacity-50">
                        <i class="fas fa-paper-plane" x-show="!loading"></i>
                        <i class="fas fa-spinner fa-spin" x-show="loading" x-cloak></i>
                        <span x-text="loading ? 'Menyimpan...' : 'Simpan Laporan'"></span>
                    </button>
                    <button type="button" @click="open = false" class="bg-gray-100 hover:bg-gray-200 px-5 py-2.5 rounded-xl text-[13px] font-medium text-navy-700 transition">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- FILTERS --}}
<div class="glass-card rounded-2xl p-4 mb-5 border border-sky-100/50 shadow-sm">
    <form action="{{ route('reports.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
        <div class="flex-1 w-full">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cari</label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-[11px]"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode booking, nama kendaraan..."
                    class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-[12px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50">
            </div>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Status</label>
            <x-searchable-select name="status" placeholder="Semua" size="sm" wrapClass="min-w-[140px]">
                <option value="">Semua</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="has_issues" {{ request('status') == 'has_issues' ? 'selected' : '' }}>Bermasalah</option>
            </x-searchable-select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2.5 rounded-xl text-[12px] font-semibold shadow-sm">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('reports.index') }}" class="bg-red-50 hover:bg-red-100 text-red-500 px-3 py-2.5 rounded-xl text-[12px] font-medium transition border border-red-100">
                <i class="fas fa-times text-[10px]"></i> Reset
            </a>
            @endif
        </div>
    </form>
</div>

{{-- REPORTS CARDS --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($reports as $r)
    @php
        $statusConfig = match($r->status) {
            'completed' => ['color' => 'emerald', 'icon' => 'fa-check-circle', 'label' => 'Selesai', 'bg' => 'from-emerald-50 to-teal-50', 'border' => 'border-emerald-200'],
            'has_issues' => ['color' => 'red', 'icon' => 'fa-exclamation-triangle', 'label' => 'Bermasalah', 'bg' => 'from-red-50 to-rose-50', 'border' => 'border-red-200'],
            default => ['color' => 'amber', 'icon' => 'fa-clock', 'label' => 'Berlangsung', 'bg' => 'from-amber-50 to-orange-50', 'border' => 'border-amber-200'],
        };
    @endphp
    <div class="glass-card rounded-2xl overflow-hidden border {{ $statusConfig['border'] }} hover:shadow-lg transition-all duration-300 group" style="box-shadow: 0 2px 16px rgba(0,0,0,0.03);">
        <div class="bg-gradient-to-r {{ $statusConfig['bg'] }} p-4 border-b {{ $statusConfig['border'] }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas {{ $statusConfig['icon'] }} text-{{ $statusConfig['color'] }}-500 text-sm"></i>
                    <span class="text-[11px] font-bold text-{{ $statusConfig['color'] }}-600 uppercase tracking-wider">{{ $statusConfig['label'] }}</span>
                </div>
                <span class="text-[11px] text-gray-400">{{ $r->created_at->format('d M Y') }}</span>
            </div>
        </div>
        <div class="p-4">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-[13px] font-bold text-navy-800">{{ $r->booking->booking_code ?? '-' }}</p>
                    <p class="text-[12px] text-gray-400 mt-0.5">{{ $r->vehicle?->name ?? '-' }}</p>
                </div>
                <div class="w-8 h-8 bg-sky-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-car text-sky-400 text-sm"></i>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="bg-gray-50 rounded-xl p-2.5">
                    <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold">Jarak</p>
                    <p class="text-[13px] font-bold text-navy-700">{{ $r->total_distance ? number_format($r->total_distance, 1) . ' km' : '-' }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-2.5">
                    <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold">Biaya</p>
                    <p class="text-[13px] font-bold text-navy-700">Rp {{ number_format($r->total_operational_cost, 0, ',', '.') }}</p>
                </div>
            </div>

            @if($r->notes)
            <p class="text-[11px] text-gray-500 mb-3 line-clamp-2">{{ Str::limit($r->notes, 80) }}</p>
            @endif

            <div class="flex items-center justify-between pt-3 border-t border-gray-100/60">
                <div class="flex items-center gap-1.5">
                    @if($r->booking?->driver?->user)
                    <div class="w-5 h-5 bg-sky-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-tie text-sky-500 text-[8px]"></i>
                    </div>
                    <span class="text-[11px] text-gray-500">{{ $r->booking->driver->user->name }}</span>
                    @endif
                </div>
                @if(
                    in_array($role, ['superadmin','owner','admin']) ||
                    ($isDriver && $r->booking?->driver_id === (\App\Models\Driver::where('user_id', auth()->id())->first()?->id ?? 0)) ||
                    ($role === 'user' && $r->booking?->user_id === auth()->id())
                )
                <a href="{{ route('reports.show', $r) }}" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-3 py-1.5 rounded-lg text-[11px] font-semibold transition flex items-center gap-1.5">
                    <i class="fas fa-eye text-[9px]"></i> Detail
                </a>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-16">
        <div class="w-20 h-20 bg-gradient-to-br from-sky-50 to-blue-50 rounded-3xl flex items-center justify-center mx-auto mb-5 border border-sky-100">
            <i class="fas fa-route text-sky-300 text-3xl"></i>
        </div>
        <h3 class="text-lg font-bold text-navy-800 mb-2">Belum ada laporan</h3>
        <p class="text-gray-400 text-[13px] mb-5">Laporan perjalanan akan muncul di sini</p>
        <a href="{{ route('reports.create') }}" class="bg-sky-500 hover:bg-sky-600 text-white px-6 py-2.5 rounded-2xl text-[13px] font-semibold inline-flex items-center gap-2 transition-all duration-300 hover:shadow-lg">
            <i class="fas fa-plus text-[11px]"></i> Buat Laporan
        </a>
    </div>
    @endforelse
</div>

@if($reports->hasPages())
<div class="mt-8 flex justify-center">
    {{ $reports->withQueryString()->links() }}
</div>
@endif
@endsection
