<?php

namespace App\Http\Middleware;

use App\Constants\Status;
use App\Models\Driver;
use App\Models\Ride;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class DriverRideCancelMiddleware
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
        $driver = auth()->user();

        $ride = Ride::where('driver_id', $driver->id)
            ->where('cancel_by_driver', $driver->id) // true
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        $cancelLimit = gs('ride_cancel_limit_driver');
        $banDays = gs('ban_days');

        if ($ride >= $cancelLimit) {
            if ($ride == $cancelLimit) {
                $driver->status = Status::USER_BAN;
                $driver->ban_reason = 'You can not cancel more than  ' . gs('ride_cancel_limit_driver') . ' rides per month';
                $driver->ban_expire = Carbon::now()->days($banDays);
                $driver->save();

                return $next($request);
            }
        }
    }
}
