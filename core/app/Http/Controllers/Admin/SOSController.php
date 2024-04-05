<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SOSAlert;
use Illuminate\Http\Request;

class SOSController extends Controller
{
    public function index()
    {
        $pageTitle = 'SOS Alerts';
        $soss = SOSAlert::latest()->with('ride')->paginate(getPaginate(10));

        return view('admin.sos.index', compact('pageTitle','soss'));
    }

    public function details($id)
    {
        $pageTitle = 'SOS Alert Details';
        $sos = SOSAlert::with(['ride'])->findOrFail($id);

        return view('admin.sos.details', compact('pageTitle','sos'));
    }

    public function status($id)
    {
       return SOSAlert::changeStatus($id);
    }
}
