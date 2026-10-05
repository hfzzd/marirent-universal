<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    $conversation = \App\Models\Conversation::find($conversationId);
    return $conversation && ($conversation->user_one_id === $user->id || $conversation->user_two_id === $user->id);
});

Broadcast::channel('online-users', function ($user) {
    if ($user) {
        return ['id' => $user->id, 'name' => $user->name];
    }
});