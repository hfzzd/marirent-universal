@extends('layouts.public')
@section('title', 'MariRent - Platform Rental Universal')
@section('meta_description', 'MariRent: kelola bisnis rental & sewa unit (mobil, motor, kamera, HP, alat camping, drone, PlayStation, alat musik) dalam satu platform. Coba demo gratis.')

@section('content')
@php
    $galeriFoto = $galeri['foto'] ?? [];
    $tabMeta = [
        'mobil'  => ['label' => 'Mobil', 'icon' => 'fa-car', 'cat' => 'mobil'],
        'motor'  => ['label' => 'Motor', 'icon' => 'fa-motorcycle', 'cat' => 'motor'],
        'hp'     => ['label' => 'Handphone', 'icon' => 'fa-mobile-screen', 'cat' => 'sewa-hp'],
        'kamera' => ['label' => 'Kamera', 'icon' => 'fa-camera', 'cat' => 'sewa-kamera'],
        'tenda'  => ['label' => 'Alat Camping', 'icon' => 'fa-campground', 'cat' => 'sewa-tenda'],
        'ps'     => ['label' => 'PlayStation', 'icon' => 'fa-gamepad', 'cat' => 'sewa-ps'],
        'drone'  => ['label' => 'Drone', 'icon' => 'fa-drone', 'cat' => 'sewa-drone'],
        'musik'  => ['label' => 'Alat Musik', 'icon' => 'fa-guitar', 'cat' => 'sewa-alat-musik'],
    ];
    $fiturTabs = [
        ['key' => 'beranda', 'label' => 'Beranda', 'icon' => 'fa-house', 'img' => 'images/app/beranda-superadmin.png', 'title' => 'Beranda & Modul Pintas', 'desc' => 'Semua modul — Mobil, Motor, Elektronik, Driver & Staff, Finance, Absensi, Booking, Laporan, Scheduler — dalam satu dasbor.'],
        ['key' => 'monitoring', 'label' => 'Monitoring', 'icon' => 'fa-chart-line', 'img' => 'images/app/monitoring.png', 'title' => 'Monitoring Aktivitas', 'desc' => 'Datasheet seluruh booking: status sewa, pembayaran, dan total — dengan filter Pending hingga Selesai.'],
        ['key' => 'scheduler', 'label' => 'Scheduler', 'icon' => 'fa-calendar-days', 'img' => 'images/app/scheduler.png', 'title' => 'Scheduler Kalender', 'desc' => 'Kalender operasional: mulai sewa, selesai sewa, penjemputan, pemulangan, dan jadwal maintenance.'],
        ['key' => 'ganti', 'label' => 'Penggantian', 'icon' => 'fa-arrow-right-arrow-left', 'img' => 'images/app/penggantian-kendaraan.png', 'title' => 'Penggantian Unit', 'desc' => 'Alur penggantian unit yang transparan: status Disetujui, selisih biaya jelas, riwayat terdokumentasi.'],
        ['key' => 'katalog', 'label' => 'Katalog', 'icon' => 'fa-tags', 'img' => 'images/app/kendaraan.png', 'title' => 'Katalog Brand & Harga', 'desc' => 'Katalog per brand lengkap dengan jumlah produk dan harga mulai per hari.'],
        ['key' => 'admin', 'label' => 'Analitik', 'icon' => 'fa-chart-pie', 'img' => 'images/app/admin.png', 'title' => 'Analitik & Laporan', 'desc' => 'Tren pendapatan 6 bulan, distribusi kategori produk, omzet, dan tim operasional.'],
    ];
    $totalUnit = array_sum($catCounts ?? []);
    $totalMerchant = \App\Models\Merchant::where('is_active', true)->where('status', 'active')->count();
    $totalSelesai = \App\Models\Booking::where('status', 'completed')->count();
@endphp

{{-- ================= HERO ================= --}}
<section class="hero-section relative overflow-hidden" data-nav="solid" style="background: linear-gradient(180deg, #bae6fd 0%, #e0f2fe 30%, #f0f9ff 60%, #ffffff 100%);">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute inset-0" style="background-image: radial-gradient(#7dd3fc 1.1px, transparent 1.1px); background-size: 26px 26px; opacity: .55;"></div>
        <div class="absolute -top-32 -right-32 w-[480px] h-[480px] rounded-full bg-sky-300/40 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-32 w-[420px] h-[420px] rounded-full bg-sky-200/60 blur-3xl"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-16 lg:pt-40 lg:pb-24 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6 fade-in-up">
                <span class="eyebrow mb-6"><i class="fas fa-sparkles text-sky-600"></i> Platform Rental Universal</span>
                <h1 class="h-display text-navy-900">Sewa & Kelola Unit Rental, <span class="text-sky-600">Semudah Pesan Ojek.</span></h1>
                <p class="lead mt-6 max-w-xl">Satu platform untuk sewa mobil, motor, kamera, HP, alat camping, drone, PlayStation & alat musik — sekaligus mengelola bisnis rental Anda: inventaris, booking, keuangan, dan tim.</p>
                <div class="flex flex-wrap gap-3 mt-9">
                    <a href="{{ route('products') }}" class="bg-sky-600 hover:bg-sky-700 text-white px-8 py-4 rounded-2xl font-bold text-[15px] shadow-lg shadow-sky-600/25 transition-all duration-300 hover:-translate-y-0.5 inline-flex items-center gap-2"><i class="fas fa-search"></i> Lihat Katalog</a>
                    <a href="{{ route('demo') }}" class="bg-white hover:bg-sky-50 text-sky-700 border border-sky-200 px-8 py-4 rounded-2xl font-bold text-[15px] transition-all duration-300 hover:-translate-y-0.5 inline-flex items-center gap-2 shadow-sm"><i class="fas fa-calendar-check"></i> Jadwalkan Demo</a>
                </div>
                <dl class="grid grid-cols-3 gap-6 mt-12 max-w-lg">
                    <div><dt class="text-[12px] font-semibold text-gray-400 uppercase tracking-wider">Unit Tersedia</dt><dd class="text-2xl font-extrabold text-navy-900 mt-1">{{ number_format($totalUnit) }}+</dd></div>
                    <div><dt class="text-[12px] font-semibold text-gray-400 uppercase tracking-wider">Mitra Aktif</dt><dd class="text-2xl font-extrabold text-navy-900 mt-1">{{ number_format($totalMerchant) }}</dd></div>
                    <div><dt class="text-[12px] font-semibold text-gray-400 uppercase tracking-wider">Sewa Selesai</dt><dd class="text-2xl font-extrabold text-navy-900 mt-1">{{ number_format($totalSelesai) }}+</dd></div>
                </dl>
            </div>
            <div class="lg:col-span-6 fade-in-up fade-in-up-delay-2">
                <div class="browser-frame">
                    <div class="browser-bar">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span><span class="w-3 h-3 rounded-full bg-amber-400"></span><span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                        <span class="ml-3 text-[11px] text-gray-400 font-semibold bg-white border border-gray-100 rounded-lg px-3 py-1 flex-1 truncate">app.marirent — Beranda</span>
                    </div>
                    <img src="{{ asset('images/app/beranda-superadmin.png') }}" alt="Dasbor aplikasi MariRent" class="w-full object-cover object-top" loading="eager">
                </div>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="card-clean p-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center flex-shrink-0"><i class="fas fa-shield-halved"></i></div>
                        <p class="text-[13px] font-bold text-navy-800 leading-snug">Inspeksi ketat<br><span class="font-medium text-gray-400">sebelum & sesudah sewa</span></p>
                    </div>
                    <div class="card-clean p-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0"><i class="fas fa-headset"></i></div>
                        <p class="text-[13px] font-bold text-navy-800 leading-snug">Support 24/7<br><span class="font-medium text-gray-400">siap membantu kapan saja</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ================= KATEGORI ================= --}}
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-12 reveal">
            <span class="eyebrow mb-4"><i class="fas fa-layer-group"></i> Katalog</span>
            <h2 class="h-section text-navy-900">Semua kebutuhan sewa, dalam satu tempat.</h2>
            <p class="lead mt-4">Delapan kategori unit dari mitra terverifikasi di seluruh Indonesia.</p>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($categories as $cat)
            @php
                $icon = match($cat->slug) {
                    'mobil' => 'fa-car', 'motor' => 'fa-motorcycle', 'sewa-hp' => 'fa-mobile-screen',
                    'sewa-kamera' => 'fa-camera', 'sewa-tenda' => 'fa-campground', 'sewa-ps' => 'fa-gamepad',
                    'sewa-drone' => 'fa-drone', 'sewa-alat-musik' => 'fa-guitar', default => 'fa-box',
                };
            @endphp
            <a href="{{ route('products', ['category' => $cat->slug]) }}" class="card-clean p-6 group reveal">
                <div class="w-12 h-12 rounded-2xl bg-sky-600 text-white flex items-center justify-center mb-4 shadow-md shadow-sky-600/20 group-hover:scale-110 transition-transform"><i class="fas {{ $icon }} text-lg"></i></div>
                <h3 class="font-bold text-navy-900">{{ $cat->name }}</h3>
                <p class="body-sm text-gray-400 mt-1">{{ $catCounts[$cat->slug] ?? 0 }} unit tersedia</p>
                <span class="inline-flex items-center gap-1.5 text-[13px] font-bold text-sky-600 mt-3">Jelajahi <i class="fas fa-arrow-right text-[11px] group-hover:translate-x-1 transition-transform"></i></span>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= GALERI ARMADA ================= --}}
<section class="section bg-sky-50/60" x-data="{ tab: 'mobil' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-10 reveal">
            <span class="eyebrow mb-4"><i class="fas fa-camera"></i> Galeri Armada</span>
            <h2 class="h-section text-navy-900">Lihat unit aslinya, bukan sekadar ikon.</h2>
            <p class="lead mt-4">Foto katalog per kategori — klik untuk melihat unit yang bisa disewa.</p>
        </div>
        <div class="flex gap-2 overflow-x-auto pb-4 mb-6 reveal">
            @foreach($tabMeta as $slug => $meta)
            <button @click="tab = '{{ $slug }}'" :class="tab === '{{ $slug }}' ? 'active' : ''" class="tab-pill flex items-center gap-2 px-5 py-2.5 rounded-xl text-[13px] font-bold bg-white border border-sky-100 text-navy-700 whitespace-nowrap shadow-sm">
                <i class="fas {{ $meta['icon'] }} text-sky-600"></i> {{ $meta['label'] }}
            </button>
            @endforeach
        </div>
        @foreach($tabMeta as $slug => $meta)
        <div x-show="tab === '{{ $slug }}'" x-cloak class="gallery-fade">
            <div class="snap-row">
                @forelse($galeriFoto[$slug] ?? [] as $foto)
                @php $nama = str_replace('-', ' ', pathinfo($foto, PATHINFO_FILENAME)); @endphp
                <a href="{{ route('products', ['category' => $meta['cat']]) }}" class="card-clean overflow-hidden w-60 group !shadow-sm">
                    <div class="h-44 bg-sky-50/50 overflow-hidden"><img src="{{ asset($foto) }}" alt="{{ $nama }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy"></div>
                    <div class="p-4"><p class="font-bold text-navy-900 text-[14px] leading-snug truncate" title="{{ $nama }}">{{ $nama }}</p><p class="text-[12px] text-sky-600 font-semibold mt-1">{{ $meta['label'] }} • Cek ketersediaan →</p></div>
                </a>
                @empty
                <p class="text-gray-400 text-sm py-8">Foto segera hadir.</p>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- ================= FITUR APLIKASI ================= --}}
<section class="section bg-white" x-data="{ ftab: 'beranda' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-10 reveal">
            <span class="eyebrow mb-4"><i class="fas fa-desktop"></i> Tur Aplikasi</span>
            <h2 class="h-section text-navy-900">Satu dasbor untuk seluruh operasional.</h2>
            <p class="lead mt-4">Tampilan asli aplikasi MariRent — dari monitoring hingga laporan.</p>
        </div>
        <div class="flex gap-2 overflow-x-auto pb-4 mb-8 reveal">
            @foreach($fiturTabs as $f)
            <button @click="ftab = '{{ $f['key'] }}'" :class="ftab === '{{ $f['key'] }}' ? 'active' : ''" class="tab-pill flex items-center gap-2 px-5 py-2.5 rounded-xl text-[13px] font-bold bg-sky-50 text-navy-700 whitespace-nowrap">
                <i class="fas {{ $f['icon'] }}"></i> {{ $f['label'] }}
            </button>
            @endforeach
        </div>
        @foreach($fiturTabs as $f)
        <div x-show="ftab === '{{ $f['key'] }}'" x-cloak class="gallery-fade grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-4">
                <h3 class="text-xl font-extrabold text-navy-900">{{ $f['title'] }}</h3>
                <p class="text-gray-500 text-[14px] leading-relaxed mt-3">{{ $f['desc'] }}</p>
                <a href="{{ route('demo') }}" class="inline-flex items-center gap-2 mt-6 bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-xl text-[13px] font-bold transition shadow-md shadow-sky-600/25"><i class="fas fa-calendar-check"></i> Coba Lewat Demo</a>
            </div>
            <div class="lg:col-span-8">
                <div class="browser-frame">
                    <div class="browser-bar"><span class="w-3 h-3 rounded-full bg-red-400"></span><span class="w-3 h-3 rounded-full bg-amber-400"></span><span class="w-3 h-3 rounded-full bg-emerald-400"></span><span class="ml-3 text-[11px] text-gray-400 font-semibold">{{ $f['title'] }}</span></div>
                    <img src="{{ asset($f['img']) }}" alt="{{ $f['title'] }}" class="w-full object-cover object-top max-h-[520px]" loading="lazy">
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- ================= KATALOG BRAND ================= --}}
<section class="section bg-sky-50/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10 reveal">
            <div class="max-w-2xl">
                <span class="eyebrow mb-4"><i class="fas fa-tags"></i> Katalog Brand</span>
                <h2 class="h-section text-navy-900">Pilih dari brand favorit Anda.</h2>
            </div>
            <a href="{{ route('public.brands') }}" class="text-[14px] font-bold text-sky-600 hover:text-sky-700">Semua brand <i class="fas fa-arrow-right ml-1 text-[12px]"></i></a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach([
                ['img' => 'images/app/brand-mobil.png', 't' => 'Brand Mobil', 'link' => 'public.brands', 'anchor' => '#kategori-mobil'],
                ['img' => 'images/app/kendaraan.png', 't' => 'Brand Motor', 'link' => 'public.brands', 'anchor' => '#kategori-motor'],
                ['img' => 'images/app/brand-kamera.png', 't' => 'Brand Kamera', 'link' => 'public.brands', 'anchor' => '#kategori-kamera'],
                ['img' => 'images/app/brand-hp.png', 't' => 'Brand Handphone', 'link' => 'public.brands', 'anchor' => '#kategori-hp'],
                ['img' => 'images/app/brand-camping.png', 't' => 'Brand Alat Camping', 'link' => 'public.brands', 'anchor' => '#kategori-tenda'],
            ] as $b)
            <a href="{{ route($b['link']) }}{{ $b['anchor'] }}" class="card-clean overflow-hidden group reveal">
                <div class="overflow-hidden bg-white"><img src="{{ asset($b['img']) }}" alt="{{ $b['t'] }}" class="w-full object-cover object-top max-h-56 group-hover:scale-[1.02] transition-transform duration-500" loading="lazy"></div>
                <div class="p-5 flex items-center justify-between"><p class="font-bold text-navy-900">{{ $b['t'] }}</p><span class="w-9 h-9 rounded-xl bg-sky-600 text-white flex items-center justify-center group-hover:bg-sky-700 transition-colors"><i class="fas fa-arrow-right text-sm"></i></span></div>
            </a>
            @endforeach
            <a href="{{ route('demo') }}" class="rounded-3xl p-8 flex flex-col justify-center text-white overflow-hidden relative reveal" style="background: linear-gradient(135deg, #0c4a6e, #0284c7);">
                <div class="absolute -right-16 -bottom-16 w-56 h-56 bg-white/10 rounded-full"></div>
                <h3 class="text-xl font-extrabold relative">Punya usaha rental?</h3>
                <p class="text-sky-200 text-[14px] mt-2 leading-relaxed relative">Digitalkan inventaris & booking Anda hari ini juga.</p>
                <span class="inline-flex items-center gap-2 mt-5 bg-white text-sky-700 px-6 py-3 rounded-xl text-[13px] font-bold w-fit relative"><i class="fas fa-calendar-check"></i> Jadwalkan Demo Gratis</span>
            </a>
        </div>
    </div>
</section>

{{-- ================= CARA SEWA ================= --}}
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12 reveal">
            <span class="eyebrow mb-4"><i class="fas fa-route"></i> Cara Sewa</span>
            <h2 class="h-section text-navy-900">Sewa dalam 4 langkah mudah.</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach([
                ['n' => '01', 't' => 'Pilih & Pesan', 'd' => 'Cari unit, tentukan tanggal, pesan online dalam hitungan menit.'],
                ['n' => '02', 't' => 'Konfirmasi & Bayar', 'd' => 'Bayar aman via transfer / e-wallet. DP 50% tersedia.'],
                ['n' => '03', 't' => 'Ambil / Diantar', 'd' => 'Ambil di lokasi mitra atau minta diantar ke alamat Anda.'],
                ['n' => '04', 't' => 'Nikmati & Kembalikan', 'd' => 'Gunakan unit, kembalikan tepat waktu, beri ulasan.'],
            ] as $s)
            <div class="card-clean p-7 reveal">
                <p class="text-[13px] font-extrabold text-sky-200 tracking-widest">{{ $s['n'] }}</p>
                <h3 class="font-bold text-navy-900 text-[16px] mt-2">{{ $s['t'] }}</h3>
                <p class="body-sm text-gray-500 mt-2">{{ $s['d'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= TESTIMONI ================= --}}
<section class="section bg-sky-50/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12 reveal">
            <span class="eyebrow mb-4"><i class="fas fa-star"></i> Testimoni</span>
            <h2 class="h-section text-navy-900">Dipercaya ribuan pelanggan.</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach([
                ['n' => 'Ahmad Rizky', 'r' => 'Sewa Mobil • Jakarta', 't' => 'Mobil bersih, driver ramah, harga transparan. Booking lewat HP selesai dalam 5 menit.'],
                ['n' => 'Sari Dewi', 'r' => 'Sewa Kamera • Bandung', 't' => 'Unit prima dan aksesoris lengkap untuk project video. Support fast respon banget.'],
                ['n' => 'Budi Santoso', 'r' => 'Sewa Camping • Bogor', 't' => 'Tenda waterproof, matras nyaman, diantar tepat waktu ke basecamp. Mantap!'],
            ] as $t)
            <figure class="card-clean p-7 reveal">
                <div class="flex gap-1 text-amber-400 text-sm mb-4"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <blockquote class="text-navy-800 text-[15px] leading-relaxed">“{{ $t['t'] }}”</blockquote>
                <figcaption class="mt-5 pt-5 border-t border-gray-100"><p class="font-bold text-navy-900 text-[14px]">{{ $t['n'] }}</p><p class="text-[12px] text-gray-400 mt-0.5">{{ $t['r'] }}</p></figcaption>
            </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= FAQ ================= --}}
<section class="section bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 reveal">
            <span class="eyebrow mb-4"><i class="fas fa-circle-question"></i> FAQ</span>
            <h2 class="h-section text-navy-900">Pertanyaan yang sering diajukan.</h2>
        </div>
        <div class="space-y-3">
            @foreach([
                ['q' => 'Apa itu MariRent?', 'a' => 'Platform digital untuk mengelola bisnis rental — armada, pemesanan online, laporan keuangan otomatis, dan pemantauan unit dalam satu aplikasi.'],
                ['q' => 'Bagaimana cara menyewa?', 'a' => 'Pilih unit di Katalog, tentukan tanggal, bayar (full/DP 50%), lalu ambil unit atau minta diantar.'],
                ['q' => 'Apakah unit diinspeksi?', 'a' => 'Ya. Setiap unit diperiksa sebelum dan sesudah masa sewa demi keamanan penyewa dan pemilik.'],
                ['q' => 'Bisakah mencoba aplikasinya dulu?', 'a' => 'Bisa. Klik Jadwalkan Demo, isi form, dan tim kami akan menghubungi Anda maksimal 1x24 jam.'],
            ] as $f)
            <details class="group card-clean !rounded-2xl overflow-hidden reveal">
                <summary class="flex items-center justify-between gap-4 px-6 py-4 cursor-pointer font-bold text-navy-900 text-[15px] list-none">
                    {{ $f['q'] }}
                    <span class="w-8 h-8 rounded-xl bg-sky-600 text-white flex items-center justify-center flex-shrink-0 group-open:rotate-180 transition-transform"><i class="fas fa-chevron-down text-xs"></i></span>
                </summary>
                <div class="px-6 pb-5 body-sm text-gray-500">{{ $f['a'] }}</div>
            </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= CTA DEMO ================= --}}
<section class="section bg-white !pt-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 reveal">
        <div class="relative overflow-hidden rounded-[2rem] px-8 py-14 md:p-16 text-center" style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 55%, #0ea5e9 100%);">
            <div class="absolute -top-24 -right-24 w-72 h-72 bg-white/10 rounded-full"></div>
            <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-white/10 rounded-full"></div>
            <div class="relative">
                <h2 class="h-section text-white max-w-2xl mx-auto">Hemat waktu, tingkatkan profit. Jalankan bisnis rental dari mana saja.</h2>
                <p class="text-sky-200 mt-4 max-w-xl mx-auto">Yuk, gabung sekarang — jadwalkan demo gratis dan lihat langsung cara kerjanya.</p>
                <div class="flex flex-wrap justify-center gap-3 mt-9">
                    <a href="{{ route('demo') }}" class="bg-white text-sky-700 hover:bg-sky-50 px-8 py-4 rounded-2xl font-bold text-[15px] shadow-xl inline-flex items-center gap-2 transition hover:-translate-y-0.5"><i class="fas fa-calendar-check"></i> Jadwalkan Demo Gratis</a>
                    <a href="https://wa.me/6281234567890?text=Halo%20min%20saya%20mau%20bertanya%20tentang%20MariRent" target="_blank" class="bg-white/10 hover:bg-white/20 text-white border border-white/25 px-8 py-4 rounded-2xl font-bold text-[15px] inline-flex items-center gap-2 transition"><i class="fab fa-whatsapp"></i> Chat via WA</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
