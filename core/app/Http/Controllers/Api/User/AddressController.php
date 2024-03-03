<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function addressInsert(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'address'=>'required',
            'title'=>'required',
            'longitude'=>'required',
            'latitude'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $user = auth()->user();

        $address = new UserAddress();
        $address->user_id = $user->id;
        $address->address = $request->address;
        $address->title = strtoupper($request->title);
        $address->longitude = $request->longitude;
        $address->latitude = $request->latitude;
        $address->additional_info = $request->additional_info;
        $address->save();

        $notify[] = 'Address saved successfully';

        return response()->json([
            'remark'=>'address_saved',
            'status'=>'success',
            'message'=>['success'=>$notify],
        ]);
    }

    public function address()
    {
        $addresses = UserAddress::where('user_id',auth()->user()->id)->get();

        if ($addresses->isEmpty()) {
            $notify[] = 'No address found';
            return response()->json([
                'remark'=>'no_address',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }
        return response()->json([
            'remark'=>'address',
            'status'=>'success',
            'data'=>$addresses,
        ]);
    }

    public function addressUpdate(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'address'=>'required',
            'title'=>'required',
            'longitude'=>'required',
            'latitude'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $address = UserAddress::find($id);
        $address->address = $request->address;
        $address->title = strtoupper($request->title);
        $address->longitude = $request->longitude;
        $address->latitude = $request->latitude;
        $address->additional_info = $request->additional_info;
        $address->save();

        $notify[] = 'Address updated successfully';
        return response()->json([
            'remark'=>'address_updated',
            'status'=>'success',
            'message'=>['success'=>$notify],
        ]);
    }

    public function addressDelete($id)
    {
        $user = auth()->user();

        $address = UserAddress::where('user_id', $user->id)->find($id);

        if(!$address) {
            return response()->json([
                'remark'=>'address_not_found',
                'status'=>'error',
                'message'=>['error'=>'Address not found'],
            ]);
        }

        $address->delete();

        $notify[] = 'Address deleted successfully';

        return response()->json([
            'remark'=>'address_deleted',
            'status'=>'success',
            'message'=>['success'=>$notify],
        ]);
    }

}
