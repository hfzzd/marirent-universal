@extends('layouts.dashboard')
@section('page-title', 'Detail Penggantian - ' . ($replacement->booking?->booking_code ?? 'N/A'))
@section('content')
<div class="max-w-4xl">
    <a href="{{ route('replacements.index') }}" class="text-sky-600 text-sm mb-4 inline-flex items-center hover:text-sky-700 transition"><i class="fas fa-arrow-left mr-1.5"></i> Kembali ke Daftar</a>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-4 text-[13px] flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-4 text-[13px] flex items-center gap-2">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Header --}}
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-navy-900 flex items-center gap-2">
                            <i class="fas fa-right-left text-sky-500"></i> Penggantian Kendaraan
                        </h2>
                        <p class="text-[13px] text-gray-400 mt-1">Dibuat: {{ $replacement->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div>
                        @if($replacement->status == 'pending')
                            <span class="badge badge-blue text-[12px] px-3 py-1.5">Pending</span>
                        @elseif($replacement->status == 'approved')
                            <span class="badge badge-green text-[12px] px-3 py-1.5">Disetujui</span>
                        @elseif($replacement->status == 'rejected')
                            <span class="badge badge-red text-[12px] px-3 py-1.5">Ditolak</span>
                        @else
                            <span class="badge badge-gray text-[12px] px-3 py-1.5">{{ ucfirst($replacement->status) }}</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-sky-50/50 p-4 rounded-xl">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Booking</p>
                        <a href="{{ $replacement->booking ? route('bookings.show', $replacement->booking) : '#' }}" class="font-bold text-sky-600 text-[15px] hover:text-sky-700 transition">{{ $replacement->booking?->booking_code ?? '-' }}</a>
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ $replacement->booking?->created_at?->format('d M Y') ?? '-' }}</p>
                    </div>
                    <div class="bg-sky-50/50 p-4 rounded-xl">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Status Booking</p>
                        <p class="font-bold text-navy-800 text-[15px] capitalize">{{ $replacement->booking?->status ?? '-' }}</p>
                        @if($replacement->booking?->start_date && $replacement->booking?->end_date)
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ $replacement->booking->start_date->format('d M Y') }} — {{ $replacement->booking->end_date->format('d M Y') }}</p>
                        @endif
                    </div>
                    <div class="bg-sky-50/50 p-4 rounded-xl">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Dimanajemen Oleh</p>
                        <p class="font-bold text-navy-800 text-[15px]">{{ $replacement->requestedBy?->name ?? '-' }}</p>
                        <p class="text-[12px] text-gray-400 mt-0.5 capitalize">{{ $replacement->requestedBy?->role ?? '-' }}</p>
                    </div>
                    @if($replacement->approvedBy)
                    <div class="bg-sky-50/50 p-4 rounded-xl">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Disetujui Oleh</p>
                        <p class="font-bold text-navy-800 text-[15px]">{{ $replacement->approvedBy->name }}</p>
                        @if($replacement->swapped_at)
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ $replacement->swapped_at->format('d M Y H:i') }}</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Vehicle Comparison --}}
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-car text-sky-500 mr-2"></i>Perbandingan Kendaraan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="border-2 border-dashed border-red-200 bg-red-50/40 p-4 rounded-xl">
                        <p class="text-[11px] text-red-400 font-semibold uppercase tracking-wider mb-2">Unit Asal</p>
                        @if($replacement->originalVehicle)
                            <p class="font-bold text-navy-800 text-[15px]">{{ $replacement->originalVehicle->name }}</p>
                            <p class="text-[12px] text-gray-400 mt-0.5">{{ $replacement->originalVehicle->brand }} {{ $replacement->originalVehicle->model }} {{ $replacement->originalVehicle->year }}</p>
                            @if($replacement->originalVehicle->license_plate)
                            <p class="text-[12px] text-gray-500 mt-1"><i class="fas fa-id-card mr-1"></i> {{ $replacement->originalVehicle->license_plate }}</p>
                            @endif
                            @if($replacement->originalVehicle->status === 'maintenance')
                            <span class="inline-flex items-center gap-1 mt-2 text-[11px] font-semibold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full"><i class="fas fa-wrench"></i> Maintenance</span>
                            @endif
                        @else
                            <p class="font-bold text-navy-800">-</p>
                        @endif
                    </div>
                    <div class="border-2 border-dashed border-green-200 bg-green-50/40 p-4 rounded-xl">
                        <p class="text-[11px] text-green-500 font-semibold uppercase tracking-wider mb-2">Unit Pengganti</p>
                        @if($replacement->replacementVehicle)
                            <p class="font-bold text-navy-800 text-[15px]">{{ $replacement->replacementVehicle->name }}</p>
                            <p class="text-[12px] text-gray-400 mt-0.5">{{ $replacement->replacementVehicle->brand }} {{ $replacement->replacementVehicle->model }} {{ $replacement->replacementVehicle->year }}</p>
                            @if($replacement->replacementVehicle->license_plate)
                            <p class="text-[12px] text-gray-500 mt-1"><i class="fas fa-id-card mr-1"></i> {{ $replacement->replacementVehicle->license_plate }}</p>
                            @endif
                            @if($replacement->replacementVehicle->daily_price)
                            <p class="text-[12px] text-sky-600 font-medium mt-1">Rp {{ number_format($replacement->replacementVehicle->daily_price, 0, ',', '.') }}/hari</p>
                            @endif
                        @else
                            <p class="font-bold text-navy-800">-</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Reason --}}
            @if($replacement->reason)
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-2"><i class="fas fa-comment-dots text-amber-400 mr-2"></i>Alasan Penggantian</h3>
                <p class="text-[13px] text-gray-600">{{ $replacement->reason }}</p>
            </div>
            @endif

            {{-- Admin Notes --}}
            @if($replacement->admin_notes)
            <div class="glass-card rounded-2xl p-6 border border-sky-100">
                <h3 class="text-[14px] font-bold text-navy-800 mb-2"><i class="fas fa-sticky-note text-sky-500 mr-2"></i>Catatan Admin</h3>
                <p class="text-[13px] text-gray-600">{{ $replacement->admin_notes }}</p>
            </div>
            @endif

            {{-- Photo Evidence --}}
            @if($replacement->initial_vehicle_photo || $replacement->final_vehicle_photo)
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-camera text-sky-500 mr-2"></i>Bukti Foto</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if($replacement->initial_vehicle_photo)
                    <div>
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-2">Foto Awal Unit</p>
                        <img src="{{ asset('storage/' . $replacement->initial_vehicle_photo) }}" alt="Foto Awal" class="w-full rounded-xl border border-gray-200 shadow-sm object-cover max-h-48">
                    </div>
                    @endif
                    @if($replacement->final_vehicle_photo)
                    <div>
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-2">Foto Akhir Unit</p>
                        <img src="{{ asset('storage/' . $replacement->final_vehicle_photo) }}" alt="Foto Akhir" class="w-full rounded-xl border border-gray-200 shadow-sm object-cover max-h-48">
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Latest Trip Report --}}
            @if($replacement->booking && $replacement->booking->tripReport)
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-3"><i class="fas fa-route text-sky-500 mr-2"></i>Laporan Perjalanan Terakhir</h3>
                @php $latestReport = $replacement->booking->tripReport; @endphp
                <div class="bg-sky-50/60 border border-sky-100 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[13px] font-semibold text-navy-800">{{ $latestReport->created_at->format('d M Y H:i') }}</p>
                        <a href="{{ route('reports.show', $latestReport) }}" class="text-[11px] text-sky-600 hover:text-sky-700 font-medium"><i class="fas fa-external-link-alt mr-1"></i>Lihat</a>
                    </div>
                    @if($latestReport->notes)
                    <p class="text-[13px] text-gray-600">{{ $latestReport->notes }}</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Approval Actions (admin) --}}
            @if(in_array(auth()->user()->role, ['superadmin','owner','admin']))
            <div class="glass-card rounded-2xl p-6 border border-sky-100" x-data="{ showStatusForm: false }">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-cog text-sky-500 mr-2"></i>Ubah Status</h3>

                @if($replacement->status == 'pending')
                <div class="flex flex-wrap gap-3 mb-4">
                    <form method="POST" action="{{ route('replacements.approve', $replacement) }}" onsubmit="return confirm('Setujui penggantian kendaraan ini?')">@csrf
                        <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition flex items-center gap-2 shadow-sm"><i class="fas fa-check"></i> Setujui</button>
                    </form>
                    @if(in_array(auth()->user()->role, ['superadmin','owner']))
                    <form method="POST" action="{{ route('replacements.reject', $replacement) }}" onsubmit="return confirm('Tolak penggantian kendaraan ini?')">@csrf
                        <button class="bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition flex items-center gap-2 shadow-sm"><i class="fas fa-times"></i> Tolak</button>
                    </form>
                    @endif
                </div>
                @endif

                @if(in_array(auth()->user()->role, ['superadmin','owner']))
                <button @click="showStatusForm = !showStatusForm" class="text-[12px] text-sky-600 hover:text-sky-700 font-medium flex items-center gap-1 transition">
                    <i class="fas fa-edit text-[10px]"></i>
                    <span x-text="showStatusForm ? 'Sembunyikan' : 'Ganti Status Manual'"></span>
                </button>

                <div x-show="showStatusForm" x-transition class="mt-4 bg-gray-50 rounded-xl p-4">
                    <form method="POST" action="{{ route('replacements.update-status', $replacement) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Status Baru</label>
                            <select name="status" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                                <option value="pending" {{ $replacement->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $replacement->status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="rejected" {{ $replacement->status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Catatan Admin</label>
                            <textarea name="admin_notes" rows="2" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white" placeholder="Catatan untuk perubahan status...">{{ $replacement->admin_notes }}</textarea>
                        </div>
                        <button type="submit" onclick="return confirm('Ubah status penggantian ini?')" class="bg-sky-500 hover:bg-sky-600 text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition flex items-center gap-2 shadow-sm">
                            <i class="fas fa-save"></i> Simpan Status
                        </button>
                    </form>
                </div>
                @endif
            </div>
            @endif
        </div>

        <div class="space-y-5">
            {{-- Price Difference --}}
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-coins text-sky-500 mr-2"></i>Selisih Harga</h3>
                @php $diff = (float) $replacement->price_difference; @endphp
                <div class="text-center py-3">
                    @if($diff > 0)
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1">Biaya Tambahan</p>
                        <p class="text-2xl font-bold text-red-600">+ Rp {{ number_format($diff, 0, ',', '.') }}</p>
                    @elseif($diff < 0)
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1">Penghematan</p>
                        <p class="text-2xl font-bold text-emerald-600">- Rp {{ number_format(abs($diff), 0, ',', '.') }}</p>
                    @else
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1">Harga Tetap</p>
                        <p class="text-2xl font-bold text-navy-700">Rp 0</p>
                    @endif
                </div>
                @if($diff != 0)
                <div class="bg-gray-50 rounded-xl p-3 mt-2">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Harga Booking Saat Ini</p>
                    <div class="space-y-1">
                        <div class="flex justify-between text-[13px]">
                            <span class="text-gray-400">Total</span>
                            <span class="font-medium text-navy-700">Rp {{ number_format($replacement->booking?->total_price ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-[13px]">
                            <span class="text-gray-400">Final</span>
                            <span class="font-medium text-navy-700">Rp {{ number_format($replacement->booking?->final_price ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Driver & User Info --}}
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-users text-sky-500 mr-2"></i>Informasi Orang Terkait</h3>
                <div class="space-y-4">
                    @if($replacement->booking?->user)
                    <div class="bg-sky-50/50 p-3.5 rounded-xl">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Penyewa</p>
                        <p class="font-bold text-navy-800 text-[14px]">{{ $replacement->booking->user->name }}</p>
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ $replacement->booking->user->email }}</p>
                        @if($replacement->booking->user->phone)
                        <p class="text-[12px] text-gray-400 mt-0.5"><i class="fas fa-phone mr-1"></i> {{ $replacement->booking->user->phone }}</p>
                        @endif
                    </div>
                    @endif
                    @if($replacement->booking?->driver && $replacement->booking?->driver?->user)
                    <div class="bg-sky-50/50 p-3.5 rounded-xl">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Driver</p>
                        <p class="font-bold text-navy-800 text-[14px]">{{ $replacement->booking->driver->user->name }}</p>
                        @if($replacement->booking->driver->license_type)
                        <p class="text-[12px] text-gray-400 mt-0.5">SIM {{ $replacement->booking->driver->license_type }}</p>
                        @endif
                        @if($replacement->booking->driver->phone ?? $replacement->booking->driver->user->phone)
                        <p class="text-[12px] text-gray-400 mt-0.5"><i class="fas fa-phone mr-1"></i> {{ $replacement->booking->driver->phone ?? $replacement->booking->driver->user->phone }}</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Timeline --}}
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-clock text-sky-500 mr-2"></i>Timeline</h3>
                <div class="space-y-4 relative">
                    <div class="absolute left-3 top-2 bottom-2 w-0.5 bg-gray-100"></div>
                    <div class="flex items-start gap-3 relative">
                        <div class="w-6 h-6 bg-sky-100 rounded-full flex items-center justify-center flex-shrink-0 z-10"><i class="fas fa-plus text-sky-500 text-[8px]"></i></div>
                        <div>
                            <p class="text-[12px] font-medium text-navy-700">Permintaan Dibuat</p>
                            <p class="text-[11px] text-gray-400">{{ $replacement->created_at->format('d M Y H:i') }}</p>
                            <p class="text-[11px] text-gray-400">oleh {{ $replacement->requestedBy?->name ?? '-' }}</p>
                        </div>
                    </div>
                    @if($replacement->status == 'approved' && $replacement->swapped_at)
                    <div class="flex items-start gap-3 relative">
                        <div class="w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0 z-10"><i class="fas fa-check text-emerald-500 text-[8px]"></i></div>
                        <div>
                            <p class="text-[12px] font-medium text-navy-700">Disetujui & Ditukar</p>
                            <p class="text-[11px] text-gray-400">{{ $replacement->swapped_at->format('d M Y H:i') }}</p>
                            @if($replacement->approvedBy)
                            <p class="text-[11px] text-gray-400">oleh {{ $replacement->approvedBy->name }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    @if($replacement->status == 'rejected')
                    <div class="flex items-start gap-3 relative">
                        <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 z-10"><i class="fas fa-times text-red-500 text-[8px]"></i></div>
                        <div>
                            <p class="text-[12px] font-medium text-red-600">Ditolak</p>
                            @if($replacement->approvedBy)
                            <p class="text-[11px] text-gray-400">oleh {{ $replacement->approvedBy->name }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Link Terkait --}}
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-3"><i class="fas fa-link text-sky-500 mr-2"></i>Link Terkait</h3>
                <div class="space-y-2">
                    @if($replacement->booking)
                    <a href="{{ route('bookings.show', $replacement->booking) }}" class="flex items-center gap-2.5 text-[13px] text-sky-600 hover:text-sky-700 hover:bg-sky-50 px-3 py-2.5 rounded-xl transition">
                        <i class="fas fa-file-alt w-4"></i> Detail Booking
                    </a>
                    @endif
                    @if($replacement->originalVehicle)
                    <a href="{{ route('vehicles.index') }}" class="flex items-center gap-2.5 text-[13px] text-sky-600 hover:text-sky-700 hover:bg-sky-50 px-3 py-2.5 rounded-xl transition">
                        <i class="fas fa-car w-4"></i> Daftar Kendaraan
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
