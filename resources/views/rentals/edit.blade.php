@extends('layouts.dashboard')
@section('title', 'Edit Rental - MariRental')
@section('page-title', 'Edit Rental #' . $rental->rental_code)

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('rentals.update', $rental->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="font-bold text-navy-800 text-lg mb-6">Pilih Kendaraan</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($vehicles as $vehicle)
                    <label class="flex items-center space-x-4 p-4 border-2 rounded-xl cursor-pointer hover:border-sky-400 transition-colors {{ $rental->vehicle_id == $vehicle->id ? 'border-sky-500 bg-sky-50' : 'border-gray-200' }}">
                        <input type="radio" name="vehicle_id" value="{{ $vehicle->id }}" class="w-4 h-4 text-sky-600" {{ $rental->vehicle_id == $vehicle->id ? 'checked' : '' }} required>
                        @if($vehicle->image)
                            <img src="{{ asset('storage/' . $vehicle->image) }}" alt="{{ $vehicle->name }}" class="w-16 h-12 object-cover rounded-lg">
                        @endif
                        <div class="flex-1">
                            <p class="font-semibold text-navy-800 text-sm">{{ $vehicle->name }}</p>
                            <p class="text-xs text-gray-500">{{ $vehicle->category->name ?? 'Mobil' }} - Rp {{ number_format($vehicle->daily_price, 0, ',', '.') }}/hari</p>
                        </div>
                    </label>
                @empty
                    <p class="text-sm text-gray-500 col-span-2">Tidak ada kendaraan tersedia.</p>
                @endforelse
            </div>
            @error('vehicle_id') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6" x-data="{ withDriver: {{ $rental->with_driver ? 'true' : 'false' }} }">
            <h3 class="font-bold text-navy-800 text-lg mb-6">Detail Rental</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-medium text-navy-800 block mb-1.5">Tanggal Mulai *</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $rental->start_date->format('Y-m-d')) }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 @error('start_date') border-red-500 @enderror">
                    @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-navy-800 block mb-1.5">Tanggal Selesai *</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $rental->end_date->format('Y-m-d')) }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 @error('end_date') border-red-500 @enderror">
                    @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="flex items-center space-x-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-sky-400 transition-colors" :class="withDriver && 'border-sky-500 bg-sky-50'">
                        <input type="checkbox" x-model="withDriver" name="with_driver" value="1" class="w-4 h-4 text-sky-600 rounded">
                        <div>
                            <p class="text-sm font-medium text-navy-800">Dengan Driver</p>
                        </div>
                    </label>
                </div>

                <div x-show="withDriver" x-cloak>
                    <label class="text-sm font-medium text-navy-800 block mb-1.5">Pilih Driver</label>
                    <select name="driver_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 pr-8">
                        <option value="">Pilih Driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ $rental->driver_id == $driver->id ? 'selected' : '' }}>
                                {{ $driver->user->name ?? 'Driver #' . $driver->id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-navy-800 block mb-1.5">Lokasi Pengambilan *</label>
                    <input type="text" name="pickup_location" value="{{ old('pickup_location', $rental->pickup_location) }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20">
                </div>

                <div>
                    <label class="text-sm font-medium text-navy-800 block mb-1.5">Lokasi Pengantaran *</label>
                    <input type="text" name="dropoff_location" value="{{ old('dropoff_location', $rental->dropoff_location) }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20">
                </div>

                <div>
                    <label class="text-sm font-medium text-navy-800 block mb-1.5">Keperluan</label>
                    <input type="text" name="purpose" value="{{ old('purpose', $rental->purpose) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20">
                </div>
            </div>

            <div class="mt-6">
                <label class="text-sm font-medium text-navy-800 block mb-1.5">Catatan Tambahan</label>
                <textarea name="notes" rows="3" placeholder="Catatan untuk rental ini..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 resize-none">{{ old('notes', $rental->notes) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('rentals.show', $rental->id) }}" class="text-gray-500 hover:text-navy-800 font-medium text-sm transition-colors">Batal</a>
            <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white px-8 py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-sky-500/30">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
