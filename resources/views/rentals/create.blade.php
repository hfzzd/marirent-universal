@extends('layouts.dashboard')
@section('title', 'Buat Rental - MariRental')
@section('page-title', 'Buat Rental Baru')

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('rentals.store') }}" method="POST">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="font-bold text-secondary text-lg mb-6">Pilih Kendaraan</h3>

            {{-- Category Filter --}}
            <div class="flex flex-wrap gap-2 mb-4">
                <button type="button" class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-medium">Semua</button>
                <button type="button" class="px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-xl text-sm font-medium transition-colors">Mobil</button>
                <button type="button" class="px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-xl text-sm font-medium transition-colors">Motor</button>
                <button type="button" class="px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-xl text-sm font-medium transition-colors">HP</button>
                <button type="button" class="px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-xl text-sm font-medium transition-colors">Kamera</button>
            </div>

            {{-- Vehicle Selection --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @php
                    $vehicleOptions = [
                        ['id' => 1, 'name' => 'Toyota Avanza', 'category' => 'Mobil', 'price' => 350000, 'image' => 'https://placehold.co/80x60/2563eb/ffffff?text=Avanza'],
                        ['id' => 2, 'name' => 'Honda Brio', 'category' => 'Mobil', 'price' => 300000, 'image' => 'https://placehold.co/80x60/2563eb/ffffff?text=Brio'],
                        ['id' => 3, 'name' => 'Honda Vario 160', 'category' => 'Motor', 'price' => 100000, 'image' => 'https://placehold.co/80x60/10b981/ffffff?text=Vario'],
                        ['id' => 4, 'name' => 'Yamaha NMAX', 'category' => 'Motor', 'price' => 120000, 'image' => 'https://placehold.co/80x60/10b981/ffffff?text=NMAX'],
                    ];
                @endphp

                @foreach($vehicleOptions as $vehicle)
                    <label class="flex items-center space-x-4 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                        <input type="radio" name="vehicle_id" value="{{ $vehicle['id'] }}" class="w-4 h-4 text-primary" required>
                        <img src="{{ $vehicle['image'] }}" alt="{{ $vehicle['name'] }}" class="w-16 h-12 object-cover rounded-lg">
                        <div class="flex-1">
                            <p class="font-semibold text-secondary text-sm">{{ $vehicle['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $vehicle['category'] }} - Rp {{ number_format($vehicle['price'], 0, ',', '.') }}/hari</p>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('vehicle_id') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6" x-data="{ withDriver: false }">
            <h3 class="font-bold text-secondary text-lg mb-6">Detail Rental</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Start Date --}}
                <div>
                    <label class="text-sm font-medium text-secondary block mb-1.5">Tanggal Mulai *</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 @error('start_date') border-red-500 @enderror">
                    @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- End Date --}}
                <div>
                    <label class="text-sm font-medium text-secondary block mb-1.5">Tanggal Selesai *</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 @error('end_date') border-red-500 @enderror">
                    @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- With Driver --}}
                <div>
                    <label class="flex items-center space-x-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary transition-colors" :class="withDriver && 'border-primary bg-primary/5'">
                        <input type="checkbox" x-model="withDriver" name="with_driver" value="1" class="w-4 h-4 text-primary rounded">
                        <div>
                            <p class="text-sm font-medium text-secondary">Dengan Driver</p>
                            <p class="text-xs text-gray-500">Biaya tambahan Rp 150.000/hari</p>
                        </div>
                    </label>
                </div>

                {{-- Driver Selection --}}
                <div x-show="withDriver" x-cloak>
                    <label class="text-sm font-medium text-secondary block mb-1.5">Pilih Driver</label>
                    <select name="driver_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 pr-8">
                        <option value="">Pilih Driver</option>
                        <option value="1">Andi (B - Rating 4.9)</option>
                        <option value="2">Budi (A - Rating 4.8)</option>
                        <option value="3">Candra (A - Rating 4.7)</option>
                    </select>
                </div>

                {{-- Pickup Location --}}
                <div>
                    <label class="text-sm font-medium text-secondary block mb-1.5">Lokasi Pengambilan *</label>
                    <select name="pickup_location" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 pr-8">
                        <option value="kantor">Kantor Pusat MariRental</option>
                        <option value="bandara">Bandara Soekarno-Hatta</option>
                        <option value="stasiun">Stasiun Gambir</option>
                        <option value="hotel">Hotel (Delivery)</option>
                    </select>
                </div>

                {{-- Purpose --}}
                <div>
                    <label class="text-sm font-medium text-secondary block mb-1.5">Keperluan</label>
                    <select name="purpose" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 pr-8">
                        <option value="personal">Pribadi</option>
                        <option value="business">Bisnis</option>
                        <option value="tourism">Wisata</option>
                        <option value="event">Acara</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
            </div>

            {{-- Notes --}}
            <div class="mt-6">
                <label class="text-sm font-medium text-secondary block mb-1.5">Catatan Tambahan</label>
                <textarea name="notes" rows="3" placeholder="Catatan untuk rental ini..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 resize-none">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('rentals.index') }}" class="text-gray-500 hover:text-secondary font-medium text-sm transition-colors">Batal</a>
            <button type="submit" class="bg-primary hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-primary/30">
                Buat Rental
            </button>
        </div>
    </form>
</div>
@endsection
