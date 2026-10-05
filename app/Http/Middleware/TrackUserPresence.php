<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserPresence
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $user = $request->user();
            $presenceKey = 'user_presence_' . $user->id;
            $wasOnline = cache($presenceKey);
            
            // Mark user as online
            cache($presenceKey, now(), now()->addMinutes(5));
            
            // Broadcast if user just came online
            if (!$wasOnline) {
                event(new \App\Events\UserOnlineStatus($user, true));
            }
        }

        return $next($request);
    }
}