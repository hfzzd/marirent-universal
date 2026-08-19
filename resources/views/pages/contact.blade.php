@extends('layouts.public')
@section('title', 'Kontak - MariRent')

@section('content')
{{-- HERO --}}
<section class="relative py-16 overflow-hidden" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-3">Hubungi Kami</h1>
        <p class="text-sky-200 text-[14px]">Ada pertanyaan? Kami siap membantu Anda</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- INFO KONTAK --}}
        <div class="space-y-5">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <div class="w-11 h-11 bg-sky-100 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-phone text-sky-600"></i>
                </div>
                <h3 class="font-bold text-navy-800 text-[15px] mb-1">Telepon</h3>
                <p class="text-gray-400 text-[13px]">+62 812 3456 7890</p>
                <p class="text-gray-400 text-[13px]">Senin - Sabtu, 08:00 - 20:00</p>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <div class="w-11 h-11 bg-sky-100 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-envelope text-sky-600"></i>
                </div>
                <h3 class="font-bold text-navy-800 text-[15px] mb-1">Email</h3>
                <p class="text-gray-400 text-[13px]">info@marirent.com</p>
                <p class="text-gray-400 text-[13px]">support@marirent.com</p>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <div class="w-11 h-11 bg-sky-100 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-map-marker-alt text-sky-600"></i>
                </div>
                <h3 class="font-bold text-navy-800 text-[15px] mb-1">Alamat</h3>
                <p class="text-gray-400 text-[13px] leading-relaxed">Jl. Sudirman No. 123<br>Jakarta Selatan, 12190<br>Indonesia</p>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <h3 class="font-bold text-navy-800 text-[15px] mb-3">Ikuti Kami</h3>
                <div class="flex gap-3">
                    <a href="#" class="w-10 h-10 bg-sky-50 hover:bg-sky-100 rounded-xl flex items-center justify-center transition"><i class="fab fa-instagram text-sky-600"></i></a>
                    <a href="#" class="w-10 h-10 bg-sky-50 hover:bg-sky-100 rounded-xl flex items-center justify-center transition"><i class="fab fa-whatsapp text-sky-600"></i></a>
                    <a href="#" class="w-10 h-10 bg-sky-50 hover:bg-sky-100 rounded-xl flex items-center justify-center transition"><i class="fab fa-facebook text-sky-600"></i></a>
                    <a href="#" class="w-10 h-10 bg-sky-50 hover:bg-sky-100 rounded-xl flex items-center justify-center transition"><i class="fab fa-youtube text-sky-600"></i></a>
                </div>
            </div>
        </div>

        {{-- FORM KONTAK --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm">
                <h3 class="font-bold text-navy-800 text-lg mb-1">Kirim Pesan</h3>
                <p class="text-gray-400 text-[13px] mb-6">Isi form di bawah ini dan kami akan membalas segera</p>
                <form>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                            <input type="text" placeholder="Nama Anda" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Email</label>
                            <input type="email" placeholder="email@anda.com" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Subjek</label>
                        <input type="text" placeholder="Perihal pesan Anda" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Kategori</label>
                        <select class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                            <option>Pertanyaan Umum</option>
                            <option>Kerjasama</option>
                            <option>Keluhan</option>
                            <option>Saran & Masukan</option>
                        </select>
                    </div>
                    <div class="mb-6">
                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Pesan</label>
                        <textarea rows="5" placeholder="Tulis pesan Anda di sini..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none resize-none"></textarea>
                    </div>
                    <button type="button" class="btn-primary text-white px-8 py-3 rounded-xl font-semibold text-[13px] shadow-lg shadow-sky-500/25 transition flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i> Kirim Pesan
                    </button>
                </form>
            </div>

            {{-- MAP PLACEHOLDER --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm mt-6 overflow-hidden">
                <div class="bg-gradient-to-br from-sky-50 to-sky-100 h-64 flex items-center justify-center">
                    <div class="text-center">
                        <i class="fas fa-map-marked-alt text-sky-300 text-5xl mb-3"></i>
                        <p class="text-sky-500 text-[13px] font-medium">Peta Lokasi Kami</p>
                        <p class="text-sky-400 text-[11px]">Jl. Sudirman No. 123, Jakarta Selatan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
