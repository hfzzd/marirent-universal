@extends('layouts.public')
@section('title', 'Jadwalkan Demo - MariRent')
@section('meta_description', 'Jadwalkan demo gratis aplikasi MariRent. Tim kami akan menghubungi Anda maksimal 1x24 jam.')

@section('content')
<section class="hero-section relative overflow-hidden bg-navy-900">
    <div class="absolute -top-32 right-0 w-[420px] h-[420px] rounded-full bg-sky-500/10 blur-3xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-14 lg:pt-36 relative text-center">
        <span class="eyebrow mb-5 !bg-white/10 !text-sky-200 !border-white/10 fade-in-up"><i class="fas fa-calendar-check"></i> Demo Gratis</span>
        <h1 class="h-display text-white fade-in-up fade-in-up-delay-1">Jadwalkan Demo Aplikasi</h1>
        <p class="lead !text-sky-200/80 max-w-xl mx-auto mt-5 fade-in-up fade-in-up-delay-2">Isi form di bawah — tim kami akan menghubungi Anda via WhatsApp maksimal 1x24 jam untuk konfirmasi jadwal.</p>
    </div>
</section>

<section class="section bg-sky-50/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- FORM --}}
            <div class="lg:col-span-7 reveal">
                <div class="card-clean !rounded-3xl p-7 md:p-10 !shadow-xl">
                    @if(session('success'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-[13px] font-semibold flex items-start gap-3">
                        <i class="fas fa-check-circle text-emerald-500 text-lg mt-0.5"></i><span>{{ session('success') }}</span>
                    </div>
                    @endif
                    <h2 class="text-xl font-extrabold text-navy-900">Formulir Permintaan Demo</h2>
                    <p class="body-sm text-gray-400 mt-1 mb-8">Semua kolom bertanda <span class="text-red-500">*</span> wajib diisi.</p>
                    <form method="POST" action="{{ route('demo.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        @csrf
                        <div>
                            <label class="block text-[12px] font-bold text-navy-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nama Anda" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                            @error('name')<p class="text-red-500 text-[12px] mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-navy-700 mb-1.5">Nama Usaha Rental</label>
                            <input type="text" name="business_name" value="{{ old('business_name') }}" placeholder="cth: Berkah Rental" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-navy-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@anda.com" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                            @error('email')<p class="text-red-500 text-[12px] mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-navy-700 mb-1.5">No. WhatsApp <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                            @error('phone')<p class="text-red-500 text-[12px] mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-navy-700 mb-1.5">Tanggal Demo <span class="text-red-500">*</span></label>
                            <input type="date" name="preferred_date" value="{{ old('preferred_date') }}" required min="{{ date('Y-m-d') }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                            @error('preferred_date')<p class="text-red-500 text-[12px] mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-navy-700 mb-1.5">Jam Demo <span class="text-red-500">*</span></label>
                            <select name="preferred_time" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition bg-white">
                                <option value="">— Pilih jam —</option>
                                @foreach(['09.00 – 10.00', '10.00 – 11.00', '11.00 – 12.00', '13.00 – 14.00', '14.00 – 15.00', '15.00 – 16.00', '16.00 – 17.00'] as $jam)
                                <option value="{{ $jam }}" {{ old('preferred_time') === $jam ? 'selected' : '' }}>{{ $jam }} WIB</option>
                                @endforeach
                            </select>
                            @error('preferred_time')<p class="text-red-500 text-[12px] mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[12px] font-bold text-navy-700 mb-1.5">Catatan / Kebutuhan Khusus</label>
                            <textarea name="notes" rows="4" placeholder="cth: ingin fokus ke laporan keuangan & 20 unit mobil" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[14px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none resize-none transition">{{ old('notes') }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="btn-primary w-full text-white px-8 py-4 rounded-2xl font-bold text-[15px] shadow-lg shadow-sky-500/25 inline-flex items-center justify-center gap-2"><i class="fas fa-paper-plane"></i> Kirim Permintaan Demo</button>
                            <p class="text-center text-[12px] text-gray-400 mt-3">atau langsung <a href="https://wa.me/6281234567890?text=Halo%20min%20saya%20mau%20jadwal%20demo%20MariRent" target="_blank" class="text-emerald-600 font-bold hover:underline">chat WhatsApp <i class="fab fa-whatsapp"></i></a></p>
                        </div>
                    </form>
                </div>
            </div>

            {{-- INFO SAMPING --}}
            <div class="lg:col-span-5 space-y-5">
                <div class="card-clean !rounded-3xl p-7 reveal reveal-delay-1">
                    <h3 class="font-extrabold text-navy-900 text-[16px] mb-5">Alur Demo</h3>
                    @foreach([
                        ['Isi formulir', 'Pilih tanggal & jam yang nyaman untuk Anda.'],
                        ['Kami menghubungi', 'Konfirmasi jadwal via WhatsApp maks. 1x24 jam.'],
                        ['Sesi demo 30 menit', 'Lihat langsung fitur yang relevan dengan usaha Anda.'],
                        ['Putuskan dengan tenang', 'Tanpa komitmen — lanjut berlangganan kapan pun siap.'],
                    ] as $i => $s)
                    <div class="flex gap-4 {{ !$loop->last ? 'pb-5 mb-5 border-b border-gray-100' : '' }}">
                        <span class="w-9 h-9 rounded-xl bg-sky-600 text-white font-extrabold text-[14px] flex items-center justify-center flex-shrink-0">{{ $i + 1 }}</span>
                        <div><p class="font-bold text-navy-900 text-[14px]">{{ $s[0] }}</p><p class="body-sm text-gray-500 mt-0.5">{{ $s[1] }}</p></div>
                    </div>
                    @endforeach
                </div>
                <div class="rounded-3xl p-7 text-white reveal reveal-delay-2" style="background: linear-gradient(135deg, #0c4a6e, #0284c7);">
                    <h3 class="font-extrabold text-[16px]">Yang akan Anda lihat</h3>
                    <ul class="mt-4 space-y-2.5 text-[14px] text-sky-100">
                        <li class="flex gap-2.5"><i class="fas fa-check text-emerald-300 mt-1"></i> Dasbor monitoring & scheduler operasional</li>
                        <li class="flex gap-2.5"><i class="fas fa-check text-emerald-300 mt-1"></i> Alur booking → pembayaran → serah terima unit</li>
                        <li class="flex gap-2.5"><i class="fas fa-check text-emerald-300 mt-1"></i> Laporan keuangan otomatis & penggantian unit</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
