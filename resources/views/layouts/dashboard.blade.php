<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard - MariRent')</title>
    <meta name="robots" content="noindex, nofollow">

    {{-- Fonts & Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    {{-- Vite (Tailwind + Custom CSS) --}}
    @vite(['resources/css/app.css'])

    @yield('styles')
    @stack('styles')
</head>
<body class="bg-sky-50/50 flex">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- SIDEBAR --}}
    <aside id="sidebar" class="sidebar w-60 max-w-[85vw] h-screen fixed top-0 left-0 z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300 overflow-y-auto flex-shrink-0">
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

            @php
                $role = auth()->user()->role;
                $catId = auth()->user()->merchantCategoryId();
                $catSlug = auth()->user()->merchantCategory?->slug;
                $showMobil = !$catId || $catId == 1;
                $showMotor = !$catId || $catId == 2;
                $elektronikType = match ($catSlug) {
                    'sewa-hp' => 'hp',
                    'sewa-kamera' => 'kamera',
                    'sewa-tenda' => 'tenda',
                    'sewa-ps' => 'ps',
                    'sewa-drone' => 'drone',
                    'sewa-alat-musik' => 'musik',
                    default => null,
                };
                $showElektronik = !$catId || $elektronikType !== null;
                $elektronikLabel = match ($catSlug) {
                    'sewa-hp' => 'HP',
                    'sewa-kamera' => 'Kamera',
                    'sewa-tenda' => 'Alat Camping',
                    'sewa-ps' => 'Playstation',
                    'sewa-drone' => 'Drone',
                    'sewa-alat-musik' => 'Alat Musik',
                    default => 'HP, Kamera & Alat',
                };
            @endphp

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
                <a href="{{ route('admin.brand-catalog.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('admin.brand-catalog*') ? 'active' : '' }}">
                    <i class="fas fa-images w-5 mr-2.5 text-sm"></i> Katalog Brand
                </a>

                <div class="sidebar-group-title mt-4">Marketplace</div>
                <a href="{{ route('superadmin.merchants') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('superadmin.merchants*') ? 'active' : '' }}">
                    <i class="fas fa-store w-5 mr-2.5 text-sm"></i> Merchant / Toko
                </a>
                <a href="{{ route('superadmin.finance') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('superadmin.finance') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding-usd w-5 mr-2.5 text-sm"></i> Komisi Platform
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
                @php
                    $ownerMerchant = auth()->user()->merchantProfile;
                    $ownerVerified = $ownerMerchant && $ownerMerchant->status === 'active' && $ownerMerchant->is_active;
                @endphp
                <div class="sidebar-group-title mt-4">Toko</div>
                <a href="{{ route('merchant.profile') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('merchant.profile') ? 'active' : '' }}">
                    <i class="fas fa-store w-5 mr-2.5 text-sm"></i> Toko Saya
                </a>
                @if($ownerVerified)
                <div class="sidebar-group-title mt-3">Inventaris & Tim</div>
                @if($showMobil)
                <a href="{{ route('vehicles.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                    <i class="fas fa-car w-5 mr-2.5 text-sm"></i> Mobil
                </a>
                @endif
                @if($showMotor)
                <a href="{{ route('motors.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('motors.*') ? 'active' : '' }}">
                    <i class="fas fa-motorcycle w-5 mr-2.5 text-sm"></i> Motor
                </a>
                @endif
                @if($showElektronik)
                <a href="{{ route('owner.elektronik.type', $elektronikType ?? 'hp') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('owner.elektronik*', 'superadmin.elektronik*') ? 'active' : '' }}">
                    <i class="fas fa-mobile-alt w-5 mr-2.5 text-sm"></i> {{ $elektronikLabel }}
                </a>
                @endif
                <a href="{{ route('admin.brand-catalog.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('admin.brand-catalog*') ? 'active' : '' }}">
                    <i class="fas fa-images w-5 mr-2.5 text-sm"></i> Katalog Brand
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
                @endif

                @elseif($role === 'admin')
                <div class="sidebar-group-title mt-4">Inventaris & Tim</div>
                @if($showMobil)
                <a href="{{ route('vehicles.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                    <i class="fas fa-car w-5 mr-2.5 text-sm"></i> Mobil
                </a>
                @endif
                @if($showMotor)
                <a href="{{ route('motors.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('motors.*') ? 'active' : '' }}">
                    <i class="fas fa-motorcycle w-5 mr-2.5 text-sm"></i> Motor
                </a>
                @endif
                @if($showElektronik)
                <a href="{{ route('owner.elektronik.type', $elektronikType ?? 'hp') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('owner.elektronik*', 'superadmin.elektronik*') ? 'active' : '' }}">
                    <i class="fas fa-mobile-alt w-5 mr-2.5 text-sm"></i> {{ $elektronikLabel }}
                </a>
                @endif
                <a href="{{ route('admin.brand-catalog.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('admin.brand-catalog*') ? 'active' : '' }}">
                    <i class="fas fa-images w-5 mr-2.5 text-sm"></i> Katalog Brand
                </a>
                <a href="{{ route('salaries.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('salaries.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave w-5 mr-2.5 text-sm"></i> Penggajian
                </a>
                <div class="sidebar-group-title mt-4">Keuangan</div>
                <a href="{{ route('invoices.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar w-5 mr-2.5 text-sm"></i> Invoice
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

                @elseif($role === 'driver')
                <div class="sidebar-group-title mt-4">Absensi</div>
                <a href="{{ route('attendance.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                    <i class="fas fa-fingerprint w-5 mr-2.5 text-sm"></i> Absen
                </a>
                <div class="sidebar-group-title mt-4">Menu</div>
                <a href="{{ route('bookings.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('bookings.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check w-5 mr-2.5 text-sm"></i> Perjalanan
                </a>
                <a href="{{ route('reports.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-route w-5 mr-2.5 text-sm"></i> Laporan
                </a>
                <div class="sidebar-group-title mt-4">Inspeksi</div>
                <a href="{{ route('driver.report') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('driver.report*') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-triangle w-5 mr-2.5 text-sm"></i> Lapor Kendala
                </a>
                <a href="{{ route('inspections.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('inspections.index') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list w-5 mr-2.5 text-sm"></i> Riwayat Inspeksi
                </a>
                <a href="{{ route('inspections.create') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('inspections.create') ? 'active' : '' }}">
                    <i class="fas fa-plus-circle w-5 mr-2.5 text-sm"></i> Inspeksi Baru
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
                <div class="sidebar-group-title mt-4">Operasional</div>
                <a href="{{ route('maintenances.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('maintenances.*') ? 'active' : '' }}">
                    <i class="fas fa-wrench w-5 mr-2.5 text-sm"></i> Maintenance
                </a>
                <a href="{{ route('invoices.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-gray-400 text-[13px] font-medium {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar w-5 mr-2.5 text-sm"></i> Invoice
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

    {{-- Backdrop untuk sidebar mobile (off-canvas) --}}
    <div id="sidebar-backdrop" class="hidden fixed inset-0 bg-navy-900/50 z-30 md:hidden" onclick="toggleSidebar()" aria-hidden="true"></div>

    {{-- MAIN CONTENT --}}
    <div class="md:pl-60 flex-1 min-h-screen">
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
                    {{-- Notification Bell --}}
                    <div class="relative" x-data="{ notifOpen: false, notifCount: 0 }" x-init="
                        fetch('{{ route('notifications.unread-count') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                            .then(r => r.json()).then(d => notifCount = d.count);
                        setInterval(() => {
                            fetch('{{ route('notifications.unread-count') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                                .then(r => r.json()).then(d => notifCount = d.count);
                        }, 30000);
                    ">
                        <a href="{{ route('notifications.index') }}" class="relative flex items-center justify-center w-9 h-9 rounded-xl hover:bg-sky-50 transition" @click.prevent="notifOpen = !notifOpen">
                            <i class="fas fa-bell text-navy-500 text-sm"></i>
                            <span x-show="notifCount > 0" x-text="notifCount" class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center shadow-sm" x-cloak></span>
                        </a>
                        <div x-show="notifOpen" @click.away="notifOpen = false" x-transition class="absolute right-0 top-full mt-2 w-72 sm:w-80 max-w-[calc(100vw-1.5rem)] bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50 animate-slide-up" x-cloak>
                            <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                                <span class="text-[13px] font-bold text-navy-800">Notifikasi</span>
                                <a href="{{ route('notifications.index') }}" class="text-[11px] text-sky-600 hover:text-sky-700 font-semibold">Lihat Semua</a>
                            </div>
                            <div class="max-h-72 overflow-y-auto" id="notif-dropdown-list">
                                <div class="px-4 py-6 text-center text-gray-400 text-[12px]">
                                    <i class="fas fa-bell-slash text-gray-300 mb-1"></i><br>Memuat notifikasi...
                                </div>
                            </div>
                        </div>
                    </div>

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

        <main class="p-3 sm:p-5">
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

            @php
                $pendingMerchant = auth()->user()?->isOwner() ? \App\Models\Merchant::where('user_id', auth()->id())->where('status', 'pending')->first() : null;
            @endphp
            @if($pendingMerchant)
                <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl text-[13px] flex items-center gap-2 animate-slide-up shadow-sm">
                    <i class="fas fa-clock text-amber-500"></i>
                    Toko Anda sedang <strong>menunggu verifikasi admin</strong>. Anda belum bisa mengelola produk & transaksi hingga toko disetujui.
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function isSidebarOpen() {
            return !document.getElementById('sidebar')?.classList.contains('-translate-x-full');
        }
        function setSidebar(open) {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (!sidebar) return;
            // On desktop the sidebar is always visible in-flow; keep it static.
            if (window.innerWidth >= 768) return;
            sidebar.classList.toggle('-translate-x-full', !open);
            if (backdrop) backdrop.classList.toggle('hidden', !open);
            // Lock body scroll while the drawer is open so it never feels like it covers the page.
            document.body.style.overflow = open ? 'hidden' : '';
        }
        function toggleSidebar() {
            setSidebar(!isSidebarOpen());
        }
        function closeSidebar() {
            setSidebar(false);
        }
        // Auto-close sidebar after clicking a link/logout inside, or anywhere outside (mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth >= 768) return;
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;
            const hamburger = e.target.closest('button[onclick="toggleSidebar()"]');
            if (hamburger) return; // handled by toggleSidebar
            if (sidebar.contains(e.target)) {
                const target = e.target.closest('a, #sidebar-logout-form button');
                if (target) closeSidebar();
                return;
            }
            // clicked outside the sidebar
            closeSidebar();
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
    <script>
        // Notification dropdown fetcher
        document.addEventListener('DOMContentLoaded', function() {
            const notifList = document.getElementById('notif-dropdown-list');
            if (notifList) {
                fetch('{{ route("notifications.index") }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                }).then(r => r.text()).then(html => {
                    // Parse and extract notification items from the HTML
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const cards = doc.querySelectorAll('.glass-card');
                    if (cards.length === 0) {
                        notifList.innerHTML = '<div class="px-4 py-6 text-center text-gray-400 text-[12px]"><i class="fas fa-bell-slash text-gray-300 mb-1"></i><br>Belum ada notifikasi</div>';
                        return;
                    }
                    notifList.innerHTML = '';
                    cards.forEach((card, i) => {
                        if (i >= 5) return; // Show max 5 in dropdown
                        const title = card.querySelector('h4')?.textContent || '';
                        const msg = card.querySelector('p')?.textContent || '';
                        const time = card.querySelector('.whitespace-nowrap')?.textContent || '';
                        const link = card.querySelector('a[href*="bookings"]')?.href || '#';
                        const unread = card.querySelector('.bg-sky-500') !== null;
                        notifList.innerHTML += '<a href="' + link + '" class="block px-4 py-3 hover:bg-sky-50 transition border-b border-gray-50 last:border-0">' +
                            '<div class="flex items-start gap-2">' +
                            (unread ? '<span class="w-2 h-2 rounded-full bg-sky-500 mt-1.5 flex-shrink-0"></span>' : '') +
                            '<div class="min-w-0"><p class="text-[12px] font-semibold text-navy-800 truncate">' + title + '</p>' +
                            '<p class="text-[11px] text-gray-500 truncate">' + msg + '</p>' +
                            '<p class="text-[10px] text-gray-400 mt-0.5">' + time + '</p></div></div></a>';
                    });
                }).catch(() => {
                    notifList.innerHTML = '<div class="px-4 py-6 text-center text-gray-400 text-[12px]">Gagal memuat notifikasi</div>';
                });
            }
        });
    </script>
</body>
</html>
