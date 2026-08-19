<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckActive
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->is_active) {
            auth()->logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact administrator.',
                ], 403);
            }

            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact administrator.');
        }

        return $next($request);
    }
}
