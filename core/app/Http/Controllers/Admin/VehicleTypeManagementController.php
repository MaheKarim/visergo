<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeManagementController extends Controller
{
    public function index()
    {
        $pageTitle = 'Vehicle Type Management';

        $vehicles = VehicleType::latest()->paginate(getPaginate());
        return view('admin.vehicle_type.index', compact('pageTitle', 'vehicles'));
    }


}
