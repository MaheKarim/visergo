<?php

namespace App\Http\Controllers\Api\Driver;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Gateway\PaymentController as GatewayPaymentController;
use App\Lib\DriverPaymentManager;
use App\Models\Deposit;
use App\Models\Driver;
use App\Models\GatewayCurrency;
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
    public function addMoney(Request $request){
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

}
