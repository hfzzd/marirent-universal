@extends(auth()->user()->role === 'user' ? 'layouts.user' : 'layouts.dashboard')
@section('title', 'Detail Booking - MariRent')
@section('page-title', 'Detail Booking - ' . $booking->booking_code)
@section('content')
<div class="max-w-4xl">
    <a href="{{ route('bookings.index') }}" class="text-sky-600 text-sm mb-4 inline-flex items-center hover:text-sky-700 transition"><i class="fas fa-arrow-left mr-1.5"></i> Kembali ke Daftar</a>

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
            {{-- Info Utama --}}
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-navy-900">{{ $booking->booking_code }}</h2>
                        <p class="text-[13px] text-gray-400 mt-1">Dibuat: {{ $booking->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($booking->status == 'pending') <span class="badge badge-blue text-[12px] px-3 py-1.5">Pending</span>
                        @elseif($booking->status == 'confirmed') <span class="badge badge-teal text-[12px] px-3 py-1.5">Confirmed</span>
                        @elseif($booking->status == 'ongoing') <span class="badge badge-yellow text-[12px] px-3 py-1.5">Ongoing</span>
                        @elseif($booking->status == 'completed') <span class="badge badge-green text-[12px] px-3 py-1.5">Selesai</span>
                        @elseif($booking->status == 'cancelled') <span class="badge badge-red text-[12px] px-3 py-1.5">Dibatalkan</span>
                        @else <span class="badge badge-gray text-[12px] px-3 py-1.5">{{ ucfirst($booking->status) }}</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    {{-- Item --}}
                    <div class="bg-sky-50/50 p-4 rounded-xl">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Barang / Kendaraan</p>
                        @if($booking->vehicle)
                        <p class="font-bold text-navy-800 text-[15px]">{{ $booking->vehicle->name }}</p>
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ $booking->vehicle->brand }} {{ $booking->vehicle->model }} {{ $booking->vehicle->year }}</p>
                        @elseif($booking->category)
                        <p class="font-bold text-navy-800 text-[15px]">{{ $booking->category->name }}</p>
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ $booking->item_type ? class_basename($booking->item_type) : '-' }}</p>
                        @else
                        <p class="font-bold text-navy-800">-</p>
                        @endif
                    </div>

                    {{-- Pengguna --}}
                    <div class="bg-sky-50/50 p-4 rounded-xl">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Pengguna</p>
                        <p class="font-bold text-navy-800 text-[15px]">{{ $booking->user->name ?? '-' }}</p>
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ $booking->user->email ?? '-' }}</p>
                    </div>

                    {{-- Tanggal --}}
                    <div class="bg-sky-50/50 p-4 rounded-xl">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Tanggal Sewa</p>
                        <p class="font-medium text-navy-800 text-[14px]">{{ $booking->start_date->format('d M Y H:i') }}</p>
                        <p class="text-[12px] text-gray-400 mt-0.5">s/d {{ $booking->end_date->format('d M Y H:i') }}</p>
                        <p class="text-[11px] text-sky-600 font-medium mt-1">{{ $booking->start_date->diffInDays($booking->end_date) }} hari</p>
                    </div>

                    {{-- Driver --}}
                    <div class="bg-sky-50/50 p-4 rounded-xl">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Driver</p>
                        @if($booking->driver)
                        <p class="font-bold text-navy-800 text-[15px]">{{ $booking->driver->user->name ?? '-' }}</p>
                        <p class="text-[12px] text-gray-400 mt-0.5">SIM {{ $booking->driver->license_type ?? '-' }}</p>
                        @else
                        <p class="font-bold text-navy-800 text-[15px]">Tanpa Driver</p>
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ ucfirst($booking->rental_type) }}</p>
                        @endif
                    </div>
                </div>

                {{-- Lokasi --}}
                @if($booking->pickup_location || $booking->dropoff_location)
                <div class="bg-sky-50/50 p-4 rounded-xl mb-6">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-2">Lokasi</p>
                    <div class="flex items-start gap-3">
                        <div class="flex flex-col items-center gap-1 mt-1">
                            <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></div>
                            <div class="w-0.5 h-6 bg-gray-200"></div>
                            <div class="w-2.5 h-2.5 bg-red-500 rounded-full"></div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[11px] text-gray-400 font-medium">Jemput</p>
                                <p class="text-[13px] text-navy-700">{{ $booking->pickup_location }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] text-gray-400 font-medium">Antar</p>
                                <p class="text-[13px] text-navy-700">{{ $booking->dropoff_location }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Aksi --}}
                @if(in_array(auth()->user()->role, ['superadmin','owner']))
                <div class="flex flex-wrap gap-2 border-t border-gray-100 pt-4">
                    @if($booking->status == 'pending')
                    <form method="POST" action="{{ route('bookings.confirm', $booking) }}">@csrf
                        <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition flex items-center gap-2 shadow-sm"><i class="fas fa-check"></i> Konfirmasi</button>
                    </form>
                    @endif
                    @if($booking->status == 'confirmed')
                    <form method="POST" action="{{ route('bookings.start', $booking) }}">@csrf
                        <button class="bg-sky-500 hover:bg-sky-600 text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition flex items-center gap-2 shadow-sm"><i class="fas fa-play"></i> Mulai Perjalanan</button>
                    </form>
                    @endif
                    @if($booking->status == 'ongoing')
                    <form method="POST" action="{{ route('bookings.complete', $booking) }}">@csrf
                        <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition flex items-center gap-2 shadow-sm"><i class="fas fa-check-double"></i> Selesai</button>
                    </form>
                    @endif
                    @if(!in_array($booking->status, ['completed','cancelled']))
                    <form method="POST" action="{{ route('bookings.cancel', $booking) }}">@csrf
                        <button class="bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition flex items-center gap-2 shadow-sm"><i class="fas fa-times"></i> Batalkan</button>
                    </form>
                    @endif
                </div>
                @endif
            </div>

            {{-- Catatan --}}
            @if($booking->notes)
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-2"><i class="fas fa-sticky-note text-amber-400 mr-2"></i>Catatan</h3>
                <p class="text-[13px] text-gray-600">{{ $booking->notes }}</p>
            </div>
            @endif

            @if($booking->status == 'cancelled' && $booking->cancellation_reason)
            <div class="glass-card rounded-2xl p-6 border border-red-100">
                <h3 class="text-[14px] font-bold text-red-600 mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Alasan Pembatalan</h3>
                <p class="text-[13px] text-gray-600">{{ $booking->cancellation_reason }}</p>
            </div>
            @endif

            {{-- KTP Photo --}}
            @if(auth()->user()->id == $booking->user_id || in_array(auth()->user()->role, ['superadmin','owner']))
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-id-card text-sky-500 mr-2"></i>Bukti Foto KTP</h3>
                @if($booking->ktp_photo)
                <div class="relative inline-block">
                    <img src="{{ asset('storage/' . $booking->ktp_photo) }}" alt="KTP {{ $booking->user->name }}" class="max-w-full md:max-w-md rounded-xl border border-gray-200 shadow-sm">
                    <div class="mt-2 flex items-center gap-2">
                        <span class="badge badge-green"><i class="fas fa-check-circle mr-1"></i> KTP Telah Diunggah</span>
                        <span class="text-[11px] text-gray-400">{{ $booking->updated_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
                @else
                @if(auth()->user()->id == $booking->user_id && !in_array($booking->status, ['completed', 'cancelled']))
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-sky-300 transition-colors" id="ktp-upload-area">
                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 mb-3"></i>
                    <p class="text-[13px] text-gray-500 mb-1">Unggah foto KTP Anda</p>
                    <p class="text-[11px] text-gray-400 mb-3">Format: JPG, PNG, WEBP (Maks. 2MB)</p>
                    <form method="POST" action="{{ route('bookings.upload-ktp', $booking) }}" enctype="multipart/form-data" id="ktp-form">
                        @csrf
                        <input type="file" name="ktp_photo" id="ktp-input" accept="image/*" class="hidden" onchange="previewKTP(this)">
                        <label for="ktp-input" class="btn-primary text-white px-5 py-2 rounded-xl text-[12px] font-semibold shadow-lg shadow-sky-500/25 transition cursor-pointer inline-flex items-center gap-1.5">
                            <i class="fas fa-upload text-[11px]"></i> Pilih Foto KTP
                        </label>
                    </form>
                    <div id="ktp-preview" class="mt-4 hidden">
                        <img id="ktp-preview-img" class="max-w-xs mx-auto rounded-lg border border-gray-200 shadow-sm">
                        <div class="mt-3 flex justify-center gap-2">
                            <button type="button" onclick="document.getElementById('ktp-form').submit()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl text-[12px] font-semibold transition shadow-sm">
                                <i class="fas fa-save mr-1"></i> Simpan KTP
                            </button>
                            <button type="button" onclick="cancelKTP()" class="bg-gray-100 hover:bg-gray-200 text-navy-700 px-4 py-2 rounded-xl text-[12px] font-medium transition">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-6">
                    <i class="fas fa-id-card text-gray-200 text-3xl mb-2"></i>
                    <p class="text-[12px] text-gray-400">Belum ada foto KTP</p>
                </div>
                @endif
                @endif
            </div>
            @endif
        </div>

        <div class="space-y-5">
            {{-- Ringkasan Biaya --}}
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-receipt text-sky-500 mr-2"></i>Ringkasan Biaya</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-[13px]">
                        <span class="text-gray-400">Harga Sewa</span>
                        <span class="font-medium text-navy-700">Rp {{ number_format($booking->base_price,0,',','.') }}</span>
                    </div>
                    @if($booking->driver_price > 0)
                    <div class="flex justify-between text-[13px]">
                        <span class="text-gray-400">Biaya Driver</span>
                        <span class="font-medium text-navy-700">Rp {{ number_format($booking->driver_price,0,',','.') }}</span>
                    </div>
                    @endif
                    @if($booking->discount > 0)
                    <div class="flex justify-between text-[13px]">
                        <span class="text-gray-400">Diskon</span>
                        <span class="font-medium text-red-500">- Rp {{ number_format($booking->discount,0,',','.') }}</span>
                    </div>
                    @endif
                    <div class="border-t border-gray-100 pt-3 flex justify-between">
                        <span class="font-bold text-navy-800">Total</span>
                        <span class="font-bold text-sky-600 text-lg">Rp {{ number_format($booking->final_price,0,',','.') }}</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-[11px] text-gray-400">Status Pembayaran</span>
                    @if($booking->payment_status == 'paid') <span class="badge badge-green">Lunas</span>
                    @elseif($booking->payment_status == 'partial') <span class="badge badge-yellow">Sebagian</span>
                    @else <span class="badge badge-red">Belum Bayar</span>
                    @endif
                </div>
            </div>

            {{-- Link Terkait --}}
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-3"><i class="fas fa-link text-sky-500 mr-2"></i>Link Terkait</h3>
                <div class="space-y-2">
                    <a href="{{ route('inspections.index', ['booking_id' => $booking->id]) }}" class="flex items-center gap-2.5 text-[13px] text-sky-600 hover:text-sky-700 hover:bg-sky-50 px-3 py-2.5 rounded-xl transition">
                        <i class="fas fa-clipboard-check w-4"></i> Inspeksi
                    </a>
                    <a href="{{ route('reports.index', ['booking_id' => $booking->id]) }}" class="flex items-center gap-2.5 text-[13px] text-sky-600 hover:text-sky-700 hover:bg-sky-50 px-3 py-2.5 rounded-xl transition">
                        <i class="fas fa-route w-4"></i> Laporan Perjalanan
                    </a>
                    <a href="{{ route('invoices.index') }}" class="flex items-center gap-2.5 text-[13px] text-sky-600 hover:text-sky-700 hover:bg-sky-50 px-3 py-2.5 rounded-xl transition">
                        <i class="fas fa-file-invoice-dollar w-4"></i> Invoice
                    </a>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-clock text-sky-500 mr-2"></i>Timeline</h3>
                <div class="space-y-4 relative">
                    <div class="absolute left-3 top-2 bottom-2 w-0.5 bg-gray-100"></div>
                    <div class="flex items-start gap-3 relative">
                        <div class="w-6 h-6 bg-sky-100 rounded-full flex items-center justify-center flex-shrink-0 z-10"><i class="fas fa-plus text-sky-500 text-[8px]"></i></div>
                        <div><p class="text-[12px] font-medium text-navy-700">Booking Dibuat</p><p class="text-[11px] text-gray-400">{{ $booking->created_at->format('d M Y H:i') }}</p></div>
                    </div>
                    @if($booking->status !== 'pending')
                    <div class="flex items-start gap-3 relative">
                        <div class="w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0 z-10"><i class="fas fa-check text-emerald-500 text-[8px]"></i></div>
                        <div><p class="text-[12px] font-medium text-navy-700">Dikonfirmasi</p></div>
                    </div>
                    @endif
                    @if($booking->actual_start_date)
                    <div class="flex items-start gap-3 relative">
                        <div class="w-6 h-6 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0 z-10"><i class="fas fa-play text-amber-500 text-[8px]"></i></div>
                        <div><p class="text-[12px] font-medium text-navy-700">Perjalanan Dimulai</p><p class="text-[11px] text-gray-400">{{ $booking->actual_start_date->format('d M Y H:i') }}</p></div>
                    </div>
                    @endif
                    @if($booking->actual_end_date)
                    <div class="flex items-start gap-3 relative">
                        <div class="w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0 z-10"><i class="fas fa-flag-checkered text-emerald-500 text-[8px]"></i></div>
                        <div><p class="text-[12px] font-medium text-navy-700">Selesai</p><p class="text-[11px] text-gray-400">{{ $booking->actual_end_date->format('d M Y H:i') }}</p></div>
                    </div>
                    @endif
                    @if($booking->status == 'cancelled')
                    <div class="flex items-start gap-3 relative">
                        <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 z-10"><i class="fas fa-times text-red-500 text-[8px]"></i></div>
                        <div><p class="text-[12px] font-medium text-red-600">Dibatalkan</p></div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewKTP(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('ktp-preview-img').src = e.target.result;
            document.getElementById('ktp-preview').classList.remove('hidden');
            document.getElementById('ktp-upload-area').querySelector('i').classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function cancelKTP() {
    document.getElementById('ktp-input').value = '';
    document.getElementById('ktp-preview').classList.add('hidden');
}
</script>
@endpush
