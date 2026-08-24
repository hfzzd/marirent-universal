<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany();
    }

    public function otherUser($currentUserId = null)
    {
        $currentUserId = $currentUserId ?? auth()->id();
        return $this->user_one_id == $currentUserId ? $this->userTwo : $this->userOne;
    }

    public static function findOrCreateBetween($userAId, $userBId)
    {
        $one = min($userAId, $userBId);
        $two = max($userAId, $userBId);

        return self::firstOrCreate(
            ['user_one_id' => $one, 'user_two_id' => $two],
            ['last_message_at' => now()]
        );
    }
}
