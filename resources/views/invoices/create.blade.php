@extends('layouts.dashboard')
@section('page-title', 'Buat Invoice Gabungan')

@section('content')
<div class="max-w-5xl">
    <a href="{{ route('invoices.index') }}" class="text-sky-600 text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>

    <div class="mb-4 bg-sky-50 border border-sky-200 rounded-xl px-4 py-3 text-[12px] text-sky-800">
        <i class="fas fa-circle-info mr-1"></i>
        <strong>Aturan 1 kategori = 1 invoice:</strong> mobil digabung dengan mobil, motor dengan motor, HP dengan HP, dan seterusnya.
        Booking beda kategori tidak bisa digabung. Pilih pelanggan dulu, lalu centang 2+ booking dalam 1 kategori yang sama.
        Booking yang sudah punya invoice solo (belum dibayar) tetap bisa dipilih — invoice lamanya digabung otomatis.
    </div>

    @if(($eligible ?? collect())->isEmpty())
    <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-[12px] text-amber-800">
        <i class="fas fa-triangle-exclamation mr-1"></i>
        <strong>Tidak ada booking yang bisa digabung.</strong>
        Syarat: status confirmed/ongoing/completed, belum lunas, belum tergabung, dan belum ada pembayaran berjalan.
        @if(($skipped ?? 0) > 0)
        <span class="block mt-1">{{ $skipped }} booking dilewati karena sudah lunas / sudah gabungan / ada pembayaran.</span>
        @endif
        <span class="block mt-1">Tip: buat booking baru (status confirmed) lalu kembali ke sini, atau lunasi dulu pembayaran pending agar bisa digabung.</span>
    </div>
    @endif

    <form method="POST" action="{{ route('invoices.store') }}"
          x-data="{
              userId: '{{ old('user_id') }}',
              taxPercent: {{ old('tax_percent', 0) }},
              discount: {{ old('discount_amount', 0) }},
              subtotal: 0,
              count: 0,
              activeCategory: '',
              activeCategoryName: '',
              recalc() {
                  let sum = 0, n = 0;
                  let cats = new Set();
                  let catName = '';
                  this.$refs.rows.querySelectorAll('input[type=checkbox]:checked').forEach(cb => {
                      sum += parseFloat(cb.dataset.price); n++;
                      cats.add(cb.dataset.category);
                      catName = cb.dataset.categoryName;
                  });
                  this.subtotal = sum; this.count = n;
                  if (cats.size === 1) {
                      this.activeCategory = [...cats][0];
                      this.activeCategoryName = catName;
                  } else if (cats.size > 1) {
                      this.activeCategory = 'MIXED';
                      this.activeCategoryName = '';
                  } else {
                      this.activeCategory = '';
                      this.activeCategoryName = '';
                  }
              },
              isDisabled(catId) {
                  return this.activeCategory !== '' && this.activeCategory !== 'MIXED' && String(catId) !== String(this.activeCategory);
              }
          }"
          @change="recalc()" x-init="recalc()">
        @csrf

        {{-- Pilih Pelanggan --}}
        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-user-tag text-sky-500"></i> Pelanggan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-semibold text-navy-700 mb-1">Pilih Pelanggan *</label>
                    <x-searchable-select name="user_id" placeholder="-- Pilih pelanggan --" required x-model="userId" @change="recalc()">
                        <option value="">-- Pilih pelanggan --</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ old('user_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->email }})</option>
                        @endforeach
                    </x-searchable-select>
                    <p class="text-[11px] text-gray-400 mt-1"><i class="fas fa-circle-info mr-1"></i> Semua sewa dalam satu invoice harus milik pelanggan yang sama.</p>
                </div>
                <div class="flex items-end">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-[12px] w-full">
                        <p class="text-gray-400 font-semibold uppercase text-[10px] tracking-widest">Kategori terpilih</p>
                        <p class="font-bold text-navy-800 mt-0.5" x-text="activeCategory === '' ? 'Belum ada' : (activeCategory === 'MIXED' ? 'Campuran (tidak valid)' : activeCategoryName)">Belum ada</p>
                        <p x-show="activeCategory === 'MIXED'" class="text-red-500 text-[11px] mt-1 font-semibold"><i class="fas fa-triangle-exclamation mr-1"></i> Hapus salah satu kategori — invoice hanya boleh 1 kategori.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pilih Sewa per kategori --}}
        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5" x-ref="rows">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-1 flex items-center gap-2"><i class="fas fa-layer-group text-violet-500"></i> Pilih Sewa <span class="text-red-500">*</span></h3>
            <p class="text-[11px] text-gray-400 mb-1">Centang minimal dua booking <strong>dalam 1 kategori yang sama</strong> untuk digabung dalam satu invoice.</p>
            <p class="text-[11px] text-amber-600 mb-4" x-show="!userId"><i class="fas fa-arrow-up mr-1"></i> Pilih pelanggan di atas dulu untuk melihat booking miliknya ({{ ($eligible ?? collect())->count() }} booking tersedia total).</p>

            @forelse(($groupedEligible ?? collect()) as $catName => $items)
            <div class="mb-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-violet-100 text-violet-700"><i class="fas fa-tag mr-1"></i>{{ $catName }}</span>
                    <span class="text-[11px] text-gray-400">{{ $items->count() }} booking tersedia</span>
                </div>
                <div class="space-y-2">
                    @foreach($items as $e)
                    <label data-user="{{ $e['user_id'] }}" x-show="userId == '{{ $e['user_id'] }}'"
                           :class="isDisabled('{{ $e['category_id'] }}') ? 'opacity-40 pointer-events-none' : ''"
                           class="flex items-center gap-3 bg-white border border-gray-200 hover:border-sky-300 has-[:checked]:bg-sky-50/60 has-[:checked]:border-sky-400 rounded-xl px-4 py-3 cursor-pointer transition">
                        <input type="checkbox" name="booking_ids[]" value="{{ $e['id'] }}" data-price="{{ $e['price'] }}"
                               data-category="{{ $e['category_id'] }}" data-category-name="{{ $e['category_name'] }}"
                               @checked(in_array($e['id'], old('booking_ids', [])))
                               :disabled="isDisabled('{{ $e['category_id'] }}')"
                               class="rounded border-gray-300 text-sky-500 focus:ring-sky-400 w-4 h-4">
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-bold text-navy-800">{{ $e['unit'] }} <span class="text-gray-300 font-normal mx-1">|</span> <span class="font-mono text-[11px] text-sky-600">{{ $e['code'] }}</span></p>
                            <p class="text-[11px] text-gray-400 mt-0.5"><i class="far fa-calendar mr-1"></i>{{ $e['period'] }} &bull; {{ $e['days'] }} hari &bull; {{ $e['user_name'] ?? '' }}</p>
                            @if(!empty($e['has_invoice']))
                            <p class="text-[10px] mt-1"><span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-bold"><i class="fas fa-file-invoice mr-0.5"></i> {{ $e['invoice_number'] ?? 'Sudah ada invoice' }} → akan digabung</span></p>
                            @else
                            <p class="text-[10px] mt-1"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-bold"><i class="fas fa-plus mr-0.5"></i> Belum ada invoice</span></p>
                            @endif
                        </div>
                        <p class="text-[13px] font-extrabold text-navy-800 whitespace-nowrap">Rp {{ number_format($e['price'], 0, ',', '.') }}</p>
                    </label>
                    @endforeach
                </div>
            </div>
            @empty
            <p class="text-[12px] text-gray-300 text-center py-6"><i class="fas fa-inbox text-2xl block mb-2 opacity-40"></i> Tidak ada booking yang bisa digabung saat ini.</p>
            @endforelse
        </div>

        {{-- Ringkasan --}}
        <div class="glass-card rounded-2xl p-6 border border-sky-100/50 shadow-sm mb-5">
            <h3 class="text-[13px] font-extrabold text-navy-800 mb-4 flex items-center gap-2"><i class="fas fa-receipt text-emerald-500"></i> Ringkasan Tagihan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-700 mb-1">Pajak (%)</label>
                        <input type="number" name="tax_percent" value="{{ old('tax_percent', 0) }}" min="0" max="100" step="0.01" x-model.number="taxPercent"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-700 mb-1">Diskon (Rp)</label>
                        <input type="number" name="discount_amount" value="{{ old('discount_amount', 0) }}" min="0" step="1000" x-model.number="discount"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-700 mb-1">Jatuh Tempo Bayar *</label>
                        <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(7)->format('Y-m-d')) }}" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-navy-700 mb-1">Catatan (opsional)</label>
                        <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500" placeholder="Contoh: tagihan gabungan sewa mobil bulan ini...">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="bg-sky-50/60 border border-sky-100 rounded-xl p-5 self-start space-y-2.5 text-[13px]">
                    <div class="flex justify-between"><span class="text-navy-500">Sewa dipilih</span><span class="font-bold text-navy-800" x-text="count + ' sewa'">0 sewa</span></div>
                    <div class="flex justify-between"><span class="text-navy-500">Subtotal</span><span class="font-semibold" x-text="'Rp ' + subtotal.toLocaleString('id-ID')">Rp 0</span></div>
                    <div class="flex justify-between"><span class="text-navy-500">Pajak (<span x-text="taxPercent"></span>%)</span><span class="font-semibold" x-text="'Rp ' + Math.round(subtotal * taxPercent / 100).toLocaleString('id-ID')">Rp 0</span></div>
                    <div class="flex justify-between"><span class="text-navy-500">Diskon</span><span class="font-semibold text-red-500" x-text="'- Rp ' + Math.min(discount, subtotal || discount).toLocaleString('id-ID')">Rp 0</span></div>
                    <div class="border-t border-sky-200 pt-2.5 flex justify-between items-center">
                        <span class="font-bold text-navy-800">Total Tagihan</span>
                        <span class="font-black text-lg text-sky-600" x-text="'Rp ' + Math.max(0, subtotal + Math.round(subtotal * taxPercent / 100) - Math.min(discount, subtotal || discount)).toLocaleString('id-ID')">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-[13px]">
            @foreach($errors->all() as $err) <p class="flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> {{ $err }}</p> @endforeach
        </div>
        @endif

        @if(session('error'))
        <div class="mb-5 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-[13px]">
            <p class="flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</p>
        </div>
        @endif

        <div class="flex gap-3 pb-2">
            <button type="submit" :disabled="activeCategory === 'MIXED'" :class="activeCategory === 'MIXED' ? 'opacity-50 cursor-not-allowed' : ''" class="btn-primary text-white px-8 py-3 rounded-xl text-sm font-bold shadow-lg shadow-sky-500/25 transition"><i class="fas fa-file-invoice mr-2"></i> Buat Invoice</button>
            <a href="{{ route('invoices.index') }}" class="bg-gray-100 hover:bg-gray-200 px-6 py-3 rounded-xl text-sm font-semibold text-navy-700 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
