<?php

namespace App\Lib;

use App\Models\Deposit;
use App\Constants\Status;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\GatewayCurrency;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\Validator;

class DriverPaymentManager
{

    private $user;
    private $column;

    public function __construct($user, $column)
    {
        $this->user = $user;
        $this->column = $column;
    }

    public function addMoney(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'      => 'required|numeric|gt:0',
            'method_code' => 'required',
            'currency'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(errorResponse('validation_error',$validator->errors()->all()));
        }

        $gateway = GatewayCurrency::whereHas('method', function ($gateway) {
            $gateway->where('status', Status::ENABLE);
        })
            ->where('method_code', $request->method_code)
            ->where('currency', $request->currency)
            ->first();

        if (!$gateway) {
            return response()->json(errorResponse('invalid_gateway_selected','Invalid gateway selected'));
        }

        if ($gateway->min_amount > $request->amount || $gateway->max_amount < $request->amount) {
            return response()->json(errorResponse('min_limit_check','Please follow payment limit'));
        }
        $column = $this->column;

        $deposit = new Deposit();
        $deposit->$column = $this->user->id;
        $deposit->amount  = $request->amount;
        $deposit->saveDeposit($gateway);

        $notify[] =  'Add money request submitted';

        return response()->json([
            'remark'  => 'add_money_requested',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'add_money' => $deposit,
                'redirect_url' => route('deposit.app.confirm', encrypt($deposit->id))
            ]
        ]);
    }

    public function completeDriverPayment($deposit)
    {
        $user   = $this->user;
        $column = $this->column;
        $user_type = Str::remove('_id', $column);

        $user->balance += $deposit->amount;
        $user->save();

        $transaction               = new Transaction();
        $transaction->$column      = $user->id;
        $transaction->amount       = $deposit->amount;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = $deposit->charge;
        $transaction->trx_type     = '+';
        $transaction->trx          = $deposit->trx;
        $transaction->remark       = 'add_money';
        $transaction->details      = 'Add Money via ' . $deposit->gatewayCurrency()->name;
        $transaction->save();

        $adminNotification = new AdminNotification();
        $adminNotification->$column    = $user->id;
        $adminNotification->title     = 'Add money successful via ' . $deposit->gatewayCurrency()->name;
        $adminNotification->click_url = urlPath('admin.deposit.pending');
        $adminNotification->save();

        notify($user, $user_type == 'user' ? 'USER_ADD_MONEY' : 'DRIVER_ADD_MONEY', [
            'method_name'     => $deposit->gatewayCurrency()->name,
            'method_currency' => $deposit->method_currency,
            'method_amount'   => showAmount($deposit->final_amount),
            'amount'          => showAmount($deposit->amount),
            'charge'          => showAmount($deposit->charge),
            'rate'            => showAmount($deposit->rate),
            'trx'             => $deposit->trx,
            'post_balance'    => showAmount($user->balance)
        ]);
    }
}
