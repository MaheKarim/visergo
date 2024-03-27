<?php

namespace App\Lib;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Driver;
use App\Models\Ride;

class RideChat {

    public static function sendMessage($rideId, $user, $message)
    {

        $ride = Ride::with('conversation')->find($rideId);

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
            if ($user instanceof Driver) {
                $conversationMessage->user_id = null;
                $conversationMessage->driver_id = auth()->user()->id;
            } else {
                $conversationMessage->user_id = auth()->user()->id;
                $conversationMessage->driver_id = null;
            }
            $conversationMessage->message = $message;
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

    public static function fullChat($id)
    {
        $ride = Ride::find($id);

        if ($ride === null) {
            return response()->json([
                'remark' => 'no_ride_found',
                'status' => 'error',
                'message' => 'Ride with the given ID was not found.',
            ], 404);
        }

        // Retrieve conversation ID associated with the ride
        $conversation = Conversation::where('ride_id', $ride->id)->first();

        if ($conversation === null) {
            return response()->json([
                'remark' => 'no_conversation_found',
                'status' => 'error',
                'message' => 'No conversation associated with the ride.',
            ], 404);
        }
        // Retrieve all messages associated with the conversation ID
        $messages = ConversationMessage::where('conversation_id', $conversation->id)->get();

        return response()->json([
            'remark' => 'messages_found',
            'status' => 'success',
            'messages' => $messages,
        ]);
    }
}
