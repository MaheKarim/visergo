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

        return formatResponse('address_list', 'success', 'Address list', $addresses);
    }
    public function store(Request $request, $id = 0)
    {
        $validator = $this->validation($request);

        if ($validator->fails()) {
            return formatResponse('validation_error', 'error', $validator->errors()->all(), []);
        }

        if($id) {
            $address = UserAddress::where('user_id', auth()->id())->find($id);

            if (!$address) {
                return formatResponse('address_not_found', 'error', 'Address not found', []);
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

        return formatResponse('address_saved', 'success', $notify, $address);
    }
    public function delete($id)
    {
        $address = UserAddress::where('user_id', auth()->id())->find($id);

        if(!$address) {
            return formatResponse('address_not_found', 'error', 'Address not found', []);
        }

        $address->delete();

        return formatResponse('address_deleted', 'success', 'Address deleted successfully', []);
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
