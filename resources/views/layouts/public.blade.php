<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MariRent Universal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                sky: { 50:'#f0f9ff',100:'#e0f2fe',200:'#bae6fd',300:'#7dd3fc',400:'#38bdf8',500:'#0ea5e9',600:'#0284c7',700:'#0369a1',800:'#075985',900:'#0c4a6e' },
                navy: { 50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a' },
            }, fontFamily: { sans: ['Inter', 'sans-serif'] }}}
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }

        /* Loading screen */
        #loader { position: fixed; inset: 0; z-index: [9999]; background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0ea5e9 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; transition: opacity 0.5s ease, visibility 0.5s ease; }
        #loader.hide { opacity: 0; visibility: hidden; pointer-events: none; }
        .loader-logo { animation: loaderPulse 1.2s ease-in-out infinite; }
        .loader-bar { width: 180px; height: 3px; background: rgba(255,255,255,0.15); border-radius: 10px; overflow: hidden; margin-top: 28px; }
        .loader-bar-inner { height: 100%; width: 40%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.9), transparent); border-radius: 10px; animation: loaderSlide 1.2s ease-in-out infinite; }
        .loader-dots span { width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,0.5); display: inline-block; margin: 0 4px; animation: loaderDots 1.4s ease-in-out infinite; }
        .loader-dots span:nth-child(2) { animation-delay: 0.2s; }
        .loader-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes loaderPulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.08); } }
        @keyframes loaderSlide { 0% { transform: translateX(-150%); } 100% { transform: translateX(400%); } }
        @keyframes loaderDots { 0%,80%,100% { transform: scale(0.6); opacity: 0.4; } 40% { transform: scale(1); opacity: 1; } }

        /* Navbar transition */
        .navbar { transition: all 0.4s cubic-bezier(0.4,0,0.2,1); }
        .navbar.scrolled { background: rgba(255,255,255,0.95) !important; backdrop-filter: blur(20px) !important; box-shadow: 0 4px 30px rgba(0,0,0,0.06); border-color: rgba(226,232,240,0.8) !important; }
        .navbar.scrolled .nav-link { color: #334155 !important; }
        .navbar.scrolled .nav-logo-text { color: #0f172a !important; }
        .navbar.at-top { background: transparent !important; backdrop-filter: none !important; border-color: transparent !important; }
        .navbar.at-top .nav-link { color: rgba(255,255,255,0.85) !important; }
        .navbar.at-top .nav-link:hover { color: #fff !important; background: rgba(255,255,255,0.1) !important; }
        .navbar.at-top .nav-link.active { color: #fff !important; background: rgba(255,255,255,0.15) !important; }
        .navbar.at-top .nav-logo-text { color: #fff !important; }
        .navbar.at-top .nav-logo-icon { background: rgba(255,255,255,0.2) !important; box-shadow: none !important; }
        .navbar.at-top .nav-cta { background: rgba(255,255,255,0.2) !important; backdrop-filter: blur(8px) !important; border: 1px solid rgba(255,255,255,0.25) !important; box-shadow: none !important; }
        .navbar.at-top .mobile-toggle { color: #fff !important; }
        .navbar.at-top .mobile-menu { background: rgba(15,23,42,0.95) !important; backdrop-filter: blur(20px) !important; }

        /* Buttons */
        .btn-primary { background: linear-gradient(135deg, #0ea5e9, #0284c7); transition: all 0.3s ease; }
        .btn-primary:hover { background: linear-gradient(135deg, #0284c7, #0369a1); transform: translateY(-1px); box-shadow: 0 8px 25px rgba(14,165,233,0.3); }
        .btn-primary:active { transform: translateY(0); }

        /* Cards */
        .category-card { transition: all 0.35s cubic-bezier(0.4,0,0.2,1); }
        .category-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(14,165,233,0.12); }
        .vehicle-card { transition: all 0.35s cubic-bezier(0.4,0,0.2,1); }
        .vehicle-card:hover { transform: translateY(-6px); box-shadow: 0 20px 50px rgba(0,0,0,0.08); }
        .vehicle-card:hover .vehicle-img { transform: scale(1.05); }
        .vehicle-img { transition: transform 0.5s cubic-bezier(0.4,0,0.2,1); }

        /* Scroll reveal */
        .reveal { opacity: 0; transform: translateY(40px); transition: all 0.7s cubic-bezier(0.4,0,0.2,1); }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        .reveal-delay-4 { transition-delay: 0.4s; }

        /* Fade in */
        .fade-in { animation: fadeIn 0.8s ease-out both; }
        .fade-in-up { animation: fadeInUp 0.8s ease-out both; }
        .fade-in-up-delay-1 { animation-delay: 0.1s; }
        .fade-in-up-delay-2 { animation-delay: 0.2s; }
        .fade-in-up-delay-3 { animation-delay: 0.3s; }
        .fade-in-up-delay-4 { animation-delay: 0.4s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes float { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-12px) rotate(2deg); } }
        @keyframes floatReverse { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(12px) rotate(-2deg); } }
        @keyframes pulse-glow { 0%,100% { box-shadow: 0 0 0 0 rgba(14,165,233,0.3); } 50% { box-shadow: 0 0 20px 4px rgba(14,165,233,0.15); } }
        @keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }

        .float { animation: float 6s ease-in-out infinite; }
        .float-reverse { animation: floatReverse 5s ease-in-out infinite; }
        .float-delay { animation: float 7s ease-in-out 1s infinite; }
        .pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }
        .gradient-shift { background-size: 200% 200%; animation: gradientShift 8s ease infinite; }

        /* Smooth scroll */
        html { scroll-behavior: smooth; }

        /* Selection color */
        ::selection { background: #bae6fd; color: #0c4a6e; }

        @yield('styles')
    </style>
    @stack('styles')
</head>
<body class="bg-sky-50/30">

    {{-- LOADING SCREEN --}}
    <div id="loader">
        <div class="loader-logo">
            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-2xl border border-white/20">
                <i class="fas fa-car-side text-white text-2xl"></i>
            </div>
        </div>
        <p class="text-white/90 font-bold text-xl mt-5 tracking-tight">Mari<span class="text-sky-300">Rent</span></p>
        <p class="text-white/50 text-[11px] mt-1 tracking-widest uppercase font-medium">Universal Rental Platform</p>
        <div class="loader-bar mt-6"><div class="loader-bar-inner"></div></div>
        <div class="loader-dots mt-4"><span></span><span></span><span></span></div>
    </div>

    {{-- NAVBAR --}}
    <nav id="mainNav" class="navbar at-top fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <div class="nav-logo-icon w-9 h-9 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/20 transition-all duration-400">
                        <i class="fas fa-car-side text-white text-sm"></i>
                    </div>
                    <span class="nav-logo-text text-lg font-bold tracking-tight transition-colors duration-400">Mari<span class="text-sky-500">Rent</span></span>
                </a>

                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('home') }}" class="nav-link text-[13px] font-medium px-3 py-2 rounded-lg transition-all duration-300 {{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                    <a href="{{ route('about') }}" class="nav-link text-[13px] font-medium px-3 py-2 rounded-lg transition-all duration-300 {{ request()->routeIs('about') ? 'active' : '' }}">Tentang Kami</a>
                    <a href="{{ route('products') }}" class="nav-link text-[13px] font-medium px-3 py-2 rounded-lg transition-all duration-300 {{ request()->routeIs('products') ? 'active' : '' }}">Produk</a>
                    <a href="{{ route('contact') }}" class="nav-link text-[13px] font-medium px-3 py-2 rounded-lg transition-all duration-300 {{ request()->routeIs('contact') ? 'active' : '' }}">Kontak</a>
                </div>

                <div class="flex items-center gap-2">
                    @auth
                        @if(auth()->user()->isUser())
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="nav-cta flex items-center gap-2 btn-primary text-white px-3 py-1.5 rounded-xl font-semibold text-[13px] shadow-lg shadow-sky-500/25 transition-all duration-300">
                                    <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center overflow-hidden">
                                        @if(auth()->user()->avatar)
                                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-user text-white text-xs"></i>
                                        @endif
                                    </div>
                                    <span class="hidden sm:inline">{{ Str::limit(auth()->user()->name, 12) }}</span>
                                    <i class="fas fa-chevron-down text-[10px] ml-0.5 transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 -translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-2xl border border-sky-100 py-2 z-50" x-cloak>
                                    <div class="px-4 py-2.5 border-b border-sky-50">
                                        <p class="text-[13px] font-semibold text-navy-800">{{ auth()->user()->name }}</p>
                                        <p class="text-[11px] text-navy-400 truncate">{{ auth()->user()->email }}</p>
                                    </div>
                                    <a href="{{ route('dashboard.profile') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-navy-600 hover:bg-sky-50 transition">
                                        <i class="fas fa-user-circle w-4 text-sm text-sky-500"></i> Profile Saya
                                    </a>
                                    <div class="border-t border-sky-50 my-1"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-red-500 hover:bg-red-50 transition w-full">
                                            <i class="fas fa-sign-out-alt w-4 text-sm"></i> Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            @if(auth()->user()->role === 'user')
                            <a href="{{ route('dashboard.profile') }}" class="nav-cta btn-primary text-white px-4 py-2 rounded-xl font-semibold text-[13px] shadow-lg shadow-sky-500/25 transition-all duration-300">
                                <i class="fas fa-user-circle mr-1.5"></i> Profil Saya
                            </a>
                            @else
                            <a href="{{ route('dashboard') }}" class="nav-cta btn-primary text-white px-4 py-2 rounded-xl font-semibold text-[13px] shadow-lg shadow-sky-500/25 transition-all duration-300">
                                <i class="fas fa-tachometer-alt mr-1.5"></i> Dashboard
                            </a>
                            @endif
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="nav-link text-[13px] font-medium px-3 py-2 transition-all duration-300">Login</a>
                        <a href="{{ route('register') }}" class="nav-cta btn-primary text-white px-4 py-2 rounded-xl font-semibold text-[13px] shadow-lg shadow-sky-500/25 transition-all duration-300">Daftar</a>
                    @endauth
                    <button onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" class="mobile-toggle md:hidden text-navy-600 ml-1 transition-colors duration-400">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
        <div id="mobileMenu" class="mobile-menu hidden md:hidden border-t border-sky-100 bg-white/95 backdrop-blur-md">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('home') }}" class="block text-[13px] font-medium text-navy-600 hover:text-sky-600 hover:bg-sky-50 px-3 py-2.5 rounded-lg transition">Beranda</a>
                <a href="{{ route('about') }}" class="block text-[13px] font-medium text-navy-600 hover:text-sky-600 hover:bg-sky-50 px-3 py-2.5 rounded-lg transition">Tentang Kami</a>
                <a href="{{ route('products') }}" class="block text-[13px] font-medium text-navy-600 hover:text-sky-600 hover:bg-sky-50 px-3 py-2.5 rounded-lg transition">Produk</a>
                <a href="{{ route('contact') }}" class="block text-[13px] font-medium text-navy-600 hover:text-sky-600 hover:bg-sky-50 px-3 py-2.5 rounded-lg transition">Kontak</a>
                @auth
                    <div class="border-t border-sky-100 mt-2 pt-2">
                        <div class="px-3 py-2">
                            <p class="text-[13px] font-semibold text-navy-800">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-navy-400">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('dashboard.profile') }}" class="block text-[13px] font-medium text-sky-600 hover:bg-sky-50 px-3 py-2.5 rounded-lg transition"><i class="fas fa-user-circle mr-2"></i> Profile Saya</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block text-[13px] font-medium text-red-500 hover:bg-red-50 px-3 py-2.5 rounded-lg transition w-full text-left"><i class="fas fa-sign-out-alt mr-2"></i> Logout</button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    @yield('content')

    {{-- FOOTER --}}
    <footer class="bg-navy-900 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-sky-500/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-sky-500/5 rounded-full translate-y-1/3 -translate-x-1/4"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/20">
                            <i class="fas fa-car-side text-white text-sm"></i>
                        </div>
                        <span class="text-lg font-bold">Mari<span class="text-sky-400">Rent</span></span>
                    </div>
                    <p class="text-gray-400 text-[13px] leading-relaxed">Platform rental universal untuk kendaraan, gadget, dan alat outdoor. Mudah, cepat, dan terpercaya.</p>
                    <div class="flex gap-3 mt-5">
                        <a href="#" class="w-9 h-9 bg-white/10 hover:bg-sky-500 rounded-lg flex items-center justify-center transition-all duration-300 hover:scale-110"><i class="fab fa-instagram text-sm"></i></a>
                        <a href="#" class="w-9 h-9 bg-white/10 hover:bg-sky-500 rounded-lg flex items-center justify-center transition-all duration-300 hover:scale-110"><i class="fab fa-whatsapp text-sm"></i></a>
                        <a href="#" class="w-9 h-9 bg-white/10 hover:bg-sky-500 rounded-lg flex items-center justify-center transition-all duration-300 hover:scale-110"><i class="fab fa-facebook text-sm"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-[13px]">Navigasi</h4>
                    <ul class="space-y-2.5 text-gray-400 text-[13px]">
                        <li><a href="{{ route('home') }}" class="hover:text-sky-400 transition">Beranda</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-sky-400 transition">Tentang Kami</a></li>
                        <li><a href="{{ route('products') }}" class="hover:text-sky-400 transition">Produk</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-sky-400 transition">Kontak</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-[13px]">Kategori</h4>
                    <ul class="space-y-2.5 text-gray-400 text-[13px]">
                        <li><a href="{{ route('products', ['category' => 'mobil']) }}" class="hover:text-sky-400 transition">Sewa Mobil</a></li>
                        <li><a href="{{ route('products', ['category' => 'motor']) }}" class="hover:text-sky-400 transition">Sewa Motor</a></li>
                        <li><a href="{{ route('products', ['category' => 'sewa-kamera']) }}" class="hover:text-sky-400 transition">Sewa Kamera</a></li>
                        <li><a href="{{ route('products', ['category' => 'sewa-tenda']) }}" class="hover:text-sky-400 transition">Sewa Tenda</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-[13px]">Kontak</h4>
                    <ul class="space-y-2.5 text-gray-400 text-[13px]">
                        <li><i class="fas fa-phone mr-2 text-sky-400"></i> +62 812 3456 7890</li>
                        <li><i class="fas fa-envelope mr-2 text-sky-400"></i> info@marirent.com</li>
                        <li><i class="fas fa-map-marker-alt mr-2 text-sky-400"></i> Jakarta, Indonesia</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 mt-10 pt-6 text-center text-gray-500 text-[12px]">
                &copy; {{ date('Y') }} MariRent Universal. All rights reserved.
            </div>
        </div>
    </footer>

    {{-- LOADER SCRIPT --}}
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('loader').classList.add('hide');
            }, 800);
            setTimeout(function() {
                document.getElementById('loader').style.display = 'none';
            }, 1400);
        });

        // Navbar scroll effect
        const nav = document.getElementById('mainNav');
        const hero = document.querySelector('.hero-section');
        function updateNav() {
            if (!hero) { nav.classList.remove('at-top'); nav.classList.add('scrolled'); return; }
            const heroH = hero.offsetHeight;
            if (window.scrollY < heroH - 80) {
                nav.classList.add('at-top');
                nav.classList.remove('scrolled');
            } else {
                nav.classList.remove('at-top');
                nav.classList.add('scrolled');
            }
        }
        window.addEventListener('scroll', updateNav, { passive: true });
        updateNav();

        // Scroll reveal
        const revealEls = document.querySelectorAll('.reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); revealObserver.unobserve(e.target); } });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
        revealEls.forEach(el => revealObserver.observe(el));

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const target = document.querySelector(a.getAttribute('href'));
                if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
            });
        });
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
