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
                <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
                    <div>
                        <h2 class="text-xl font-bold text-navy-900">{{ $booking->booking_code }}
                            @if(($booking->source ?? 'online') == 'manual')<span class="badge badge-teal text-[10px] align-middle ml-1">Booking Manual</span>@endif
                        </h2>
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    {{-- Item --}}
                    <div class="bg-gradient-to-br from-sky-50/80 to-sky-100/40 p-4 rounded-xl border border-sky-100/60">
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
                    <div class="bg-gradient-to-br from-emerald-50/60 to-emerald-100/30 p-4 rounded-xl border border-emerald-100/50">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Pengguna</p>
                        <p class="font-bold text-navy-800 text-[15px]">{{ $booking->user->name ?? '-' }}</p>
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ $booking->user->email ?? '-' }}</p>
                    </div>

                    {{-- Tanggal --}}
                    <div class="bg-gradient-to-br from-amber-50/60 to-amber-100/30 p-4 rounded-xl border border-amber-100/50" x-data="{ rescheduleOpen: false }">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Tanggal Sewa</p>
                        <p class="font-medium text-navy-800 text-[14px]">{{ $booking->start_date->format('d M Y H:i') }}</p>
                        <p class="text-[12px] text-gray-400 mt-0.5">s/d {{ $booking->end_date->format('d M Y H:i') }}</p>
                        <p class="text-[11px] text-sky-600 font-medium mt-1">{{ $booking->start_date->diffInDays($booking->end_date) }} hari</p>

                        @if($isMerchantStaff && in_array($booking->status, ['pending', 'confirmed', 'ongoing']))
                        <button type="button" @click="rescheduleOpen = !rescheduleOpen" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold transition">
                            <i class="fas fa-calendar-plus text-[10px]"></i> Ubah Jadwal
                        </button>
                        <form method="POST" action="{{ route('bookings.reschedule', $booking) }}" x-show="rescheduleOpen" x-transition class="mt-3 space-y-2.5">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-semibold text-navy-700 mb-1">Mulai Baru</label>
                                <input type="datetime-local" name="start_date" value="{{ $booking->start_date->format('Y-m-d\TH:i') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-navy-700 mb-1">Selesai Baru</label>
                                <input type="datetime-local" name="end_date" value="{{ $booking->end_date->format('Y-m-d\TH:i') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                            </div>
                            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white py-2 rounded-lg text-[11px] font-bold transition">
                                <i class="fas fa-refresh text-[10px] mr-1"></i> Simpan Ubah Jadwal & Hitung Ulang
                            </button>
                        </form>
                        @endif
                    </div>

                    @if($booking->vehicle)
                    {{-- Driver --}}
                    <div class="bg-gradient-to-br from-purple-50/60 to-purple-100/30 p-4 rounded-xl border border-purple-100/50">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Driver</p>
                        @if($booking->driver && $booking->with_driver)
                        <p class="font-bold text-navy-800 text-[15px]">{{ $booking->driver->user->name ?? '-' }}</p>
                        <p class="text-[12px] text-gray-400 mt-0.5">SIM {{ $booking->driver->license_type ?? '-' }}</p>
                        @else
                        <p class="font-bold text-navy-800 text-[15px]">Lepas Kunci</p>
                        <p class="text-[12px] text-gray-400 mt-0.5">Tanpa driver</p>
                        @endif
                    </div>
                    @endif

                    {{-- Assign Driver (Owner/Superadmin) --}}
                    @if($booking->vehicle && in_array(auth()->user()->role, ['superadmin','owner']) && !in_array($booking->status, ['completed', 'cancelled']))
                    <div class="bg-gradient-to-br from-indigo-50/60 to-indigo-100/30 p-4 rounded-xl border border-indigo-100/50" x-data="{ open: false }">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-1">Kelola Driver</p>
                                @if($booking->driver)
                                <p class="text-[12px] text-navy-700 font-medium">Saat ini: {{ $booking->driver->user->name ?? ('#' . $booking->driver->id) }}</p>
                                @else
                                <p class="text-[12px] text-navy-700 font-medium">Belum ada driver ditugaskan</p>
                                @endif
                            </div>
                            <button type="button" @click="open = !open" class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold transition whitespace-nowrap">
                                <i class="fas fa-user-cog text-[10px] mr-1"></i> {{ $booking->driver ? 'Ganti' : 'Tugaskan' }}
                            </button>
                        </div>
                        <form method="POST" action="{{ route('bookings.assign-driver', $booking) }}" x-show="open" x-cloak x-transition class="mt-3 space-y-2.5">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-semibold text-navy-700 mb-1">Pilih Driver *</label>
                                <select name="driver_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <option value="">-- Pilih driver tersedia --</option>
                                    @foreach($availableDrivers as $d)
                                    <option value="{{ $d->id }}" {{ $booking->driver_id == $d->id ? 'selected' : '' }}>
                                        {{ $d->user->name ?? ('#' . $d->id) }} ({{ $d->status == 'off_duty' ? 'Tersedia' : $d->status }})
                                    </option>
                                    @endforeach
                                </select>
                                @if($availableDrivers->isEmpty())
                                <p class="text-[11px] text-red-500 mt-1">Tidak ada driver tersedia untuk unit ini.</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-[11px] font-bold transition" {{ $availableDrivers->isEmpty() ? 'disabled' : '' }}>
                                    <i class="fas fa-check text-[10px] mr-1"></i> Simpan Driver
                                </button>
                                @if($booking->driver)
                                <button type="submit" formaction="{{ route('bookings.remove-driver', $booking) }}" onclick="return confirm('Lepas driver dari booking ini?')" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg text-[11px] font-medium transition">
                                    <i class="fas fa-user-minus text-[10px] mr-1"></i> Lepas
                                </button>
                                @endif
                            </div>
                            <p class="text-[10px] text-gray-400">Harga driver dihitung ulang otomatis dari tarif with-driver unit dan ditambahkan ke tagihan.</p>
                        </form>
                    </div>
                    @endif
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
                        <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow-md hover:shadow-emerald-500/20 active:scale-[0.97]"><i class="fas fa-check"></i> Konfirmasi</button>
                    </form>
                    @endif
                    @if($booking->status == 'confirmed')
                    <form method="POST" action="{{ route('bookings.start', $booking) }}">@csrf
                        <button class="bg-sky-500 hover:bg-sky-600 text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow-md hover:shadow-sky-500/20 active:scale-[0.97]"><i class="fas fa-play"></i> Mulai Perjalanan</button>
                    </form>
                    @endif
                    @if($booking->status == 'ongoing')
                    <form method="POST" action="{{ route('bookings.complete', $booking) }}">@csrf
                        <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow-md hover:shadow-emerald-500/20 active:scale-[0.97]"><i class="fas fa-check-double"></i> Selesai</button>
                    </form>
                    @endif
                    @if(!in_array($booking->status, ['completed','cancelled']))
                    <form method="POST" action="{{ route('bookings.cancel', $booking) }}" onsubmit="return confirm('Yakin batalkan booking ini?')">@csrf
                        <button class="bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow-md hover:shadow-red-500/20 active:scale-[0.97]"><i class="fas fa-times"></i> Batalkan</button>
                    </form>
                    @endif
                </div>
                @endif

                {{-- Aksi Inspector --}}
                @if(auth()->user()->role === 'inspector' && in_array($booking->status, ['confirmed','ongoing']) && !$booking->with_driver)
                <div class="border-t border-gray-100 pt-4 mt-4">
                    <a href="{{ route('inspections.create', ['booking_id' => $booking->id]) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white px-5 py-3 rounded-xl text-[13px] font-bold transition-all duration-200 shadow-md shadow-emerald-500/20 active:scale-[0.97]">
                        <i class="fas fa-clipboard-check"></i> Inspeksi Unit Booking Ini
                    </a>
                    <p class="text-[11px] text-gray-400 mt-2">Klik untuk langsung membuka form inspeksi dengan booking & unit ini sudah terpilih.</p>
                </div>
                @endif

                {{-- Ganti Kendaraan Langsung (Quick Swap) --}}
                @if($booking->vehicle && in_array($booking->status, ['confirmed','ongoing']) && in_array(auth()->user()->role, ['superadmin','owner']) && $swappableVehicles->isNotEmpty())
                <div x-data="{ open: false }" class="mt-4 border-t border-gray-100 pt-4">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <p class="text-[12px] font-bold text-navy-800 flex items-center gap-2"><i class="fas fa-right-left text-sky-500"></i> Ganti Kendaraan</p>
                            <p class="text-[11px] text-gray-400">Tukar unit langsung saat sewa berjalan. Hanya unit dalam kategori yang sama ({{ $booking->vehicle->category?->name ?? '-' }}). Harga tetap kecuali diisi biaya tambahan.</p>
                        </div>
                        <button type="button" @click="open = !open" class="bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200 px-4 py-2 rounded-xl text-[12px] font-bold transition whitespace-nowrap">
                            <i class="fas fa-repeat mr-1"></i> Ganti Sekarang
                        </button>
                    </div>
                    <form method="POST" action="{{ route('bookings.replace-vehicle', $booking) }}" x-show="open" x-cloak x-transition class="mt-3 bg-sky-50/70 border border-sky-100 rounded-xl p-4 space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-navy-700 mb-1">Kendaraan Pengganti *</label>
                                <select name="replacement_vehicle_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white">
                                    <option value="">-- Pilih unit tersedia --</option>
                                    @foreach($swappableVehicles as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->category?->name ?? '-' }}) - Rp {{ number_format($v->daily_price, 0, ',', '.') }}/hari</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-navy-700 mb-1">Alasan</label>
                                <input type="text" name="reason" placeholder="Contoh: ban bocor / rusak mendadak" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-navy-700 mb-1">Biaya Tambahan / Selisih (opsional)</label>
                                <input type="number" step="0.01" min="-999999999" name="price_difference" placeholder="0 = harga tetap" value="{{ old('price_difference') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                <p class="text-[10px] text-gray-400 mt-1">Kosongkan / 0 bila harga sewa tidak berubah. Bisa diubah kapan saja lewat invoice.</p>
                            </div>
                            <div class="flex items-end pb-1">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="hidden" name="mark_maintenance" value="0">
                                    <input type="checkbox" name="mark_maintenance" value="1" checked class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                                    <span class="text-[11px] font-semibold text-navy-700">Unit lama rusak &rarr; tandai maintenance</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" onclick="return confirm('Tukar kendaraan untuk booking ini sekarang?')" class="btn-primary text-white px-5 py-2 rounded-lg text-[12px] font-bold shadow-md shadow-sky-500/20 transition"><i class="fas fa-arrows-rotate mr-1"></i> Konfirmasi Tukar Unit</button>
                            <span class="text-[10px] text-gray-400">Penyewa otomatis mendapat notifikasi penggantian.</span>
                        </div>
                    </form>
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

            {{-- Riwayat Penggantian Kendaraan --}}
            @if($replacements->isNotEmpty())
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-right-left text-sky-500 mr-2"></i>Riwayat Penggantian Kendaraan</h3>
                <div class="space-y-3">
                    @foreach($replacements as $r)
                    <div class="flex items-start gap-3 bg-sky-50/60 border border-sky-100 rounded-xl p-3">
                        <div class="w-8 h-8 rounded-lg bg-sky-500/10 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fas fa-exchange-alt text-sky-600 text-[12px]"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-semibold text-navy-800 leading-snug">
                                {{ $r->originalVehicle?->name ?? 'Unit lama' }}
                                <i class="fas fa-arrow-right text-sky-400 mx-1.5"></i>
                                {{ $r->replacementVehicle?->name ?? 'Unit baru' }}
                            </p>
                            @if($r->reason)
                            <p class="text-[11px] text-gray-500 mt-0.5">Alasan: {{ $r->reason }}</p>
                            @endif
                            <p class="text-[10px] text-gray-400 mt-1">
                                {{ optional($r->swapped_at ?? $r->created_at)->format('d M Y H:i') }} oleh {{ $r->requestedBy?->name ?? '-' }}
                                @if((float) $r->price_difference != 0)
                                    &bull; Selisih: <span class="{{ (float) $r->price_difference > 0 ? 'text-red-600 font-semibold' : 'text-emerald-600 font-semibold' }}">Rp {{ number_format((float) $r->price_difference, 0, ',', '.') }}</span>
                                @else
                                    &bull; Harga tetap
                                @endif
                                @if($r->originalVehicle && $r->originalVehicle->status === 'maintenance')
                                    &bull; <span class="text-amber-600">Unit lama di-maintenance</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
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
                    <img src="{{ asset('storage/' . $booking->ktp_photo) }}" alt="KTP {{ $booking->user?->name }}" class="max-w-full md:max-w-md rounded-xl border border-gray-200 shadow-sm">
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
            <div class="glass-card rounded-2xl p-6 bg-gradient-to-br from-white to-sky-50/30">
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
                @if($booking->payment_plan)
                <div class="mt-3 flex items-center justify-between bg-sky-50 border border-sky-100 rounded-xl px-3.5 py-2.5">
                    <span class="text-[11px] font-bold text-sky-700"><i class="fas fa-credit-card mr-1"></i> Rencana Pembayaran</span>
                    <span class="text-[11px] font-bold text-navy-700">{{ $booking->payment_plan === 'dp50' ? 'DP 50%' : 'Bayar Penuh' }}</span>
                </div>
                @endif
                @if($booking->payment_plan === 'dp50' && $booking->getDpAmount() > 0)
                <div class="mt-3 flex items-center justify-between bg-amber-50 border border-amber-100 rounded-xl px-3.5 py-2.5">
                    <span class="text-[11px] font-bold text-amber-700"><i class="fas fa-hand-holding-dollar mr-1"></i> DP Minimal</span>
                    <span class="text-[11px] font-bold text-amber-700">Rp {{ number_format($booking->getDpAmount(), 0, ',', '.') }}</span>
                </div>
                @endif
                @if($booking->payment_due_date && $booking->payment_status != 'paid')
                <div class="mt-3 flex items-center justify-between bg-amber-50 border border-amber-100 rounded-xl px-3.5 py-2.5">
                    <span class="text-[11px] font-bold text-amber-700"><i class="fas fa-calendar-day mr-1"></i> Jatuh Tempo</span>
                    @php
                        $due = \Carbon\Carbon::parse($booking->payment_due_date);
                        $isOverdue = $due->isPast();
                    @endphp
                    <span class="text-[11px] font-bold {{ $isOverdue ? 'text-red-600' : 'text-navy-700' }}">
                        {{ $due->translatedFormat('d M Y') }}
                        @if($isOverdue) (Terlambat) @else ({{ now()->diffInDays($due) }} hari lagi) @endif
                    </span>
                </div>
                @endif
                @php
                    $verifiedPayments = $booking->payments->where('status', 'verified');
                    $lastVerifiedPayment = $verifiedPayments->sortByDesc('verified_at')->first();
                    $paymentConfirmed = $booking->payment_status === 'paid' || $verifiedPayments->isNotEmpty();
                    $verifiedTotal = $verifiedPayments->sum('amount') ?: (float) ($booking->invoice?->paid_amount ?? 0);
                @endphp
                @if($paymentConfirmed)
                <div class="mt-3 bg-emerald-50 border border-emerald-200 rounded-xl px-3.5 py-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-bold text-emerald-700"><i class="fas fa-circle-check mr-1"></i> Pembayaran Terkonfirmasi</span>
                        <span class="badge badge-green text-[10px]">{{ $booking->payment_status === 'paid' ? 'Lunas' : 'Terverifikasi' }}</span>
                    </div>
                    <p class="text-[12px] font-bold text-emerald-800 mt-2">Rp {{ number_format($verifiedTotal, 0, ',', '.') }}</p>
                    @if($lastVerifiedPayment)
                    <p class="text-[10px] text-emerald-700 mt-1">{{ ucfirst(str_replace('_', ' ', $lastVerifiedPayment->method)) }} &bull; {{ $lastVerifiedPayment->verified_at?->format('d M Y H:i') }}</p>
                    @elseif($booking->invoice?->paid_at)
                    <p class="text-[10px] text-emerald-700 mt-1">Dikonfirmasi {{ $booking->invoice->paid_at->format('d M Y H:i') }}</p>
                    @endif
                </div>
                @endif
            </div>

            {{-- Timeline --}}
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-[14px] font-bold text-navy-800 mb-4"><i class="fas fa-clock text-sky-500 mr-2"></i>Timeline</h3>
                <div class="space-y-5 relative">
                    <div class="absolute left-3.5 top-3 bottom-3 w-0.5 bg-gradient-to-b from-sky-200 via-emerald-200 to-gray-100"></div>
                    <div class="flex items-start gap-3.5 relative">
                        <div class="w-7 h-7 bg-gradient-to-br from-sky-400 to-sky-500 rounded-full flex items-center justify-center flex-shrink-0 z-10 shadow-sm shadow-sky-500/20"><i class="fas fa-plus text-white text-[9px]"></i></div>
                        <div class="pt-0.5"><p class="text-[12px] font-semibold text-navy-700">Booking Dibuat</p><p class="text-[11px] text-gray-400">{{ $booking->created_at->format('d M Y H:i') }}</p></div>
                    </div>
                    @if($booking->status !== 'pending')
                    <div class="flex items-start gap-3.5 relative">
                        <div class="w-7 h-7 bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 z-10 shadow-sm shadow-emerald-500/20"><i class="fas fa-check text-white text-[9px]"></i></div>
                        <div class="pt-0.5"><p class="text-[12px] font-semibold text-navy-700">Dikonfirmasi</p></div>
                    </div>
                    @endif
                    @if($booking->actual_start_date)
                    <div class="flex items-start gap-3.5 relative">
                        <div class="w-7 h-7 bg-gradient-to-br from-amber-400 to-amber-500 rounded-full flex items-center justify-center flex-shrink-0 z-10 shadow-sm shadow-amber-500/20"><i class="fas fa-play text-white text-[9px]"></i></div>
                        <div class="pt-0.5"><p class="text-[12px] font-semibold text-navy-700">Perjalanan Dimulai</p><p class="text-[11px] text-gray-400">{{ $booking->actual_start_date->format('d M Y H:i') }}</p></div>
                    </div>
                    @endif
                    @if($booking->actual_end_date)
                    <div class="flex items-start gap-3.5 relative">
                        <div class="w-7 h-7 bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 z-10 shadow-sm shadow-emerald-500/20"><i class="fas fa-flag-checkered text-white text-[9px]"></i></div>
                        <div class="pt-0.5"><p class="text-[12px] font-semibold text-navy-700">Selesai</p><p class="text-[11px] text-gray-400">{{ $booking->actual_end_date->format('d M Y H:i') }}</p></div>
                    </div>
                    @endif
                    @if($booking->status == 'cancelled')
                    <div class="flex items-start gap-3.5 relative">
                        <div class="w-7 h-7 bg-gradient-to-br from-red-400 to-red-500 rounded-full flex items-center justify-center flex-shrink-0 z-10 shadow-sm shadow-red-500/20"><i class="fas fa-times text-white text-[9px]"></i></div>
                        <div class="pt-0.5"><p class="text-[12px] font-semibold text-red-600">Dibatalkan</p></div>
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
