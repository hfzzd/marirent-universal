@extends('layouts.dashboard')
@section('page-title', 'Katalog Foto Brand')
@section('content')
<div class="max-w-6xl">
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-4 text-[13px] flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-navy-800">Katalog Foto Brand</h2>
            <p class="text-[12px] text-gray-400 mt-0.5">Kelola foto katalog untuk brand yang ada di produk</p>
        </div>
        <a href="{{ route('admin.brand-catalog.create') }}" class="bg-gradient-to-r from-sky-500 to-sky-600 text-white px-4 py-2 rounded-xl text-[13px] font-semibold shadow-lg shadow-sky-500/25 transition-all duration-300 hover:scale-105 flex items-center gap-2">
            <i class="fas fa-plus text-[10px]"></i> Tambah Foto
        </a>
    </div>

    {{-- Filters --}}
    <div class="flex gap-2 mb-6">
        <a href="{{ route('admin.brand-catalog.index') }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ !$activeType ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300' }}">Semua</a>
        @foreach($typeLabels as $key => $label)
        <a href="{{ route('admin.brand-catalog.index', ['item_type' => $key]) }}" class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition {{ $activeType == $key ? 'bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/25' : 'bg-white text-gray-500 border border-gray-200 hover:border-sky-300' }}">{{ $label }}</a>
        @endforeach
    </div>

    {{-- Brand Groups --}}
    @php $grouped = $allBrands->groupBy('item_type'); @endphp

    @forelse($grouped as $type => $brands)
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
            <h3 class="text-[14px] font-bold text-navy-800 uppercase tracking-wider">{{ $typeLabels[$type] ?? $type }}</h3>
            <span class="text-[11px] text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ $brands->count() }} brand</span>
        </div>

        <div class="space-y-3">
            @foreach($brands as $brandData)
            @php $brandPhotos = $brandData['photos']; @endphp
            <div class="glass-card rounded-2xl overflow-hidden border border-gray-100/60" style="box-shadow: 0 2px 16px rgba(0,0,0,0.03);">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center gap-3">
                        @if($brandPhotos->count())
                        <div class="w-14 h-14 rounded-xl overflow-hidden border border-gray-200 flex-shrink-0">
                            <img src="{{ $brandPhotos->first()->photo_url }}" alt="{{ $brandData['brand'] }}" class="w-full h-full object-cover">
                        </div>
                        @else
                        <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center border border-dashed border-gray-300 flex-shrink-0">
                            <i class="fas fa-image text-gray-300"></i>
                        </div>
                        @endif
                        <div>
                            <h4 class="font-bold text-navy-800 text-[13px]">{{ $brandData['brand'] }}</h4>
                            <p class="text-[11px] text-gray-400">{{ $brandPhotos->count() }} foto katalog</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.brand-catalog.create', ['brand' => $brandData['brand'], 'item_type' => $type]) }}" class="bg-sky-50 hover:bg-sky-100 text-sky-600 px-3 py-1.5 rounded-lg text-[11px] font-semibold transition flex items-center gap-1">
                            <i class="fas fa-plus text-[9px]"></i> Tambah Foto
                        </a>
                        <form method="POST" action="{{ route('admin.brand-catalog.destroy-brand') }}" class="inline" onsubmit="return confirm('Hapus seluruh katalog brand {{ $brandData['brand'] }} beserta semua fotonya?')">
                            @csrf @method('DELETE')
                            <input type="hidden" name="brand_name" value="{{ $brandData['brand'] }}">
                            <input type="hidden" name="item_type" value="{{ $type }}">
                            <button class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-lg text-[11px] font-semibold transition flex items-center gap-1" title="Hapus katalog brand">
                                <i class="fas fa-trash text-[9px]"></i> Hapus Katalog
                            </button>
                        </form>
                    </div>
                </div>

                @if($brandPhotos->count())
                <div class="border-t border-gray-100 px-4 py-3 bg-gray-50/50">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        @foreach($brandPhotos as $photo)
                        <div class="relative group rounded-xl overflow-hidden border border-gray-200 bg-white">
                            <img src="{{ $photo->photo_url }}" alt="{{ $photo->brand_name }}" class="w-full h-24 object-cover">
                            <div class="absolute top-1 right-1">
                                @if($photo->is_active)
                                <span class="bg-emerald-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full">Aktif</span>
                                @else
                                <span class="bg-gray-400 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full">Off</span>
                                @endif
                            </div>
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                <form method="POST" action="{{ route('admin.brand-catalog.toggle', $photo) }}" class="inline">@csrf
                                    <button class="w-8 h-8 rounded-lg bg-white/90 text-gray-600 flex items-center justify-center text-[10px] hover:bg-white transition" title="{{ $photo->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="fas {{ $photo->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.brand-catalog.edit', $photo) }}" class="w-8 h-8 rounded-lg bg-white/90 text-sky-600 flex items-center justify-center text-[10px] hover:bg-white transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.brand-catalog.destroy', $photo) }}" class="inline" onsubmit="return confirm('Hapus foto ini?')">@csrf @method('DELETE')
                                    <button class="w-8 h-8 rounded-lg bg-white/90 text-red-600 flex items-center justify-center text-[10px] hover:bg-white transition" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            @if($photo->caption)
                            <div class="p-2">
                                <p class="text-[10px] text-gray-400 line-clamp-1">{{ $photo->caption }}</p>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="text-center py-16">
        <div class="w-20 h-20 bg-gradient-to-br from-sky-50 to-blue-50 rounded-3xl flex items-center justify-center mx-auto mb-5 border border-sky-100">
            <i class="fas fa-images text-sky-300 text-3xl"></i>
        </div>
        <h3 class="text-lg font-bold text-navy-800 mb-2">Belum ada brand di database</h3>
        <p class="text-gray-400 text-[13px]">Tambahkan produk terlebih dahulu untuk melihat brand</p>
    </div>
    @endforelse
</div>
@endsection