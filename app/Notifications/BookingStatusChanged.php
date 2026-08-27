<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class BookingStatusChanged extends Notification
{
    use Queueable;

    protected string $oldStatus;
    protected string $newStatus;

    public function __construct(Booking $booking, string $oldStatus, string $newStatus)
    {
        $this->booking = $booking;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $itemName = $this->booking->vehicle?->name ?? $this->booking->category?->name ?? 'Unit Sewa';
        $bookingCode = $this->booking->booking_code;

        $statusLabels = [
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'ongoing' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $oldLabel = $statusLabels[$this->oldStatus] ?? ucfirst($this->oldStatus);
        $newLabel = $statusLabels[$this->newStatus] ?? ucfirst($this->newStatus);

        $messages = [
            'confirmed' => "Booking {$bookingCode} ({$itemName}) telah dikonfirmasi. Silakan lakukan pembayaran atau persiapan.",
            'ongoing' => "Booking {$bookingCode} ({$itemName}) telah dimulai. Perjalanan sedang berjalan.",
            'completed' => "Booking {$bookingCode} ({$itemName}) telah selesai. Terima kasih!",
            'cancelled' => "Booking {$bookingCode} ({$itemName}) telah dibatalkan.",
            'pending' => "Booking {$bookingCode} ({$itemName}) menunggu konfirmasi.",
        ];

        return new DatabaseMessage([
            'title' => 'Status Booking Diubah',
            'message' => $messages[$this->newStatus] ?? "Status booking {$bookingCode} berubah dari {$oldLabel} ke {$newLabel}.",
            'booking_code' => $bookingCode,
            'item_name' => $itemName,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'type' => 'booking_status_changed',
            'url' => route('bookings.show', $this->booking),
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable)->data;
    }
}
