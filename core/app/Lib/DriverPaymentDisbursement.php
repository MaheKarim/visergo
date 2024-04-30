<?php

namespace App\Lib;

use App\Models\Ride;

class DriverPaymentDisbursement
{

    public static function cashPaymentDisbursement($rideId)
    {
        $ride = Ride::find($rideId);
        $driver = $ride->driver;
        $driver->total_earning += $ride->driver_amount;
        $driver->balance -= ($ride->admin_commission + $ride->vat_amount);
        $driver->save();
    }

    public static function onlinePaymentDisbursement($rideId)
    {
        $ride = Ride::find($rideId);
        $driver = $ride->driver;
        $driver->total_earning += $ride->driver_amount;
        $driver->balance += $ride->driver_amount;
        $driver->save();
    }
}
