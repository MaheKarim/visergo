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
            $ride = Ride::where('driver_id', $driver->id)
                ->where('ride_request_type', Status::RIDE)
                ->where('status', Status::RIDE_INITIATED)
                ->first();

            if ($driver->is_driving == Status::IDLE && !$ride) {
                return $next($request);
            } else {
                if ($request->is('api/*')) {
                    $notify[] = 'You can not accept multiple ride requests at the same time';
                    return response()->json([
                        'remark'=>'unverified',
                        'status'=>'error',
                        'message'=>['error'=>$notify],
                        'data'=>[
                            'is_driving'=>$driver->is_driving,
                        ],
                    ]);
                }
            }
        }
        abort(403);
    }
}
