@extends('layouts.dashboard')
@section('page-title', 'Ajukan Penggantian Kendaraan')
@section('content')
<div class="max-w-2xl">
    <a href="{{ route('replacements.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="glass-card rounded-2xl p-6">
        <h2 class="text-lg font-bold text-navy-800 mb-4"><i class="fas fa-right-left text-sky-500 mr-2"></i>Ajukan Penggantian Kendaraan</h2>
        <form method="POST" action="{{ route('replacements.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Booking *</label>
                    <select name="booking_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">Pilih Booking</option>
                        @foreach($bookings as $b)
                        <option value="{{ $b->id }}" data-vehicle-id="{{ $b->vehicle_id }}">{{ $b->booking_code }} - {{ $b->vehicle?->name ?? '-' }} @if($b->status === 'ongoing')(sedang berjalan)@endif</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Unit Pengganti *</label>
                    <select name="replacement_vehicle_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <option value="">Pilih Unit Pengganti</option>
                        @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->category?->name ?? '-' }}) - Rp {{ number_format($v->daily_price,0,',','.') }}/hari</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Alasan Penggantian *</label>
                    <textarea name="reason" rows="3" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500" placeholder="Jelaskan alasan penggantian kendaraan...">{{ old('reason') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Selisih Harga (Rp)</label>
                        <input type="number" step="0.01" min="-999999999" name="price_difference" placeholder="0" value="{{ old('price_difference', 0) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500">
                        <p class="text-[11px] text-gray-400 mt-1">Positif = lebih mahal, Negatif = lebih murah</p>
                    </div>
                    <div class="flex items-center pb-1">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="mark_maintenance" value="0">
                            <input type="checkbox" name="mark_maintenance" value="1" checked class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                            <span class="text-sm font-medium text-navy-700">Unit lama &rarr; tandai maintenance</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-medium text-navy-700 mb-1.5">Foto Awal Unit (opsional)</label>
                        <input type="file" name="initial_vehicle_photo" accept="image/*" class="w-full text-sm text-navy-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-navy-700 mb-1.5">Foto Akhir Unit (opsional)</label>
                        <input type="file" name="final_vehicle_photo" accept="image/*" class="w-full text-sm text-navy-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100">
                    </div>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">Ajukan</button>
                <a href="{{ route('replacements.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
