<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;

class DriverCheckStatus
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
            if ($driver->status  && $driver->ev  && $driver->sv  && $driver->tv) {
                return $next($request);
            } else {
                if ($request->is('api/*')) {
                    $notify[] = 'You need to verify your account first.';
                    return response()->json([
                        'remark'=>'unverified',
                        'status'=>'error',
                        'message'=>['error'=>$notify],
                        'data'=>[
                            'is_ban'=>$driver->status,
                            'email_verified'=>$driver->ev,
                            'mobile_verified'=>$driver->sv,
                            'twofa_verified'=>$driver->tv,
                        ],
                    ]);
                }else{
                    return to_route('driver.authorization');
                }
            }
        }
        abort(403);
    }
}
