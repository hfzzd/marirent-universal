<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard - MariRent')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: {
                sky: { 50:'#f0f9ff',100:'#e0f2fe',200:'#bae6fd',300:'#7dd3fc',400:'#38bdf8',500:'#0ea5e9',600:'#0284c7',700:'#0369a1',800:'#075985',900:'#0c4a6e' },
                navy: { 50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a' },
            }}}
        }
    </script>
    <style>
        * { scrollbar-width: thin; scrollbar-color: #334155 transparent; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        .sidebar { background: linear-gradient(180deg, #0c1929 0%, #0f2340 50%, #132f52 100%); }
        .sidebar-link { transition: all 0.2s ease; border-left: 3px solid transparent; }
        .sidebar-link:hover { background: rgba(56,189,248,0.08); border-left-color: #38bdf8; }
        .sidebar-link.active { background: rgba(56,189,248,0.12); border-left-color: #38bdf8; color: #38bdf8; }
        .stat-card { transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,0.08); }
        .btn-primary { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
        .btn-primary:hover { background: linear-gradient(135deg, #0284c7, #0369a1); }
        .badge { padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; display: inline-block; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #e0f2fe; color: #075985; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .badge-teal { background: #ccfbf1; color: #115e59; }
        .sidebar-group-title { font-size: 10px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #475569; padding: 8px 12px 4px; }
        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.6); }
        .gradient-sky { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); }
        .gradient-card-1 { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
        .gradient-card-2 { background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%); }
        .gradient-card-3 { background: linear-gradient(135deg, #7dd3fc 0%, #38bdf8 100%); }
        .gradient-card-4 { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
        .animate-fade-in { animation: fadeIn 0.5s ease-out; }
        .animate-slide-up { animation: slideUp 0.4s ease-out; }
        .animate-count { animation: countUp 1s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes countUp { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }
        .tooltip { position: relative; }
        .tooltip::after { content: attr(data-tip); position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background: #1e293b; color: white; padding: 4px 8px; border-radius: 6px; font-size: 11px; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
        .tooltip:hover::after { opacity: 1; }
        @yield('styles')
    </style>
    @stack('styles')
</head>
<body class="bg-sky-50/50 flex">
    {{-- SIDEBAR --}}
    <aside id="sidebar" class="sidebar w-60 h-screen sticky top-0 z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300 overflow-y-auto flex-shrink-0">
        <div class="p-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 mb-5 pb-4 border-b border-white/5">
                <div class="w-9 h-9 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/20">
                    <i class="fas fa-car-side text-white text-sm"></i>
                </div>
                <div>
                    <span class="text-[15px] font-bold text-white tracking-tight block leading-tight">Mari<span class="text-sky-400">Rent</span></span>
                    <span class="text-[9px] text-sky-300/50 font-medium tracking-widest uppercase">Universal</span>
                </div>
            </a>

            @php $role = auth()->user()->role; @endphp

            <nav class="space-y-0.5">
                @if($role !== 'user')
                <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home w-5 mr-2.5 text-sm"></i> Beranda
                </a>

                <div class="sidebar-group-title mt-3">Komunikasi</div>
                <a href="{{ route('mail.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('mail.*') ? 'active' : '' }}">
                    <i class="fas fa-envelope w-5 mr-2.5 text-sm"></i> Mail Inbox
                </a>
                <a href="{{ route('chat.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                    <i class="fas fa-comments w-5 mr-2.5 text-sm"></i> Live Chat
                </a>
                <a href="{{ route('contacts.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('contacts.*') ? 'active' : '' }}">
                    <i class="fas fa-address-book w-5 mr-2.5 text-sm"></i> Buku Kontak
                </a>
                @endif

                @if($role === 'superadmin')
                <div class="sidebar-group-title mt-4">Monitoring</div>
                <a href="{{ route('superadmin.monitoring') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('superadmin.monitoring*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar w-5 mr-2.5 text-sm"></i> Monitoring
                </a>
                <a href="{{ route('superadmin.monitoring-vehicle') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('superadmin.monitoring-vehicle') ? 'active' : '' }}">
                    <i class="fas fa-car w-5 mr-2.5 text-sm"></i> Monitoring Vehicle
                </a>
                <a href="{{ route('superadmin.scheduler') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('superadmin.scheduler*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt w-5 mr-2.5 text-sm"></i> Scheduler
                </a>

                <div class="sidebar-group-title mt-4">Inventaris</div>
                <a href="{{ route('vehicles.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                    <i class="fas fa-car w-5 mr-2.5 text-sm"></i> Mobil
                </a>
                <a href="{{ route('motors.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('motors.*') ? 'active' : '' }}">
                    <i class="fas fa-motorcycle w-5 mr-2.5 text-sm"></i> Motor
                </a>
                <a href="{{ route('superadmin.elektronik.type', 'kamera') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('superadmin.elektronik*') ? 'active' : '' }}">
                    <i class="fas fa-camera w-5 mr-2.5 text-sm"></i> Elektronik & Alat
                </a>

                <div class="sidebar-group-title mt-4">Keuangan</div>
                <a href="{{ route('superadmin.finance') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('superadmin.finance') ? 'active' : '' }}">
                    <i class="fas fa-wallet w-5 mr-2.5 text-sm"></i> Finance
                </a>
                <a href="{{ route('invoices.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar w-5 mr-2.5 text-sm"></i> Invoice
                </a>

                <div class="sidebar-group-title mt-4">Tim</div>
                <a href="{{ route('drivers.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('drivers.*') ? 'active' : '' }}">
                    <i class="fas fa-id-card w-5 mr-2.5 text-sm"></i> Driver
                </a>
                <a href="{{ route('superadmin.absen') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('superadmin.absen') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list w-5 mr-2.5 text-sm"></i> Absen Driver
                </a>
                <a href="{{ route('salaries.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('salaries.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave w-5 mr-2.5 text-sm"></i> Penggajian
                </a>

                <div class="sidebar-group-title mt-4">Operasional</div>
                <a href="{{ route('bookings.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('bookings.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check w-5 mr-2.5 text-sm"></i> Booking
                </a>
                <a href="{{ route('inspections.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('inspections.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check w-5 mr-2.5 text-sm"></i> Inspeksi
                </a>
                <a href="{{ route('maintenances.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('maintenances.*') ? 'active' : '' }}">
                    <i class="fas fa-wrench w-5 mr-2.5 text-sm"></i> Maintenance
                </a>
                <a href="{{ route('reports.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-route w-5 mr-2.5 text-sm"></i> Laporan
                </a>
                <a href="{{ route('replacements.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('replacements.*') ? 'active' : '' }}">
                    <i class="fas fa-exchange-alt w-5 mr-2.5 text-sm"></i> Penggantian
                </a>

                @elseif($role === 'owner')
                <div class="sidebar-group-title mt-4">Inventaris & Tim</div>
                <a href="{{ route('vehicles.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                    <i class="fas fa-car w-5 mr-2.5 text-sm"></i> Mobil
                </a>
                <a href="{{ route('motors.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('motors.*') ? 'active' : '' }}">
                    <i class="fas fa-motorcycle w-5 mr-2.5 text-sm"></i> Motor
                </a>
                <a href="{{ route('owner.elektronik.type', 'hp') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('owner.elektronik*') ? 'active' : '' }}">
                    <i class="fas fa-mobile-alt w-5 mr-2.5 text-sm"></i> HP, Kamera & Alat
                </a>
                <a href="{{ route('drivers.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('drivers.*') ? 'active' : '' }}">
                    <i class="fas fa-id-card w-5 mr-2.5 text-sm"></i> Driver
                </a>
                <a href="{{ route('salaries.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('salaries.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave w-5 mr-2.5 text-sm"></i> Penggajian
                </a>
                <div class="sidebar-group-title mt-4">Keuangan</div>
                <a href="{{ route('owner.revenue.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('owner.revenue*') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding-dollar w-5 mr-2.5 text-sm"></i> Pendapatan
                </a>
                <div class="sidebar-group-title mt-4">Operasional</div>
                <a href="{{ route('bookings.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('bookings.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check w-5 mr-2.5 text-sm"></i> Booking
                </a>
                <a href="{{ route('invoices.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar w-5 mr-2.5 text-sm"></i> Invoice
                </a>
                <a href="{{ route('inspections.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('inspections.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check w-5 mr-2.5 text-sm"></i> Inspeksi
                </a>
                <a href="{{ route('maintenances.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('maintenances.*') ? 'active' : '' }}">
                    <i class="fas fa-wrench w-5 mr-2.5 text-sm"></i> Maintenance
                </a>
                <a href="{{ route('reports.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-route w-5 mr-2.5 text-sm"></i> Laporan
                </a>
                <a href="{{ route('replacements.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('replacements.*') ? 'active' : '' }}">
                    <i class="fas fa-exchange-alt w-5 mr-2.5 text-sm"></i> Penggantian
                </a>

                @elseif($role === 'driver')
                <div class="sidebar-group-title mt-4">Menu</div>
                <a href="{{ route('bookings.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('bookings.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check w-5 mr-2.5 text-sm"></i> Perjalanan
                </a>
                <a href="{{ route('reports.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-route w-5 mr-2.5 text-sm"></i> Laporan
                </a>
                <a href="{{ route('replacements.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('replacements.*') ? 'active' : '' }}">
                    <i class="fas fa-exchange-alt w-5 mr-2.5 text-sm"></i> Penggantian
                </a>

                @elseif($role === 'inspector')
                <div class="sidebar-group-title mt-4">Tugas Inspeksi</div>
                <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check w-5 mr-2.5 text-sm"></i> Dashboard
                </a>
                <a href="{{ route('inspections.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('inspections.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list w-5 mr-2.5 text-sm"></i> Riwayat Inspeksi
                </a>
                <a href="{{ route('inspections.create') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('inspections.create') ? 'active' : '' }}">
                    <i class="fas fa-plus-circle w-5 mr-2.5 text-sm"></i> Inspeksi Baru
                </a>
                <a href="{{ route('bookings.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('bookings.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check w-5 mr-2.5 text-sm"></i> Booking
                </a>

                @else
                <div class="sidebar-group-title mt-4">Akun</div>
                <a href="{{ route('dashboard.profile') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('dashboard.profile') ? 'active' : '' }}">
                    <i class="fas fa-user-circle w-5 mr-2.5 text-sm"></i> Profil Saya
                </a>
                <div class="sidebar-group-title mt-4">Menu</div>
                <a href="{{ route('bookings.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('bookings.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check w-5 mr-2.5 text-sm"></i> Booking Saya
                </a>
                <a href="{{ route('invoices.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar w-5 mr-2.5 text-sm"></i> Invoice Saya
                </a>
                <a href="{{ route('item-replacements.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('item-replacements.*') ? 'active' : '' }}">
                    <i class="fas fa-sync-alt w-5 mr-2.5 text-sm"></i> Penggantian Unit
                </a>
                @endif
            </nav>

            <div class="mt-6 pt-4 border-t border-white/5">
                <a href="{{ route('home') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium">
                    <i class="fas fa-globe w-5 mr-2.5 text-sm"></i> Lihat Website
                </a>
                <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                    @csrf
                    <button type="submit" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-red-400/70 hover:text-red-400 text-[13px] font-medium w-full">
                        <i class="fas fa-sign-out-alt w-5 mr-2.5 text-sm"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <div class="flex-1 min-h-screen">
        {{-- TOPBAR --}}
        <header class="bg-white/80 backdrop-blur-md border-b border-sky-100 sticky top-0 z-30">
            <div class="flex items-center justify-between px-5 py-3">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="md:hidden text-navy-600 hover:text-sky-600 transition">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <h1 class="text-[15px] font-semibold text-navy-800 hidden md:block">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative" x-data="{ open: false }">
                        <button onclick="this.nextElementSibling.classList.toggle('hidden')" class="flex items-center gap-2.5 hover:bg-sky-50 rounded-xl px-2.5 py-1.5 transition">
                            <div class="w-8 h-8 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl flex items-center justify-center shadow-md shadow-sky-500/20">
                                <span class="text-white font-bold text-xs">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            </div>
                            <div class="text-left hidden sm:block">
                                <p class="text-[13px] font-semibold text-navy-800 leading-tight">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] text-sky-500 font-medium capitalize">{{ auth()->user()->role }}</p>
                            </div>
                            <i class="fas fa-chevron-down text-[10px] text-navy-400 hidden sm:block"></i>
                        </button>
                        <div class="hidden absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50 animate-slide-up">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-[13px] font-semibold text-navy-800">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-gray-400">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-navy-600 hover:bg-sky-50 transition">
                                <i class="fas fa-globe text-sm w-4"></i> Lihat Website
                            </a>
                            <hr class="my-1 border-gray-100">
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
        </header>

        <main class="p-5">
            @if(session('success'))
                <div class="mb-4 bg-sky-50 border border-sky-200 text-sky-700 px-4 py-3 rounded-xl text-[13px] flex items-center animate-slide-up shadow-sm">
                    <i class="fas fa-check-circle mr-2 text-sky-500"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-[13px] flex items-center animate-slide-up shadow-sm">
                    <i class="fas fa-exclamation-circle mr-2 text-red-400"></i> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-[13px] animate-slide-up shadow-sm">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
        }
        // Close sidebar on outside click (mobile)
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const btn = e.target.closest('button[onclick="toggleSidebar()"]');
            if (window.innerWidth < 768 && !sidebar.contains(e.target) && !btn) {
                sidebar.classList.add('-translate-x-full');
            }
        });
        // Close dropdowns on outside click
        document.addEventListener('click', function(e) {
            document.querySelectorAll('[x-data]').forEach(el => {
                if (!el.contains(e.target)) {
                    el.querySelectorAll('.hidden').forEach(d => {
                        if (d.classList.contains('absolute')) d.classList.add('hidden');
                    });
                }
            });
        });
        // Animate numbers on load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-count]').forEach(el => {
                const target = parseInt(el.getAttribute('data-count'));
                const prefix = el.getAttribute('data-prefix') || '';
                const suffix = el.getAttribute('data-suffix') || '';
                let current = 0;
                const step = Math.ceil(target / 40);
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) { current = target; clearInterval(timer); }
                    el.textContent = prefix + current.toLocaleString('id-ID') + suffix;
                }, 25);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
