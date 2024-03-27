<?php

namespace App\Lib;

use App\Models\Ride;
use App\Models\RideCancel;
use Carbon\Carbon;

class CancelRide
{
    public static function ride($rideId, $userId, $driverId, $cancelReason)
    {
        $rideCancel = new RideCancel();
        $rideCancel->ride_id = $rideId;
        $rideCancel->user_id = $userId;
        $rideCancel->driver_id = $driverId;
        $rideCancel->cancel_reason = $cancelReason;
        $rideCancel->ride_canceled_at = now();
        $rideCancel->save();

    }
}
