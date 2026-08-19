@extends('layouts.dashboard')
@section('page-title', isset($vehicle) ? 'Edit Kendaraan' : 'Tambah Kendaraan')
@section('content')
<div class="max-w-3xl">
    <a href="{{ route('vehicles.index') }}" class="text-primary-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ isset($vehicle) ? route('vehicles.update', $vehicle) : route('vehicles.store') }}" enctype="multipart/form-data">
            @csrf
            @if(isset($vehicle)) @method('PUT') @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-navy-700 mb-1">Nama Kendaraan *</label>
                    <input type="text" name="name" value="{{ old('name', $vehicle->name ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Kategori *</label>
                    <select name="category_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ old('category_id', $vehicle->category_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Merek</label>
                    <input type="text" name="brand" value="{{ old('brand', $vehicle->brand ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Model</label>
                    <input type="text" name="model" value="{{ old('model', $vehicle->model ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Tahun</label>
                    <input type="number" name="year" value="{{ old('year', $vehicle->year ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Warna</label>
                    <input type="text" name="color" value="{{ old('color', $vehicle->color ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Plat Nomor *</label>
                    <input type="text" name="license_plate" value="{{ old('license_plate', $vehicle->license_plate ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Kursi</label>
                    <input type="number" name="seats" value="{{ old('seats', $vehicle->seats ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Transmisi</label>
                    <select name="transmission" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="">Pilih</option>
                        <option value="automatic" {{ old('transmission', $vehicle->transmission ?? '') == 'automatic' ? 'selected' : '' }}>Automatic</option>
                        <option value="manual" {{ old('transmission', $vehicle->transmission ?? '') == 'manual' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Bahan Bakar</label>
                    <select name="fuel_type" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="">Pilih</option>
                        <option value="gasoline" {{ old('fuel_type', $vehicle->fuel_type ?? '') == 'gasoline' ? 'selected' : '' }}>Bensin</option>
                        <option value="diesel" {{ old('fuel_type', $vehicle->fuel_type ?? '') == 'diesel' ? 'selected' : '' }}>Diesel</option>
                        <option value="electric" {{ old('fuel_type', $vehicle->fuel_type ?? '') == 'electric' ? 'selected' : '' }}>Listrik</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-navy-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">{{ old('description', $vehicle->description ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Harga per Jam (Rp)</label>
                    <input type="number" name="hourly_price" value="{{ old('hourly_price', $vehicle->hourly_price ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Harga per Hari (Rp) *</label>
                    <input type="number" name="daily_price" value="{{ old('daily_price', $vehicle->daily_price ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Harga per Minggu (Rp)</label>
                    <input type="number" name="weekly_price" value="{{ old('weekly_price', $vehicle->weekly_price ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Harga per Bulan (Rp)</label>
                    <input type="number" name="monthly_price" value="{{ old('monthly_price', $vehicle->monthly_price ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Harga + Driver/Hari (Rp)</label>
                    <input type="number" name="with_driver_daily_price" value="{{ old('with_driver_daily_price', $vehicle->with_driver_daily_price ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="available" {{ old('status', $vehicle->status ?? '') == 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="maintenance" {{ old('status', $vehicle->status ?? '') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-navy-700 mb-1">Kondisi</label>
                    <select name="condition" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="excellent" {{ old('condition', $vehicle->condition ?? 'good') == 'excellent' ? 'selected' : '' }}>Sangat Baik</option>
                        <option value="good" {{ old('condition', $vehicle->condition ?? 'good') == 'good' ? 'selected' : '' }}>Baik</option>
                        <option value="fair" {{ old('condition', $vehicle->condition ?? '') == 'fair' ? 'selected' : '' }}>Cukup</option>
                        <option value="poor" {{ old('condition', $vehicle->condition ?? '') == 'poor' ? 'selected' : '' }}>Buruk</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-navy-700 mb-1">Foto</label>
                    <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="md:col-span-2 flex items-center gap-6">
                    <label class="flex items-center text-sm">
                        <input type="checkbox" name="with_driver" value="1" {{ old('with_driver', $vehicle->with_driver ?? false) ? 'checked' : '' }} class="mr-2 rounded text-primary-600"> Sedia dengan Driver
                    </label>
                    <label class="flex items-center text-sm">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $vehicle->is_active ?? true) ? 'checked' : '' }} class="mr-2 rounded text-primary-600"> Aktif
                    </label>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">{{ isset($vehicle) ? 'Update' : 'Simpan' }}</button>
                <a href="{{ route('vehicles.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium text-navy-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
