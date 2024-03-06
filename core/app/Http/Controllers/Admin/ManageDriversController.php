<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\NotificationLog;
use App\Models\Transaction;
use App\Models\Driver;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ManageDriversController extends Controller
{
    public function allDrivers()
    {
        $pageTitle = 'All Drivers';
        $drivers = $this->driverData();
        return view('admin.drivers.list', compact('pageTitle', 'drivers'));
    }

    public function activeDrivers()
    {
        $pageTitle = 'Active Drivers';
        $drivers = $this->driverData('active');
        return view('admin.drivers.list', compact('pageTitle', 'drivers'));
    }

    public function bannedDrivers()
    {
        $pageTitle = 'Banned Drivers';
        $drivers = $this->driverData('banned');
        return view('admin.drivers.list', compact('pageTitle', 'drivers'));
    }

    public function emailUnverifiedDrivers()
    {
        $pageTitle = 'Email Unverified Drivers';
        $drivers = $this->driverData('emailUnverified');
        return view('admin.drivers.list', compact('pageTitle', 'drivers'));
    }

    public function kycUnverifiedDrivers()
    {
        $pageTitle = 'KYC Unverified Drivers';
        $drivers = $this->driverData('kycUnverified');
        return view('admin.drivers.list', compact('pageTitle', 'drivers'));
    }

    public function mobileUnverifiedDrivers()
    {
        $pageTitle = 'Mobile Unverified Drivers';
        $drivers = $this->driverData('mobileUnverified');
        return view('admin.drivers.list', compact('pageTitle', 'drivers'));
    }

    public function kycPendingDrivers()
    {
        $pageTitle = 'KYC Unverified Drivers';
        $drivers = $this->driverData('kycPending');
        return view('admin.drivers.list', compact('pageTitle', 'drivers'));
    }

    public function emailVerifiedDrivers()
    {
        $pageTitle = 'Email Verified Drivers';
        $drivers = $this->driverData('emailVerified');
        return view('admin.drivers.list', compact('pageTitle', 'drivers'));
    }


    public function mobileVerifiedDrivers()
    {
        $pageTitle = 'Mobile Verified Drivers';
        $drivers = $this->driverData('mobileVerified');
        return view('admin.drivers.list', compact('pageTitle', 'drivers'));
    }


    public function driversWithBalance()
    {
        $pageTitle = 'Drivers with Balance';
        $drivers = $this->driverData('withBalance');
        return view('admin.drivers.list', compact('pageTitle', 'drivers'));
    }


    protected function driverData($scope = null){
        if ($scope) {
            $drivers = Driver::$scope();
        }else{
            $drivers = Driver::query();
        }
        return $drivers->searchable(['username','email'])->orderBy('id','desc')->paginate(getPaginate());
    }


    public function detail($id)
    {
        $driver = Driver::findOrFail($id);
        $pageTitle = 'Driver Detail - '.$driver->username;

        $totalDeposit = Deposit::where('driver_id',$driver->id)->where('status',Status::PAYMENT_SUCCESS)->sum('amount');
        $totalWithdrawals = Withdrawal::where('driver_id',$driver->id)->where('status',Status::PAYMENT_SUCCESS)->sum('amount');
        $totalTransaction = Transaction::where('driver_id',$driver->id)->count();
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        return view('admin.drivers.detail', compact('pageTitle', 'driver','totalDeposit','totalWithdrawals','totalTransaction','countries'));
    }


    public function kycDetails($id)
    {
        $pageTitle = 'KYC Details';
        $driver = Driver::findOrFail($id);
        return view('admin.drivers.kyc_detail', compact('pageTitle','driver'));
    }

    public function kycApprove($id)
    {
        $driver = Driver::findOrFail($id);
        $driver->kv = 1;
        $driver->save();

        notify($driver,'KYC_APPROVE',[]);

        $notify[] = ['success','KYC approved successfully'];
        return to_route('admin.drivers.kyc.pending')->withNotify($notify);
    }

    public function kycReject($id)
    {
        $driver = Driver::findOrFail($id);
        foreach ($driver->kyc_data as $kycData) {
            if ($kycData->type == 'file') {
                fileManager()->removeFile(getFilePath('verify').'/'.$kycData->value);
            }
        }
        $driver->kv = 0;
        $driver->kyc_data = null;
        $driver->save();

        notify($driver,'KYC_REJECT',[]);

        $notify[] = ['success','KYC rejected successfully'];
        return to_route('admin.drivers.kyc.pending')->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);
        $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryArray   = (array)$countryData;
        $countries      = implode(',', array_keys($countryArray));

        $countryCode    = $request->country;
        $country        = $countryData->$countryCode->country;
        $dialCode       = $countryData->$countryCode->dial_code;

        $request->validate([
            'firstname' => 'required|string|max:40',
            'lastname' => 'required|string|max:40',
            'email' => 'required|email|string|max:40|unique:drivers,email,' . $driver->id,
            'mobile' => 'required|string|max:40|unique:drivers,mobile,' . $driver->id,
            'country' => 'required|in:'.$countries,
        ]);
        $driver->mobile = $dialCode.$request->mobile;
        $driver->country_code = $countryCode;
        $driver->firstname = $request->firstname;
        $driver->lastname = $request->lastname;
        $driver->email = $request->email;
        $driver->address = [
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => @$country,
        ];
        $driver->ev = $request->ev ? Status::VERIFIED : Status::UNVERIFIED;
        $driver->sv = $request->sv ? Status::VERIFIED : Status::UNVERIFIED;
        $driver->ts = $request->ts ? Status::ENABLE : Status::DISABLE;
        if (!$request->kv) {
            $driver->kv = 0;
            if ($driver->kyc_data) {
                foreach ($driver->kyc_data as $kycData) {
                    if ($kycData->type == 'file') {
                        fileManager()->removeFile(getFilePath('verify').'/'.$kycData->value);
                    }
                }
            }
            $driver->kyc_data = null;
        }else{
            $driver->kv = 1;
        }
        $driver->save();

        $notify[] = ['success', 'Driver details updated successfully'];
        return back()->withNotify($notify);
    }

    public function addSubBalance(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'act' => 'required|in:add,sub',
            'remark' => 'required|string|max:255',
        ]);

        $driver = Driver::findOrFail($id);
        $amount = $request->amount;
        $trx = getTrx();

        $transaction = new Transaction();

        if ($request->act == 'add') {
            $driver->balance += $amount;

            $transaction->trx_type = '+';
            $transaction->remark = 'balance_add';

            $notifyTemplate = 'BAL_ADD';

            $notify[] = ['success', gs('cur_sym') . $amount . ' added successfully'];

        } else {
            if ($amount > $driver->balance) {
                $notify[] = ['error', $driver->username . ' doesn\'t have sufficient balance.'];
                return back()->withNotify($notify);
            }

            $driver->balance -= $amount;

            $transaction->trx_type = '-';
            $transaction->remark = 'balance_subtract';

            $notifyTemplate = 'BAL_SUB';
            $notify[] = ['success', gs('cur_sym') . $amount . ' subtracted successfully'];
        }

        $driver->save();

        $transaction->driver_id = $driver->id;
        $transaction->amount = $amount;
        $transaction->post_balance = $driver->balance;
        $transaction->charge = 0;
        $transaction->trx =  $trx;
        $transaction->details = $request->remark;
        $transaction->save();

        notify($driver, $notifyTemplate, [
            'trx' => $trx,
            'amount' => showAmount($amount),
            'remark' => $request->remark,
            'post_balance' => showAmount($driver->balance)
        ]);

        return back()->withNotify($notify);
    }

    public function login($id){
        Auth::loginUsingId($id);
        return "We are working under water, Please search on Sky!";
    }

    public function status(Request $request,$id)
    {
        $driver = Driver::findOrFail($id);
        if ($driver->status == Status::DRIVER_ACTIVE) {
            $request->validate([
                'reason'=>'required|string|max:255'
            ]);
            $driver->status = Status::DRIVER_BAN;
            $driver->ban_reason = $request->reason;
            $notify[] = ['success','Driver banned successfully'];
        }else{
            $driver->status = Status::DRIVER_ACTIVE;
            $driver->ban_reason = null;
            $notify[] = ['success','Driver unbanned successfully'];
        }
        $driver->save();
        return back()->withNotify($notify);

    }

    public function showNotificationSingleForm($id)
    {
        $driver = Driver::findOrFail($id);
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning','Notification options are disabled currently'];
            return to_route('admin.drivers.detail',$driver->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $driver->username;
        return view('admin.drivers.notification_single', compact('pageTitle', 'driver'));
    }

    public function sendNotificationSingle(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'subject' => 'required|string',
        ]);

        $driver = Driver::findOrFail($id);
        notify($driver,'DEFAULT',[
            'subject'=>$request->subject,
            'message'=>$request->message,
        ]);
        $notify[] = ['success', 'Notification sent successfully'];
        return back()->withNotify($notify);
    }

    public function showNotificationAllForm()
    {
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning','Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }
        $notifyToDriver = Driver::notifyToDriver();
        $drivers = Driver::active()->count();
        $pageTitle = 'Notification to Verified Drivers';
        return view('admin.drivers.notification_all', compact('pageTitle','drivers','notifyToDriver'));
    }

    public function sendNotificationAll(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'message'                      => 'required',
            'subject'                      => 'required',
            'start'                        => 'required',
            'batch'                        => 'required',
            'being_sent_to'                => 'required',
            'user'                         => 'required_if:being_sent_to,selectedUsers',
            'number_of_top_deposited_user' => 'required_if:being_sent_to,topDepositedUsers|integer|gte:0',
            'number_of_days'               => 'required_if:being_sent_to,notLoginUsers|integer|gte:0',
        ], [
            'number_of_days.required_if'               => "Number of days field is required",
            'number_of_top_deposited_user.required_if' => "Number of top deposited user field is required",
        ]);

        if ($validator->fails()) return response()->json(['error' => $validator->errors()->all()]);

        $scope = $request->being_sent_to;
        $drivers = Driver::oldest()->active()->$scope()->skip($request->start)->limit($request->batch)->get();
        foreach ($drivers as $user) {
            notify($user, 'DEFAULT', [
                'subject' => $request->subject,
                'message' => $request->message,
            ]);
        }
        return response()->json([
            'total_sent' => $drivers->count(),
        ]);
    }

    public function list()
    {
        $query = Driver::active();

        if (request()->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', '%' . request()->search . '%')->orWhere('username', 'like', '%' . request()->search . '%');
            });
        }
        $drivers = $query->orderBy('id', 'desc')->paginate(getPaginate());
        return response()->json([
            'success' => true,
            'drivers'   => $drivers,
            'more'    => $drivers->hasMorePages()
        ]);
    }

    public function notificationLog($id){
        $driver = Driver::findOrFail($id);
        $pageTitle = 'Notifications Sent to '.$driver->username;
        $logs = NotificationLog::where('driver_id',$id)->with('driver')->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle','logs','driver'));
    }
}
