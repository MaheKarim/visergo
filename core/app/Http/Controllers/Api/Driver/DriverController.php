<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\Form;
use App\Models\GeneralSetting;
use App\Models\Transaction;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class DriverController extends Controller
{
    public function driverDataSubmit(Request $request)
    {
        $driver = auth()->user();

        if ($driver->profile_complete == 1) {
            $notify[] = 'You\'ve already completed your profile';
            return response()->json([
                'remark'=>'already_completed',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }
        $validator = Validator::make($request->all(), [
            'firstname'=>'required',
            'lastname'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $driver->firstname = $request->firstname;
        $driver->lastname = $request->lastname;
        $driver->address = [
            'country'=>@$driver->address->country,
            'address'=>$request->address,
            'state'=>$request->state,
            'zip'=>$request->zip,
            'city'=>$request->city,
        ];
        $driver->profile_complete = 1;
        $driver->save();

        $notify[] = 'Profile completed successfully';
        return response()->json([
            'remark'=>'profile_completed',
            'status'=>'success',
            'message'=>['success'=>$notify],
        ]);
    }

    public function verificationForm()
    {
        if (auth()->user()->dv == 2) {
            $notify[] = 'Your verification is under review';
            return response()->json([
                'remark'=>'under_review',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }
        if (auth()->user()->dv == 1) {
            $notify[] = 'You are already verified';
            return response()->json([
                'remark'=>'already_verified',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }
        $form = Form::where('act','driver_kyc')->first();
        $notify[] = 'Verification field is below';
        return response()->json([
            'remark'=>'kyc_form',
            'status'=>'success',
            'message'=>['success'=>$notify],
            'data'=>[
                'form'=>$form->form_data
            ]
        ]);
    }

    public function verificationFormSubmit(Request $request)
    {
        $form = Form::where('act','driver_kyc')->first();
        $formData = $form->form_data;
        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);

        $validator = Validator::make($request->all(), $validationRule);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $driverData = $formProcessor->processFormData($request, $formData);
        $driver = auth()->user();
        $driver->driver_verification = $driverData;
        $driver->dv = 2;

        // Add validation rule for license_image field
        $validationRule['license_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'; // Adjust max file size as needed
        $validationRule['license_number'] = 'required|string';
        $validationRule['license_expire'] = ['required', 'date', 'after:today'];
        $validator = Validator::make($request->all(), $validationRule);
        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        if ($request->hasFile('license_image')) {
            try {
                $old = $driver->license_image;
                $driver->license_image = fileUploader($request->license_image, getFilePath('licenseImage'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return response()->json([
                    'remark' => 'validation_error',
                    'status' => 'error',
                    'message' => ['error' => $notify],
                ]);
            }
        }


        $driver->license_number = $request->license_number;
        $driver->license_expire = $request->license_expire;
        $driver->save();

        $notify[] = 'Driver Verification data submitted successfully';
        return response()->json([
            'remark'=>'kyc_submitted',
            'status'=>'success',
            'message'=>['success'=>$notify],
        ]);

    }

    public function vehicleVerification()
    {
        if (auth()->user()->vv == 2) {
            $notify[] = 'Your vehicle verification is under review';
            return response()->json([
                'remark'=>'under_review',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }
        if (auth()->user()->vv == 1) {
            $notify[] = 'You are already verified';
            return response()->json([
                'remark'=>'already_verified',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }
        $form = Form::where('act','vehicle_kyc')->first();
        $notify[] = 'Verification field is below';
        return response()->json([
            'remark'=>'kyc_form',
            'status'=>'success',
            'message'=>['success'=>$notify],
            'data'=>[
                'form'=>$form->form_data
            ]
        ]);
    }

    public function vehicleVerificationSubmit(Request $request)
    {

        $form = Form::where('act','vehicle_kyc')->first();
        $formData = $form->form_data;
        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);

        $validator = Validator::make($request->all(), $validationRule);
        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $driverData = $formProcessor->processFormData($request, $formData);


        $driver = auth()->user();
        $driver->vehicle_verification = $driverData;
        $driver->vv = 2;


        $vehicles = VehicleModel::active()->with('colors')->get();
        // Assuming you want to display the vehicle data in JSON response
        $vehicleData = [];
        foreach ($vehicles as $vehicle) {
            $vehicleData[] = [
                'brand' => $vehicle->brand->name,
                'name' => $vehicle->name,
                'year' => $vehicle->year,
                'vehicleType' => $vehicle->vehicleType->name,
                'vehicleClass' => $vehicle->vehicleClass->name,
                'colors' => $vehicle->colors->pluck('name')->toArray(),
            ];
        }
        $vehicle = new Vehicle();
        $vehicle->driver_id = $driver->id;
        $vehicle->brand_id = $request->brand_id;
        $vehicle->name = $request->name;
        $vehicle->year = $request->year;
        $vehicle->vehicle_type_id = $request->vehicle_type_id;
        $vehicle->vehicle_class_id = $request->vehicle_class_id;
        $vehicle->color_id = $request->color_id;
        $vehicle->save();
        $driver->save();

        $notify[] = 'Vehicle verification data submitted successfully';
        return response()->json([
            'remark'=>'kyc_submitted',
            'status'=>'success',
            'message'=>['success'=>$notify],
        ]);
    }

    public function depositHistory(Request $request)
    {
        $deposits = auth()->user()->deposits();
        if ($request->search) {
            $deposits = $deposits->where('trx',$request->search);
        }
        $deposits = $deposits->with(['gateway'])->orderBy('id','desc')->paginate(getPaginate());
        $notify[] = 'Deposit data';
        return response()->json([
            'remark'=>'deposits',
            'status'=>

                'success',
            'message'=>['success'=>$notify],
            'data'=>[
                'deposits'=>$deposits
            ]
        ]);
    }

    public function transactions(Request $request)
    {
        $remarks = Transaction::distinct('remark')->get('remark');
        $transactions = Transaction::where('driver_id',auth()->id());

        if ($request->search) {
            $transactions = $transactions->where('trx',$request->search);
        }


        if ($request->type) {
            $type = $request->type == 'plus' ? '+' : '-';
            $transactions = $transactions->where('trx_type',$type);
        }

        if ($request->remark) {
            $transactions = $transactions->where('remark',$request->remark);
        }

        $transactions = $transactions->orderBy('id','desc')->paginate(getPaginate());
        $notify[] = 'Transactions data';
        return response()->json([
            'remark'=>'transactions',
            'status'=>'success',
            'message'=>['success'=>$notify],
            'data'=>[
                'transactions'=>$transactions,
                'remarks'=>$remarks,
            ]
        ]);
    }

    public function submitProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname'=>'required',
            'lastname'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $driver = auth()->user();

        $driver->firstname = $request->firstname;
        $driver->lastname = $request->lastname;
        $driver->address = [
            'country'=>@$driver->address->country,
            'address'=>$request->address,
            'state'=>$request->state,
            'zip'=>$request->zip,
            'city'=>$request->city,
        ];
        $driver->save();

        $notify[] = 'Profile updated successfully';
        return response()->json([
            'remark'=>'profile_updated',
            'status'=>'success',
            'message'=>['success'=>$notify],
        ]);
    }

    public function submitPassword(Request $request)
    {
        $passwordValidation = Password::min(6);
        $general = GeneralSetting::first();
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => ['required','confirmed',$passwordValidation]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $driver = auth()->user();
        if (Hash::check($request->current_password, $driver->password)) {
            $password = Hash::make($request->password);
            $driver->password = $password;
            $driver->save();
            $notify[] = 'Password changed successfully';
            return response()->json([
                'remark'=>'password_changed',
                'status'=>'success',
                'message'=>['success'=>$notify],
            ]);
        } else {
            $notify[] = 'The password doesn\'t match!';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }
    }
}
