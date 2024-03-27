<?php

namespace App\Http\Middleware;

use App\Constants\Status;
use App\Lib\CancelRide;
use App\Models\Ride;
use App\Models\RideCancel;
use App\Traits\RideCancelTrait;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class DriverRideCancelMiddleware
{
    use RideCancelTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $rideId = $request->route()->parameter('id');
        $driver = auth()->id();
        $cancel = RideCancel::where('driver_id', $driver)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        $cancelLimit = gs('ride_cancel_limit_driver');

        if ($cancelLimit === -1) {
            return $next($request);
        }

        $banDays = gs('ban_days');

        if ($cancel >= $cancelLimit) {
            $this->cancelRide($rideId,Status::DRIVER_TYPE, auth()->id(),$request->cancel_reason);
            $this->banDriver($driver, $cancelLimit ,$banDays);

            return response()->json([
                'message' => 'You have been banned from using the platform for ' . $banDays . ' days. You reached the maximum ride cancellation limit for this month.',
                'status' => 'error',
            ], 403);

        }
        return $next($request);
    }
}
