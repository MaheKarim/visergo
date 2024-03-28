<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\RewardPoints;
use App\Models\Deposit;
use App\Models\GatewayCurrency;
use App\Models\Ride;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function methods()
    {
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->active();
        })->with('method')->orderby('method_code')->get();
        $notify[] = 'Payment Methods';
        return response()->json([
            'remark' => 'deposit_methods',
            'message' => ['success' => $notify],
            'data' => [
                'methods' => $gatewayCurrency
            ],
        ]);
    }

    public function depositInsert(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|gt:0',
            'method_code' => 'required',
            'currency' => 'required',
            'ride_id' => 'required|exists:rides,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $user = auth()->user();
        $ride = Ride::where('user_id', $user->id)->ongoingRide()->paymentPending()->find($id);

        if ($ride == null) {
            $notify[] = 'Invalid ride request';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }

        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->where('method_code', $request->method_code)->where('currency', $request->currency)->first();
        if (!$gate) {
            $notify[] = 'Invalid gateway';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }
        $amount = $ride->total;
        if ($gate->min_amount > $amount || $gate->max_amount < $amount) {
            $notify[] =  'Please follow deposit limit';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }

        $charge = $gate->fixed_charge + ($amount * $gate->percent_charge / 100);
        $payable = $amount + $charge;
        $finalAmount = $payable * $gate->rate;
        $trx = getTrx();

        $data = new Deposit();
        $data->user_id = $user->id;
        $data->method_code = $gate->method_code;
        $data->method_currency = strtoupper($gate->currency);
        $data->amount = $amount;
        $data->ride_id = $ride->id;
        $data->charge = $charge;
        $data->rate = $gate->rate;
        $data->final_amount = $finalAmount;
        $data->btc_amount = 0;
        $data->btc_wallet = "";
        $data->trx = $trx;
        $data->save();

        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $finalAmount;
        $transaction->post_balance =  $finalAmount;
        $transaction->charge = $charge;
        $transaction->trx_type = '+';
        $transaction->details = 'Deposit Via '.$gate->name;
        $transaction->trx = $trx;
        $transaction->save();

        $totalPoints = RewardPoints::distribute($ride->id);

        $ride->payment_status = Status::PAYMENT_PENDING; // PAYMENT PENDING
        $ride->payment_type = Status::ONLINE_PAYMENT;
        $ride->status = Status::RIDE_COMPLETED;
        $ride->point = $totalPoints;
        $ride->save();
        //TODO:: Add Points Disbursement For Driver
        $notify[] =  'Deposit inserted';
        return response()->json([
            'remark'=>'deposit_inserted',
            'status'=>'success',
            'message'=>['success'=>$notify],
            'data'=>[
                'deposit' => $data,
                'redirect_url' => route('deposit.app.confirm', encrypt($data->id))
            ]
        ]);
    }

    public function method($id)
    {
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->active();
        })->with('method')->where('id', $id)->orderby('method_code')->get();
        $notify[] = 'Payment Methods';
        return response()->json([
            'remark' => 'deposit_methods',
            'message' => ['success' => $notify],
            'data' => [
                'methods' => $gatewayCurrency
            ],
        ]);
    }
}
