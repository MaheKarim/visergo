<?php

namespace App\Lib;

use App\Models\Driver;
use App\Models\Ride;
use App\Models\Transaction;

class DriverOnlinePaymentTransaction {


    public static function disbursement($rideId, $deposit)
    {
        $ride = Ride::find($rideId);
        $driver = Driver::find($ride->driver_id);
        $driver->balance += $ride->driver_amount;
        $driver->save();

        $transaction = new Transaction();
        $transaction->driver_id = $driver->id;
        $transaction->amount = $ride->driver_amount;
        $transaction->post_balance = $driver->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '+';
        $transaction->details = 'Deposit For ' . $ride->id;
        $transaction->trx = $deposit->trx;
        $transaction->remark = 'deposit';
        $transaction->save();

        if ($ride->tips != null) {
            $driver->balance += $ride->tips;
            $driver->save();

            $transaction = new Transaction();
            $transaction->driver_id = $driver->id;
            $transaction->amount = $ride->tips;
            $transaction->post_balance = $driver->balance;
            $transaction->charge = 0;
            $transaction->trx_type = '+';
            $transaction->details = 'Deposit Tips For ' . $ride->id;
            $transaction->trx = $deposit->trx;
            $transaction->remark = 'deposit';
            $transaction->save();
        }
        // Make a Notification
    }
}
