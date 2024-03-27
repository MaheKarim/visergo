<?php

namespace App\Traits;

use App\Constants\Status;
use App\Lib\CancelRide;
use App\Models\Driver;
use App\Models\Ride;
use Illuminate\Support\Carbon;

trait RideCancelTrait
{
    protected function banDriver($driverId, $cancelLimit, $banDays)
    {
        $driver = auth()->user();
        $driver->current_status = Status::OFFLINE;
        $driver->ban_reason = 'You can not cancel more than ' . $cancelLimit . ' rides per month';
        $driver->ban_expire = Carbon::now()->addDays($banDays);
        $driver->status = Status::DRIVER_BAN;
        $driver->save();
    }
    protected function cancelRide($rideId, $driverId, $cancelReason)
    {
        CancelRide::ride($rideId, null, $driverId, $cancelReason);

        $ride = Ride::find($rideId);
        $ride->driver_id = null;
        $ride->status = Status::RIDE_INITIATED;
        $ride->save();

        Driver::updateIsDriving(auth()->id(), Status::IDLE);
    }

}
