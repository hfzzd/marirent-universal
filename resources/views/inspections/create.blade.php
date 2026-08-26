@extends('layouts.dashboard')
@section('page-title', 'Buat Inspeksi Baru')
@section('content')
<div class="max-w-4xl">
    <a href="{{ route('inspections.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>

    <form method="POST" action="{{ route('inspections.store') }}" x-data="inspectionForm()" class="space-y-6">
        @csrf

        {{-- Header --}}
        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl p-6 relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="absolute -right-2 -bottom-4 w-16 h-16 bg-white/5 rounded-full"></div>
            <h2 class="text-xl font-bold relative z-10"><i class="fas fa-clipboard-check mr-2"></i>Inspeksi Unit</h2>
            <p class="text-emerald-100 text-sm mt-1 relative z-10">Isi form inspeksi untuk kendaraan, elektronik, atau alat camping</p>
        </div>

        {{-- Step Indicators --}}
        <div class="flex items-center gap-2 text-xs font-medium">
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700">
                <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">1</span> Booking
            </div>
            <div class="w-8 h-px bg-gray-200"></div>
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full" :class="selectedBookingId ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold" :class="selectedBookingId ? 'bg-emerald-500 text-white' : 'bg-gray-300 text-white'">2</span> Target
            </div>
            <div class="w-8 h-px bg-gray-200"></div>
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full" :class="selectedItemId ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold" :class="selectedItemId ? 'bg-emerald-500 text-white' : 'bg-gray-300 text-white'">3</span> Kondisi
            </div>
            <div class="w-8 h-px bg-gray-200"></div>
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full" :class="damageItems.filter(d=>d).length || completenessItems.filter(c=>c).length ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold" :class="damageItems.filter(d=>d).length || completenessItems.filter(c=>c).length ? 'bg-emerald-500 text-white' : 'bg-gray-300 text-white'">4</span> Temuan
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">

                {{-- Booking & Type --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center"><i class="fas fa-calendar-check text-emerald-500 text-sm"></i></div>
                        Data Booking
                    </h3>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-navy-600 mb-1.5">Booking *</label>
                            <select name="booking_id" x-model="selectedBookingId" @change="detectScope()" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 transition bg-gray-50/50">
                                <option value="">Pilih Booking</option>
                                @foreach($bookings as $b)
                                <option value="{{ $b->id }}"
                                    data-vehicle="{{ $b->vehicle_id }}"
                                    data-item-type="{{ $b->item_type }}"
                                    data-item-id="{{ $b->item_id }}"
                                    data-scope="{{ $b->vehicle_id ? 'kendaraan' : ($b->item_type === 'App\Models\Phone' || $b->item_type === 'App\Models\Camera' ? 'elektronik' : 'camping') }}">
                                    {{ $b->booking_code }} - {{ $b->vehicle?->name ?? $b->bookingItems->first()?->item_type ?? ($b->category?->name ?? '-') }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-navy-600 mb-1.5">Tipe Inspeksi *</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="type" value="pre_rental" class="peer sr-only" checked>
                                    <div class="border-2 border-gray-200 rounded-xl p-3 text-center transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:border-gray-300 cursor-pointer">
                                        <i class="fas fa-clipboard-list text-emerald-500 mb-1"></i>
                                        <p class="text-xs font-semibold text-navy-700">Pre-Rental</p>
                                        <p class="text-[10px] text-gray-400">Sebelum sewa</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="type" value="post_rental" class="peer sr-only">
                                    <div class="border-2 border-gray-200 rounded-xl p-3 text-center transition-all peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:border-gray-300 cursor-pointer">
                                        <i class="fas fa-clipboard-check text-amber-500 mb-1"></i>
                                        <p class="text-xs font-semibold text-navy-700">Post-Rental</p>
                                        <p class="text-[10px] text-gray-400">Setelah sewa</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Scope & Item Selection --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center"><i class="fas fa-crosshairs text-emerald-500 text-sm"></i></div>
                        Target Inspeksi
                    </h3>

                    {{-- Scope Selector - Card Style --}}
                    <div class="mb-5">
                        <label class="block text-xs font-medium text-navy-600 mb-2">Scope Inspeksi *</label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach([
                                ['val' => 'kendaraan', 'icon' => 'fa-car', 'color' => 'blue', 'label' => 'Kendaraan', 'desc' => 'Mobil & Motor'],
                                ['val' => 'elektronik', 'icon' => 'fa-mobile-alt', 'color' => 'purple', 'label' => 'Elektronik', 'desc' => 'HP & Kamera'],
                                ['val' => 'camping', 'icon' => 'fa-campground', 'color' => 'emerald', 'label' => 'Alat Camping', 'desc' => 'Tenda & Alat'],
                            ] as $opt)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="scope" value="{{ $opt['val'] }}" x-model="scope" @change="updateItemList()" class="peer sr-only">
                                <div class="border-2 border-gray-200 rounded-xl p-4 text-center transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:border-gray-300 cursor-pointer group">
                                    <div class="w-12 h-12 mx-auto mb-2 bg-{{ $opt['color'] }}-100 text-{{ $opt['color'] }}-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                                        <i class="fas {{ $opt['icon'] }} text-lg"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-navy-700">{{ $opt['label'] }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $opt['desc'] }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Item Selector --}}
                    <div>
                        <label class="block text-xs font-medium text-navy-600 mb-1.5">Item yang Diinspeksi *</label>
                        <select name="inspection_item_id" x-model="selectedItemId" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 transition bg-gray-50/50">
                            <option value="">Pilih Item</option>
                            <template x-if="scope === 'kendaraan'">
                                <template x-for="v in vehicles" :key="v.id">
                                    <option :value="v.id" x-text="v.name + ' (' + (v.category?.name || '-') + ')'"></option>
                                </template>
                            </template>
                            <template x-if="scope === 'elektronik'">
                                <template x-for="e in [...phones, ...cameras]" :key="e.id">
                                    <option :value="e.id" x-text="e.name + ' (' + e.brand + ')'"></option>
                                </template>
                            </template>
                            <template x-if="scope === 'camping'">
                                <template x-for="c in equipments" :key="c.id">
                                    <option :value="c.id" x-text="c.name + ' (' + c.brand + ')'"></option>
                                </template>
                            </template>
                        </select>
                    </div>

                    <div class="mt-4">
                        <label class="block text-xs font-medium text-navy-600 mb-1.5">Lama Pemakaian (jam)</label>
                        <input type="number" name="usage_duration_hours" min="0" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 transition bg-gray-50/50" placeholder="Contoh: 48 (opsional)">
                    </div>
                </div>

                {{-- Condition Assessment - Vehicle Only --}}
                <div class="bg-white rounded-2xl shadow-sm p-6" x-show="scope === 'kendaraan'" x-transition>
                    <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center"><i class="fas fa-gauge-high text-emerald-500 text-sm"></i></div>
                        Kondisi Kendaraan
                    </h3>
                    <div class="grid sm:grid-cols-2 gap-4">
                        @foreach([
                            'exterior_condition' => ['Eksterior', 'fa-car', 'blue'],
                            'interior_condition' => ['Interior', 'fa-couch', 'purple'],
                            'engine_condition' => ['Mesin', 'fa-cog', 'red'],
                            'tire_condition' => ['Ban', 'fa-circle', 'gray'],
                            'brake_condition' => ['Rem', 'fa-hand-paper', 'amber'],
                            'electrical_condition' => ['Kelistrikan', 'fa-bolt', 'yellow'],
                        ] as $field => [$label, $icon, $color])
                        <div class="border border-gray-100 rounded-xl p-3 hover:border-emerald-200 transition">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-7 h-7 bg-{{ $color }}-100 text-{{ $color }}-600 rounded-lg flex items-center justify-center"><i class="fas {{ $icon }} text-xs"></i></div>
                                <label class="text-xs font-semibold text-navy-700">{{ $label }}</label>
                            </div>
                            <input type="number" name="{{ $field }}" min="1" max="10" value="7" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 transition text-center font-bold text-navy-800">
                        </div>
                        @endforeach
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4 mt-4">
                        <div class="border border-gray-100 rounded-xl p-3">
                            <label class="text-xs font-semibold text-navy-700 flex items-center gap-1.5 mb-2">
                                <i class="fas fa-gas-pump text-gray-400"></i> Level Bensin (%)
                            </label>
                            <div class="relative">
                                <input type="number" name="fuel_level" min="0" max="100" value="100" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 transition text-center font-bold text-navy-800">
                                <div class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">%</div>
                            </div>
                        </div>
                        <div class="border border-gray-100 rounded-xl p-3">
                            <label class="text-xs font-semibold text-navy-700 flex items-center gap-1.5 mb-2">
                                <i class="fas fa-tachometer-alt text-gray-400"></i> Odometer (km)
                            </label>
                            <input type="number" name="odometer_reading" min="0" step="0.1" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 transition text-center font-bold text-navy-800" placeholder="Opsional">
                        </div>
                    </div>
                </div>

                {{-- Overall Condition (always shown) --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center"><i class="fas fa-star text-emerald-500 text-sm"></i></div>
                        Kondisi Keseluruhan
                    </h3>
                    <div class="border border-gray-100 rounded-xl p-4">
                        <label class="block text-xs font-medium text-navy-600 mb-2">Skor Kondisi (1-10) *</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="overall_condition" min="1" max="10" value="7" required class="w-20 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 transition text-center font-bold text-lg text-navy-800">
                            <div class="flex-1">
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300" :class="overallColor()" :style="'width:' + (overall_condition * 10) + '%'"></div>
                                </div>
                                <p class="text-[11px] mt-1" :class="overallTextColor()" x-text="overallLabel()"></p>
                            </div>
                        </div>
                        <input type="hidden" x-model="overall_condition" name="overall_condition_display">
                    </div>
                </div>

                {{-- Damage Items with Presets --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center"><i class="fas fa-triangle-exclamation text-red-500 text-sm"></i></div>
                        Temuan Kerusakan
                    </h3>

                    {{-- Quick Add Presets --}}
                    <div class="mb-4">
                        <p class="text-[11px] text-gray-400 mb-2">Klik untuk menambahkan cepat:</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(['Lecet cat', 'Penyok ringan', 'Pecah kaca', 'Ban kempes', 'Lampu mati', 'Mesin berisik', 'Rem bunyi', 'AC tidak dingin', 'Audio error', 'Jok sobek', 'Spion patah', 'Karpet kotor'] as $preset)
                            <button type="button" @click="addDamagePreset('{{ $preset }}')" class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-red-50 text-red-600 hover:bg-red-100 transition border border-red-100 hover:border-red-200">
                                <i class="fas fa-plus text-[8px] mr-1"></i>{{ $preset }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-2" id="damageItems">
                        <template x-for="(item, index) in damageItems" :key="index">
                            <div class="flex items-center gap-2 group">
                                <div class="w-6 h-6 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                                    <span class="text-[10px] font-bold text-red-400" x-text="index + 1"></span>
                                </div>
                                <input type="text" :name="'damage_items[' + index + ']'" x-model="damageItems[index]" class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 focus:border-red-300 transition" placeholder="Deskripsi kerusakan...">
                                <button type="button" @click="damageItems.splice(index, 1)" class="text-red-300 hover:text-red-600 px-2 opacity-0 group-hover:opacity-100 transition"><i class="fas fa-times"></i></button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="damageItems.push('')" class="mt-3 text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                        <i class="fas fa-plus-circle"></i> Tambah Manual
                    </button>
                </div>

                {{-- Completeness with Presets --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center"><i class="fas fa-list-check text-blue-500 text-sm"></i></div>
                        Kelengkapan
                    </h3>

                    {{-- Quick Add Presets --}}
                    <div class="mb-4">
                        <p class="text-[11px] text-gray-400 mb-2">Klik untuk menambahkan cepat:</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(['Kunci serep', 'Buku manual', 'Charger', 'Kabel USB', 'Earphone', 'Hardcase', 'Filter UV', 'Mounting', 'Tenda stakes', 'Fly sheet', 'Pasak tenda', 'Tascarrier'] as $preset)
                            <button type="button" @click="addCompletenessPreset('{{ $preset }}')" class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition border border-blue-100 hover:border-blue-200">
                                <i class="fas fa-plus text-[8px] mr-1"></i>{{ $preset }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-2" id="completenessItems">
                        <template x-for="(item, index) in completenessItems" :key="index">
                            <div class="flex items-center gap-2 group">
                                <div class="w-6 h-6 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                                    <span class="text-[10px] font-bold text-blue-400" x-text="index + 1"></span>
                                </div>
                                <input type="text" :name="'completeness[' + index + ']'" x-model="completenessItems[index]" class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-300 transition" placeholder="Deskripsi kelengkapan...">
                                <button type="button" @click="completenessItems.splice(index, 1)" class="text-red-300 hover:text-red-600 px-2 opacity-0 group-hover:opacity-100 transition"><i class="fas fa-times"></i></button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="completenessItems.push('')" class="mt-3 text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                        <i class="fas fa-plus-circle"></i> Tambah Manual
                    </button>
                </div>

                {{-- Notes --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center"><i class="fas fa-sticky-note text-amber-500 text-sm"></i></div>
                        Catatan & Rekomendasi
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-navy-600 mb-1.5">Catatan Inspeksi</label>
                            <textarea name="notes" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 transition bg-gray-50/50" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-navy-600 mb-1.5">Rekomendasi</label>
                            <textarea name="recommendations" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 transition bg-gray-50/50" placeholder="Rekomendasi perbaikan, perawatan, dll...">{{ old('recommendations') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary Sidebar --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-24">
                    <h3 class="text-sm font-bold text-navy-800 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center"><i class="fas fa-info-circle text-emerald-500 text-sm"></i></div>
                        Ringkasan
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Booking</span>
                            <span class="font-semibold text-navy-800 text-right text-xs" x-text="getBookingCode() || '-'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Scope</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold" :class="scopeBadge()" x-text="scope || '-'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Item</span>
                            <span class="font-semibold text-navy-800 text-right text-xs leading-tight max-w-[150px]" x-text="getSelectedItemName() || '-'"></span>
                        </div>
                        <hr class="border-gray-100">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Temuan</span>
                            <span class="font-semibold text-red-600" x-text="damageItems.filter(d => d).length + ' item'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Kelengkapan</span>
                            <span class="font-semibold text-blue-600" x-text="completenessItems.filter(c => c).length + ' item'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Kondisi</span>
                            <span class="font-bold text-lg" :class="overallTextColor()" x-text="overall_condition + '/10'"></span>
                        </div>
                    </div>
                    <button type="submit" class="w-full mt-6 bg-gradient-to-r from-emerald-500 to-teal-600 text-white py-3.5 rounded-xl font-bold text-sm hover:from-emerald-600 hover:to-teal-700 transition shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2">
                        <i class="fas fa-check-circle"></i> Simpan Inspeksi
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function inspectionForm() {
    return {
        selectedBookingId: '',
        scope: 'kendaraan',
        selectedItemId: '',
        overall_condition: 7,
        damageItems: [''],
        completenessItems: [''],
        vehicles: @json($vehicles),
        phones: @json($phones),
        cameras: @json($cameras),
        equipments: @json($equipments),

        detectScope() {
            const select = document.querySelector('select[name="booking_id"]');
            const opt = select.options[select.selectedIndex];
            if (opt) {
                this.scope = opt.getAttribute('data-scope') || 'kendaraan';
                const vehicleId = opt.getAttribute('data-vehicle');
                if (vehicleId && vehicleId !== 'null') {
                    this.selectedItemId = vehicleId;
                }
            }
        },

        updateItemList() { this.selectedItemId = ''; },

        getBookingCode() {
            const select = document.querySelector('select[name="booking_id"]');
            if (!select || !select.value) return '';
            const opt = select.options[select.selectedIndex];
            return opt.text.split(' - ')[0];
        },

        getSelectedItemName() {
            if (!this.selectedItemId) return '';
            const all = [...this.vehicles, ...this.phones, ...this.cameras, ...this.equipments];
            const found = all.find(i => i.id == this.selectedItemId);
            return found ? found.name : '';
        },

        addDamagePreset(preset) {
            const emptyIdx = this.damageItems.findIndex(d => !d);
            if (emptyIdx >= 0) this.damageItems[emptyIdx] = preset;
            else this.damageItems.push(preset);
        },

        addCompletenessPreset(preset) {
            const emptyIdx = this.completenessItems.findIndex(c => !c);
            if (emptyIdx >= 0) this.completenessItems[emptyIdx] = preset;
            else this.completenessItems.push(preset);
        },

        overallColor() {
            const v = parseInt(this.overall_condition);
            if (v >= 8) return 'bg-green-500';
            if (v >= 6) return 'bg-yellow-500';
            if (v >= 4) return 'bg-orange-500';
            return 'bg-red-500';
        },

        overallTextColor() {
            const v = parseInt(this.overall_condition);
            if (v >= 8) return 'text-green-600';
            if (v >= 6) return 'text-yellow-600';
            if (v >= 4) return 'text-orange-600';
            return 'text-red-600';
        },

        overallLabel() {
            const v = parseInt(this.overall_condition);
            if (v >= 9) return 'Sangat Baik';
            if (v >= 7) return 'Baik';
            if (v >= 5) return 'Cukup';
            if (v >= 3) return 'Kurang';
            return 'Sangat Kurang';
        },

        scopeBadge() {
            const map = { kendaraan: 'bg-blue-100 text-blue-700', elektronik: 'bg-purple-100 text-purple-700', camping: 'bg-emerald-100 text-emerald-700' };
            return map[this.scope] || 'bg-gray-100 text-gray-700';
        }
    }
}
</script>
@endsection
