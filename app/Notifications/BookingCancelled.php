<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking, ?string $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $itemName = $this->booking->vehicle?->name ?? $this->booking->category?->name ?? 'Unit Sewa';
        $bookingCode = $this->booking->booking_code;

        return new DatabaseMessage([
            'title' => 'Booking Dibatalkan',
            'message' => "Booking {$bookingCode} ({$itemName}) telah dibatalkan."
                . ($this->reason ? " Alasan: {$this->reason}" : ''),
            'booking_code' => $bookingCode,
            'item_name' => $itemName,
            'old_status' => 'ongoing',
            'new_status' => 'cancelled',
            'type' => 'booking_cancelled',
            'url' => route('bookings.show', $this->booking),
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable)->data;
    }
}
