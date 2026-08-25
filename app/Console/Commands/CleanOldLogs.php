<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanOldLogs extends Command
{
    /**
     * Nama dan signature dari Artisan command.
     *
     * @var string
     */
    protected $signature = 'logs:clean {--days=30 : Hapus log yang lebih dari N hari}';

    /**
     * Deskripsi dari Artisan command.
     *
     * @var string
     */
    protected $description = 'Bersihkan data log / activity log lama dari database secara berkala';

    /**
     * Jalankan command.
     */
    public function handle(): void
    {
        $days = (int) $this->option('days');

        $this->info("Membersihkan log yang lebih dari {$days} hari...");

        // TODO: Sesuaikan nama model log dengan yang digunakan proyek Anda
        // Jika menggunakan spatie/laravel-activitylog:
        //
        // $deleted = \Spatie\Activitylog\Models\Activity::where(
        //     'created_at', '<', now()->subDays($days)
        // )->delete();
        //
        // $this->info("Total {$deleted} log berhasil dihapus.");

        // Contoh: hapus file log Laravel di storage/logs yang lebih dari N hari
        $logPath = storage_path('logs');
        $deleted = 0;

        foreach (glob("{$logPath}/*.log") as $file) {
            if (filemtime($file) < now()->subDays($days)->timestamp) {
                unlink($file);
                $deleted++;
            }
        }

        $this->info("Total {$deleted} file log berhasil dihapus.");

        Log::info('[Scheduler] CleanOldLogs dijalankan pada '.now()." (hapus log > {$days} hari)");
    }
}
