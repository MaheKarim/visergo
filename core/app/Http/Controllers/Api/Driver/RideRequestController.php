<?php

namespace App\Http\Controllers\Api\Driver;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Driver;
use App\Models\GatewayCurrency;
use App\Models\Ride;
use App\Traits\RideCancelTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Gateway\PaymentController as GatewayPaymentController;

class RideRequestController extends Controller
{
    use RideCancelTrait;

    public function ongoingRequests()
    {
        $driver = auth()->user();
        $ride = Ride::where('driver_id', $driver->id)
            ->whereIn('status', [Status::RIDE_ACTIVE, Status::RIDE_ONGOING, Status::RIDE_END])
            ->with(['destinations', 'user'])
            ->first();

        if ($ride == null) {
            return formatResponse('no_ride_found', 'error', 'No ride found');
        } else {
            return formatResponse('ride_found', 'success', 'Ride found', $ride);
        }
    }

    public function rideRequests()
    {
        $liveRequests = Ride::where('status', Status::RIDE_INITIATED)->with('destinations')->latest()->get();

        if ($liveRequests->isEmpty()) {

            return formatResponse('no_ride_requests', 'error', 'There are no ride requests at this moment');
        }

        return formatResponse('ride_requests', 'success', 'Ride requests list', $liveRequests);
    }

    public function rideRequestAccept(Request $request, $id)
    {
        $driver = auth()->user();
        $ride = Ride::where('id', $id)->where('status', Status::RIDE_INITIATED)->first();
        if ($ride == null) {
            $notify = 'No Ride Found';
            return formatResponse('ride_request_fail', 'error', $notify);
        }
        $ride->status = Status::RIDE_ACTIVE;
        $ride->driver_id = auth()->id();
        $ride->save();

        $driver->is_driving = Status::DRIVING;
        $driver->save();

        $notify = 'Ride Accepted Successfully';
        return formatResponse('ride_request_accept', 'success', $notify, $ride);
    }

    public function rideRequestStart(Request $request, $id)
    {
        $driver = auth()->user();
        $ride = Ride::where('driver_id', $driver->id)
            ->where('status', Status::RIDE_ACTIVE)->find($id);

        if ($ride == null) {
            return formatResponse('no_ride_found', 'error', 'No ride found');
        }

        $otp = $request->otp;
        if ($ride->otp != $otp) {
            return formatResponse('otp_mismatch', 'error', 'Invalid OTP');
        } else {
            $ride->otp = null;
        }

        $ride->status = Status::RIDE_ONGOING;
        $ride->ride_start_at = Carbon::now();
        $ride->save();

        $notify[] = 'Ride started successfully';
        return formatResponse('ride_start', 'success', $notify, $ride);
    }

    public function rideRequestEnd(Request $request, $id)
    {
        $driver = auth()->user();
        $ride = Ride::where('driver_id', $driver->id)
            ->where('status', Status::RIDE_ONGOING)->find($id);

        if ($ride == null) {
            $notify[] = 'No Ride Found';
            return formatResponse('no_request_found', 'error', $notify);
        }

        $ride->status = Status::RIDE_END;
        $ride->ride_completed_at = Carbon::now();
        $ride->payment_status = Status::PAYMENT_PENDING;
        $ride->save();

        $driver->is_driving = Status::IDLE;
        $driver->save();

        $notify[] = 'Ride end successfully';
        return formatResponse('ride_end', 'success', $notify, $ride);
    }

    public function rideRequestCancel(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cancel_reason' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'ride_cancel_fail',
                'status' => 'error',
                'message' => $validator->errors(),
            ]);
        }

        $driver = auth()->user();
        $ride = Ride::where('driver_id', $driver->id)->where('status', [Status::RIDE_ACTIVE])->find($id);

        if ($ride == null) {
            $notify[] = 'No ride found';
            return formatResponse('no_request_found', 'error', $notify);
        }

        if ($ride->status == Status::RIDE_CANCELED) {

            return formatResponse('ride_cancel', 'error', 'Ride Already Cancelled', $ride);
        }

        if ($ride->status == Status::RIDE_ONGOING) {

            return formatResponse('ride_cancel', 'error', 'You can not cancel ongoing ride', $ride);
        }

        $this->cancelRide($ride->id, Status::DRIVER_TYPE, $driver->id, $request->cancel_reason);

        $notify[] = 'Ride Cancelled Successfully';

        return formatResponse('ride_cancel', 'success', $notify, $ride);
    }

    public function rideRequestCashAccept(Request $request, $id)
    {
        $ride = Ride::where('status', Status::RIDE_END)->find($id);

        if (!$ride) {
            return formatResponse('no_request_found', 'error', 'No ride found');
        }

        $deposit = Deposit::where('ride_id', $ride->id)->orderBy('id', 'desc')->first();

        if (!$deposit) {
            $gateway = new GatewayCurrency();
            $gateway->manualGateway(Status::CASH_PAYMENT);

            if (!($gateway instanceof GatewayCurrency)) {
                return response()->json($gateway, 422);
            }

            $deposit = new Deposit();
            $deposit->driver_id = $ride->driver_id;
            $deposit->ride_id = $ride->id;
            $deposit->amount = $ride->total;
            $deposit->detail = 'Cash Payment Accept by  ' . $ride->driver->fullName;
            $deposit->saveDeposit($gateway);
        }

        $ride->payment_status = Status::PAYMENT_SUCCESS;
        $ride->payment_type = Status::CASH_PAYMENT;
        $ride->is_cash_accept = Status::YES;
        $ride->status = Status::RIDE_COMPLETED;
        $ride->save();

        $driver = Driver::find($ride->driver_id);
        $driver->is_driving = Status::IDLE;
        $driver->save();


        try {
            GatewayPaymentController::userDataUpdate($deposit);
        } catch (\Exception $e) {

            return formatResponse('driver_data_update_error', 'error', $e->getMessage());
        }

        $notify[] = 'Payment Accepted Successfully';

        return formatResponse('ride_request_accept', 'success', $notify, $ride);

    }

}
