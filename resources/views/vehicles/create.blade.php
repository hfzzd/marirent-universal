@extends('layouts.dashboard')
@section('page-title', (isset($selectedType) && $selectedType === 'motor' ? 'Tambah Motor' : 'Tambah Mobil'))
@section('content')
<div class="max-w-3xl">
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ (isset($selectedType) && $selectedType === 'motor') ? route('motors.index') : route('vehicles.index') }}" class="text-sky-600 hover:text-sky-700 text-xs font-semibold flex items-center gap-1.5 transition">
            <i class="fas fa-arrow-left"></i> Kembali ke Inventaris
        </a>
    </div>

    <div class="glass-card rounded-2xl shadow-sm border border-sky-100/50 p-6 md:p-8">
        <div class="flex items-center gap-3 pb-5 mb-6 border-b border-gray-100">
            <div class="w-10 h-10 rounded-xl {{ (isset($selectedType) && $selectedType === 'motor') ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600' }} flex items-center justify-center text-lg shadow-sm">
                <i class="fas fa-{{ (isset($selectedType) && $selectedType === 'motor') ? 'motorcycle' : 'car' }}"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-navy-800">{{ (isset($selectedType) && $selectedType === 'motor') ? 'Tambah Unit Motor Baru' : 'Tambah Unit Mobil Baru' }}</h3>
                <p class="text-xs text-gray-400">Lengkapi formulir di bawah untuk menambahkan unit ke inventaris rental.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('vehicles.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="md:col-span-2">
                    <label class="block font-semibold text-navy-700 mb-1">Nama Kendaraan *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Toyota Avanza 1.5G / Honda Vario 160" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Kategori *</label>
                    <select name="category_id" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ (old('category_id') == $c->id || (!old('category_id') && isset($defaultCategory) && $defaultCategory->id == $c->id)) ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Merek</label>
                    <input type="text" name="brand" value="{{ old('brand') }}" placeholder="Contoh: Toyota / Honda / Yamaha" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Model / Varian</label>
                    <input type="text" name="model" value="{{ old('model') }}" placeholder="Contoh: Grand New / CBS-ISS" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Tahun Pembuatan</label>
                    <input type="number" name="year" value="{{ old('year', date('Y')) }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Warna</label>
                    <input type="text" name="color" value="{{ old('color') }}" placeholder="Hitam / Putih / Abu-abu" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Plat Nomor *</label>
                    <input type="text" name="license_plate" value="{{ old('license_plate') }}" placeholder="B 1234 XYZ" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none uppercase font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Kapasitas Kursi</label>
                    <input type="number" name="seats" value="{{ old('seats', (isset($selectedType) && $selectedType === 'motor') ? 2 : 5) }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Transmisi</label>
                    <select name="transmission" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                        <option value="">Pilih</option>
                        <option value="automatic" {{ old('transmission') == 'automatic' ? 'selected' : '' }}>Matic (Automatic)</option>
                        <option value="manual" {{ old('transmission') == 'manual' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Bahan Bakar</label>
                    <select name="fuel_type" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                        <option value="gasoline" {{ old('fuel_type', 'gasoline') == 'gasoline' ? 'selected' : '' }}>Bensin</option>
                        <option value="diesel" {{ old('fuel_type') == 'diesel' ? 'selected' : '' }}>Diesel</option>
                        <option value="electric" {{ old('fuel_type') == 'electric' ? 'selected' : '' }}>Listrik</option>
                        <option value="hybrid" {{ old('fuel_type') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Tarif Sewa per Hari (Rp) *</label>
                    <input type="number" name="daily_price" value="{{ old('daily_price') }}" placeholder="Contoh: 350000" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Tarif Sewa per Minggu (Rp)</label>
                    <input type="number" name="weekly_price" value="{{ old('weekly_price') }}" placeholder="Opsional" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Tarif + Supir per Hari (Rp)</label>
                    <input type="number" name="with_driver_daily_price" value="{{ old('with_driver_daily_price') }}" placeholder="Opsional untuk sewa mobil" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Status Awal</label>
                    <select name="status" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                        <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-navy-700 mb-1">Kondisi Fisik</label>
                    <select name="condition" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white">
                        <option value="excellent" {{ old('condition') == 'excellent' ? 'selected' : '' }}>Sangat Baik</option>
                        <option value="good" {{ old('condition', 'good') == 'good' ? 'selected' : '' }}>Baik</option>
                        <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Cukup</option>
                        <option value="poor" {{ old('condition') == 'poor' ? 'selected' : '' }}>Kurang</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold text-navy-700 mb-1">Foto Unit</label>
                    <input type="file" name="image" accept="image/*" class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold text-navy-700 mb-1">Deskripsi & Fasilitas</label>
                    <textarea name="description" rows="3" placeholder="Informasi tambahan terkait kendaraan..." class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">{{ old('description') }}</textarea>
                </div>
                <div class="md:col-span-2 flex items-center gap-6 pt-2">
                    <label class="flex items-center text-xs text-navy-700 font-medium cursor-pointer">
                        <input type="checkbox" name="with_driver" value="1" {{ old('with_driver') ? 'checked' : '' }} class="mr-2 rounded text-sky-600 focus:ring-sky-500"> Tersedia opsi dengan Driver
                    </label>
                    <label class="flex items-center text-xs text-navy-700 font-medium cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="mr-2 rounded text-sky-600 focus:ring-sky-500"> Aktifkan di Katalog Publik
                    </label>
                </div>
            </div>
            <div class="mt-6 pt-5 border-t border-gray-100 flex items-center gap-3">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-sky-500/20">
                    <i class="fas fa-save mr-1.5"></i> Simpan Unit
                </button>
                <a href="{{ (isset($selectedType) && $selectedType === 'motor') ? route('motors.index') : route('vehicles.index') }}" class="bg-gray-100 hover:bg-gray-200 px-5 py-2.5 rounded-xl text-xs font-semibold text-navy-700 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
