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
            if ($driver->is_driving == Status::IDLE) {
                $ride = Ride::where('driver_id', $driver->id)
                    ->where('ride_request_type', Status::RIDE)
                    ->whereIn('status', [Status::RIDE_INITIATED, Status::RIDE_ACTIVE, Status::RIDE_ONGOING])
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
        abort(403);
    }
}
