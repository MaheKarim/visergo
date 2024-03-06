<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Lib\FormProcessor;
use Illuminate\Http\Request;

class KycController extends Controller
{
    public function setting($act = null)
    {
        $pageTitle = 'KYC Verification Form';
        $form = Form::where('act', $act)->first();
        if ($act == 'kyc'){
            $formTitle = 'Rider Verification Form';
        } elseif ($act == 'driver_kyc'){
            $formTitle = 'Driver Verification Form';
        } else {
            $formTitle = 'Vehicle Verification Form';
        }
        return view('admin.kyc.setting',compact('pageTitle','form', 'act', 'formTitle'));
    }

    public function settingUpdate(Request $request, $act = null)
    {
        $formProcessor = new FormProcessor();
        $generatorValidation = $formProcessor->generatorValidation();
        $request->validate($generatorValidation['rules'],$generatorValidation['messages']);
        $exist = Form::where('act',$act)->first();
        if ($exist) {
            $isUpdate = true;
        }else{
            $isUpdate = false;
        }
        $formProcessor->generate($act, $isUpdate,'act');

        $notify[] = ['success','Verification data updated successfully'];
        return back()->withNotify($notify);
    }
}
