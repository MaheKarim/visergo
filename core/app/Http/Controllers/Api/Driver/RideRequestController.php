<?php

namespace App\Http\Controllers\Api\Driver;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\DriverPaymentDisbursement;
use App\Lib\RewardPoints;
use App\Models\Deposit;
use App\Models\Driver;
use App\Models\GatewayCurrency;
use App\Models\Ride;
use App\Traits\RideCancelTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RideRequestController extends Controller
{
    use RideCancelTrait;

    public function ongoingRequests()
    {
        $driver = auth()->user();
        $ride = Ride::where('driver_id', $driver->id)
            ->whereIn('status', [Status::RIDE_ACTIVE, Status::RIDE_ONGOING, Status::RIDE_END])->first();

        if ($ride == null) {
            return response()->json([
                'remark' => 'no_ride_found',
                'status' => 'error',
                'data' => [
                    'ride' => $ride
                ]
            ]);
        } else {
            return response()->json([
                'remark' => 'ride_found',
                'status' => 'success',
                'data' => [
                    'ride' => $ride
                ]
            ]);
        }
    }

    public function rideRequests()
    {
        $liveRequests = Ride::where('status', Status::RIDE_INITIATED)->with('destinations')->latest()->get();

        if ($liveRequests->isEmpty()) {
            return response()->json([
                'remark' => 'ride_requests',
                'status' => 'success',
                'message' => 'There are no ride requests at this moment.',
                'data' => []
            ]);
        }
        return response()->json([
            'remark' => 'ride_requests',
            'status' => 'success',
            'message' => 'Ride requests list',
            'data' => [
                'live_requests' => $liveRequests
            ]
        ]);
    }

    public function rideRequestAccept(Request $request, $id)
    {
        $driver = auth()->user();
        $ride = Ride::where('id', $id)->where('status', Status::RIDE_INITIATED)->first();
        if ($ride == null) {
            $notify = 'No Ride Found';
            return response()->json([
                'remark' => 'no_request_found',
                'status' => 'error',
                'message' => $notify,
                'data' => [
                    'ride' => $ride, $driver
                ]
            ]);
        }
        $ride->status = Status::RIDE_ACTIVE;
        $ride->driver_id = auth()->user()->id;
        $ride->save();

        $driver->is_driving = Status::DRIVING;
        $driver->save();
        $notify = 'Ride Accepted Successfully';
        return response()->json([
            'remark' => 'ride_request_accept',
            'status' => 'success',
            'message' => $notify,
            'data' => [
                'ride' => $ride
            ]
        ]);
    }

    public function rideRequestStart(Request $request, $id)
    {
        $driver = auth()->user();
        $ride = Ride::where('driver_id', $driver->id)
            ->where('status', Status::RIDE_ACTIVE)->find($id);

        if ($ride == null) {
            return response()->json([
                'remark' => 'no_request_found',
                'status' => 'error',
                'message' => [],
                'data' => [
                    'ride' => $ride
                ]
            ]);
        }

        $otp = $request->otp;
        if ($ride->otp != $otp) {
            return response()->json([
                'remark' => 'otp_mismatch',
                'status' => 'error',
                'message' => 'Invalid OTP',
                'data' => [
                    'otp' => $otp
                ]
            ]);
        } else {
            $ride->otp = null;
        }

        $ride->status = Status::RIDE_ONGOING;
        $ride->ride_start_at = Carbon::now();
        $ride->save();

        $notify[] = 'Ride Started Successfully';
        return response()->json([
            'remark' => 'ride_start',
            'status' => 'success',
            'message' => $notify,
            'data' => [
                'ride' => $ride
            ]
        ]);
    }

    public function rideRequestEnd(Request $request, $id)
    {
        $driver = auth()->user();
        $ride = Ride::where('driver_id', $driver->id)
            ->where('status', Status::RIDE_ONGOING)->find($id);

        if ($ride == null) {
            $notify[] = 'No Ride Found';
            return response()->json([
                'remark' => 'no_request_found',
                'status' => 'error',
                'message' => $notify,
                'data' => [
                    'ride' => $ride
                ]
            ]);
        }

        $ride->status = Status::RIDE_END;
        $ride->ride_completed_at = Carbon::now();
        $ride->payment_status = Status::PAYMENT_PENDING;
        $ride->save();

        $driver->is_driving = Status::IDLE;
        $driver->save();

        $notify[] = 'Ride End Successfully';
        return response()->json([
            'remark' => 'ride_complete',
            'status' => 'success',
            'message' => $notify,
            'data' => [
                'ride' => $ride
            ]
        ]);
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
            $notify[] = 'No Ride Found';
            return response()->json([
                'remark' => 'no_request_found',
                'status' => 'error',
                'message' => $notify,
                'data' => [
                    'ride' => $ride
                ]
            ]);
        }

        if ($ride->status == Status::RIDE_CANCELED) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ride Already Cancelled',
                'data' => $ride,
            ]);
        }

        if ($ride->status == Status::RIDE_ONGOING) {
            $notify = 'You can not cancel ongoing ride';
            return response()->json([
                'remark' => 'ride_cancel_fail',
                'status' => 'error',
                'message' => $notify,
            ]);
        }

        $this->cancelRide($ride->id, Status::DRIVER_TYPE, $driver->id, $request->cancel_reason);

        $notify[] = 'Ride Cancelled Successfully';
        return response()->json([
            'remark' => 'ride_cancel',
            'status' => 'success',
            'message' => $notify,
            'data' => [
                'ride' => $ride
            ]
        ]);
    }

    public function rideRequestCashAccept(Request $request, $id)
    {
        $ride = Ride::where('status', Status::RIDE_END)->where('payment_type', Status::CASH_PAYMENT)->find($id);

        if (!$ride) {
            $notify[] = 'No Ride Found';
            return response()->json([
                'remark' => 'no_request_found',
                'status' => 'error',
                'message' => $notify,
                'data' => [
                    'ride' => $ride
                ]
            ]);
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
        // Driver Balance & Points Disbursement
//        RewardPoints::distribute($ride->id);
//        DriverPaymentDisbursement::cashPaymentDisbursement($ride->id);

        $ride->payment_status = Status::PAYMENT_SUCCESS;
        $ride->is_cash_accept = Status::YES;
        $ride->status = Status::RIDE_COMPLETED;
        $ride->save();

        $driver = Driver::find($ride->driver_id);
        $driver->is_driving = Status::IDLE;
        $driver->save();

        $notify[] = 'Ride Completed Successfully';
        return response()->json([
            'remark' => 'ride_request_accept',
            'status' => 'success',
            'message' => $notify,
            'data' => [
                'ride' => $ride
            ]
        ]);

    }

}
