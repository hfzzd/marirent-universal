@extends('layouts.dashboard')
@section('page-title', 'Buat Inspeksi')
@section('content')
<div class="max-w-3xl">
    <a href="{{ route('inspections.index') }}" class="text-primary-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ route('inspections.store') }}">
            @csrf
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Booking *</label>
                        <select name="booking_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="">Pilih Booking</option>
                            @foreach($bookings as $b)
                            <option value="{{ $b->id }}" {{ ($booking?->id ?? old('booking_id')) == $b->id ? 'selected' : '' }}>{{ $b->booking_code }} - {{ $b->vehicle->name ?? ($b->category->name ?? '-') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Tipe Inspeksi *</label>
                        <select name="type" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="pre_rental">Sebelum Rental (Pre-Rental)</option>
                            <option value="post_rental">Sesudah Rental (Post-Rental)</option>
                        </select>
                    </div>
                </div>
                <p class="text-sm font-medium text-navy-700">Kondisi Kendaraan (1-10)</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach(['exterior_condition' => 'Eksterior', 'interior_condition' => 'Interior', 'engine_condition' => 'Mesin', 'tire_condition' => 'Ban', 'brake_condition' => 'Rem', 'electrical_condition' => 'Kelistrikan', 'overall_condition' => 'Keseluruhan'] as $key => $label)
                    <div>
                        <label class="block text-xs text-navy-500 mb-1">{{ $label }}</label>
                        <input type="number" name="{{ $key }}" value="{{ old($key, 7) }}" min="1" max="10" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Level Bahan Bakar (%)</label>
                        <input type="number" name="fuel_level" value="{{ old('fuel_level', 100) }}" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy-700 mb-1">Odometer (km)</label>
                        <input type="number" name="odometer_reading" value="{{ old('odometer_reading') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Catatan Kerusakan (opsional)</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500" placeholder="Deskripsikan kondisi kendaraan...">{{ old('notes') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Rekomendasi (opsional)</label>
                    <textarea name="recommendations" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">{{ old('recommendations') }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">Simpan Inspeksi</button>
                <a href="{{ route('inspections.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
