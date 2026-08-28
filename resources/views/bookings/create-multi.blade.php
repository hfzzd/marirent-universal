@extends('layouts.dashboard')
@section('page-title', 'Booking Multi-Item')
@section('content')
<div class="max-w-5xl mx-auto">
    <a href="{{ route('home') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>

    <form method="POST" action="{{ route('bookings.store-multi') }}" enctype="multipart/form-data" x-data="multiItemForm()">
        @csrf

        {{-- Header --}}
        <div class="bg-gradient-to-r from-sky-500 to-sky-700 text-white rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold"><i class="fas fa-layer-group mr-2"></i>Booking Multi-Item</h2>
            <p class="text-sky-100 text-sm mt-1">Sewa beberapa item sekaligus dalam satu booking</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Left: Items Selection --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Rental Period --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-navy-800 mb-4"><i class="fas fa-calendar-alt mr-2 text-sky-500"></i>Periode Sewa</h3>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-navy-600 mb-1">Tipe Sewa *</label>
                            <select name="rental_type" x-model="rentalType" @change="recalculateAll()" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500" required>
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                                <option value="hourly">Per Jam</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-navy-600 mb-1">Tanggal Mulai *</label>
                            <input type="datetime-local" name="start_date" x-model="startDate" @change="recalculateAll()" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-navy-600 mb-1">Tanggal Selesai *</label>
                            <input type="datetime-local" name="end_date" x-model="endDate" @change="recalculateAll()" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500" required>
                        </div>
                        <div class="flex items-end">
                            <div class="bg-sky-50 rounded-lg px-4 py-2.5 text-sm w-full">
                                <span class="text-navy-500">Durasi:</span>
                                <span class="font-bold text-sky-700 ml-1" x-text="durationText">-</span>
                                <input type="hidden" name="payment_plan" value="full">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Add Items --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-navy-800 mb-4"><i class="fas fa-plus-circle mr-2 text-sky-500"></i>Tambah Item</h3>

                    @if($phones->count() > 0 || $cameras->count() > 0 || $equipments->count() > 0 || $playstations->count() > 0 || $drones->count() > 0 || $instruments->count() > 0)
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        @if($phones->count() > 0)
                        <button type="button" @click="showPicker = 'hp'" class="border-2 border-gray-200 rounded-xl p-3 text-center hover:border-sky-400 transition-all">
                            <div class="w-10 h-10 mx-auto mb-2 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center"><i class="fas fa-mobile-alt"></i></div>
                            <span class="text-xs font-semibold text-navy-700">HP</span>
                        </button>
                        @endif
                        @if($cameras->count() > 0)
                        <button type="button" @click="showPicker = 'kamera'" class="border-2 border-gray-200 rounded-xl p-3 text-center hover:border-sky-400 transition-all">
                            <div class="w-10 h-10 mx-auto mb-2 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center"><i class="fas fa-camera"></i></div>
                            <span class="text-xs font-semibold text-navy-700">Kamera</span>
                        </button>
                        @endif
                        @if($equipments->count() > 0)
                        <button type="button" @click="showPicker = 'tenda'" class="border-2 border-gray-200 rounded-xl p-3 text-center hover:border-sky-400 transition-all">
                            <div class="w-10 h-10 mx-auto mb-2 bg-green-100 text-green-600 rounded-lg flex items-center justify-center"><i class="fas fa-campground"></i></div>
                            <span class="text-xs font-semibold text-navy-700">Alat Camping</span>
                        </button>
                        @endif
                        @if($playstations->count() > 0)
                        <button type="button" @click="showPicker = 'ps'" class="border-2 border-gray-200 rounded-xl p-3 text-center hover:border-sky-400 transition-all">
                            <div class="w-10 h-10 mx-auto mb-2 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center"><i class="fas fa-gamepad"></i></div>
                            <span class="text-xs font-semibold text-navy-700">Playstation</span>
                        </button>
                        @endif
                        @if($drones->count() > 0)
                        <button type="button" @click="showPicker = 'drone'" class="border-2 border-gray-200 rounded-xl p-3 text-center hover:border-sky-400 transition-all">
                            <div class="w-10 h-10 mx-auto mb-2 bg-cyan-100 text-cyan-600 rounded-lg flex items-center justify-center"><i class="fas fa-drone"></i></div>
                            <span class="text-xs font-semibold text-navy-700">Drone</span>
                        </button>
                        @endif
                        @if($instruments->count() > 0)
                        <button type="button" @click="showPicker = 'musik'" class="border-2 border-gray-200 rounded-xl p-3 text-center hover:border-sky-400 transition-all">
                            <div class="w-10 h-10 mx-auto mb-2 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center"><i class="fas fa-guitar"></i></div>
                            <span class="text-xs font-semibold text-navy-700">Alat Musik</span>
                        </button>
                        @endif
                    </div>

                    {{-- HP Picker --}}
                    <div x-show="showPicker === 'hp'" x-transition class="border border-purple-200 rounded-xl p-4 bg-purple-50/50">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-purple-700"><i class="fas fa-mobile-alt mr-2"></i>Pilih HP</span>
                            <button type="button" @click="showPicker = null" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto">
                            @foreach($phones as $p)
                            <button type="button" @click="addItem('hp', {{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->daily_price }}, {{ $p->hourly_price ?? 0 }}, {{ $p->weekly_price ?? 0 }}, {{ $p->monthly_price ?? 0 }}); showPicker = null"
                                class="text-left border border-gray-200 rounded-lg p-3 hover:border-purple-400 hover:bg-white transition-all">
                                <div class="font-medium text-xs text-navy-800">{{ $p->name }}</div>
                                <div class="text-xs text-navy-500">{{ $p->phone_model }} - {{ $p->storage_capacity }}</div>
                                <div class="text-xs font-bold text-purple-600 mt-1">Rp {{ number_format($p->daily_price,0,',','.') }}/hari</div>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Camera Picker --}}
                    <div x-show="showPicker === 'kamera'" x-transition class="border border-blue-200 rounded-xl p-4 bg-blue-50/50">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-blue-700"><i class="fas fa-camera mr-2"></i>Pilih Kamera</span>
                            <button type="button" @click="showPicker = null" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto">
                            @foreach($cameras as $c)
                            <button type="button" @click="addItem('kamera', {{ $c->id }}, '{{ addslashes($c->name) }}', {{ $c->daily_price }}, {{ $c->hourly_price ?? 0 }}, {{ $c->weekly_price ?? 0 }}, {{ $c->monthly_price ?? 0 }}); showPicker = null"
                                class="text-left border border-gray-200 rounded-lg p-3 hover:border-blue-400 hover:bg-white transition-all">
                                <div class="font-medium text-xs text-navy-800">{{ $c->name }}</div>
                                <div class="text-xs text-navy-500">{{ $c->camera_model }} - {{ $c->sensor_size }}</div>
                                <div class="text-xs font-bold text-blue-600 mt-1">Rp {{ number_format($c->daily_price,0,',','.') }}/hari</div>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Camping Equipment Picker --}}
                    <div x-show="showPicker === 'tenda'" x-transition class="border border-green-200 rounded-xl p-4 bg-green-50/50">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-green-700"><i class="fas fa-campground mr-2"></i>Pilih Alat Camping</span>
                            <button type="button" @click="showPicker = null" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto">
                            @foreach($equipments as $e)
                            <button type="button" @click="addItem('tenda', {{ $e->id }}, '{{ addslashes($e->name) }}', {{ $e->daily_price }}, {{ $e->hourly_price ?? 0 }}, {{ $e->weekly_price ?? 0 }}, {{ $e->monthly_price ?? 0 }}); showPicker = null"
                                class="text-left border border-gray-200 rounded-lg p-3 hover:border-green-400 hover:bg-white transition-all">
                                <div class="font-medium text-xs text-navy-800">{{ $e->name }}</div>
                                <div class="text-xs text-navy-500">{{ $e->equipment_model }} - {{ $e->capacity }}</div>
                                <div class="text-xs font-bold text-green-600 mt-1">Rp {{ number_format($e->daily_price,0,',','.') }}/hari</div>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Playstation Picker --}}
                    <div x-show="showPicker === 'ps'" x-transition class="border border-indigo-200 rounded-xl p-4 bg-indigo-50/50">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-indigo-700"><i class="fas fa-gamepad mr-2"></i>Pilih Playstation</span>
                            <button type="button" @click="showPicker = null" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto">
                            @foreach($playstations as $p)
                            <button type="button" @click="addItem('ps', {{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->daily_price }}, {{ $p->hourly_price ?? 0 }}, {{ $p->weekly_price ?? 0 }}, {{ $p->monthly_price ?? 0 }}); showPicker = null"
                                class="text-left border border-gray-200 rounded-lg p-3 hover:border-indigo-400 hover:bg-white transition-all">
                                <div class="font-medium text-xs text-navy-800">{{ $p->name }}</div>
                                <div class="text-xs text-navy-500">{{ $p->console_model }} - {{ $p->storage_capacity }}</div>
                                <div class="text-xs font-bold text-indigo-600 mt-1">Rp {{ number_format($p->daily_price,0,',','.') }}/hari</div>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Drone Picker --}}
                    <div x-show="showPicker === 'drone'" x-transition class="border border-cyan-200 rounded-xl p-4 bg-cyan-50/50">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-cyan-700"><i class="fas fa-drone mr-2"></i>Pilih Drone</span>
                            <button type="button" @click="showPicker = null" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto">
                            @foreach($drones as $d)
                            <button type="button" @click="addItem('drone', {{ $d->id }}, '{{ addslashes($d->name) }}', {{ $d->daily_price }}, {{ $d->hourly_price ?? 0 }}, {{ $d->weekly_price ?? 0 }}, {{ $d->monthly_price ?? 0 }}); showPicker = null"
                                class="text-left border border-gray-200 rounded-lg p-3 hover:border-cyan-400 hover:bg-white transition-all">
                                <div class="font-medium text-xs text-navy-800">{{ $d->name }}</div>
                                <div class="text-xs text-navy-500">{{ $d->drone_model }} - {{ $d->camera_resolution }}</div>
                                <div class="text-xs font-bold text-cyan-600 mt-1">Rp {{ number_format($d->daily_price,0,',','.') }}/hari</div>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Musical Instrument Picker --}}
                    <div x-show="showPicker === 'musik'" x-transition class="border border-rose-200 rounded-xl p-4 bg-rose-50/50">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-rose-700"><i class="fas fa-guitar mr-2"></i>Pilih Alat Musik</span>
                            <button type="button" @click="showPicker = null" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto">
                            @foreach($instruments as $m)
                            <button type="button" @click="addItem('musik', {{ $m->id }}, '{{ addslashes($m->name) }}', {{ $m->daily_price }}, {{ $m->hourly_price ?? 0 }}, {{ $m->weekly_price ?? 0 }}, {{ $m->monthly_price ?? 0 }}); showPicker = null"
                                class="text-left border border-gray-200 rounded-lg p-3 hover:border-rose-400 hover:bg-white transition-all">
                                <div class="font-medium text-xs text-navy-800">{{ $m->name }}</div>
                                <div class="text-xs text-navy-500">{{ $m->instrument_model }} - {{ $m->instrument_type ? ucfirst($m->instrument_type) : '' }}</div>
                                <div class="text-xs font-bold text-rose-600 mt-1">Rp {{ number_format($m->daily_price,0,',','.') }}/hari</div>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="text-center py-6 text-navy-400 text-sm">Tidak ada item yang tersedia</div>
                    @endif
                </div>

                {{-- Selected Items List --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-navy-800 mb-4"><i class="fas fa-list mr-2 text-sky-500"></i>Item yang Dipilih (<span x-text="items.length">0</span>)</h3>

                    <template x-if="items.length === 0">
                        <div class="text-center py-8 text-navy-400 text-sm">
                            <i class="fas fa-hand-pointer text-2xl mb-2 text-navy-300"></i>
                            <p>Belum ada item dipilih. Klik tombol di atas untuk menambah item.</p>
                        </div>
                    </template>

                    <div class="space-y-4">
                        <template x-for="(item, index) in items" :key="item.id + '-' + index">
                            <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50">
                                <input type="hidden" :name="'items[' + index + '][type]'" :value="item.type">
                                <input type="hidden" :name="'items[' + index + '][id]'" :value="item.id">
                                <input type="hidden" :name="'items[' + index + '][urgency]'" :value="item.urgency">
                                <input type="hidden" :name="'items[' + index + '][with_insurance]'" :value="item.with_insurance ? '1' : '0'">
                                <template x-for="(acc, ai) in item.accessories" :key="ai">
                                    <input type="hidden" :name="'items[' + index + '][accessories][' + ai + ']'" :value="acc">
                                </template>

                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <div class="font-bold text-sm text-navy-800" x-text="item.name"></div>
                                        <div class="text-xs text-navy-500 uppercase" x-text="item.type"></div>
                                    </div>
                                    <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 text-sm"><i class="fas fa-trash"></i></button>
                                </div>

                                <div class="grid sm:grid-cols-3 gap-3">
                                    {{-- Urgency --}}
                                    <div>
                                        <label class="block text-xs font-medium text-navy-600 mb-1">Tingkat Urgensi</label>
                                        <select @change="item.urgency = $event.target.value; recalculateAll()" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-sky-500">
                                            <option value="normal" :selected="item.urgency === 'normal'">Normal</option>
                                            <option value="urgent" :selected="item.urgency === 'urgent'">Urgent (+10%)</option>
                                            <option value="very_urgent" :selected="item.urgency === 'very_urgent'">Sangat Urgent (+20%)</option>
                                        </select>
                                    </div>

                                    {{-- Insurance --}}
                                    <div>
                                        <label class="block text-xs font-medium text-navy-600 mb-1">Asuransi</label>
                                        <label class="flex items-center gap-2 mt-1 cursor-pointer">
                                            <input type="checkbox" :checked="item.with_insurance" @change="item.with_insurance = $event.target.checked; recalculateAll()" class="rounded border-gray-300 text-sky-600">
                                            <span class="text-xs text-navy-600">+Rp <span x-text="Number(item.insuranceRate * duration).toLocaleString('id-ID')"></span></span>
                                        </label>
                                    </div>

                                    {{-- Price --}}
                                    <div class="text-right">
                                        <div class="text-xs text-navy-500">Subtotal</div>
                                        <div class="font-bold text-sm text-sky-700">Rp <span x-text="Number(item.total).toLocaleString('id-ID')"></span></div>
                                    </div>
                                </div>

                                {{-- Accessories --}}
                                <div x-show="getAccessories(item.type).length > 0" class="mt-3 pt-3 border-t border-gray-200">
                                    <label class="block text-xs font-medium text-navy-600 mb-2">Aksesoris</label>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="acc in getAccessories(item.type)" :key="acc.name">
                                            <label class="flex items-center gap-1 border border-gray-200 rounded-lg px-2 py-1 text-xs cursor-pointer hover:border-sky-400">
                                                <input type="checkbox" :checked="item.accessories.includes(acc.name)" @change="toggleAccessory(item, acc.name)" class="rounded border-gray-300 text-sky-600 text-xs">
                                                <span x-text="acc.name"></span>
                                                <span class="text-navy-400">(+Rp <span x-text="Number(acc.price).toLocaleString('id-ID')"></span>)</span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Notes & KTP --}}
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-navy-800 mb-4"><i class="fas fa-file-alt mr-2 text-sky-500"></i>Lainnya</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-navy-600 mb-1">Catatan</label>
                            <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-navy-600 mb-1">Foto KTP *</label>
                            <input type="file" name="ktp_photo" accept="image/*" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-sky-100 file:text-sky-700 hover:file:bg-sky-200">
                            @error('ktp_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Summary --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-24">
                    <h3 class="text-sm font-bold text-navy-800 mb-4"><i class="fas fa-receipt mr-2 text-sky-500"></i>Ringkasan</h3>

                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-navy-500">Jumlah Item</span>
                            <span class="font-semibold text-navy-800" x-text="items.length">0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-navy-500">Tipe Sewa</span>
                            <span class="font-semibold text-navy-800 capitalize" x-text="rentalType === 'daily' ? 'Harian' : (rentalType === 'weekly' ? 'Mingguan' : (rentalType === 'monthly' ? 'Bulanan' : 'Per Jam'))"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-navy-500">Durasi</span>
                            <span class="font-semibold text-navy-800" x-text="durationText">-</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-3 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-navy-500">Subtotal</span>
                            <span class="font-semibold text-navy-800">Rp <span x-text="Number(subtotal).toLocaleString('id-ID')"></span></span>
                        </div>
                        <div class="flex justify-between text-sm" x-show="totalInsurance > 0">
                            <span class="text-navy-500">Asuransi</span>
                            <span class="font-semibold text-blue-600">Rp <span x-text="Number(totalInsurance).toLocaleString('id-ID')"></span></span>
                        </div>
                        <div class="flex justify-between text-sm" x-show="totalAccessories > 0">
                            <span class="text-navy-500">Aksesoris</span>
                            <span class="font-semibold text-orange-600">Rp <span x-text="Number(totalAccessories).toLocaleString('id-ID')"></span></span>
                        </div>
                        <div class="flex justify-between text-sm" x-show="totalUrgency > 0">
                            <span class="text-navy-500">Biaya Urgensi</span>
                            <span class="font-semibold text-red-600">Rp <span x-text="Number(totalUrgency).toLocaleString('id-ID')"></span></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-navy-500">Deposit</span>
                            <span class="font-semibold text-navy-800">Rp <span x-text="Number(totalDeposit).toLocaleString('id-ID')"></span></span>
                        </div>
                    </div>

                    <div class="border-t-2 border-sky-200 pt-3 mb-6">
                        <div class="flex justify-between">
                            <span class="font-bold text-navy-800">TOTAL</span>
                            <span class="font-bold text-xl text-sky-700">Rp <span x-text="Number(grandTotal).toLocaleString('id-ID')"></span></span>
                        </div>
                    </div>

                    <button type="submit" :disabled="items.length === 0"
                        class="w-full bg-gradient-to-r from-sky-500 to-sky-700 text-white py-3 rounded-xl font-bold text-sm hover:from-sky-600 hover:to-sky-800 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-check-circle mr-2"></i>Buat Booking
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function multiItemForm() {
    return {
        items: [],
        showPicker: null,
        rentalType: 'daily',
        startDate: '',
        endDate: '',
        accessoriesData: {
            hp: [
                {'name': 'Casing', 'price': 5000},
                {'name': 'Screen Protector', 'price': 3000},
                {'name': 'Charger Tambahan', 'price': 5000},
                {'name': 'Power Bank', 'price': 10000},
                {'name': 'Earphone', 'price': 5000},
            ],
            kamera: [
                {'name': 'Lens Tambahan', 'price': 25000},
                {'name': 'Tripod', 'price': 15000},
                {'name': 'Tas Kamera', 'price': 10000},
                {'name': 'Memory Card 64GB', 'price': 10000},
                {'name': 'Battery Extra', 'price': 8000},
                {'name': 'Flash External', 'price': 15000},
            ],
            tenda: [
                {'name': 'Tas Carry', 'price': 8000},
                {'name': 'Mounting/Tiang', 'price': 10000},
                {'name': 'Lampu Tenda', 'price': 5000},
                {'name': 'Sleeping Bag', 'price': 15000},
                {'name': 'Matras', 'price': 8000},
            ],
            ps: [
                {'name': 'Controller Tambahan', 'price': 15000},
                {'name': 'Disk Game', 'price': 20000},
                {'name': 'Kabel HDMI', 'price': 5000},
                {'name': 'Charging Dock', 'price': 8000},
            ],
            drone: [
                {'name': 'Battery Tambahan', 'price': 20000},
                {'name': 'Charger', 'price': 5000},
                {'name': 'Propeller Cadangan', 'price': 10000},
                {'name': 'Tas Drone', 'price': 10000},
            ],
            musik: [
                {'name': 'Softcase', 'price': 10000},
                {'name': 'Tuner', 'price': 5000},
                {'name': 'Kabel', 'price': 8000},
                {'name': 'Pick Spare', 'price': 3000},
            ],
        },

        get duration() {
            if (!this.startDate || !this.endDate) return 0;
            const start = new Date(this.startDate);
            const end = new Date(this.endDate);
            const diff = end - start;
            if (diff <= 0) return 0;
            if (this.rentalType === 'hourly') return Math.max(1, Math.ceil(diff / 3600000));
            return Math.max(1, Math.ceil(diff / 86400000));
        },

        get durationText() {
            if (this.duration === 0) return '-';
            if (this.rentalType === 'hourly') return this.duration + ' jam';
            if (this.rentalType === 'daily') return this.duration + ' hari';
            if (this.rentalType === 'weekly') return this.duration + ' minggu';
            return this.duration + ' bulan';
        },

        get subtotal() {
            return this.items.reduce((sum, item) => {
                const price = this.rentalType === 'hourly' ? item.hourlyPrice :
                    this.rentalType === 'weekly' ? item.weeklyPrice :
                    this.rentalType === 'monthly' ? item.monthlyPrice : item.dailyPrice;
                return sum + (price * this.duration);
            }, 0);
        },

        get totalInsurance() {
            return this.items.reduce((sum, item) => {
                if (!item.with_insurance) return sum;
                return sum + (item.dailyPrice * 0.05 * this.duration);
            }, 0);
        },

        get totalAccessories() {
            return this.items.reduce((sum, item) => {
                const accs = this.accessoriesData[item.type] || [];
                return sum + item.accessories.reduce((a, name) => {
                    const found = accs.find(x => x.name === name);
                    return a + (found ? found.price * this.duration : 0);
                }, 0);
            }, 0);
        },

        get totalUrgency() {
            return this.items.reduce((sum, item) => {
                const price = this.rentalType === 'hourly' ? item.hourlyPrice :
                    this.rentalType === 'weekly' ? item.weeklyPrice :
                    this.rentalType === 'monthly' ? item.monthlyPrice : item.dailyPrice;
                const base = price * this.duration;
                if (item.urgency === 'urgent') return sum + Math.round(base * 0.10);
                if (item.urgency === 'very_urgent') return sum + Math.round(base * 0.20);
                return sum;
            }, 0);
        },

        get totalDeposit() {
            return this.items.reduce((sum, item) => sum + Math.round(item.dailyPrice * 0.3), 0);
        },

        get grandTotal() {
            return this.subtotal + this.totalInsurance + this.totalAccessories + this.totalUrgency + this.totalDeposit;
        },

        getAccessories(type) {
            return this.accessoriesData[type] || [];
        },

        addItem(type, id, name, dailyPrice, hourlyPrice, weeklyPrice, monthlyPrice) {
            const insuranceRate = Math.round(dailyPrice * 0.05);
            this.items.push({
                type, id, name, dailyPrice, hourlyPrice, weeklyPrice, monthlyPrice,
                insuranceRate,
                urgency: 'normal',
                with_insurance: false,
                accessories: [],
                total: 0,
            });
            this.recalculateAll();
        },

        removeItem(index) {
            this.items.splice(index, 1);
            this.recalculateAll();
        },

        toggleAccessory(item, name) {
            const idx = item.accessories.indexOf(name);
            if (idx === -1) item.accessories.push(name);
            else item.accessories.splice(idx, 1);
            this.recalculateAll();
        },

        recalculateAll() {
            this.items.forEach(item => {
                const price = this.rentalType === 'hourly' ? item.hourlyPrice :
                    this.rentalType === 'weekly' ? item.weeklyPrice :
                    this.rentalType === 'monthly' ? item.monthlyPrice : item.dailyPrice;

                let subtotal = price * this.duration;
                let insuranceFee = item.with_insurance ? item.dailyPrice * 0.05 * this.duration : 0;

                const accs = this.accessoriesData[item.type] || [];
                let accCost = item.accessories.reduce((a, name) => {
                    const found = accs.find(x => x.name === name);
                    return a + (found ? found.price * this.duration : 0);
                }, 0);

                let urgencyFee = 0;
                if (item.urgency === 'urgent') urgencyFee = Math.round(subtotal * 0.10);
                if (item.urgency === 'very_urgent') urgencyFee = Math.round(subtotal * 0.20);

                let deposit = Math.round(item.dailyPrice * 0.3);

                item.total = subtotal + insuranceFee + accCost + urgencyFee + deposit;
            });
        }
    }
}
</script>
@endsection