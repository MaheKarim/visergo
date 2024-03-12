<?php

namespace App\Http\Controllers\Api\Driver;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Ride;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
            ->where('ride_request_type', Status::RIDE)
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

    public function rideChat(Request $request, $id)
    {
        $driver = auth()->user();

        $ride = Ride::where('ride_request_type', Status::RIDE)
            ->whereIn('status', [Status::RIDE_ACTIVE, Status::RIDE_ONGOING])
            ->where('driver_id', $driver->id)
            ->with('conversation')
            ->find($id);

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
           $conversation = $ride->conversation;
           if(!$ride->conversation) {
                $conversation = new Conversation();
                $conversation->ride_id= $ride->id;
                $conversation->user_id = $ride->user_id;
                $conversation->driver_id = $ride->driver_id;
                $conversation->save();
           }
            // Create a new conversation message
           $conversationMessage = new ConversationMessage();
           $conversationMessage->conversation_id = $conversation->id;
           $conversationMessage->user_id = null;
           $conversationMessage->driver_id = $ride->driver_id;
           $conversationMessage->message = $request->input('message');
           $conversationMessage->save();

           $notify = 'Message Sent Successfully';
            return response()->json([
                'remark' => 'ride_chat_sent',
                'status' => 'success',
                'message' => $notify,
                'data' => [
                    'msg' => $conversationMessage
                ]
            ]);
        }
    }

    public function rideChatMessages($id)
    {
        // Find the ride
        $ride = Ride::find($id);

        if ($ride === null) {
            return response()->json([
                'remark' => 'no_ride_found',
                'status' => 'error',
                'message' => 'Ride with the given ID was not found.',
            ], 404);
        }

        // Retrieve conversation ID associated with the ride
        $conversationId = Conversation::where('ride_id', $ride->id)->first();

        if ($conversationId === null) {
            return response()->json([
                'remark' => 'no_conversation_found',
                'status' => 'error',
                'message' => 'No conversation associated with the ride.',
            ], 404);
        }

        // Retrieve all messages associated with the conversation ID
        $messages = ConversationMessage::where('conversation_id', $conversationId->id)->get();

        return response()->json([
            'remark' => 'messages_found',
            'status' => 'success',
            'messages' => $messages,
        ]);
    }
}
