<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\CancellationReason;
use Illuminate\Http\Request;

class CancellationReasonController extends Controller
{
    public function index()
    {
        $pageTitle = 'Rider Cancellation Reason';
        $reasons = CancellationReason::where('for', Status::RIDER)->latest()->paginate(getPaginate());

        return view('admin.cancellation_reason.rider', compact('pageTitle', 'reasons'));
    }

    public function driver()
    {
        $pageTitle = 'Driver Cancellation Reason';
        $reasons = CancellationReason::where('for', Status::DRIVER)->latest()->paginate(getPaginate());

        return view('admin.cancellation_reason.driver', compact('pageTitle', 'reasons'));
    }

    public function store(Request $request, $id = 0)
    {
        $request->validate([
            'for' => 'required|numeric',
            'reason' => 'required|string',
        ]);

        if(!$id){
            $reason = new CancellationReason();
            $notification = 'Cancellation Reason added successfully';
        } else {
            $reason = CancellationReason::findOrFail($id);
            $notification = 'Cancellation Reason updated successfully';
        }

        $reason->for = $request->for;
        $reason->reason = $request->reason;
        $reason->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return CancellationReason::changeStatus($id);
    }
}
