<?php

namespace App\Http\Middleware;

use App\Constants\Status;
use Closure;
use Illuminate\Support\Facades\Auth;

class DriverOnlineStatus
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
            if ($driver->current_status == Status::ONLINE) {
                return $next($request);
            } else {
                if ($request->is('api/*')) {
                    $notify[] = 'You need to be online';
                    return response()->json([
                        'remark'=>'unverified',
                        'status'=>'error',
                        'message'=>['error'=>$notify],
                        'data'=>[
                            'current_status'=>$driver->current_status,
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
