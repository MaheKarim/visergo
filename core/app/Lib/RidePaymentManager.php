<?php

namespace App\Lib;

use App\Constants\Status;
use App\Models\Transaction;

class RidePaymentManager
{
    private $deposit;
    private $ride;
    private $driver;

    public function __construct($deposit)
    {
        $this->deposit = $deposit;
        $this->ride = $deposit->ride;
        $this->driver = $deposit->ride->driver;
        $this->completeRidePayment();
    }

    public function completeRidePayment()
    {
        $deposit                    = $this->deposit;
        $ride                       = $deposit->ride;

//        if ($ride->payment_type == Status::ONLINE_PAYMENT) {
//            $ride->status = Status::RIDE_COMPLETED;
//        } else {
//            $ride->status = Status::RIDE_END;
//        }

        $ride->status = Status::RIDE_COMPLETED;

        $ride->payment_status       = Status::PAYMENT_SUCCESS;
        $ride->ride_completed_at    = now();

        $totalPoint = RewardPoints::distribute($ride->id);

        $ride->point = $totalPoint;
        $ride->save();


        if($deposit->method_code == Status::CASH_PAYMENT){
            $this->completeCashPayment();
        }

        if ($deposit->method_code == Status::WALLET_PAYMENT) {
            $this->completeWalletPayment($deposit, $ride);
        }

        if ($deposit->method_code == Status::ONLINE_PAYMENT) {
            $this->completeOnlinePayment();
        }

        $this->payAdminCommission();

    }

    public function completeCashPayment()
    {
        $driver = $this->driver;
        $ride = $this->ride;

        DriverPaymentDisbursement::cashPaymentDisbursement($ride->id);

        $transaction               = new Transaction();
        $transaction->user_id      = $ride->user->id;
        $transaction->amount       = $ride->total;
        $transaction->post_balance = $driver->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '+';
        $transaction->trx          = $this->deposit->trx;
        $transaction->remark       = 'ride_fee';
        $transaction->details      = 'Ride fee received';
        $transaction->save();

        notify($driver, 'RIDE_FEE_RECEIVE', [
            'ride_uid'        => $ride->uuid,
            'ride_amount'     => showAmount($ride->amount),
            'pickup_location' => $ride->pickup_location,
            'destination'     => $ride->destination,
            'completed_at'    => showDateTime($ride->completed_at, 'd M Y i:s A'),
            'post_balance'    => showAmount($driver->balance),
            'trx'             => $this->deposit->trx,
        ]);
    }

    private function completeOnlinePayment()
    {
        $driver = $this->driver;
        $ride = $this->ride;

        DriverPaymentDisbursement::onlinePaymentDisbursement($ride->id);

        $transaction               = new Transaction();
        $transaction->driver_id    = $driver->id;
        $transaction->amount       = $ride->total;
        $transaction->post_balance = $driver->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '+';
        $transaction->trx          = $this->deposit->trx;
        $transaction->remark       = 'ride_fee';
        $transaction->details      = 'Ride fee received';
        $transaction->save();

        notify($driver, 'RIDE_FEE_RECEIVE', [
            'ride_uid'        => $ride->uuid,
            'ride_amount'     => showAmount($ride->amount),
            'pickup_location' => $ride->pickup_location,
            'destination'     => $ride->destination,
            'completed_at'    => showDateTime($ride->completed_at, 'd M Y i:s A'),
            'post_balance'    => showAmount($driver->balance),
            'trx'             => $this->deposit->trx,
        ]);
    }

    private function payAdminCommission()
    {
        $driver = $this->driver;
        $ride = $this->ride;

        $driver->balance -= $ride->admin_commission;
        $driver->balance -= $ride->vat_amount;
        $driver->save();

        $transaction               = new Transaction();
        $transaction->driver_id    = $driver->id;
        $transaction->amount       = $ride->admin_commission + $ride->vat_amount;
        $transaction->post_balance = $driver->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '-';
        $transaction->trx          = $this->deposit->trx;
        $transaction->remark       = 'ride_commission';
        $transaction->details      = 'Ride commission paid';
        $transaction->save();

        notify($driver, 'RIDE_COMMISSION_GIVEN', [
            'ride_uid'       => $ride->uuid,
            'ride_amount'     => showAmount($ride->total),
            'commission'      => showAmount($ride->admin_commission),
            'pickup_location' => $ride->pickup_location,
            'destination'     => $ride->destination,
            'completed_at'    => showDateTime($ride->completed_at, 'd M Y i:s A'),
            'post_balance'    => showAmount($driver->balance),
            'trx'             => $this->deposit->trx,
        ]);
    }

    private function completeWalletPayment($deposit, $ride)
    {
        $rider = $deposit->user;
        $rider->balance -= $deposit->amount;
        $rider->save();

        notify($rider, 'WALLET_RIDE_PAYMENT', [
            'uuid'            => $ride->uuid,
            'ride_amount'     => showAmount($ride->amount),
            'pickup_location' => $ride->pickup_location,
            'destination'     => $ride->destination,
            'completed_at'    => showDateTime($ride->completed_at, 'd M Y i:s A'),
            'post_balance'    => showAmount($rider->balance),
            'trx'             => $this->deposit->trx,
        ]);
    }
}
