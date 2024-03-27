<?php

namespace App\Http\Controllers\Api\Driver;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\RideChat;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Driver;
use App\Models\Ride;
use App\Models\RideCancel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RideRequestController extends Controller
{

    public function ongoingRequests()
    {
        $driver = auth()->user();
        $ride = Ride::where('driver_id', $driver->id)
            ->whereIn('status', [Status::RIDE_ACTIVE, Status::RIDE_ONGOING])->first();
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
        $liveRequests = Ride::where('status', Status::RIDE_INITIATED)->latest()->get();

        if ($liveRequests->isEmpty()) {
            return response()->json([
                'remark' => 'ride_requests',
                'status' => 'success',
                'message' => 'There are no ride requests at this moment.',
                'data' => []
            ]);
        }
        return response()->json([
            'remark'=>'ride_requests',
            'status'=>'success',
            'message'=>[],
            'data'=>[
                'live_requests'=>$liveRequests
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
            ->where('service_id', Status::RIDE_SERVICE)
            ->where('status', Status::RIDE_ACTIVE)->first();

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

        return response()->json([
            'remark' => 'ride_start',
            'status' => 'success',
            'message' => [],
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
            return response()->json([
                'remark' => 'no_request_found',
                'status' => 'error',
                'message' => [],
                'data' => [
                    'ride' => $ride
                ]
            ]);
        } else {
            $ride->status = Status::RIDE_END;
            $ride->ride_completed_at = Carbon::now();
            $ride->payment_status = Status::PAYMENT_PENDING;
            $ride->save();

            $driver->is_driving = Status::IDLE;
            $driver->save();
            return response()->json([
                'remark' => 'ride_complete',
                'status' => 'success',
                'message' => [],
                'data' => [
                    'ride' => $ride
                ]
            ]);
        }
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
        $cancelRide = Ride::where('driver_id', $driver->id)->where('status',[Status::RIDE_ACTIVE])->find($id);

        if ($cancelRide == null) {
            return response()->json([
                'remark' => 'no_request_found',
                'status' => 'error',
                'message' => [],
                'data' => [
                    'ride' => $cancelRide
                ]
            ]);
        }

        if ($cancelRide->status == Status::RIDE_CANCELED) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ride Already Cancelled',
                'data' => $cancelRide,
            ]);
        }

        if ($cancelRide->status == Status::RIDE_ONGOING) {
            $notify = 'You can not cancel ongoing ride';
            return response()->json([
               'remark' => 'ride_cancel_fail',
               'status' => 'error',
               'message' => $notify,
            ]);
        }
        $cancelRide->driver_id = null;
        $cancelRide->status = Status::RIDE_INITIATED;
        $cancelRide->save();

        $rideCancel = new RideCancel();
        $rideCancel->ride_id = $cancelRide->id;
        $rideCancel->user_id = null;
        $rideCancel->driver_id = $driver->id;
        $rideCancel->cancel_reason = $request->cancel_reason;
        $rideCancel->ride_canceled_at = now();
        $rideCancel->save();

        $driver->is_driving = Status::IDLE;
        $driver->save();

        return response()->json([
            'remark' => 'ride_cancel',
            'status' => 'success',
            'message' => [],
            'data' => [
                'ride' => $cancelRide
            ]
        ]);
    }

}
