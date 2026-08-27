<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class BookingCreated extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $itemName = $this->booking->vehicle?->name ?? $this->booking->category?->name ?? 'Unit Sewa';
        $bookingCode = $this->booking->booking_code;

        return new DatabaseMessage([
            'title' => 'Booking Baru Dibuat',
            'message' => "Booking baru {$bookingCode} untuk {$itemName} telah dibuat. Status: Menunggu konfirmasi.",
            'booking_code' => $bookingCode,
            'item_name' => $itemName,
            'new_status' => 'pending',
            'type' => 'booking_created',
            'url' => route('bookings.show', $this->booking),
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable)->data;
    }
}
