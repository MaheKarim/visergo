<?php

namespace App\Http\Controllers\Api\Driver;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Gateway\PaymentController as GatewayPaymentController;
use App\Lib\DriverPaymentManager;
use App\Models\Deposit;
use App\Models\Driver;
use App\Models\GatewayCurrency;
use App\Models\Ride;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public $paymentManager, $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            $this->paymentManager = new DriverPaymentManager($this->user, 'driver_id');
            return $next($request);
        });
    }

    public function addMoney(Request $request)
    {
        return $this->paymentManager->addMoney($request);
    }

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
    public function acceptCashPayment($id)
    {
        $ride = Ride::ongoingRide()->where('driver_id', auth()->id())->find($id);

        if($ride->payment_status != Status::PAYMENT_PENDING){
            $notify[] = 'Not eligible for payment';
            return response()->json(errorResponse('not_eligible',$notify));
        }

        if (!$ride) {
            $notify[] = 'The ride is invalid';
            return response()->json(errorResponse('ride_invalid',$notify));
        }

        $deposit = Deposit::where('ride_id', $ride->id)->orderBy('id', 'desc')->first();

        if (!$deposit) {
            $gateway = new GatewayCurrency();
            $gateway->manualGateway(Status::CASH_PAYMENT);

            if (!($gateway instanceof GatewayCurrency)) {
                return response()->json($gateway);
            }

            $deposit = new Deposit();
            $deposit->amount = $ride->total;
            $deposit->driver_id = $ride->driver_id;
            $deposit->ride_id = $ride->id;
            $deposit->detail = 'Cash Payment Accept by  ' . $ride->driver->fullName;
            $deposit->saveDeposit($gateway);

            $ride->payment_type = Status::CASH_PAYMENT;
            $ride->status = Status::RIDE_COMPLETED;
            $ride->is_cash_accept = Status::YES;
            $ride->save();

            $driver = Driver::find($ride->driver_id);
            $driver->is_driving = Status::IDLE;
            $driver->save();
        }

        if (@$deposit->status == Status::PAYMENT_SUCCESS) {
            $notify[] = 'Invalid request';
            return response()->json(errorResponse('invalid_request',$notify));
        }

        try {
            GatewayPaymentController::userDataUpdate($deposit);
        } catch (\Exception $e) {
            return response()->json([
                'remark'  => 'driver_data_update_error',
                'status'  => 'error',
                'message' => ['error' => $e->getMessage()],
            ]);
        }

        $notify[] = 'Payment accepted successfully';

        return response()->json([
            'remark'  => 'payment_accepted',
            'status'  => 'success',
            'message' => ['success' => $notify]
        ]);
    }
}
