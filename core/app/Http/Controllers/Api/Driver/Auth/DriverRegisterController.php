<?php

namespace App\Http\Controllers\Api\Driver\Auth;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverLogin;
use App\Models\GeneralSetting;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class DriverRegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('driver.guest');
        $this->middleware('registration.status');
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $general = GeneralSetting::first();
        $passwordValidation = Password::min(6);
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }
        $agree = 'nullable';
        if ($general->agree) {
            $agree = 'required';
        }
        $countryData = (array)json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes = implode(',',array_column($countryData, 'dial_code'));
        $countries = implode(',',array_column($countryData, 'country'));
        $validate = Validator::make($data, [
            'email' => 'required|string|email|unique:drivers|max:255',
            'mobile' => 'required|regex:/^([0-9]*)$/',
            'password' => ['required','confirmed',$passwordValidation],
            'username' => 'required|alpha_num|unique:drivers|min:6',
            'captcha' => 'sometimes|required',
            'mobile_code' => 'required|in:'.$mobileCodes,
            'country_code' => 'required|in:'.$countryCodes,
            'country' => 'required|in:'.$countries,
            'agree' => $agree
        ]);
        return $validate;
    }


    public function register(Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        if(preg_match("/[^a-z0-9_]/", trim($request->username))){
            $response[] = 'No special character, space or capital letters in username.';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$response],
            ]);
        }

        $exist = Driver::where('mobile',$request->mobile_code.$request->mobile)->first();
        if ($exist) {
            $response[] = 'The mobile number already exists';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$response],
            ]);
        }

        $driver = $this->create($request->all());

        $response['access_token'] =  $driver->createToken('auth_token')->plainTextToken;
        $response['driver'] = $driver;
        $response['token_type'] = 'Bearer';
        $notify[] = 'Registration successful';
        return response()->json([
            'remark'=>'registration_success',
            'status'=>'success',
            'message'=>['success'=>$notify],
            'data'=>$response
        ]);

    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $general = GeneralSetting::first();

        $referBy = @$data['reference'];
        if ($referBy) {
            $referUser = Driver::where('username', $referBy)->first();
        } else {
            $referUser = null;
        }
        // Driver Create
        $driver = new Driver();
        $driver->email = strtolower($data['email']);
        $driver->password = Hash::make($data['password']);
        $driver->username = $data['username'];
        $driver->ref_by = $referUser ? $referUser->id : 0;
        $driver->country_code = $data['country_code'];
        $driver->mobile = $data['mobile_code'].$data['mobile'];
        $driver->address = [
            'address' => '',
            'state' => '',
            'zip' => '',
            'country' => isset($data['country']) ? $data['country'] : null,
            'city' => ''
        ];
        $driver->dv = $general->dv ? Status::UNVERIFIED : Status::VERIFIED;
        $driver->vv = $general->vv ? Status::UNVERIFIED : Status::VERIFIED;
        $driver->ev = $general->ev ? Status::UNVERIFIED : Status::VERIFIED;
        $driver->sv = $general->sv ? Status::UNVERIFIED : Status::VERIFIED;
        $driver->ts = 0;
        $driver->tv = 1;
        $driver->save();


//        $adminNotification = new AdminNotification();
//        $adminNotification->driver_id = $driver->id;
//        $adminNotification->title = 'New driver registered';
//        $adminNotification->click_url = urlPath('admin.drivers.detail',$driver->id);
//        $adminNotification->save();


        //Login Log Create
        $ip = getRealIP();
        $exist = DriverLogin::where('driver_ip',$ip)->first();
        $driverLogin = new DriverLogin();

        //Check exist or not
        if ($exist) {
            $driverLogin->longitude =  $exist->longitude;
            $driverLogin->latitude =  $exist->latitude;
            $driverLogin->city =  $exist->city;
            $driverLogin->country_code = $exist->country_code;
            $driverLogin->country =  $exist->country;
        }else{
            $info = json_decode(json_encode(getIpInfo()), true);
            $driverLogin->longitude =  @implode(',',$info['long']);
            $driverLogin->latitude =  @implode(',',$info['lat']);
            $driverLogin->city =  @implode(',',$info['city']);
            $driverLogin->country_code = @implode(',',$info['code']);
            $driverLogin->country =  @implode(',', $info['country']);
        }

        $driverAgent = osBrowser();
        $driverLogin->driver_id = $driver->id;
        $driverLogin->driver_ip =  $ip;

        $driverLogin->browser = @$driverAgent['browser'];
        $driverLogin->os = @$driverAgent['os_platform'];
        $driverLogin->save();

        return $driver;
    }

}
