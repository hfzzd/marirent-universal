<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Carbon\Carbon;

/**
 * Layanan invoice per kategori: 1 kategori = 1 invoice.
 *
 * - Setiap booking baru otomatis digabung ke invoice terbuka
 *   milik user yang sama + kategori yang sama + owner yang sama,
 *   selama invoice belum ada pembayaran terverifikasi.
 * - Invoice gabungan manual wajib 1 kategori (mobil ke mobil,
 *   motor ke motor, dst).
 */
class InvoiceService
{
    /**
     * Resolve kategori booking: prioritaskan booking.category_id,
     * fallback ke vehicle.category_id / item.category_id.
     */
    public function resolveCategoryId(Booking $booking): ?int
    {
        if ($booking->category_id) {
            return (int) $booking->category_id;
        }

        $booking->loadMissing(['vehicle.category', 'category']);

        if ($booking->vehicle && $booking->vehicle->category_id) {
            return (int) $booking->vehicle->category_id;
        }

        if ($booking->item_id && $booking->item_type) {
            try {
                $item = $booking->item;
                if ($item && $item->category_id) {
                    return (int) $item->category_id;
                }
            } catch (\Throwable) {
                // abaikan, fallback null
            }
        }

        return null;
    }

    /**
     * Resolve owner (merchant) booking untuk grouping invoice.
     */
    public function resolveOwnerId(Booking $booking): ?int
    {
        if ($booking->vehicle && $booking->vehicle->owner_id) {
            return (int) $booking->vehicle->owner_id;
        }

        if ($booking->item_id && $booking->item_type) {
            try {
                $item = $booking->item;
                if ($item && isset($item->owner_id) && $item->owner_id) {
                    return (int) $item->owner_id;
                }
            } catch (\Throwable) {
            }
        }

        return null;
    }

    /**
     * Cari invoice terbuka yang bisa ditempeli booking baru:
     * user sama + kategori sama + owner sama + type rental +
     * status draft/sent + belum ada pembayaran verified + belum lunas.
     */
    public function findOpenInvoiceFor(Booking $booking): ?Invoice
    {
        $categoryId = $this->resolveCategoryId($booking);
        $ownerId = $this->resolveOwnerId($booking);

        if (!$categoryId) {
            return null;
        }

        $query = Invoice::where('user_id', $booking->user_id)
            ->where('type', 'rental')
            ->whereIn('status', ['draft', 'sent'])
            ->where('paid_amount', 0)
            ->where(function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->orWhere(function ($qq) use ($categoryId) {
                      $qq->whereNull('category_id')
                         ->whereHas('booking', fn($bq) => $bq->where('category_id', $categoryId));
                  });
            });

        if ($ownerId) {
            $query->where('owner_id', $ownerId);
        }

        // Hanya invoice yang belum jatuh tempo & belum ada payment verified/pending?
        // Pending boleh tetap digabung? Tidak — agar tidak mengacaukan verifikasi,
        // hanya gabung jika TIDAK ada payment pending/verified sama sekali.
        $candidates = $query->with(['payments', 'booking'])->latest('id')->get();

        foreach ($candidates as $invoice) {
            // Lewati jika sudah ada pembayaran apapun (pending/verified)
            if ($invoice->payments()->whereIn('status', ['pending', 'verified'])->exists()) {
                continue;
            }
            // Lewati jika sudah jatuh tempo
            if ($invoice->due_date && Carbon::parse($invoice->due_date)->isPast()) {
                continue;
            }
            // Pastikan semua booking yang sudah menempel juga 1 kategori
            $attachedCategories = $this->attachedCategoryIds($invoice);
            if ($attachedCategories->isNotEmpty() && !$attachedCategories->contains($categoryId)) {
                continue;
            }

            return $invoice;
        }

        return null;
    }

    /**
     * Kategori-kategori yang sudah menempel pada invoice.
     *
     * @return \Illuminate\Support\Collection<int>
     */
    public function attachedCategoryIds(Invoice $invoice): \Illuminate\Support\Collection
    {
        $ids = collect();

        $invoice->loadMissing(['booking.category', 'booking.vehicle.category', 'bookings.category', 'bookings.vehicle.category', 'category']);

        if ($invoice->category_id) {
            $ids->push((int) $invoice->category_id);
        }

        foreach ([$invoice->booking, ...$invoice->bookings] as $b) {
            if (!$b) {
                continue;
            }
            $cid = $b->category_id
                ?? $b->vehicle?->category_id
                ?? null;
            if ($cid) {
                $ids->push((int) $cid);
            }
        }

        // Fallback: baca dari deskripsi item? tidak perlu.

        return $ids->filter()->unique()->values();
    }

    /**
     * Validasi kumpulan booking layak digabung 1 invoice:
     * - user sama, owner sama (jika terdeteksi), kategori sama.
     *
     * @return array{ok: bool, message: ?string, category_id: ?int, user_id: ?int, owner_id: ?int}
     */
    public function validateMergeable(iterable $bookings): array
    {
        $bookings = collect($bookings)->values();
        if ($bookings->isEmpty()) {
            return ['ok' => false, 'message' => 'Pilih minimal satu booking.', 'category_id' => null, 'user_id' => null, 'owner_id' => null];
        }

        $userIds = $bookings->pluck('user_id')->unique();
        if ($userIds->count() > 1) {
            return ['ok' => false, 'message' => 'Semua booking dalam 1 invoice harus milik pelanggan yang sama.', 'category_id' => null, 'user_id' => null, 'owner_id' => null];
        }

        $categoryIds = $bookings->map(fn($b) => $this->resolveCategoryId($b))->unique();
        // Abaikan null hanya jika SEMUA null (invoice non-rental legacy)
        $nonNull = $categoryIds->filter(fn($v) => $v !== null);
        if ($nonNull->count() > 1) {
            return ['ok' => false, 'message' => 'Invoice gabungan harus 1 kategori (mis. mobil ke mobil, motor ke motor). Pilihan Anda mencampur beberapa kategori.', 'category_id' => null, 'user_id' => null, 'owner_id' => null];
        }

        $ownerIds = $bookings->map(fn($b) => $this->resolveOwnerId($b))->filter()->unique();
        if ($ownerIds->count() > 1) {
            return ['ok' => false, 'message' => 'Semua booking dalam 1 invoice harus dari merchant/company yang sama.', 'category_id' => null, 'user_id' => null, 'owner_id' => null];
        }

        return [
            'ok' => true,
            'message' => null,
            'category_id' => $nonNull->first(),
            'user_id' => $userIds->first(),
            'owner_id' => $ownerIds->first(),
        ];
    }

    /**
     * Tempelkan booking ke invoice yang sudah ada (1 kategori).
     */
    public function appendBooking(Invoice $invoice, Booking $booking): Invoice
    {
        $invoice->loadMissing(['items', 'bookings', 'booking']);

        // Cegah duplikat
        if ((int) $invoice->booking_id === (int) $booking->id || $invoice->bookings()->where('booking_id', $booking->id)->exists()) {
            return $invoice->fresh();
        }

        $categoryId = $this->resolveCategoryId($booking);
        $attached = $this->attachedCategoryIds($invoice);
        if ($attached->isNotEmpty() && $categoryId && !$attached->contains($categoryId)) {
            throw new \InvalidArgumentException('Booking beda kategori tidak bisa digabung ke invoice ini.');
        }

        $unit = $booking->vehicle?->name ?? $booking->item?->name ?? ($booking->category?->name ?? '-');
        $days = 1;
        try {
            $days = $booking->start_date && $booking->end_date ? max(1, $booking->start_date->diffInDays($booking->end_date)) : 1;
        } catch (\Throwable) {
        }

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => "Sewa {$unit} ({$booking->booking_code}) - {$days} hari",
            'quantity' => 1,
            'unit_price' => (float) $booking->final_price,
            'total_price' => (float) $booking->final_price,
        ]);

        $invoice->bookings()->syncWithoutDetaching([$booking->id]);

        // Set kategori jika masih kosong
        if (!$invoice->category_id && $categoryId) {
            $invoice->category_id = $categoryId;
        }

        $this->recalculateTotals($invoice->fresh());

        // Gabung catatan DP bila perlu
        if ($booking->payment_plan === 'dp50' && $booking->getDpAmount()) {
            $note = 'Sistem pembayaran DP 50%: DP minimal Rp ' . number_format((float) $booking->getDpAmount(), 0, ',', '.');
            if (!str_contains((string) $invoice->notes, 'DP 50%')) {
                $invoice->update(['notes' => trim(trim((string) $invoice->notes, ' |') . ' | ' . $note), 'category_id' => $invoice->category_id]);
            }
        }

        // Perpanjang due_date mengikuti booking terjauh
        try {
            $maxEnd = Booking::where(function ($q) use ($invoice, $booking) {
                $ids = $invoice->bookings()->pluck('bookings.id')->push($invoice->booking_id)->push($booking->id)->filter()->unique();
                $q->whereIn('id', $ids);
            })->max('end_date');
            if ($maxEnd && (!$invoice->due_date || Carbon::parse($maxEnd)->gt($invoice->due_date))) {
                $invoice->update(['due_date' => $maxEnd]);
            }
        } catch (\Throwable) {
        }

        return $invoice->fresh();
    }

    /**
     * Ambil invoice solo (1 booking) yang masih terbuka untuk booking ini.
     * Return null jika booking belum punya invoice, sudah gabungan, atau terkunci pembayaran.
     */
    public function soloOpenInvoiceFor(Booking $booking): ?Invoice
    {
        $booking->loadMissing(['invoice.payments', 'invoice.bookings', 'invoice.booking', 'invoices.payments', 'invoices.bookings']);

        $candidates = collect();
        if ($booking->invoice) {
            $candidates->push($booking->invoice);
        }
        foreach ($booking->invoices as $inv) {
            if (!$candidates->contains(fn($c) => (int) $c->id === (int) $inv->id)) {
                $candidates->push($inv);
            }
        }

        foreach ($candidates as $inv) {
            // Hanya invoice solo: total booking tertaut <= 1 (primary + pivot)
            $linkedCount = ($inv->booking_id ? 1 : 0) + $inv->bookings->count();
            // Hitung unik (primary bisa sama dengan pivot)
            $ids = collect([$inv->booking_id, ...$inv->bookings->pluck('id')])->filter()->unique();
            if ($ids->count() > 1) {
                continue; // sudah gabungan, jangan digabung ulang
            }
            if (!in_array($inv->status, ['draft', 'sent'], true)) {
                continue;
            }
            if ((float) $inv->paid_amount > 0) {
                continue;
            }
            if ($inv->payments()->whereIn('status', ['pending', 'verified'])->exists()) {
                continue;
            }

            return $inv;
        }

        // Belum punya invoice sama sekali -> layak dibuatkan baru (return null sebagai penanda)
        if ($candidates->isEmpty()) {
            return null;
        }

        return null;
    }

    /**
     * Apakah booking layak dipilih untuk invoice gabungan?
     * - status confirmed/ongoing/completed
     * - payment_status bukan paid
     * - belum punya invoice ATAU punya invoice solo terbuka (tanpa payment)
     */
    public function isEligibleForMerge(Booking $booking): array
    {
        if (!in_array($booking->status, ['confirmed', 'ongoing', 'completed'], true)) {
            return ['ok' => false, 'reason' => 'Status booking ' . $booking->booking_code . ' (' . $booking->status . ') tidak bisa digabung.'];
        }
        if (($booking->payment_status ?? 'unpaid') === 'paid') {
            return ['ok' => false, 'reason' => 'Booking ' . $booking->booking_code . ' sudah lunas.'];
        }

        $booking->loadMissing(['invoice', 'invoices']);
        $hasAny = (bool) $booking->invoice || $booking->invoices->isNotEmpty();

        if (!$hasAny) {
            return ['ok' => true, 'reason' => null, 'has_invoice' => false, 'solo_invoice_id' => null];
        }

        // Punya invoice: pastikan solo & terbuka
        $solo = $this->soloOpenInvoiceFor($booking);
        // Bedakan "punya invoice tapi terkunci" vs "solo terbuka"
        // soloOpenInvoiceFor return null untuk kedua kasus, jadi cek manual:
        $candidates = collect();
        if ($booking->invoice) {
            $candidates->push($booking->invoice);
        }
        foreach ($booking->invoices as $inv) {
            if (!$candidates->contains(fn($c) => (int) $c->id === (int) $inv->id)) {
                $candidates->push($inv);
            }
        }

        if ($candidates->isEmpty()) {
            return ['ok' => true, 'reason' => null, 'has_invoice' => false, 'solo_invoice_id' => null];
        }

        if ($solo) {
            return ['ok' => true, 'reason' => null, 'has_invoice' => true, 'solo_invoice_id' => $solo->id];
        }

        // Punya invoice tapi tidak solo-terbuka (sudah gabungan / ada payment / sudah partial)
        $first = $candidates->first();
        $linkedIds = collect([$first->booking_id, ...$first->bookings->pluck('id')])->filter()->unique();
        if ($linkedIds->count() > 1) {
            return ['ok' => false, 'reason' => 'Booking ' . $booking->booking_code . ' sudah tergabung dalam invoice ' . $first->invoice_number . '.'];
        }
        if ((float) $first->paid_amount > 0 || $first->payments()->whereIn('status', ['pending', 'verified'])->exists()) {
            return ['ok' => false, 'reason' => 'Booking ' . $booking->booking_code . ' sudah ada pembayaran berjalan dan tidak bisa digabung ulang.'];
        }

        return ['ok' => false, 'reason' => 'Booking ' . $booking->booking_code . ' tidak bisa digabung (status invoice: ' . $first->status . ').'];
    }

    /**
     * Gabungkan beberapa booking (yang boleh sudah punya invoice solo)
     * menjadi 1 invoice baru per kategori. Invoice solo lama yang kosong
     * (tanpa payment) dihapus dalam transaksi yang sama.
     */
    public function consolidateBookings(iterable $bookings, array $options = []): Invoice
    {
        $bookings = collect($bookings)->values();
        $check = $this->validateMergeable($bookings);
        if (!$check['ok']) {
            throw new \InvalidArgumentException($check['message']);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($bookings, $check, $options) {
            $oldIds = [];
            foreach ($bookings as $b) {
                $elig = $this->isEligibleForMerge($b);
                if (!$elig['ok']) {
                    throw new \InvalidArgumentException($elig['reason']);
                }
                if (!empty($elig['solo_invoice_id'])) {
                    $oldIds[] = $elig['solo_invoice_id'];
                }
                // Cegah booking yang menempel di >1 invoice berbeda (data anomali)
                $b->loadMissing(['invoice', 'invoices']);
                $allIds = collect();
                if ($b->invoice) {
                    $allIds->push($b->invoice->id);
                }
                foreach ($b->invoices as $inv) {
                    $allIds->push($inv->id);
                }
                $allIds = $allIds->unique()->values();
                if ($allIds->count() > 1) {
                    throw new \InvalidArgumentException('Booking ' . $b->booking_code . ' menempel pada beberapa invoice berbeda, rapikan dulu sebelum digabung.');
                }
            }
            $oldIds = collect($oldIds)->unique()->values();

            // Pastikan tidak ada payment di invoice lama (pengaman ganda)
            foreach ($oldIds as $oid) {
                $old = Invoice::find($oid);
                if (!$old) {
                    continue;
                }
                if ((float) $old->paid_amount > 0 || $old->payments()->whereIn('status', ['pending', 'verified'])->exists()) {
                    throw new \InvalidArgumentException('Invoice ' . $old->invoice_number . ' sudah ada pembayaran dan tidak bisa digabung.');
                }
            }

            $first = $bookings->first();
            $subtotal = (float) $bookings->sum('final_price');
            $taxPercent = (float) ($options['tax_percent'] ?? 0);
            $taxAmount = round($subtotal * $taxPercent / 100, 2);
            $discountAmount = (float) ($options['discount_amount'] ?? 0);
            $totalAmount = max(0, $subtotal + $taxAmount - $discountAmount);

            $ownerId = $check['owner_id'] ?? $options['owner_id'] ?? $this->resolveOwnerId($first);
            if (!$ownerId) {
                $ownerId = \App\Models\User::where('role', 'superadmin')->orderBy('id')->value('id');
            }

            $catName = $first->category?->name ?? $first->vehicle?->category?->name ?? '-';
            $notes = $options['notes'] ?? null;
            if ($notes) {
                $notes = ($check['category_id'] ? ('Kategori: ' . $catName . ' | ') : '') . $notes;
            } else {
                $notes = $check['category_id'] ? ('Invoice gabungan 1 kategori: ' . $catName) : null;
            }

            // due_date: pakai opsi, atau end_date terjauh
            $dueDate = $options['due_date'] ?? null;
            if (!$dueDate) {
                try {
                    $dueDate = $bookings->map(fn($b) => $b->end_date ? Carbon::parse($b->end_date) : null)->filter()->max() ?? now()->addDays(7);
                } catch (\Throwable) {
                    $dueDate = now()->addDays(7);
                }
            }

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber('rental'),
                'booking_id' => $first->id,
                'user_id' => $check['user_id'] ?? $first->user_id,
                'owner_id' => $ownerId,
                'category_id' => $check['category_id'],
                'type' => 'rental',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'due_amount' => $totalAmount,
                'status' => 'draft',
                'due_date' => $dueDate,
                'notes' => $notes,
            ]);

            foreach ($bookings as $b) {
                $unit = $b->vehicle?->name;
                if (!$unit) {
                    try {
                        $unit = $b->item?->name;
                    } catch (\Throwable) {
                        $unit = null;
                    }
                }
                $unit = $unit ?? ($b->category?->name ?? '-');
                $days = 1;
                try {
                    $days = $b->start_date && $b->end_date ? max(1, $b->start_date->diffInDays($b->end_date)) : 1;
                } catch (\Throwable) {
                }
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => "Sewa {$unit} ({$b->booking_code}) - {$days} hari",
                    'quantity' => 1,
                    'unit_price' => (float) $b->final_price,
                    'total_price' => (float) $b->final_price,
                ]);
            }

            $invoice->bookings()->attach($bookings->pluck('id'));

            // Hapus invoice solo lama (soft delete) + detach pivot agar tidak ganda
            foreach ($oldIds as $oid) {
                $old = Invoice::find($oid);
                if (!$old || (int) $old->id === (int) $invoice->id) {
                    continue;
                }
                $old->bookings()->detach();
                $old->items()->delete();
                $old->delete();
            }

            return $invoice->fresh();
        });
    }

    /**
     * Hitung ulang subtotal/total/due dari booking + item yang menempel.
     */
    public function recalculateTotals(Invoice $invoice): Invoice
    {
        $invoice->loadMissing(['items', 'bookings', 'booking']);
        $fresh = $invoice->fresh();
        $fresh->loadMissing(['items', 'bookings']);

        $bookingIds = $fresh->bookings->pluck('id');
        if ($fresh->booking_id) {
            $bookingIds->push($fresh->booking_id);
        }
        $bookingIds = $bookingIds->filter()->unique();

        if ($bookingIds->isNotEmpty()) {
            $newTotal = (float) Booking::whereIn('id', $bookingIds)->sum('final_price');
            // Jika ada item manual (pajak/diskon dari invoice gabungan), pertahankan selisih?
            // Untuk invoice otomatis (tanpa pajak/diskon), total = sum booking.
            // Untuk invoice gabungan manual (ada pajak/diskon), hitung ulang dari subtotal.
            $paid = (float) $fresh->paid_amount;
            $subtotal = $newTotal;
            $total = max(0, $subtotal + (float) $fresh->tax_amount - (float) $fresh->discount_amount);

            // Sinkronkan baris item yang cocok kode booking
            foreach ($fresh->items as $item) {
                foreach (Booking::whereIn('id', $bookingIds)->get() as $b) {
                    if (str_contains((string) $item->description, (string) $b->booking_code)) {
                        if ((float) $item->total_price !== (float) $b->final_price) {
                            $item->update(['unit_price' => (float) $b->final_price, 'total_price' => (float) $b->final_price]);
                        }
                        break;
                    }
                }
            }

            $fresh->update([
                'subtotal' => $subtotal,
                'total_amount' => $total,
                'due_amount' => max(0, $total - $paid),
                'status' => $paid >= $total && $total > 0 ? 'paid' : ($paid > 0 ? 'partial' : ($fresh->status === 'draft' ? 'draft' : 'sent')),
            ]);
        }

        return $fresh->fresh();
    }
}
