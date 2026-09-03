@extends('layouts.dashboard')
@section('page-title', 'Lapor Kendala Kendaraan')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('dashboard') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-[13px] flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="bg-gradient-to-r from-red-500 to-orange-500 text-white rounded-2xl p-6 relative overflow-hidden mb-6">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
        <h2 class="text-xl font-bold relative z-10"><i class="fas fa-exclamation-triangle mr-2"></i>Lapor Kendala Kendaraan</h2>
        <p class="text-red-100 text-sm mt-1 relative z-10">Sampaikan masalah kendaraan yang Anda temui selama perjalanan kepada inspector.</p>
    </div>

    @if($bookings->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-car text-gray-400 text-2xl"></i>
        </div>
        <p class="text-navy-700 font-bold text-sm">Tidak ada perjalanan aktif</p>
        <p class="text-gray-400 text-xs mt-1">Anda belum memiliki booking dengan driver yang sedang berjalan.</p>
    </div>
    @else
    <form method="POST" action="{{ route('driver.report.store') }}" class="space-y-5">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center"><i class="fas fa-calendar-check text-blue-500 text-sm"></i></div>
                Pilih Perjalanan
            </h3>
            <select name="booking_id" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-400 transition bg-gray-50/50">
                <option value="">Pilih booking yang sedang berjalan</option>
                @foreach($bookings as $b)
                <option value="{{ $b->id }}" {{ old('booking_id') == $b->id ? 'selected' : '' }}>
                    {{ $b->booking_code }} — {{ $b->vehicle?->name ?? '-' }} ({{ $b->vehicle?->license_plate ?? '-' }})
                </option>
                @endforeach
            </select>
            @error('booking_id')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center"><i class="fas fa-tools text-red-500 text-sm"></i></div>
                Deskripsi Kendala
            </h3>
            <textarea name="problem_description" rows="4" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-400 transition bg-gray-50/50" placeholder="Jelaskan kendala yang Anda temui, misalnya: rem bunyi, AC tidak dingin, ban kempes...">{{ old('problem_description') }}</textarea>
            @error('problem_description')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center"><i class="fas fa-flag text-amber-500 text-sm"></i></div>
                Tingkat Urgensi
            </h3>
            <div class="grid grid-cols-3 gap-3">
                @foreach([
                    ['val' => 'low', 'icon' => 'fa-info-circle', 'color' => 'green', 'label' => 'Rendah', 'desc' => 'Tidak mendesak'],
                    ['val' => 'medium', 'icon' => 'fa-exclamation-circle', 'color' => 'amber', 'label' => 'Sedang', 'desc' => 'Perlu perhatian'],
                    ['val' => 'high', 'icon' => 'fa-exclamation-triangle', 'color' => 'red', 'label' => 'Tinggi', 'desc' => 'Mendesak / berbahaya'],
                ] as $opt)
                <label class="relative cursor-pointer">
                    <input type="radio" name="urgency" value="{{ $opt['val'] }}" {{ old('urgency') === $opt['val'] ? 'checked' : '' }} class="peer sr-only" {{ $loop->first ? 'checked' : '' }}>
                    <div class="border-2 border-gray-200 rounded-xl p-3 text-center transition-all peer-checked:border-{{ $opt['color'] }}-500 peer-checked:bg-{{ $opt['color'] }}-50 hover:border-gray-300 cursor-pointer">
                        <i class="fas {{ $opt['icon'] }} text-{{ $opt['color'] }}-500 mb-1"></i>
                        <p class="text-xs font-semibold text-navy-700">{{ $opt['label'] }}</p>
                        <p class="text-[10px] text-gray-400">{{ $opt['desc'] }}</p>
                    </div>
                </label>
                @endforeach
            </div>
            @error('urgency')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-red-500 to-orange-500 text-white py-3.5 rounded-xl font-bold text-sm hover:from-red-600 hover:to-orange-600 transition shadow-lg shadow-red-500/20 flex items-center justify-center gap-2">
            <i class="fas fa-paper-plane"></i> Kirim Laporan Kendala
        </button>
    </form>
    @endif
</div>
@endsection
