<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\GatewayCurrency;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Gateway\PaymentController as GatewayPaymentController;

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


    public function initPayment(Request $request)
    {
        $validator = Validator::make($request->all(), $this->paymentValidation());

        if ($validator->fails()) {
            return response()->json(errorResponse('payment_validation_error', $validator->errors()->first()), 422);
        }

        $ride = Ride::where('user_id', auth()->id())->ongoingRide()->paymentPending()->find($request->ride_id);

        if ($ride == null) {
            return response()->json(errorResponse('ride_not_found', 'No Ride Found'), 404);
        }

        $amount = $request->tips ? $ride->total + $request->tips : $ride->total;
        /* Coupon Disbursement Task Incomplete */
        $gateway = $this->paymentGateway($request, $amount, $ride);

        if (!$gateway instanceof GatewayCurrency) {
            return response()->json($gateway, 422);
        }
        $deposit = new Deposit();
        $deposit->user_id = $ride->user_id;
        $deposit->ride_id = $ride->id;
        $deposit->amount = $amount;
        $deposit->detail = 'Payment sent by ' . $ride->user->fullName;
        $deposit->saveDeposit($gateway);

        $ride->payment_type = $request->payment_type;
        $ride->save();

        if ($request->payment_type == Status::CASH_PAYMENT) {
            return $this->cashPayment();
        }  else {
            return $this->gatewayPayment($deposit);
        }
    }

    private function paymentValidation()
    {
        $paymentTypes = implode(',', [Status::CASH_PAYMENT, Status::ONLINE_PAYMENT, Status::WALLET_PAYMENT]);

        return [
            'payment_type' => 'required|in:' . $paymentTypes,
            'method_code'  => 'required_if:payment_type,2',
            'currency'     => 'required_if:payment_type,2',
            'ride_id'      => 'required',
        ];
    }

    private function paymentGateway($request, $amount, $deposit)
    {
        if ($request->payment_type == Status::CASH_PAYMENT) {
            $gateway = new GatewayCurrency();
            $gateway->manualGateway(Status::CASH_PAYMENT);
            // Call the userUpdate method
            GatewayPaymentController::userDataUpdate($deposit);
        } else {
            $gateway = GatewayCurrency::whereHas('method', function ($gateway) {
                $gateway->where('status', Status::ENABLE);
            })->where('method_code', $request->method_code)->where('currency', $request->currency)->first();
            if (!$gateway) {
                return errorResponse('invalid_gateway_selected', 'Invalid gateway selected');
            }

            if ($gateway->min_amount > $amount) {
                return errorResponse('min_limit_check', 'Minimum limit for this gateway is ' . $gateway->min_amount);
            }

            if ($gateway->max_amount < $amount) {
                return errorResponse('max_limit_check', 'Maximum limit for this gateway is ' . $gateway->max_amount);
            }
        }

        return $gateway;
    }

    private function cashPayment()
    {
        $notify[] =  'Cash payment request placed successfully';

        return response()->json([
            'remark'  => 'cash_payment',
            'status'  => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    private function gatewayPayment($deposit)
    {
        $notify[] =  'Payment inserted';
        return response()->json([
            'remark'  => 'payment_inserted',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'payment' => $deposit,
                'redirect_url' => route('deposit.app.confirm', encrypt($deposit->id))
            ]
        ]);
    }

}
