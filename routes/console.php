<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes / Scheduler
|--------------------------------------------------------------------------
|
| Di sini Anda mendefinisikan semua jadwal (scheduled tasks) untuk proyek
| marirent. Laravel akan menjalankan scheduler ini setiap menit via cron:
|
|   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
|
| Untuk testing di local, jalankan:
|   php artisan schedule:work
|
*/

/**
 * Kirim reminder booking kepada user setiap hari pukul 08:00 pagi.
 */
Schedule::command('booking:send-reminders')
    ->dailyAt('08:00')
    ->name('send-booking-reminders')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

/**
 * Perbarui status booking yang sudah expired setiap hari tengah malam.
 */
Schedule::command('booking:update-expired')
    ->dailyAt('00:05')
    ->name('update-expired-bookings')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

/**
 * Bersihkan file log lama (lebih dari 30 hari) setiap minggu pada hari Minggu.
 */
Schedule::command('logs:clean --days=30')
    ->weekly()
    ->sundays()
    ->at('02:00')
    ->name('clean-old-logs')
    ->appendOutputTo(storage_path('logs/scheduler.log'));
