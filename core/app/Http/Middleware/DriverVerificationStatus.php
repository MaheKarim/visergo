<?php

namespace App\Http\Middleware;

use App\Constants\Status;
use Closure;
use Illuminate\Support\Facades\Auth;

class DriverVerificationStatus
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $driver = auth()->user();
            $general = gs();
            if (($general->dv == Status::KYC_UNVERIFIED || $general->dv == Status::KYC_PENDING) || ($general->vv == Status::KYC_UNVERIFIED || $general->vv == Status::KYC_PENDING)
                && $request->is('api/*')
                && ($driver->dv == 0 || $driver->dv == 2)
                && ($driver->vv == 0 || $driver->vv == 2)
            ) {
                $notify = [];
                if ($driver->dv == 0) {
                    $notify[] = 'Verify your driving licence';
                }
                if ($driver->dv == 2) {
                    $notify[] = 'Your driving licence has been pending';
                }
                if ($driver->vv == 0) {
                    $notify[] = 'Verify your vehicle registration';
                }
                if ($driver->vv == 2) {
                    $notify[] = 'Your vehicle registration has been pending';
                }
                return response()->json([
                    'remark' => 'driver_vehicle_verification',
                    'status' => 'error',
                    'message' => ['error' => $notify],
                    'data' => [
                        'is_ban' => $driver->status,
                        'driver_verified' => $driver->dv,
                        'vehicle_verified' => $driver->vv,
                    ],
                ]);
            }

            return $next($request);
        }
        abort(403);
    }
}

