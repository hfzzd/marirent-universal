@extends('layouts.dashboard')
@section('page-title', 'Edit ' . $vehicle->name)
@section('content')
@php 
    $categories = \App\Models\Category::where('is_active', true)->get(); 
    $isMotor = $vehicle->category && $vehicle->category->slug === 'motor';
@endphp
<div class="max-w-3xl">
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ $isMotor ? route('motors.index') : route('vehicles.index') }}" class="text-sky-600 hover:text-sky-700 text-xs font-semibold flex items-center gap-1.5 transition">
            <i class="fas fa-arrow-left"></i> Kembali ke Inventaris {{ $isMotor ? 'Motor' : 'Mobil' }}
        </a>
    </div>

    <div class="glass-card rounded-2xl shadow-sm border border-sky-100/50 p-6 md:p-8">
        <div class="flex items-center gap-3 pb-5 mb-6 border-b border-gray-100">
            <div class="w-10 h-10 rounded-xl {{ $isMotor ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600' }} flex items-center justify-center text-lg shadow-sm">
                <i class="fas fa-{{ $isMotor ? 'motorcycle' : 'car' }}"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-navy-800">Edit Data {{ $vehicle->name }}</h3>
                <p class="text-xs text-gray-400">Perbarui spesifikasi, tarif, kondisi, atau ketersediaan unit.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="md:col-span-2">
                    <label class="block font-semibold text-navy-700 mb-1">Nama Kendaraan *</label>
                    <input type="text" name="name" value="{{ old('name', $vehicle->name) }}" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Kategori *</label>
                    <select name="category_id" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ old('category_id', $vehicle->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Merek</label>
                    <input type="text" name="brand" value="{{ old('brand', $vehicle->brand) }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Model / Varian</label>
                    <input type="text" name="model" value="{{ old('model', $vehicle->model) }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Tahun Pembuatan</label>
                    <input type="number" name="year" value="{{ old('year', $vehicle->year) }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Warna</label>
                    <input type="text" name="color" value="{{ old('color', $vehicle->color) }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Plat Nomor *</label>
                    <input type="text" name="license_plate" value="{{ old('license_plate', $vehicle->license_plate) }}" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none uppercase font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Kapasitas Kursi</label>
                    <input type="number" name="seats" value="{{ old('seats', $vehicle->seats) }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Transmisi</label>
                    <select name="transmission" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                        <option value="">Pilih</option>
                        <option value="automatic" {{ old('transmission', $vehicle->transmission) == 'automatic' ? 'selected' : '' }}>Matic (Automatic)</option>
                        <option value="manual" {{ old('transmission', $vehicle->transmission) == 'manual' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Bahan Bakar</label>
                    <select name="fuel_type" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                        <option value="gasoline" {{ old('fuel_type', $vehicle->fuel_type) == 'gasoline' ? 'selected' : '' }}>Bensin</option>
                        <option value="diesel" {{ old('fuel_type', $vehicle->fuel_type) == 'diesel' ? 'selected' : '' }}>Diesel</option>
                        <option value="electric" {{ old('fuel_type', $vehicle->fuel_type) == 'electric' ? 'selected' : '' }}>Listrik</option>
                        <option value="hybrid" {{ old('fuel_type', $vehicle->fuel_type) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Tarif Sewa per Hari (Rp) *</label>
                    <input type="number" name="daily_price" value="{{ old('daily_price', $vehicle->daily_price) }}" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Tarif Sewa per Minggu (Rp)</label>
                    <input type="number" name="weekly_price" value="{{ old('weekly_price', $vehicle->weekly_price) }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Tarif + Supir per Hari (Rp)</label>
                    <input type="number" name="with_driver_daily_price" value="{{ old('with_driver_daily_price', $vehicle->with_driver_daily_price) }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Status Ketersediaan</label>
                    <select name="status" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                        <option value="available" {{ old('status', $vehicle->status) == 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="rented" {{ old('status', $vehicle->status) == 'rented' ? 'selected' : '' }}>Disewa</option>
                        <option value="maintenance" {{ old('status', $vehicle->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="reserved" {{ old('status', $vehicle->status) == 'reserved' ? 'selected' : '' }}>Direservasi</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Kondisi Fisik</label>
                    <select name="condition" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                        <option value="excellent" {{ old('condition', $vehicle->condition) == 'excellent' ? 'selected' : '' }}>Sangat Baik</option>
                        <option value="good" {{ old('condition', $vehicle->condition) == 'good' ? 'selected' : '' }}>Baik</option>
                        <option value="fair" {{ old('condition', $vehicle->condition) == 'fair' ? 'selected' : '' }}>Cukup</option>
                        <option value="poor" {{ old('condition', $vehicle->condition) == 'poor' ? 'selected' : '' }}>Kurang</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold text-navy-700 mb-1">Ganti Foto (Opsional)</label>
                    <input type="file" name="image" accept="image/*" class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                    @if($vehicle->image)
                    <p class="text-[11px] text-gray-400 mt-1">Foto saat ini tersimpan: <span class="font-mono text-navy-600">{{ $vehicle->image }}</span></p>
                    @endif
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold text-navy-700 mb-1">Deskripsi & Fasilitas</label>
                    <textarea name="description" rows="3" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">{{ old('description', $vehicle->description) }}</textarea>
                </div>
                <div class="md:col-span-2 flex items-center gap-6 pt-2">
                    <label class="flex items-center text-xs text-navy-700 font-medium cursor-pointer">
                        <input type="checkbox" name="with_driver" value="1" {{ old('with_driver', $vehicle->with_driver) ? 'checked' : '' }} class="mr-2 rounded text-sky-600 focus:ring-sky-500"> Tersedia opsi dengan Driver
                    </label>
                    <label class="flex items-center text-xs text-navy-700 font-medium cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $vehicle->is_active) ? 'checked' : '' }} class="mr-2 rounded text-sky-600 focus:ring-sky-500"> Aktifkan di Katalog Publik
                    </label>
                </div>
            </div>
            <div class="mt-6 pt-5 border-t border-gray-100 flex items-center gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-sky-500/20">
                    <i class="fas fa-save mr-1.5"></i> Perbarui Data
                </button>
                <a href="{{ $isMotor ? route('motors.index') : route('vehicles.index') }}" class="bg-gray-100 hover:bg-gray-200 px-5 py-2.5 rounded-xl text-xs font-semibold text-navy-700 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
