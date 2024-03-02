<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CancellationReason;
use Illuminate\Http\Request;

class CancellationReasonController extends Controller
{
    public function index()
    {
        $pageTitle = 'Cancellation Reason';
        $reasons = CancellationReason::latest()->paginate(getPaginate());

        return view('admin.cancellation_reason.index', compact('pageTitle', 'reasons'));
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
