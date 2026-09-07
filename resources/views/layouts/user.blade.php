<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Akun Saya - MariRent')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                sky: { 50:'#f0f9ff',100:'#e0f2fe',200:'#bae6fd',300:'#7dd3fc',400:'#38bdf8',500:'#0ea5e9',600:'#0284c7',700:'#0369a1',800:'#075985',900:'#0c4a6e' },
                navy: { 50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a' },
            }}}
        }
    </script>
    <style>
        * { font-family: 'Inter', sans-serif; scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .btn-primary { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
        .btn-primary:hover { background: linear-gradient(135deg, #0284c7, #0369a1); }
        .glass-card { background: rgba(255,255,255,0.9); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.6); }
        .nav-link { position: relative; transition: all 0.2s; }
        .nav-link::after { content: ''; position: absolute; bottom: -2px; left: 50%; width: 0; height: 2px; background: #0ea5e9; transition: all 0.3s; transform: translateX(-50%); border-radius: 1px; }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }
        .nav-link:hover, .nav-link.active { color: #0284c7; }
        .animate-fade-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @yield('styles')
    </style>
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-sky-50 via-white to-sky-50 min-h-screen">
    {{-- NAVBAR --}}
    <nav class="bg-white/80 backdrop-blur-xl border-b border-sky-100/50 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/20">
                        <i class="fas fa-car-side text-white text-sm"></i>
                    </div>
                    <div>
                        <span class="text-[15px] font-bold text-navy-900 tracking-tight block leading-tight">Mari<span class="text-sky-500">Rent</span></span>
                        <span class="text-[8px] text-sky-400 font-semibold tracking-[0.2em] uppercase">Universal</span>
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('dashboard.profile') }}" class="nav-link px-3.5 py-2 text-[13px] font-semibold text-gray-500 rounded-lg {{ request()->routeIs('dashboard.profile') ? 'active text-sky-600' : '' }}">
                        <i class="fas fa-user-circle mr-1.5 text-[11px]"></i> Profil
                    </a>
                    <a href="{{ route('bookings.index') }}" class="nav-link px-3.5 py-2 text-[13px] font-semibold text-gray-500 rounded-lg {{ request()->routeIs('bookings.*') ? 'active text-sky-600' : '' }}">
                        <i class="fas fa-calendar-check mr-1.5 text-[11px]"></i> Booking
                    </a>
                    <a href="{{ route('invoices.index') }}" class="nav-link px-3.5 py-2 text-[13px] font-semibold text-gray-500 rounded-lg {{ request()->routeIs('invoices.*') ? 'active text-sky-600' : '' }}">
                        <i class="fas fa-file-invoice-dollar mr-1.5 text-[11px]"></i> Invoice
                    </a>
                    <a href="{{ route('chat.index') }}" class="nav-link px-3.5 py-2 text-[13px] font-semibold text-gray-500 rounded-lg {{ request()->routeIs('chat.*') ? 'active text-sky-600' : '' }}">
                        <i class="fas fa-comments mr-1.5 text-[11px]"></i> Chat
                    </a>
                    <a href="{{ route('mail.index') }}" class="nav-link px-3.5 py-2 text-[13px] font-semibold text-gray-500 rounded-lg {{ request()->routeIs('mail.*') ? 'active text-sky-600' : '' }}">
                        <i class="fas fa-envelope mr-1.5 text-[11px]"></i> Pesan
                    </a>
                </div>

                {{-- Right Side --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="hidden sm:flex items-center gap-1.5 bg-sky-50 hover:bg-sky-100 text-sky-600 px-3.5 py-2 rounded-xl text-[12px] font-semibold transition-all duration-200">
                        <i class="fas fa-globe text-[10px]"></i> Website
                    </a>

                    {{-- Avatar Dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 hover:bg-sky-50 rounded-xl px-2 py-1.5 transition-all duration-200">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="" class="w-8 h-8 rounded-xl object-cover shadow-md shadow-sky-500/10 border-2 border-white">
                            @else
                                <div class="w-8 h-8 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-md shadow-sky-500/20">
                                    <span class="text-white font-bold text-xs">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                </div>
                            @endif
                            <span class="text-[13px] font-semibold text-navy-700 hidden sm:block">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down text-[9px] text-gray-400 hidden sm:block transition-transform duration-200" :class="open && 'rotate-180'"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="hidden absolute right-0 top-full mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-gray-100 py-2 z-50" style="display: none;">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-[13px] font-bold text-navy-800">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('dashboard.profile') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-navy-600 hover:bg-sky-50 transition">
                                <i class="fas fa-user-circle text-sm w-4 text-gray-400"></i> Profil Saya
                            </a>
                            <a href="{{ route('bookings.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-navy-600 hover:bg-sky-50 transition">
                                <i class="fas fa-calendar-check text-sm w-4 text-gray-400"></i> Booking Saya
                            </a>
                            <a href="{{ route('invoices.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-navy-600 hover:bg-sky-50 transition">
                                <i class="fas fa-file-invoice-dollar text-sm w-4 text-gray-400"></i> Invoice Saya
                            </a>
                            <hr class="my-1.5 border-gray-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-red-500 hover:bg-red-50 transition w-full">
                                    <i class="fas fa-sign-out-alt text-sm w-4"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mobile Nav --}}
            <div class="md:hidden flex items-center gap-1 pb-3 -mt-1 overflow-x-auto">
                <a href="{{ route('dashboard.profile') }}" class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-[12px] font-semibold whitespace-nowrap transition-all duration-200 {{ request()->routeIs('dashboard.profile') ? 'bg-sky-500 text-white shadow-lg shadow-sky-500/25' : 'bg-gray-100 text-gray-500' }}">
                    <i class="fas fa-user-circle text-[10px]"></i> Profil
                </a>
                <a href="{{ route('bookings.index') }}" class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-[12px] font-semibold whitespace-nowrap transition-all duration-200 {{ request()->routeIs('bookings.*') ? 'bg-sky-500 text-white shadow-lg shadow-sky-500/25' : 'bg-gray-100 text-gray-500' }}">
                    <i class="fas fa-calendar-check text-[10px]"></i> Booking
                </a>
                <a href="{{ route('invoices.index') }}" class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-[12px] font-semibold whitespace-nowrap transition-all duration-200 {{ request()->routeIs('invoices.*') ? 'bg-sky-500 text-white shadow-lg shadow-sky-500/25' : 'bg-gray-100 text-gray-500' }}">
                    <i class="fas fa-file-invoice-dollar text-[10px]"></i> Invoice
                </a>
            </div>
        </div>
    </nav>

    {{-- CONTENT --}}
    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8 animate-fade-in">
        @if(session('success'))
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-3.5 rounded-2xl text-[13px] flex items-center gap-2.5 shadow-sm animate-fade-in">
                <div class="w-8 h-8 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-emerald-500 text-sm"></i></div>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-5 py-3.5 rounded-2xl text-[13px] flex items-center gap-2.5 shadow-sm animate-fade-in">
                <div class="w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation text-red-500 text-sm"></i></div>
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-5 py-3.5 rounded-2xl text-[13px] animate-fade-in shadow-sm">
                @foreach($errors->all() as $error)
                    <div class="flex items-center gap-2"><i class="fas fa-exclamation-circle text-red-400 text-[10px]"></i> {{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="border-t border-gray-100 mt-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[12px] text-gray-400">&copy; {{ date('Y') }} MariRent Universal. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="text-[12px] text-gray-400 hover:text-sky-500 transition">Beranda</a>
                <a href="{{ route('products') }}" class="text-[12px] text-gray-400 hover:text-sky-500 transition">Produk</a>
                <a href="{{ route('about') }}" class="text-[12px] text-gray-400 hover:text-sky-500 transition">Tentang</a>
                <a href="{{ route('contact') }}" class="text-[12px] text-gray-400 hover:text-sky-500 transition">Kontak</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
