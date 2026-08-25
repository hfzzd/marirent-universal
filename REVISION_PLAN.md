# MariRent - Revisi Implementasi Plan

## Overview
8 revisi utama yang mencakup UI/UX, fitur operasional, pembayaran, dan invoice system.

---

## Revisi 1: Navbar Scrollable untuk Semua Dashboard
**Goal**: Sidebar bisa di-scroll secara independen di dashboard admin, inspector, driver, dan owner.

### Files to Modify:
- `resources/views/layouts/dashboard.blade.php`

### Changes:
- Ubah sidebar dari `fixed` menjadi `sticky top-0` agar natural scroll
- Tambah `h-screen overflow-y-auto` pada sidebar
- Pastikan main content area memiliki scroll sendiri
- Topbar tetap `sticky top-0` (sudah benar)

### Implementation:
```blade
<!-- Sidebar: ubah dari fixed menjadi sticky -->
<aside class="sidebar w-60 h-screen sticky top-0 z-40 ...overflow-y-auto">
<!-- Main content: pastikan flex-1 dengan scroll -->
<div class="flex-1 md:ml-60 min-h-screen">
    <header class="sticky top-0 z-30 ..."> <!-- topbar tetap sticky -->
    <main class="p-5"> <!-- content scroll naturally -->
```

---

## Revisi 2: Monitoring Scheduler (Panel Real-time + Scheduler Otomatis)
**Goal**: Dashboard admin memiliki panel status untuk penagihan, keterlambatan, DP, penjemputan, pemulangan kendaraan. Plus scheduler otomatis untuk notifikasi.

### A. Panel Real-time di Dashboard Superadmin
**Files to Modify:**
- `resources/views/dashboard/superadmin.blade.php`
- `app/Http/Controllers/Web/DashboardController.php` (add data queries)

**Panel Cards (setelah stat widgets):**
1. **Penagihan** - Booking completed tapi invoice belum lunas
2. **Keterlambatan** - Booking yang end_date sudah lewat tapi status masih ongoing
3. **DP Belum Dibayar** - Booking confirmed dengan payment_status unpaid/partial
4. **Penjemputan Hari Ini** - Booking yang start_date = hari ini
5. **Pemulangan Hari Ini** - Booking yang end_date = hari ini

### B. Scheduler/Observers
**Files to Create:**
- `app/Console/Commands/CheckOverdueBookings.php` - Cron: mark overdue bookings
- `app/Console/Commands/CheckUpcomingReturns.php` - Cron: reminder pemulangan
- `app/Console/Commands/SendPaymentReminders.php` - Cron: reminder penagihan
- `app/Console/Kernel.php` - Register scheduled tasks

### Implementation Panel:
```blade
{{-- Scheduler Status Panels --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    {{-- Penagihan --}}
    <div class="glass-card rounded-2xl p-5 border border-red-100/50">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase text-gray-400">Perlu Penagihan</p>
                <h3 class="text-2xl font-black text-red-600">{{ $needCollection->count() }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                <i class="fas fa-file-invoice text-red-500"></i>
            </div>
        </div>
    </div>
    {{-- Keterlambatan, DP, Penjemputan, Pemulangan --}}
</div>
```

---

## Revisi 3: Form Booking HP, Kamera, Alat (Enhanced)
**Goal**: Form booking item dengan field tambahan: aksesoris, asuransi, deposit, urgency, garansi, per kategori.

### Files to Modify:
- `resources/views/bookings/create-item.blade.php` (enhance existing)
- `app/Http/Controllers/Web/BookingWebController.php` (add `storeItem` method)
- `routes/web.php` (add route for store-item)
- `app/Models/Booking.php` (add `deposit_amount`, `insurance_fee`, `accessories` json field)

### Database Migration:
```php
// Add to bookings table
$table->decimal('deposit_amount', 12, 2)->default(0);
$table->decimal('insurance_fee', 12, 2)->default(0);
$table->json('accessories')->nullable();
$table->enum('urgency', ['normal', 'urgent', 'very_urgent'])->default('normal');
$table->boolean('with_insurance')->default(false);
```

### New Form Sections:
1. **Aksesoris** (checkboxes based on item type):
   - HP: Casing, Screen Protector, Charger Tambahan, Power Bank
   - Kamera: Lens Tambahan, Tripod, Tas Kamera, Memory Card, Battery Extra
   - Alat: Tas Carry, Mounting, Filter, dll
2. **Asuransi Unit** (toggle + info)
3. **Deposit** (auto-calculated, refundable)
4. **Urgency Level** (radio: Normal, Urgent, Sangat Urgent - affects pricing)
5. **Spesifikasi Kategori**:
   - HP: Storage capacity, warna preferensi
   - Kamera: Lens type, shooting purpose
   - Alat: Ukuran, kapasitas

---

## Revisi 4: Pergantian Kendaraan Saat Sudah Jalan
**Goal**: Support pergantian kendaraan untuk booking ongoing (baik lepas kunci maupun dengan driver). Driver tetap, hanya kendaraan diganti.

### Files to Modify:
- `app/Http/Controllers/Web/ReplacementWebController.php` (enhance `approve`)
- `resources/views/replacements/create.blade.php` (add status info)
- `resources/views/replacements/index.blade.php` (add tipe info)
- `app/Models/VehicleReplacement.php` (add `handover_type` field)

### Database Migration:
```php
// Add to vehicle_replacements table
$table->enum('handover_type', ['lepas_kunci', 'with_driver'])->default('lepas_kunci');
$table->text('handover_notes')->nullable();
$table->timestamp('actual_handover_at')->nullable();
```

### Flow:
1. Admin/driver mengajukan pergantian (termasuk info tipe: lepas kunci/with_driver)
2. Admin approve
3. Kendaraan lama dikembalikan, kendaraan baru diserahterimakan
4. Driver TIDAK berubah
5. Booking vehicle_id diupdate
6. Jika ongoing: vehicle status lama → available, vehicle baru → rented
7. Price difference dihitung dan ditambahkan ke invoice

---

## Revisi 5: Pilihan Lepas Kunci / Sama Driver (Modern UI)
**Goal**: Radio button/card selection yang lebih prominent dan modern.

### Files to Modify:
- `resources/views/bookings/create.blade.php` (replace checkbox with card selection)

### UI Design:
```blade
{{-- Pilihan Tipe Penggunaan --}}
<div>
    <label class="block text-[12px] font-semibold text-navy-700 mb-2">Tipe Penggunaan *</label>
    <div class="grid grid-cols-2 gap-3" x-data="{ mode: '{{ old('with_driver') ? 'driver' : 'lepas_kunci' }}' }">
        {{-- Lepas Kunci --}}
        <label class="cursor-pointer">
            <input type="radio" name="with_driver" value="0" x-model="mode" class="peer sr-only">
            <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-2xl p-5 transition-all hover:border-sky-300 group">
                <div class="w-12 h-12 rounded-xl bg-blue-50 group-peer-checked:bg-sky-100 flex items-center justify-center mb-3">
                    <i class="fas fa-key text-blue-500 text-xl"></i>
                </div>
                <p class="font-bold text-navy-800 text-[14px]">Lepas Kunci</p>
                <p class="text-[11px] text-gray-400 mt-1">Kendarai sendiri kendaraan pilihan Anda</p>
                <p class="text-[13px] font-bold text-sky-600 mt-2">Rp {{ number_format($vehicle->daily_price, 0, ',', '.') }}/hari</p>
            </div>
        </label>
        {{-- Sama Driver --}}
        <label class="cursor-pointer">
            <input type="radio" name="with_driver" value="1" x-model="mode" class="peer sr-only">
            <div class="border-2 border-gray-200 peer-checked:border-sky-500 peer-checked:bg-sky-50 rounded-2xl p-5 transition-all hover:border-sky-300 group">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 group-peer-checked:bg-sky-100 flex items-center justify-center mb-3">
                    <i class="fas fa-user-tie text-emerald-500 text-xl"></i>
                </div>
                <p class="font-bold text-navy-800 text-[14px]">Sama Driver</p>
                <p class="text-[11px] text-gray-400 mt-1">Driver profesional siap mengantar Anda</p>
                <p class="text-[13px] font-bold text-sky-600 mt-2">+ Rp {{ number_format($vehicle->with_driver_daily_price ?? 0, 0, ',', '.') }}/hari</p>
            </div>
        </label>
    </div>
</div>
```

---

## Revisi 6: Pembayaran Manual (Lanjutan)
**Goal**: Flow pembayaran manual yang lebih robust: cash auto-verify, transfer/e-wallet pending → admin verify.

### Files to Modify:
- `app/Http/Controllers/Web/InvoiceWebController.php` (enhance `pay`)
- `resources/views/invoices/show.blade.php` (enhanced payment form + proof upload)
- `routes/web.php` (add verify payment route)

### New Routes:
```php
Route::post('/invoices/{invoice}/payments/{payment}/verify', [InvoiceWebController::class, 'verifyPayment'])->name('invoices.verify-payment');
Route::post('/invoices/{invoice}/payments/{payment}/reject', [InvoiceWebController::class, 'rejectPayment'])->name('invoices.reject-payment');
```

### Controller Methods:
```php
public function verifyPayment(Invoice $invoice, Payment $payment) {
    $payment->update(['status' => 'verified']);
    // Update invoice paid_amount, due_amount, status
    $this->recalculateInvoice($invoice);
    return back()->with('success', 'Pembayaran diverifikasi');
}

public function rejectPayment(Invoice $invoice, Payment $payment) {
    $payment->update(['status' => 'rejected']);
    return back()->with('success', 'Pembayaran ditolak');
}

protected function recalculateInvoice(Invoice $invoice) {
    $verifiedTotal = $invoice->payments()->where('status', 'verified')->sum('amount');
    $invoice->update([
        'paid_amount' => $verifiedTotal,
        'due_amount' => max(0, $invoice->total_amount - $verifiedTotal),
        'status' => $verifiedTotal >= $invoice->total_amount ? 'paid' : ($verifiedTotal > 0 ? 'partial' : 'sent'),
    ]);
    // Also update related booking payment_status
    if ($invoice->booking) {
        $invoice->booking->update([
            'payment_status' => $invoice->status === 'paid' ? 'paid' : ($invoice->status === 'partial' ? 'partial' : 'unpaid'),
        ]);
    }
}
```

### Payment Form Enhancement:
- Tambah upload bukti pembayaran (untuk transfer/e-wallet)
- Tampilkan status pending/verified per payment
- Admin panel: tombol verify/reject per payment

---

## Revisi 7: Invoice Status Auto-Update
**Goal**: Invoice otomatis update status saat user membayar.

### Already Partially Implemented
The current `InvoiceWebController@pay` already updates status for cash payments. Need to extend:

### Files to Modify:
- `app/Http/Controllers/Web/InvoiceWebController.php` - ensure `recalculateInvoice` is called
- `app/Observers/PaymentObserver.php` (new) - auto-update on payment status change

### Observer Implementation:
```php
// app/Observers/PaymentObserver.php
class PaymentObserver {
    public function updated(Payment $payment) {
        if ($payment->isDirty('status')) {
            $invoice = $payment->invoice;
            $this->recalculateInvoice($invoice);
        }
    }
    
    protected function recalculateInvoice(Invoice $invoice) {
        $verifiedTotal = $invoice->payments()->where('status', 'verified')->sum('amount');
        $status = 'sent';
        if ($verifiedTotal >= $invoice->total_amount) $status = 'paid';
        elseif ($verifiedTotal > 0) $status = 'partial';
        
        $invoice->update([
            'paid_amount' => $verifiedTotal,
            'due_amount' => max(0, $invoice->total_amount - $verifiedTotal),
            'status' => $status,
        ]);
        
        if ($invoice->booking) {
            $invoice->booking->update([
                'payment_status' => $status === 'paid' ? 'paid' : ($status === 'partial' ? 'partial' : 'unpaid'),
            ]);
        }
    }
}
```

### Register Observer:
```php
// app/Providers/AppServiceProvider.php
Payment::observe(PaymentObserver::class);
```

---

## Revisi 8: Multi-Item Booking → 1 Invoice
**Goal**: User bisa pilih beberapa item (HP, Kamera, Alat) dalam 1 booking, lalu 1 invoice untuk semua.

### Database Migration:
```php
// New table: booking_items (for multi-item bookings)
Schema::create('booking_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
    $table->string('item_type'); // phone, camera, camping_equipment
    $table->unsignedBigInteger('item_id');
    $table->string('rental_type')->default('daily');
    $table->decimal('unit_price', 12, 2);
    $table->integer('quantity')->default(1);
    $table->decimal('subtotal', 12, 2);
    $table->json('accessories')->nullable();
    $table->boolean('with_insurance')->default(false);
    $table->decimal('insurance_fee', 12, 2)->default(0);
    $table->timestamps();
});
```

### Files to Create/Modify:
- `database/migrations/xxxx_create_booking_items_table.php`
- `app/Models/BookingItem.php` (new)
- `app/Models/Booking.php` (add `bookingItems` relationship)
- `resources/views/bookings/create-multi.blade.php` (new form)
- `app/Http/Controllers/Web/BookingWebController.php` (add `createMulti`, `storeMulti`)
- `routes/web.php` (add routes)
- `resources/views/invoices/show.blade.php` (show multi-item breakdown)

### Multi-Item Booking Form:
- Item selector: dropdown per kategori (HP, Kamera, Alat)
- "Tambah Item" button (Alpine.js dynamic form)
- Per-item: rental type, duration, aksesoris, asuransi
- Real-time total calculation
- Checkout → 1 booking, N booking_items

### Invoice Generation:
Saat booking multi-item dibuat, invoice akan memiliki InvoiceItems:
- 1 baris per item sewa (deskripsi: "Sewa [Item Name] - [durasi]")
- Subtotal, pajak, diskon, total

---

## Implementation Order

### Phase 1: Foundation (Revisi 1 + 5)
1. Fix navbar/sidebar scroll
2. Modernisasi UI lepas kunci vs sama driver

### Phase 2: Core Features (Revisi 3 + 4 + 8)
3. Enhanced form booking elektronik/alat
4. Pergantian kendaraan saat ongoing
5. Multi-item booking system + migration

### Phase 3: Payment & Invoice (Revisi 6 + 7)
6. Manual payment flow enhancement
7. Invoice auto-update + observer

### Phase 4: Monitoring (Revisi 2)
8. Dashboard monitoring panels
9. Scheduler/cron jobs

---

## Database Migrations Summary

1. `add_fields_to_bookings_table` - deposit_amount, insurance_fee, accessories, urgency
2. `add_fields_to_vehicle_replacements_table` - handover_type, handover_notes, actual_handover_at
3. `create_booking_items_table` - for multi-item bookings
4. `add_deposit_to_payments_table` - is_deposit_refundable, refunded_at

## Route Summary (New Routes)

```php
// Multi-item booking
Route::get('/bookings/create-multi', [BookingWebController::class, 'createMulti'])->name('bookings.create-multi');
Route::post('/bookings/store-multi', [BookingWebController::class, 'storeMulti'])->name('bookings.store-multi');

// Payment verification
Route::post('/invoices/{invoice}/payments/{payment}/verify', [InvoiceWebController::class, 'verifyPayment'])->name('invoices.verify-payment');
Route::post('/invoices/{invoice}/payments/{payment}/reject', [InvoiceWebController::class, 'rejectPayment'])->name('invoices.reject-payment');

// Scheduler/monitoring
Route::get('/superadmin/scheduler', [SuperadminController::class, 'scheduler'])->name('superadmin.scheduler');
```
