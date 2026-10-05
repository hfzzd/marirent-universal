<?php

namespace App\Notifications;

use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewChatMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ChatMessage $chatMessage) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $senderName = $this->chatMessage->sender->name;
        return (new MailMessage)
            ->subject("Pesan Baru dari {$senderName} - MariRent")
            ->greeting("Halo {$notifiable->name},")
            ->line("Anda menerima pesan baru dari {$senderName} di MariRent Messenger.")
            ->line('"' . substr($this->chatMessage->message, 0, 100) . '..."')
            ->action('Balas Pesan', route('chat.index', ['conversation_id' => $this->chatMessage->conversation_id]))
            ->line('Terima kasih telah menggunakan layanan kami!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Pesan Chat Baru',
            'message' => "Pesan baru dari {$this->chatMessage->sender->name}",
            'conversation_id' => $this->chatMessage->conversation_id,
            'type' => 'chat_message',
            'url' => route('chat.index', ['conversation_id' => $this->chatMessage->conversation_id]),
        ];
    }
}