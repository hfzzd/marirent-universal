<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MariRent Universal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                sky: { 50:'#f0f9ff',100:'#e0f2fe',200:'#bae6fd',300:'#7dd3fc',400:'#38bdf8',500:'#0ea5e9',600:'#0284c7',700:'#0369a1',800:'#075985',900:'#0c4a6e' },
                navy: { 50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a' },
            }}}
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .btn-primary { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
        .btn-primary:hover { background: linear-gradient(135deg, #0284c7, #0369a1); }
        .category-card { transition: all 0.25s ease; }
        .category-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(14,165,233,0.12); }
        .vehicle-card { transition: all 0.25s ease; }
        .vehicle-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,0.08); }
        .filter-active { background-color: #0ea5e9 !important; color: white !important; border-color: #0ea5e9 !important; }
        .glass { background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.6); }
        .fade-in { animation: fadeIn 0.6s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @yield('styles')
    </style>
    @stack('styles')
</head>
<body class="bg-sky-50/30">
    {{-- NAVBAR --}}
    <nav class="bg-white/90 backdrop-blur-md border-b border-sky-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/20">
                        <i class="fas fa-car-side text-white text-sm"></i>
                    </div>
                    <span class="text-lg font-bold text-navy-900 tracking-tight">Mari<span class="text-sky-600">Rent</span></span>
                </a>

                {{-- NAV LINKS --}}
                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('home') }}" class="text-[13px] font-medium text-navy-600 hover:text-sky-600 hover:bg-sky-50 px-3 py-2 rounded-lg transition {{ request()->routeIs('home') ? 'text-sky-600 bg-sky-50' : '' }}">Beranda</a>
                    <a href="{{ route('about') }}" class="text-[13px] font-medium text-navy-600 hover:text-sky-600 hover:bg-sky-50 px-3 py-2 rounded-lg transition {{ request()->routeIs('about') ? 'text-sky-600 bg-sky-50' : '' }}">Tentang Kami</a>
                    <a href="{{ route('products') }}" class="text-[13px] font-medium text-navy-600 hover:text-sky-600 hover:bg-sky-50 px-3 py-2 rounded-lg transition {{ request()->routeIs('products') ? 'text-sky-600 bg-sky-50' : '' }}">Produk</a>
                    <a href="{{ route('contact') }}" class="text-[13px] font-medium text-navy-600 hover:text-sky-600 hover:bg-sky-50 px-3 py-2 rounded-lg transition {{ request()->routeIs('contact') ? 'text-sky-600 bg-sky-50' : '' }}">Kontak</a>
                </div>

                <div class="flex items-center gap-2">
                    @auth
                        @if(auth()->user()->isUser())
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="flex items-center gap-2 btn-primary text-white px-3 py-1.5 rounded-xl font-semibold text-[13px] shadow-lg shadow-sky-500/25 transition">
                                    <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center overflow-hidden">
                                        @if(auth()->user()->avatar)
                                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-user text-white text-xs"></i>
                                        @endif
                                    </div>
                                    <span class="hidden sm:inline">{{ Str::limit(auth()->user()->name, 12) }}</span>
                                    <i class="fas fa-chevron-down text-[10px] ml-0.5 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-sky-100 py-2 z-50" x-cloak>
                                    <div class="px-4 py-2 border-b border-sky-50">
                                        <p class="text-[13px] font-semibold text-navy-800">{{ auth()->user()->name }}</p>
                                        <p class="text-[11px] text-navy-400 truncate">{{ auth()->user()->email }}</p>
                                    </div>
                                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-navy-600 hover:bg-sky-50 transition">
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
                            <a href="{{ route('dashboard') }}" class="btn-primary text-white px-4 py-2 rounded-xl font-semibold text-[13px] shadow-lg shadow-sky-500/25 transition">
                                <i class="fas fa-tachometer-alt mr-1.5"></i> Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-[13px] font-medium text-navy-600 hover:text-sky-600 px-3 py-2 transition">Login</a>
                        <a href="{{ route('register') }}" class="btn-primary text-white px-4 py-2 rounded-xl font-semibold text-[13px] shadow-lg shadow-sky-500/25 transition">Daftar</a>
                    @endauth
                    {{-- Mobile menu --}}
                    <button onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" class="md:hidden text-navy-600 ml-1">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
        {{-- Mobile dropdown --}}
        <div id="mobileMenu" class="hidden md:hidden border-t border-sky-100 bg-white/95 backdrop-blur-md">
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
                        <a href="{{ route('dashboard') }}" class="block text-[13px] font-medium text-sky-600 hover:bg-sky-50 px-3 py-2.5 rounded-lg transition">
                            <i class="fas fa-user-circle mr-2"></i> Profile Saya
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block text-[13px] font-medium text-red-500 hover:bg-red-50 px-3 py-2.5 rounded-lg transition w-full text-left">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    @yield('content')

    {{-- FOOTER --}}
    <footer class="bg-navy-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/20">
                            <i class="fas fa-car-side text-white text-sm"></i>
                        </div>
                        <span class="text-lg font-bold">Mari<span class="text-sky-400">Rent</span></span>
                    </div>
                    <p class="text-gray-400 text-[13px] leading-relaxed">Platform rental universal untuk kendaraan, gadget, dan alat outdoor. Mudah, cepat, dan terpercaya.</p>
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
                    <div class="flex gap-3 mt-4">
                        <a href="#" class="w-9 h-9 bg-white/10 hover:bg-sky-500 rounded-lg flex items-center justify-center transition"><i class="fab fa-instagram text-sm"></i></a>
                        <a href="#" class="w-9 h-9 bg-white/10 hover:bg-sky-500 rounded-lg flex items-center justify-center transition"><i class="fab fa-whatsapp text-sm"></i></a>
                        <a href="#" class="w-9 h-9 bg-white/10 hover:bg-sky-500 rounded-lg flex items-center justify-center transition"><i class="fab fa-facebook text-sm"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/10 mt-10 pt-6 text-center text-gray-500 text-[12px]">
                &copy; {{ date('Y') }} MariRent Universal. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll for anchor links
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
