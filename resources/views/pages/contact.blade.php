@extends('layouts.public')
@section('title', 'Kontak - MariRent')

@section('content')
{{-- HERO --}}
<section class="hero-section relative py-24 overflow-hidden" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 40%, #0ea5e9 100%);">
    <div class="absolute inset-0">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4 float"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-white/5 rounded-full translate-y-1/3 -translate-x-1/4 float-reverse"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-sky-200 px-4 py-1.5 rounded-full text-[12px] font-semibold mb-5 fade-in-up">
            <i class="fas fa-headset"></i> Dukungan
        </span>
        <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4 fade-in-up fade-in-up-delay-1">Hubungi <span class="text-sky-300">Kami</span></h1>
        <p class="text-sky-200/80 text-[15px] max-w-xl mx-auto fade-in-up fade-in-up-delay-2">Ada pertanyaan atau masalah? Tim kami siap membantu Anda kapan saja</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    {{-- INFO KONTAK CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-14 reveal">
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group cursor-default">
            <div class="w-12 h-12 bg-gradient-to-br from-sky-400 to-sky-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-sky-500/20 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-phone text-white text-lg"></i>
            </div>
            <h3 class="font-bold text-navy-800 text-[15px] mb-2">Telepon</h3>
            <p class="text-gray-400 text-[13px] mb-1">+62 812 3456 7890</p>
            <p class="text-gray-300 text-[11px]">Senin - Sabtu, 08:00 - 20:00</p>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group cursor-default">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-envelope text-white text-lg"></i>
            </div>
            <h3 class="font-bold text-navy-800 text-[15px] mb-2">Email</h3>
            <p class="text-gray-400 text-[13px] mb-1">info@marirent.com</p>
            <p class="text-gray-300 text-[11px]">support@marirent.com</p>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group cursor-default">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-amber-500/20 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-map-marker-alt text-white text-lg"></i>
            </div>
            <h3 class="font-bold text-navy-800 text-[15px] mb-2">Alamat</h3>
            <p class="text-gray-400 text-[13px] leading-relaxed">Jl. Sudirman No. 123<br>Jakarta Selatan, 12190</p>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group cursor-default">
            <div class="w-12 h-12 bg-gradient-to-br from-violet-400 to-violet-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-violet-500/20 group-hover:scale-110 transition-transform duration-300">
                <i class="fab fa-whatsapp text-white text-lg"></i>
            </div>
            <h3 class="font-bold text-navy-800 text-[15px] mb-2">WhatsApp</h3>
            <p class="text-gray-400 text-[13px] mb-1">+62 812 3456 7890</p>
            <p class="text-gray-300 text-[11px]">Chat langsung 24 jam</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        {{-- FORM KONTAK --}}
        <div class="lg:col-span-3 reveal">
            <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-md shadow-sky-500/20">
                        <i class="fas fa-paper-plane text-white text-sm"></i>
                    </div>
                    <h3 class="font-bold text-navy-800 text-lg">Kirim Pesan</h3>
                </div>
                <p class="text-gray-400 text-[13px] mb-6 ml-13">Isi form di bawah ini dan kami akan membalas segera</p>
                <form>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                            <input type="text" placeholder="Nama Anda" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all duration-200 hover:border-gray-300">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Email</label>
                            <input type="email" placeholder="email@anda.com" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all duration-200 hover:border-gray-300">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Subjek</label>
                            <input type="text" placeholder="Perihal pesan Anda" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all duration-200 hover:border-gray-300">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Kategori</label>
                            <select class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white transition-all duration-200 hover:border-gray-300">
                                <option>Pertanyaan Umum</option>
                                <option>Kerjasama</option>
                                <option>Keluhan</option>
                                <option>Saran & Masukan</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Pesan</label>
                        <textarea rows="5" placeholder="Tulis pesan Anda di sini..." class="w-full border border-gray-200 rounded-xl px-4 py-3 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none resize-none transition-all duration-200 hover:border-gray-300"></textarea>
                    </div>
                    <button type="button" class="btn-primary text-white px-8 py-3.5 rounded-xl font-semibold text-[13px] shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i> Kirim Pesan
                    </button>
                </form>
            </div>
        </div>

        {{-- SIDEBAR --}}
        <div class="lg:col-span-2 space-y-5 reveal reveal-delay-2">
            {{-- SOSIAL --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <h3 class="font-bold text-navy-800 text-[15px] mb-4">Ikuti Kami</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="#" class="flex items-center gap-3 bg-pink-50 hover:bg-pink-100 rounded-xl px-4 py-3 transition-all duration-200 group">
                        <i class="fab fa-instagram text-pink-500 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[12px] font-semibold text-pink-700">Instagram</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 bg-emerald-50 hover:bg-emerald-100 rounded-xl px-4 py-3 transition-all duration-200 group">
                        <i class="fab fa-whatsapp text-emerald-500 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[12px] font-semibold text-emerald-700">WhatsApp</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 bg-blue-50 hover:bg-blue-100 rounded-xl px-4 py-3 transition-all duration-200 group">
                        <i class="fab fa-facebook text-blue-500 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[12px] font-semibold text-blue-700">Facebook</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 bg-red-50 hover:bg-red-100 rounded-xl px-4 py-3 transition-all duration-200 group">
                        <i class="fab fa-youtube text-red-500 text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[12px] font-semibold text-red-700">YouTube</span>
                    </a>
                </div>
            </div>

            {{-- JAM OPERASIONAL --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <h3 class="font-bold text-navy-800 text-[15px] mb-4 flex items-center gap-2">
                    <i class="fas fa-clock text-sky-500"></i> Jam Operasional
                </h3>
                <div class="space-y-2.5">
                    <div class="flex justify-between items-center text-[13px]">
                        <span class="text-gray-500">Senin - Jumat</span>
                        <span class="font-semibold text-navy-800">08:00 - 20:00</span>
                    </div>
                    <div class="flex justify-between items-center text-[13px]">
                        <span class="text-gray-500">Sabtu</span>
                        <span class="font-semibold text-navy-800">08:00 - 17:00</span>
                    </div>
                    <div class="flex justify-between items-center text-[13px]">
                        <span class="text-gray-500">Minggu</span>
                        <span class="font-semibold text-red-500">Tutup</span>
                    </div>
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
                        <p class="text-sky-700 text-[13px] font-semibold">Lokasi Kami</p>
                        <p class="text-sky-500 text-[11px] mt-0.5">Jl. Sudirman No. 123, Jakarta Selatan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
