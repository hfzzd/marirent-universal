@extends('layouts.dashboard')
@section('title', 'Dashboard - MariRental')
@section('page-title', 'Dashboard')

@section('content')
@php
    $role = Auth::user()->role ?? 'user';
@endphp

@if(in_array($role, ['superadmin', 'owner']))
    {{-- Admin Dashboard --}}
    @php
        $totalRevenue = \App\Models\Invoice::where('status', 'paid')->sum('paid_amount');
        $activeRentals = \App\Models\Booking::whereIn('status', ['confirmed', 'ongoing'])->count();
        $availableVehicles = \App\Models\Vehicle::where('status', 'available')->where('is_active', true)->count();
        $pendingInvoices = \App\Models\Invoice::whereIn('status', ['sent', 'partial'])->count();
        $recentBookings = \App\Models\Booking::with(['user', 'vehicle'])->latest()->limit(5)->get();
        $selectedYear = request('year', date('Y'));
        $monthlyRevenue = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyRevenue[$m] = \App\Models\Invoice::where('status', 'paid')
                ->whereYear('paid_at', $selectedYear)->whereMonth('paid_at', $m)->sum('paid_amount');
        }
        $maxMonthly = max(1, max($monthlyRevenue));
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Total Revenue --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-semibold text-accent bg-accent/10 px-2.5 py-1 rounded-lg">+12.5%</span>
            </div>
            <h3 class="text-2xl font-bold text-secondary">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
            <p class="text-gray-500 text-sm mt-1">Total Pendapatan</p>
        </div>

        {{-- Active Rentals --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <span class="text-xs font-semibold text-accent bg-accent/10 px-2.5 py-1 rounded-lg">+5.2%</span>
            </div>
            <h3 class="text-2xl font-bold text-secondary">{{ $activeRentals }}</h3>
            <p class="text-gray-500 text-sm mt-1">Rental Aktif</p>
        </div>

        {{-- Available Vehicles --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <span class="text-xs font-semibold text-red-500 bg-red-50 px-2.5 py-1 rounded-lg">-3.1%</span>
            </div>
            <h3 class="text-2xl font-bold text-secondary">{{ $availableVehicles }}</h3>
            <p class="text-gray-500 text-sm mt-1">Kendaraan Tersedia</p>
        </div>

        {{-- Pending Invoices --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                </div>
                <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg">Perlu Ditindak</span>
            </div>
            <h3 class="text-2xl font-bold text-secondary">{{ $pendingInvoices }}</h3>
            <p class="text-gray-500 text-sm mt-1">Invoice Pending</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Chart --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-secondary text-lg">Pendapatan Bulanan</h3>
                <form method="GET" action="{{ route('dashboard') }}">
                    <select name="year" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-secondary focus:outline-none">
                        @foreach([date('Y'), date('Y')-1, date('Y')-2] as $y)
                        <option value="{{ $y }}" {{ (int)$selectedYear === (int)$y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="h-64 flex items-end justify-between space-x-2 px-4">
                @php
                    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                @endphp
                @foreach($monthlyRevenue as $m => $val)
                    @php $h = $maxMonthly > 0 ? max(4, round($val / $maxMonthly * 100)) : 4; @endphp
                    <div class="flex-1 flex flex-col items-center" title="Rp {{ number_format($val, 0, ',', '.') }}">
                        <div class="w-full rounded-t-lg {{ $val > 0 ? 'bg-primary/80 hover:bg-primary transition-colors' : 'bg-gray-100' }}" style="height: {{ $h }}%"></div>
                        <span class="text-xs text-gray-400 mt-2">{{ $months[$m-1] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent Activity (real) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-secondary text-lg mb-4">Aktivitas Terbaru</h3>
            <div class="space-y-4">
                @forelse(\App\Models\Booking::with('user')->latest()->limit(5)->get() as $b)
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-sky-500 rounded-full mt-2 flex-shrink-0"></div>
                        <div>
                            <p class="text-sm text-secondary">Booking {{ $b->booking_code }} - {{ $b->status }}</p>
                            <p class="text-xs text-gray-400">{{ $b->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Rentals Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-secondary text-lg">Booking Terbaru</h3>
            <a href="{{ route('bookings.index') }}" class="text-sm text-primary font-semibold hover:underline">Lihat Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Kode</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Pelanggan</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Unit</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentBookings as $b)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-semibold text-primary"><a href="{{ route('bookings.show', $b) }}">{{ $b->booking_code }}</a></td>
                            <td class="px-6 py-4 text-sm text-secondary font-medium">{{ $b->user?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $b->vehicle?->name ?? $b->item?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $b->start_date?->format('d M Y') }} - {{ $b->end_date?->format('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold px-3 py-1.5 rounded-lg {{ $b->status === 'ongoing' ? 'bg-green-50 text-green-700' : ($b->status === 'completed' ? 'bg-gray-100 text-gray-600' : 'bg-amber-50 text-amber-700') }}">{{ ucfirst($b->status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-secondary">Rp {{ number_format($b->final_price, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">Belum ada booking.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@elseif($role === 'driver' || $role === 'staff')
    @php
        $myDriver = \App\Models\Driver::where('user_id', auth()->id())->first();
        $myActive = $myDriver ? \App\Models\Booking::where('driver_id', $myDriver->id)->where('status', 'ongoing')->count() : 0;
        $myDone = $myDriver ? \App\Models\Booking::where('driver_id', $myDriver->id)->where('status', 'completed')->count() : 0;
        $myActiveBooking = $myDriver ? \App\Models\Booking::where('driver_id', $myDriver->id)->whereIn('status', ['confirmed', 'ongoing'])->with(['vehicle', 'user'])->latest()->first() : null;
    @endphp
    {{-- Driver Dashboard --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
            <h3 class="text-2xl font-bold text-secondary">{{ $myActive }}</h3>
            <p class="text-gray-500 text-sm">Trip Aktif</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-2xl font-bold text-secondary">{{ $myDone }}</h3>
            <p class="text-gray-500 text-sm">Trip Selesai</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center mb-4">
                <i class="fas fa-calendar-check text-accent"></i>
            </div>
            <h3 class="text-2xl font-bold text-secondary">{{ $myActive + $myDone }}</h3>
            <p class="text-gray-500 text-sm">Total Penugasan</p>
        </div>
    </div>

    {{-- Active Trip --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-secondary text-lg mb-4">Trip Aktif</h3>
        @if($myActiveBooking)
        <div class="bg-gradient-to-r from-primary/5 to-blue-50 rounded-xl p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                <div>
                    <p class="text-lg font-bold text-secondary">Booking {{ $myActiveBooking->booking_code }} - {{ $myActiveBooking->vehicle?->name ?? '-' }}</p>
                    <p class="text-gray-500 mt-1">Pelanggan: {{ $myActiveBooking->user?->name ?? '-' }}</p>
                    <p class="text-gray-500">{{ $myActiveBooking->start_date?->format('d M Y') }} - {{ $myActiveBooking->end_date?->format('d M Y') }}</p>
                </div>
                <a href="{{ route('bookings.show', $myActiveBooking) }}" class="mt-4 sm:mt-0 bg-primary hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors">
                    Lihat Booking
                </a>
            </div>
        </div>
        @else
        <p class="text-sm text-gray-400">Tidak ada trip aktif saat ini.</p>
        @endif
    </div>
@else
    {{-- Default Dashboard for other roles --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="text-center py-8">
            <div class="w-16 h-16 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-info-circle text-2xl text-sky-600"></i>
            </div>
            <h3 class="text-lg font-bold text-navy-800 mb-2">Dashboard tidak tersedia</h3>
            <p class="text-gray-500">Silakan gunakan menu di sidebar untuk navigasi.</p>
        </div>
    </div>
@endif
@endsection
