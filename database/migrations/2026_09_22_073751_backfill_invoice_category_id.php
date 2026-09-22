<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill invoice.category_id dari booking terkait (1 kategori = 1 invoice).
        $invoices = DB::table('invoices')->whereNull('category_id')->get(['id', 'booking_id']);
        foreach ($invoices as $inv) {
            $catId = null;
            if ($inv->booking_id) {
                $catId = DB::table('bookings')->where('id', $inv->booking_id)->value('category_id');
                if (!$catId) {
                    $vehicleId = DB::table('bookings')->where('id', $inv->booking_id)->value('vehicle_id');
                    if ($vehicleId) {
                        $catId = DB::table('vehicles')->where('id', $vehicleId)->value('category_id');
                    }
                }
            }
            if (!$catId) {
                $pivotBookingId = DB::table('booking_invoice')->where('invoice_id', $inv->id)->orderBy('booking_id')->value('booking_id');
                if ($pivotBookingId) {
                    $catId = DB::table('bookings')->where('id', $pivotBookingId)->value('category_id');
                }
            }
            if ($catId) {
                DB::table('invoices')->where('id', $inv->id)->update(['category_id' => $catId]);
            }
        }
    }

    public function down(): void
    {
        // no-op: backfill tidak perlu di-rollback
    }
};
