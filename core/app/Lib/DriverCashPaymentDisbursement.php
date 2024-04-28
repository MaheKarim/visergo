<?php

namespace App\Lib;

use App\Models\Ride;

class DriverCashPaymentDisbursement
{

    public static function balanceDisbursement($rideId)
    {
        $ride = Ride::find($rideId);
        $driver = $ride->driver;
        $driver->total_earning += $ride->driver_amount;
        $driver->balance -= ($ride->admin_commission + $ride->vat_amount);
        $driver->save();
    }
}
