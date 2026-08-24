<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateExpiredBookings extends Command
{
    /**
     * Nama dan signature dari Artisan command.
     *
     * @var string
     */
    protected $signature = 'booking:update-expired';

    /**
     * Deskripsi dari Artisan command.
     *
     * @var string
     */
    protected $description = 'Otomatis mengubah status booking menjadi expired jika tanggal selesai sudah lewat';

    /**
     * Jalankan command.
     */
    public function handle(): void
    {
        $this->info('Mengecek booking yang sudah expired...');

        // TODO: Sesuaikan nama model dan kolom dengan skema database Anda
        // Contoh: update semua booking yang end_date sudah lewat hari ini
        //
        // $updated = \App\Models\Booking::where('status', 'active')
        //     ->whereDate('end_date', '<', now()->toDateString())
        //     ->update(['status' => 'expired']);
        //
        // $this->info("Total {$updated} booking berhasil diubah menjadi expired.");

        $this->info('Selesai. (Aktifkan logika di dalam command ini setelah model siap)');

        Log::info('[Scheduler] UpdateExpiredBookings dijalankan pada '.now());
    }
}
