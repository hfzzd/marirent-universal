@extends('layouts.dashboard')
@section('page-title', 'Edit Foto Katalog Brand')
@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.brand-catalog.index') }}" class="text-sky-600 text-[13px] mb-5 inline-flex items-center hover:text-sky-700 transition"><i class="fas fa-arrow-left mr-1.5"></i> Kembali</a>

    <div class="glass-card rounded-2xl p-6">
        <h2 class="text-lg font-bold text-navy-800 mb-1">Edit Foto Katalog</h2>
        <p class="text-[12px] text-gray-400 mb-5">Update foto katalog untuk <span class="font-semibold text-sky-600">{{ $photo->brand_name }}</span></p>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-4 text-[13px]">
            @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.brand-catalog.update', $photo) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Kategori *</label>
                    <select name="item_type" id="item_type" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all hover:border-gray-300">
                        @foreach($typeLabels as $key => $label)
                        <option value="{{ $key }}" {{ old('item_type', $photo->item_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nama Brand *</label>
                    <input type="text" name="brand_name" id="brand_name" value="{{ old('brand_name', $photo->brand_name) }}" required list="brand-list" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all hover:border-gray-300">
                    <datalist id="brand-list"></datalist>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Foto Katalog {{ $photo->photo_path ? '(Kosongkan jika tidak ingin mengganti)' : '*' }}</label>
                @if($photo->photo_path)
                <div class="mb-3">
                    <img src="{{ $photo->photo_url }}" alt="{{ $photo->brand_name }}" class="w-48 h-32 object-cover rounded-xl border border-gray-200">
                </div>
                @endif
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-sky-300 transition bg-gray-50/50">
                    <i class="fas fa-cloud-upload-alt text-sky-300 text-2xl mb-2"></i>
                    <p class="text-[12px] text-gray-400 mb-2">Klik atau seret foto baru ke sini</p>
                    <input type="file" name="photo" accept="image/*" class="w-full text-[11px] text-navy-600 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100 file:transition">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Caption</label>
                    <input type="text" name="caption" value="{{ old('caption', $photo->caption) }}" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all hover:border-gray-300" placeholder="Deskripsi singkat (opsional)">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Urutan Tampil</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $photo->sort_order) }}" min="0" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-gray-50/50 transition-all hover:border-gray-300">
                </div>
            </div>

            <div>
                <label class="inline-flex items-center gap-2.5 cursor-pointer bg-gray-50 px-4 py-2.5 rounded-xl border border-gray-200 hover:border-sky-300 transition">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $photo->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-sky-600 focus:ring-sky-500 w-4 h-4">
                    <div>
                        <span class="text-[11px] font-medium text-navy-700">Aktif</span>
                        <p class="text-[10px] text-gray-400">Tampilkan di halaman brand publik</p>
                    </div>
                </label>
            </div>

            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white px-6 py-2.5 rounded-xl text-[13px] font-bold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-2">
                    <i class="fas fa-save"></i> Update
                </button>
                <a href="{{ route('admin.brand-catalog.index') }}" class="bg-gray-100 hover:bg-gray-200 px-5 py-2.5 rounded-xl text-[13px] font-medium text-navy-700 transition">Batal</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const brandData = @json($existingBrands);
const brandList = document.getElementById('brand-list');
const typeSelect = document.getElementById('item_type');

function updateBrandList() {
    const type = typeSelect.value;
    const brands = brandData[type] || [];
    brandList.innerHTML = '';
    brands.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b;
        brandList.appendChild(opt);
    });
}

typeSelect.addEventListener('change', updateBrandList);
updateBrandList();
</script>
@endpush
@endsection