@extends('layouts.dashboard')
@section('page-title', 'Edit ' . ucfirst($type) . ' - ' . $item->name)

@section('content')
<div class="max-w-3xl">
    <a href="{{ route(($prefix ?? 'superadmin') . '.elektronik.type', $type) }}" class="text-sky-600 text-sm mb-4 inline-flex items-center hover:text-sky-700 transition"><i class="fas fa-arrow-left mr-1.5"></i> Kembali ke Daftar {{ ucfirst($type) }}</a>

    <div class="glass-card rounded-2xl p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 {{ $type == 'kamera' ? 'bg-violet-100' : ($type == 'tenda' ? 'bg-emerald-100' : 'bg-blue-100') }} rounded-xl flex items-center justify-center">
                @if($type == 'kamera') <i class="fas fa-camera text-violet-500"></i>
                @elseif($type == 'tenda') <i class="fas fa-campground text-emerald-500"></i>
                @else <i class="fas fa-mobile-alt text-blue-500"></i>
                @endif
            </div>
            <div>
                <h2 class="text-lg font-bold text-navy-800">Edit {{ ucfirst($type) }}</h2>
                <p class="text-[12px] text-gray-400">{{ $item->name }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-5 text-[13px]">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route(($prefix ?? 'superadmin') . '.elektronik.update', [$type, $item->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Nama Barang *</label>
                    <input type="text" name="name" value="{{ old('name', $item->name) }}" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Kategori *</label>
                    <select name="category_id" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ old('category_id', $item->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Merek</label>
                    <input type="text" name="brand" value="{{ old('brand', $item->brand) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>

                @if($type == 'kamera')
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Model Kamera</label>
                    <input type="text" name="camera_model" value="{{ old('camera_model', $item->camera_model) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Ukuran Sensor</label>
                    <input type="text" name="sensor_size" value="{{ old('sensor_size', $item->sensor_size) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Lens Included</label>
                    <input type="text" name="lens_included" value="{{ old('lens_included', $item->lens_included) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Aksesoris (koma-separated)</label>
                    <input type="text" name="accessories" value="{{ old('accessories', is_array($item->accessories) ? implode(', ', $item->accessories) : $item->accessories) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                @endif

                @if($type == 'hp')
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Model HP</label>
                    <input type="text" name="phone_model" value="{{ old('phone_model', $item->phone_model) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Kapasitas Storage</label>
                    <input type="text" name="storage_capacity" value="{{ old('storage_capacity', $item->storage_capacity) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">RAM</label>
                    <input type="text" name="ram" value="{{ old('ram', $item->ram) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Warna</label>
                    <input type="text" name="color" value="{{ old('color', $item->color) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                @endif

                @if($type == 'tenda')
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Model Tenda</label>
                    <input type="text" name="equipment_model" value="{{ old('equipment_model', $item->equipment_model) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Jenis Tenda</label>
                    <input type="text" name="tent_type" value="{{ old('tent_type', $item->type) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Kapasitas (Orang)</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $item->capacity) }}" min="1" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Berat</label>
                    <input type="text" name="weight" value="{{ old('weight', $item->weight) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Material</label>
                    <input type="text" name="material" value="{{ old('material', $item->material) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                @endif

                <div class="md:col-span-2">
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">{{ old('description', $item->description) }}</textarea>
                </div>

                <div class="md:col-span-2 border-t border-gray-100 pt-4 mt-2">
                    <p class="text-[12px] font-semibold text-navy-800 mb-3"><i class="fas fa-tag mr-1.5 text-sky-500"></i> Harga Sewa</p>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Harga per Jam (Rp)</label>
                    <input type="number" name="hourly_price" value="{{ old('hourly_price', $item->hourly_price) }}" min="0" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Harga per Hari (Rp) *</label>
                    <input type="number" name="daily_price" value="{{ old('daily_price', $item->daily_price) }}" min="0" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Harga per Minggu (Rp)</label>
                    <input type="number" name="weekly_price" value="{{ old('weekly_price', $item->weekly_price) }}" min="0" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Harga per Bulan (Rp)</label>
                    <input type="number" name="monthly_price" value="{{ old('monthly_price', $item->monthly_price) }}" min="0" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                </div>

                <div class="md:col-span-2 border-t border-gray-100 pt-4 mt-2">
                    <p class="text-[12px] font-semibold text-navy-800 mb-3"><i class="fas fa-cog mr-1.5 text-sky-500"></i> Pengaturan</p>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                        <option value="available" {{ old('status', $item->status) == 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="rented" {{ old('status', $item->status) == 'rented' ? 'selected' : '' }}>Disewa</option>
                        <option value="maintenance" {{ old('status', $item->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="reserved" {{ old('status', $item->status) == 'reserved' ? 'selected' : '' }}>Reservasi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Kondisi</label>
                    <select name="condition" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                        <option value="excellent" {{ old('condition', $item->condition) == 'excellent' ? 'selected' : '' }}>Sangat Baik</option>
                        <option value="good" {{ old('condition', $item->condition) == 'good' ? 'selected' : '' }}>Baik</option>
                        <option value="fair" {{ old('condition', $item->condition) == 'fair' ? 'selected' : '' }}>Cukup</option>
                        <option value="poor" {{ old('condition', $item->condition) == 'poor' ? 'selected' : '' }}>Kurang</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1.5">Foto Barang</label>
                    @if($item->image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                    </div>
                    @endif
                    <input type="file" name="image" accept="image/*" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-[12px] file:font-semibold file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100">
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }} class="rounded text-sky-500 focus:ring-sky-500">
                        <span class="text-[13px] text-navy-700 font-medium">Aktifkan barang ini</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-4 border-t border-gray-100">
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-xl text-[13px] font-semibold shadow-lg shadow-sky-500/25 transition">
                    <i class="fas fa-save mr-1.5"></i> Update {{ ucfirst($type) }}
                </button>
                <a href="{{ route(($prefix ?? 'superadmin') . '.elektronik.type', $type) }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-2.5 rounded-xl text-[13px] font-medium text-navy-700 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
