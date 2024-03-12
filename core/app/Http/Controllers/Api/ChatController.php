<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\RideChat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function sendRideChat(Request $request, $id)
    {
        $driver = auth()->user();
        $message = $request->input('message');

        $result = RideChat::sendMessage($id, $driver, $message);

        return response()->json([
            'remark' => 'remark',
            'status' => 'success',
            'message' => $result,
        ]);
    }

    public function rideChatMessages($id)
    {
        return RideChat::fullChat($id);
    }
}
