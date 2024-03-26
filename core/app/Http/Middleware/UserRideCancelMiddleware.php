<?php

namespace App\Http\Middleware;

use App\Models\Ride;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserRideCancelMiddleware
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
        if (Auth::check()) {
            $user = auth()->user();

            $cancelCount = Ride::where('user_id', $user->id)
                ->where('cancel_by_user', $user->id) // true
                ->whereMonth('created_at', Carbon::now()->month)
                ->count();

            $cancelLimit = gs('ride_cancel_limit_user');

            if ($cancelLimit === -1) {
                return $next($request);
            }

            if ($cancelCount >= gs('ride_cancel_limit_user')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You can not cancel more than '.gs('ride_cancel_limit_user').' rides per month.',
                ]);

            } else {
                return $next($request);
            }
        } else {
            abort(403);
        }
    }
}
