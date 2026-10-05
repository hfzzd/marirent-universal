<?php

namespace App\Jobs;

use App\Events\UserOnlineStatus;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CheckOfflineUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $users = User::where('is_active', true)->get();
        
        foreach ($users as $user) {
            $presenceKey = 'user_presence_' . $user->id;
            $lastSeen = Cache::get($presenceKey);
            
            // If no activity for 5 minutes, mark as offline
            if (!$lastSeen) {
                event(new UserOnlineStatus($user, false));
            }
        }
    }
}