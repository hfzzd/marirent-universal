<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MariRent Universal')</title>
    <meta name="description" content="@yield('meta_description', 'Platform rental universal untuk kendaraan, gadget, dan alat outdoor. Mudah, cepat, dan terpercaya.')">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('og_title', 'MariRent Universal')">
    <meta property="og:description" content="@yield('og_description', 'Platform rental universal untuk kendaraan, gadget, dan alat outdoor.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.png'))">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'MariRent Universal')">
    <meta name="twitter:description" content="@yield('og_description', 'Platform rental universal untuk kendaraan, gadget, dan alat outdoor.')">

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    {{-- Fonts & Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Vite (Tailwind + Custom CSS) --}}
    @vite(['resources/css/app.css'])

    @yield('styles')
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
                    <a href="{{ route('public.brands') }}" class="nav-link text-[13px] font-medium px-3 py-2 rounded-lg transition-all duration-300 {{ request()->routeIs('public.brands') || request()->routeIs('public.brand') ? 'active' : '' }}">Brand</a>
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
                <a href="{{ route('public.brands') }}" class="block text-[13px] font-medium text-navy-600 hover:text-sky-600 hover:bg-sky-50 px-3 py-2.5 rounded-lg transition">Brand</a>
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
                        <a href="https://instagram.com/marirent" target="_blank" class="w-9 h-9 bg-white/10 hover:bg-pink-500 rounded-lg flex items-center justify-center transition-all duration-300 hover:scale-110"><i class="fab fa-instagram text-sm"></i></a>
                        <a href="https://wa.me/6281234567890" target="_blank" class="w-9 h-9 bg-white/10 hover:bg-emerald-500 rounded-lg flex items-center justify-center transition-all duration-300 hover:scale-110"><i class="fab fa-whatsapp text-sm"></i></a>
                        <a href="https://facebook.com/marirent" target="_blank" class="w-9 h-9 bg-white/10 hover:bg-blue-500 rounded-lg flex items-center justify-center transition-all duration-300 hover:scale-110"><i class="fab fa-facebook text-sm"></i></a>
                        <a href="https://tiktok.com/@marirent" target="_blank" class="w-9 h-9 bg-white/10 hover:bg-navy-700 rounded-lg flex items-center justify-center transition-all duration-300 hover:scale-110"><i class="fab fa-tiktok text-sm"></i></a>
                    </div>

                    {{-- Newsletter --}}
                    <div class="mt-6">
                        <p class="text-[13px] font-semibold mb-2">Newsletter</p>
                        <div class="flex gap-2" x-data="{ email: '', subscribed: false }">
                            <input type="email" x-model="email" placeholder="Email Anda" class="flex-1 bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-[12px] text-white placeholder-gray-500 focus:outline-none focus:border-sky-400 transition">
                            <button @click="if(email) { subscribed = true; email = ''; }" x-show="!subscribed" class="bg-sky-500 hover:bg-sky-600 text-white px-3 py-2 rounded-lg text-[12px] font-semibold transition">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                            <span x-show="subscribed" class="text-emerald-400 text-[12px] flex items-center"><i class="fas fa-check mr-1"></i> Tersimpan!</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-[13px]">Navigasi</h4>
                    <ul class="space-y-2.5 text-gray-400 text-[13px]">
                        <li><a href="{{ route('home') }}" class="hover:text-sky-400 transition">Beranda</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-sky-400 transition">Tentang Kami</a></li>
                        <li><a href="{{ route('public.brands') }}" class="hover:text-sky-400 transition">Brand</a></li>
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
                        <li><i class="fas fa-clock mr-2 text-sky-400"></i> Buka 24 Jam</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 mt-10 pt-6 text-center text-gray-500 text-[12px]">
                &copy; {{ date('Y') }} MariRent Universal. All rights reserved.
            </div>
        </div>
    </footer>

    {{-- COOKIE CONSENT --}}
    <div x-data="cookieConsent()" x-init="init()" class="cookie-consent" :class="{ 'show': visible }">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-cookie-bite text-amber-400 text-lg"></i>
                <p class="text-[13px] text-gray-300">Kami menggunakan cookie untuk pengalaman terbaik. <a href="{{ route('about') }}" class="text-sky-400 underline">Pelajari lebih lanjut</a></p>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <button @click="accept()" class="bg-sky-500 hover:bg-sky-600 text-white px-5 py-2 rounded-xl text-[12px] font-semibold transition">Terima</button>
                <button @click="visible = false" class="bg-white/10 hover:bg-white/20 text-gray-300 px-5 py-2 rounded-xl text-[12px] font-medium transition">Tolak</button>
            </div>
        </div>
    </div>

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
    <script>
        function cookieConsent() {
            return {
                visible: false,
                init() {
                    if (!localStorage.getItem('mari_cookie_consent')) {
                        setTimeout(() => { this.visible = true; }, 2000);
                    }
                },
                accept() {
                    localStorage.setItem('mari_cookie_consent', '1');
                    this.visible = false;
                }
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
