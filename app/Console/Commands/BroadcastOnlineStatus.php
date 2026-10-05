<?php

namespace App\Console\Commands;

use App\Events\UserOnlineStatus;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class BroadcastOnlineStatus extends Command
{
    protected $signature = 'presence:check';
    protected $description = 'Check and broadcast user online/offline status';

    public function handle(): int
    {
        $users = User::where('is_active', true)->get();
        $checked = 0;

        foreach ($users as $user) {
            $presenceKey = 'user_presence_' . $user->id;
            $lastSeen = Cache::get($presenceKey);
            
            if ($lastSeen) {
                // User is online
                event(new UserOnlineStatus($user, true));
                $this->info("User {$user->name} is online");
            } else {
                // User is offline
                event(new UserOnlineStatus($user, false));
                $this->info("User {$user->name} is offline");
            }
            $checked++;
        }

        $this->info("Checked {$checked} users");
        return Command::SUCCESS;
    }
}