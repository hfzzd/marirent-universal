<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboxMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'body',
        'is_read',
        'is_starred_sender',
        'is_starred_receiver',
        'is_trash_sender',
        'is_trash_receiver',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_starred_sender' => 'boolean',
        'is_starred_receiver' => 'boolean',
        'is_trash_sender' => 'boolean',
        'is_trash_receiver' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
