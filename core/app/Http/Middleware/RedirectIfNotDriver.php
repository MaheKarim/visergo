<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotDriver
{
    public function handle($request, Closure $next, $guard = 'driver')
    {
        if (!Auth::guard($guard)->check()) {
            return to_route('driver.login');
        }

        return $next($request);
    }
}
