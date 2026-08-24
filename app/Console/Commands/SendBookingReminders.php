<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBookingReminders extends Command
{
    /**
     * Nama dan signature dari Artisan command.
     *
     * @var string
     */
    protected $signature = 'booking:send-reminders';

    /**
     * Deskripsi dari Artisan command.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi/reminder kepada user yang memiliki booking mendekati tanggal mulai sewa';

    /**
     * Jalankan command.
     */
    public function handle(): void
    {
        $this->info('Memulai pengiriman reminder booking...');

        // TODO: Sesuaikan nama model dan kolom dengan skema database Anda
        // Contoh: cari booking yang mulai besok dan belum dikirim reminder
        //
        // $tomorrow = now()->addDay()->toDateString();
        //
        // $bookings = \App\Models\Booking::with('user')
        //     ->whereDate('start_date', $tomorrow)
        //     ->where('status', 'confirmed')
        //     ->where('reminder_sent', false)
        //     ->get();
        //
        // foreach ($bookings as $booking) {
        //     // Kirim email reminder
        //     \Mail::to($booking->user->email)
        //         ->send(new \App\Mail\BookingReminderMail($booking));
        //
        //     // Tandai sudah dikirim
        //     $booking->update(['reminder_sent' => true]);
        // }
        //
        // $this->info("Reminder terkirim ke {$bookings->count()} user.");

        $this->info('Selesai. (Aktifkan logika di dalam command ini setelah model siap)');

        Log::info('[Scheduler] SendBookingReminders dijalankan pada '.now());
    }
}
