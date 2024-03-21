<?php

namespace App\Http\Middleware;

use App\Constants\Status;
use App\Models\Ride;
use Closure;
use Illuminate\Support\Facades\Auth;

class DrivingCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $driver = auth()->user();
            if ($driver->is_driving != Status::DRIVING) {
                $ride = Ride::where('driver_id', $driver->id)
                    ->where('service_id', $request->service_id)
                    ->whereIn('status', [Status::RIDE_ACTIVE, Status::RIDE_ONGOING, Status::RIDE_END])
                    ->first();

                if (!$ride) {
                    return $next($request);
                } else {
                    if ($request->is('api/*')) {
                        $notify[] = 'You cannot accept multiple ride requests at the same time';
                        return response()->json([
                            'remark' => 'unverified',
                            'status' => 'error',
                            'message' => ['error' => $notify],
                            'data' => [
                                'is_driving' => $driver->is_driving,
                            ],
                        ]);
                    }
                }
            }
        }
        $notify[] = 'You can not accept ride requests at the moment';
        return response()->json([
            'remark' => 'unverified',
            'status' => 'error',
            'message' => ['error' => $notify],
            'data' => [
                'is_driving' => $driver->is_driving,
            ],
        ], 403);
    }
}
