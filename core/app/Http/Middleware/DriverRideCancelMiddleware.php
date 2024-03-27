<?php

namespace App\Http\Middleware;

use App\Constants\Status;
use App\Lib\CancelRide;
use App\Models\Ride;
use App\Models\RideCancel;
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
        $rideId = $request->route()->parameter('id');
        $driver = auth()->user();
        $cancel = RideCancel::where('driver_id', $driver->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        $cancelLimit = gs('ride_cancel_limit_driver');

        if ($cancelLimit === -1) {
            return $next($request);
        }

        $banDays = gs('ban_days');

        if ($cancel >= $cancelLimit) {

            if ($cancel == $cancelLimit) {
                // If $ride is equal to $cancelLimit
                CancelRide::ride($rideId, null, $driver->id, $request->cancel_reason);
                $ride = Ride::find($rideId);
                $ride->status = Status::RIDE_INITIATED;
                $ride->driver_id = null;
                $ride->save();

                $driver->is_driving = Status::IDLE;
                $driver->current_status = Status::OFFLINE;
                $driver->ban_reason = 'You can not cancel more than ' . gs('ride_cancel_limit_driver') . ' rides per month';
                $driver->ban_expire = Carbon::now()->addDays($banDays);
                $driver->status = Status::DRIVER_BAN;
                $driver->save();

                return response()->json([
                    'message' => 'You have been banned from using the platform for ' . $banDays . ' days. You reached the maximum ride cancellation limit for this month.',
                    'status' => 'error',
                ], 403);
            }
            else {
                // If $ride is greater than $cancelLimit
                return response()->json([
                    'message' => 'You have exceeded the maximum ride cancellation limit for this month.',
                    'status' => 'error',
                ], 403);
            }
        }
        return $next($request);
    }
}
