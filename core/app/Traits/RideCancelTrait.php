<?php

namespace App\Traits;

use App\Constants\Status;
use App\Lib\CancelRide;
use App\Models\RideCancel;
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

    protected function cancelRide($rideId, $type, $id, $reason)
    {

        $cancel = new RideCancel();
        $cancel->ride_id = $rideId;
        if ($type == Status::USER_TYPE) {
            $cancel->user_id = $id;
        } else {
            $cancel->driver_id = $id;
        }
        $cancel->cancel_reason = $reason;
        $cancel->ride_canceled_at = now();
        $cancel->save();

        if ($type == Status::USER_TYPE) {
            $ride = Ride::find($rideId);
            $ride->status = Status::RIDE_CANCELED;
            $ride->save();
        }

        if ($type == Status::DRIVER_TYPE) {
            $ride = Ride::find($rideId);
            $ride->status = Status::RIDE_INITIATED;
            $ride->driver_id = null;
            $ride->save();
            Driver::updateIsDriving(auth()->id());
        }
    }

}
