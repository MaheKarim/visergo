<?php

namespace App\Http\Controllers\Api\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverPasswordReset;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class DriverForgotPasswordController extends Controller
{
    public function sendResetCodeEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value'=>'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $fieldType = $this->findFieldType();
        $driver = Driver::where($fieldType, $request->value)->first();

        if (!$driver) {
            $notify[] = 'Couldn\'t find any account with this information';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }

        DriverPasswordReset::where('email', $driver->email)->delete();
        $code = verificationCode(6);
        $password = new DriverPasswordReset();
        $password->email = $driver->email;
        $password->token = $code;
        $password->created_at = \Carbon\Carbon::now();
        $password->save();

        $driverIpInfo = getIpInfo();
        $driverBrowserInfo = osBrowser();
        notify($driver, 'PASS_RESET_CODE', [
            'code' => $code,
            'operating_system' => @$driverBrowserInfo['os_platform'],
            'browser' => @$driverBrowserInfo['browser'],
            'ip' => @$driverIpInfo['ip'],
            'time' => @$driverIpInfo['time']
        ],['email']);

        $email = $driver->email;
        $response[] = 'Verification code sent to mail';
        return response()->json([
            'remark'=>'code_sent',
            'status'=>'success',
            'message'=>['success'=>$response],
            'data'=>[
                'email'=>$email
            ]
        ]);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'email' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }
        $code =  $request->code;

        if (DriverPasswordReset::where('token', $code)->where('email', $request->email)->count() != 1) {
            $notify[] = 'Verification code doesn\'t match';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }

        $response[] = 'You can change your password.';
        return response()->json([
            'remark'=>'verified',
            'status'=>'success',
            'message'=>['success'=>$response],
        ]);
    }

    public function reset(Request $request)
    {

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }


        $reset = DriverPasswordReset::where('token', $request->token)->orderBy('created_at', 'desc')->first();
        if (!$reset) {
            $response[] = 'Invalid verification code';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['success'=>$response],
            ]);
        }

        $driver = Driver::where('email', $reset->email)->first();
        $driver->password = bcrypt($request->password);
        $driver->save();



        $driverIpInfo = getIpInfo();
        $driverBrowser = osBrowser();
        notify($driver, 'PASS_RESET_DONE', [
            'operating_system' => @$driverBrowser['os_platform'],
            'browser' => @$driverBrowser['browser'],
            'ip' => @$driverIpInfo['ip'],
            'time' => @$driverIpInfo['time']
        ],['email']);


        $response[] = 'Password changed successfully';
        return response()->json([
            'remark'=>'password_changed',
            'status'=>'success',
            'message'=>['success'=>$response],
        ]);
    }

    protected function rules()
    {
        $passwordValidation = Password::min(6);
        $general = GeneralSetting::first();
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required','confirmed',$passwordValidation],
        ];
    }

    private function findFieldType()
    {
        $input = request()->input('value');

        $fieldType = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$fieldType => $input]);
        return $fieldType;
    }
}
