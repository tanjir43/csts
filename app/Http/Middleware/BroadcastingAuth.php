<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BroadcastingAuth
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            auth()->setUser($request->user());
        }

        return $next($request);
    }
}
