<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{

    public function addresses()
    {
        $addresses = UserAddress::where('user_id', auth()->id())->get();

        return response()->json([
            'remark'=>'address',
            'status'=>'success',
            'data'=>$addresses,
        ]);
    }

    public function store(Request $request, $id = 0)
    {
        $validator = $this->validation($request);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        if($id) {
            $address = UserAddress::where('user_id', auth()->id())->find($id);

            if (!$address) {
                return response()->json([
                    'remark' => 'address_not_found',
                    'status' => 'error',
                    'message' => ['error' => 'Address not found'],
                ]);
            }
            $notify[] = 'Address updated successfully';
        }else{
            $address = new UserAddress();
            $notify[] = 'Address saved successfully';
        }

        $address->user_id = auth()->id();
        $address->address = $request->address;
        $address->title = $request->title;
        $address->longitude = $request->longitude;
        $address->latitude = $request->latitude;
        $address->additional_info = $request->additional_info;
        $address->save();

        return response()->json([
            'remark'=>'address_saved',
            'status'=>'success',
            'message'=>['success'=>$notify],
        ]);
    }
    public function delete($id)
    {
        $address = UserAddress::where('user_id', auth()->id())->find($id);

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
            'message'=>['success' => $notify],
        ]);
    }

    private function validation($request){
        return Validator::make($request->all(), [
            'address'=>'required',
            'title'=>'required',
            'longitude'=>'required',
            'latitude'=>'required',
            'additional_info'=>'nullable',
        ]);
    }

}
