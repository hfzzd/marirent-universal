@extends('layouts.public')
@section('title', 'Kontak - MariRent Universal')
@section('meta_description', 'Hubungi tim MariRent - Kami siap membantu Anda 24/7. Telepon, email, WhatsApp, atau kunjungi kantor kami di Jakarta.')

@section('content')
{{-- HERO --}}
<section class="hero-section relative py-24 overflow-hidden" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 40%, #0ea5e9 100%);">
    <div class="absolute inset-0">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4 float"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-white/5 rounded-full translate-y-1/3 -translate-x-1/4 float-reverse"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-sky-200 px-4 py-1.5 rounded-full text-[12px] font-semibold mb-5 fade-in-up">
            <i class="fas fa-headset"></i> Dukungan Pelanggan
        </span>
        <h1 class="text-3xl sm:text-5xl font-extrabold text-white mb-4 fade-in-up fade-in-up-delay-1">Hubungi <span class="text-sky-300">Kami</span></h1>
        <p class="text-sky-200/80 text-base lg:text-lg max-w-xl mx-auto fade-in-up fade-in-up-delay-2">Ada pertanyaan atau masalah? Tim kami siap membantu Anda kapan saja</p>
    </div>
</section>

{{-- CONTACT INFO CARDS --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-14 reveal">
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group cursor-default">
            <div class="w-12 h-12 bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-sky-500/20 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-phone text-white text-lg"></i>
            </div>
            <h3 class="font-bold text-navy-800 text-[15px] mb-2">Telepon</h3>
            <p class="text-gray-600 text-[13px] mb-1">+62 812 3456 7890</p>
            <p class="text-gray-400 text-[11px]">Senin - Sabtu, 08:00 - 20:00</p>
            <a href="tel:+6281234567890" class="inline-flex items-center gap-1.5 mt-3 text-sky-600 text-[12px] font-semibold hover:text-sky-700">
                <i class="fas fa-phone-alt text-[10px]"></i> Hubungi Sekarang
            </a>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group cursor-default">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-envelope text-white text-lg"></i>
            </div>
            <h3 class="font-bold text-navy-800 text-[15px] mb-2">Email</h3>
            <p class="text-gray-600 text-[13px] mb-1">info@marirent.com</p>
            <p class="text-gray-400 text-[11px]">support@marirent.com</p>
            <a href="mailto:support@marirent.com" class="inline-flex items-center gap-1.5 mt-3 text-emerald-600 text-[12px] font-semibold hover:text-emerald-700">
                <i class="fas fa-paper-plane text-[10px]"></i> Kirim Email
            </a>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group cursor-default">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-amber-500/20 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-map-marker-alt text-white text-lg"></i>
            </div>
            <h3 class="font-bold text-navy-800 text-[15px] mb-2">Alamat Kantor</h3>
            <p class="text-gray-600 text-[13px] leading-relaxed mb-3">Jl. Sudirman No. 123<br>Jakarta Selatan, 12190</p>
            <a href="https://maps.google.com" target="_blank" class="inline-flex items-center gap-1.5 text-amber-600 text-[12px] font-semibold hover:text-amber-700">
                <i class="fas fa-external-link-alt text-[10px]"></i> Buka Maps
            </a>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group cursor-default">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform duration-300">
                <i class="fab fa-whatsapp text-white text-lg"></i>
            </div>
            <h3 class="font-bold text-navy-800 text-[15px] mb-2">WhatsApp</h3>
            <p class="text-gray-600 text-[13px] mb-1">+62 812 3456 7890</p>
            <p class="text-gray-400 text-[11px]">Chat langsung 24 jam</p>
            <a href="https://wa.me/6281234567890" target="_blank" class="inline-flex items-center gap-1.5 mt-3 text-emerald-600 text-[12px] font-semibold hover:text-emerald-700">
                <i class="fab fa-whatsapp text-[10px]"></i> Chat Sekarang
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        {{-- CONTACT FORM --}}
        <div class="lg:col-span-3 reveal">
            <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-md shadow-sky-500/20">
                        <i class="fas fa-paper-plane text-white text-sm"></i>
                    </div>
                    <h3 class="font-bold text-navy-800 text-lg">Kirim Pesan ke Kami</h3>
                </div>
                <p class="text-gray-400 text-[13px] mb-6 ml-13">Isi form di bawah ini dan kami akan membalas segera</p>
                @if(session('success'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2 animate-slide-up">
                    <i class="fas fa-check-circle text-emerald-500 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                <form method="POST" action="{{ route('contact.submit') }}" class="space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nama Anda" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all duration-200 hover:border-gray-300">
                            @error('name') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@anda.com" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all duration-200 hover:border-gray-300">
                            @error('email') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all duration-200 hover:border-gray-300">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Subjek <span class="text-red-500">*</span></label>
                            <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="Perihal pesan Anda" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all duration-200 hover:border-gray-300">
                            @error('subject') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Pesan <span class="text-red-500">*</span></label>
                        <textarea name="message" rows="5" required placeholder="Tulis pesan Anda di sini..." class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none resize-none transition-all duration-200 hover:border-gray-300">{{ old('message') }}</textarea>
                        @error('message') <span class="text-red-500 text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn-primary text-white px-8 py-3.5 rounded-xl font-semibold text-[13px] shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-2 w-full md:w-auto mx-auto md:mx-0">
                        <i class="fas fa-paper-plane"></i> Kirim Pesan
                    </button>
                </form>
            </div>
        </div>

        {{-- SIDEBAR --}}
        <div class="lg:col-span-2 space-y-5 reveal reveal-delay-2">
            {{-- SOCIAL MEDIA --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <h3 class="font-bold text-navy-800 text-[15px] mb-4">Ikuti Kami</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="https://instagram.com/marirent" target="_blank" class="flex items-center gap-3 bg-pink-50 hover:bg-pink-100 rounded-xl px-4 py-3 transition-all duration-200 group">
                        <i class="fab fa-instagram text-pink-500 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[12px] font-semibold text-pink-700">Instagram</span>
                    </a>
                    <a href="https://wa.me/6281234567890" target="_blank" class="flex items-center gap-3 bg-emerald-50 hover:bg-emerald-100 rounded-xl px-4 py-3 transition-all duration-200 group">
                        <i class="fab fa-whatsapp text-emerald-500 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[12px] font-semibold text-emerald-700">WhatsApp</span>
                    </a>
                    <a href="https://facebook.com/marirent" target="_blank" class="flex items-center gap-3 bg-blue-50 hover:bg-blue-100 rounded-xl px-4 py-3 transition-all duration-200 group">
                        <i class="fab fa-facebook text-blue-500 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[12px] font-semibold text-blue-700">Facebook</span>
                    </a>
                    <a href="https://tiktok.com/@marirent" target="_blank" class="flex items-center gap-3 bg-navy-50 hover:bg-navy-100 rounded-xl px-4 py-3 transition-all duration-200 group">
                        <i class="fab fa-tiktok text-navy-600 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[12px] font-semibold text-navy-700">TikTok</span>
                    </a>
                </div>
            </div>

            {{-- BUSINESS HOURS --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <h3 class="font-bold text-navy-800 text-[15px] mb-4 flex items-center gap-2">
                    <i class="fas fa-clock text-sky-500"></i> Jam Operasional
                </h3>
                <div class="space-y-2.5">
                    <div class="flex justify-between items-center text-[13px] py-2 border-b border-gray-50">
                        <span class="text-gray-500">Senin - Jumat</span>
                        <span class="font-semibold text-navy-800">08:00 - 20:00</span>
                    </div>
                    <div class="flex justify-between items-center text-[13px] py-2 border-b border-gray-50">
                        <span class="text-gray-500">Sabtu</span>
                        <span class="font-semibold text-navy-800">08:00 - 17:00</span>
                    </div>
                    <div class="flex justify-between items-center text-[13px] py-2">
                        <span class="text-gray-500">Minggu</span>
                        <span class="font-semibold text-red-500">Tutup</span>
                    </div>
                    <div class="pt-2 text-center">
                        <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-full text-[11px] font-semibold">
                            <i class="fas fa-comments text-[10px]"></i> WhatsApp 24/7
                        </span>
                    </div>
                </div>
            </div>

            {{-- FAQ QUICK --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <h3 class="font-bold text-navy-800 text-[15px] mb-4 flex items-center gap-2">
                    <i class="fas fa-question-circle text-sky-500"></i> Pertanyaan Cepat
                </h3>
                <div class="space-y-3">
                    @foreach([
                        'Bagaimana cara menyewa?' => 'Pilih produk, tentukan tanggal, bayar DP, dan unit siap dipakai.',
                        'Apakah bisa sewa tanpa driver?' => 'Ya, semua kendaraan tersedia opsi lepas kunci.',
                        'Bagaimana pembayarannya?' => 'Transfer bank, e-wallet (Dana, OVO, GoPay), atau COD.',
                        'Apakah unit diinspeksi?' => 'Ya, inspeksi ketat sebelum & sesudah sewa untuk keamanan Anda.',
                    ] as $q => $a)
                    <div class="bg-sky-50 rounded-xl p-3 hover:bg-sky-100 transition-colors cursor-pointer group">
                        <p class="font-semibold text-navy-800 text-[12px] mb-1 group-hover:text-sky-700">{{ $q }}</p>
                        <p class="text-gray-500 text-[11px]">{{ $a }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- MAP --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-br from-sky-50 to-sky-100 h-48 flex items-center justify-center relative overflow-hidden">
                    <div class="absolute inset-0 opacity-30">
                        <div class="absolute top-4 right-4 w-16 h-16 bg-sky-300/30 rounded-full float"></div>
                        <div class="absolute bottom-4 left-4 w-12 h-12 bg-sky-300/20 rounded-full float-reverse"></div>
                    </div>
                    <div class="text-center relative z-10">
                        <div class="w-14 h-14 bg-white/80 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-sm">
                            <i class="fas fa-map-marked-alt text-sky-500 text-2xl"></i>
                        </div>
                        <p class="text-sky-700 text-[13px] font-semibold">Lokasi Kantor Kami</p>
                        <p class="text-sky-500 text-[11px] mt-0.5">Jl. Sudirman No. 123, Jakarta Selatan</p>
                        <a href="https://maps.google.com" target="_blank" class="inline-flex items-center gap-1.5 mt-3 text-sky-600 text-[11px] font-semibold hover:text-sky-700">
                            <i class="fas fa-external-link-alt text-[9px]"></i> Buka di Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20 reveal">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl gradient-shift" style="background: linear-gradient(135deg, #0369a1, #0ea5e9, #0284c7, #0369a1); background-size: 300% 300%;">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-1/2 translate-x-1/3"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full translate-y-1/3 -translate-x-1/4"></div>
            </div>
            <div class="relative px-8 md:px-14 py-14 text-center">
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">Masih Butuh Bantuan?</h2>
                <p class="text-sky-200 text-[14px] mb-8 max-w-lg mx-auto">Tim support kami siap 24/7 via WhatsApp untuk menjawab semua pertanyaan Anda.</p>
                <a href="https://wa.me/6281234567890" target="_blank" class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3.5 rounded-xl font-bold text-[14px] shadow-xl shadow-emerald-500/25 transition-all duration-300 hover:scale-105 inline-flex items-center gap-2">
                    <i class="fab fa-whatsapp"></i> Chat WhatsApp Sekarang
                </a>
            </div>
        </div>
    </div>
</section>
@endsection