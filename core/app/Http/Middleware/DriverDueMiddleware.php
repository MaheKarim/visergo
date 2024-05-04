<?php

namespace App\Http\Middleware;

use App\Models\Driver;
use Closure;
use Illuminate\Http\Request;

class DriverDueMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $driver = auth()->id();
        $driver = Driver::find($driver);

        $dueLimit = gs('driver_min_due');

        if ($driver->balance >= $dueLimit) {
            return $next($request);
        } else {
            $notify[] = 'You reached the maximum due limit. Your balance is ' . showAmount($driver->balance) .  ' ' .gs('cur_text');
            return response()->json([
                'message' => $notify,
                'status' => 'error',
            ], 403);
        }
    }
}
