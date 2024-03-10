<?php

namespace App\Http\Controllers\Api\Driver;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Ride;
use Illuminate\Http\Request;

class RideRequestController extends Controller
{
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

     public function rideRequestAccept(Request $request, $id = 0): \Illuminate\Http\JsonResponse
    {
        $driver = auth()->user();
        $ride = Ride::where('id',$request->id)->where('status',Status::RIDE_INITIATED)->first();
        if (!$ride) {
            return response()->json([
                'remark'=>'ride_request_accept',
                'status'=>'error',
                'message'=>[],
                'data'=>[
                    'ride'=>$ride
                ]
            ]);
        }
        $ride->status = Status::RIDE_ACTIVE;
        $ride->driver_id = auth()->user()->id;
        $ride->save();
        $driver->is_driving = Status::DRIVING;
        $driver->save();
        return response()->json([
            'remark'=>'ride_request_accept',
            'status'=>'success',
            'message'=>[],
            'data'=>[
                'ride'=>$ride
            ]
        ]);
    }
}
