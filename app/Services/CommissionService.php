<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Merchant;
use App\Models\Setting;

/**
 * Menghitung komisi platform & pendapatan bersih merchant.
 *
 * Model: platform (superadmin/developer) memungut komisi. Hasil komisi
 * langsung tercatat saat invoice lunas (tanpa alur penarikan/escrow).
 */
class CommissionService
{
    /**
     * Terapkan komisi ke sebuah invoice.
     *
     * Dipanggil di dalam event `saving` model Invoice ketika status "paid".
     * Method ini hanya menghitung & mengisi properti; simpan dilakukan oleh
     * event handler (menghindari re-entry / infinite loop).
     *
     * Mengembalikan true jika diterapkan.
     */
    public function apply(Invoice $invoice): bool
    {
        // Hanya untuk invoice yang menghasilkan pendapatan pada merchant.
        if (!$this->shouldApplyCommission($invoice)) {
            return false;
        }

        $rate = $this->resolveRate($invoice);
        $total = (float) $invoice->total_amount;
        $platformFee = round($total * $rate / 100, 2);
        $merchantRevenue = round($total - $platformFee, 2);

        $invoice->commission_rate = $rate;
        $invoice->platform_fee = $platformFee;
        $invoice->merchant_revenue = $merchantRevenue;

        return true;
    }

    /**
     * Apakah invoice ini layak dikenakan komisi platform?
     */
    public function shouldApplyCommission(Invoice $invoice): bool
    {
        if (!$invoice->owner_id) {
            return false;
        }

        // Jenis invoice yang merupakan pemasukan rental (source merchant).
        return in_array($invoice->type, ['rental', 'manual_income', 'damage', 'replacement'], true);
    }

    /**
     * Resolusi rate komisi: prioritaskan rate merchant (toko), fallback ke setting global.
     */
    public function resolveRate(Invoice $invoice): float
    {
        $merchantRate = Merchant::where('user_id', $invoice->owner_id)->value('commission_rate');

        if ($merchantRate !== null && (float) $merchantRate > 0) {
            return (float) $merchantRate;
        }

        try {
            $globalRate = (float) Setting::get('commission_rate', 10);
        } catch (\Throwable $e) {
            $globalRate = 10;
        }

        return $globalRate;
    }
}
