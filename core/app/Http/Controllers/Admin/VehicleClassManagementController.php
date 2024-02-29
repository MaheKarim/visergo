<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleClass;
use Illuminate\Http\Request;

class VehicleClassManagementController extends Controller
{
    public function index()
    {
        $pageTitle = 'Vehicle Class Management';
        $vehicleClasses = VehicleClass::latest()->searchable(['name'])->paginate(getPaginate());

        return view('admin.vehicle_class.index', compact('pageTitle', 'vehicleClasses'));
    }

    public function store(Request $request, $id = 0)
    {
        $request->validate([
            'name' => 'required|max:40|min:0',
            'class_base_fare' => 'required|numeric|gte:0',
        ]);

        if (!$id) {
            $class = new VehicleClass();
            $notification = 'Vehicle class added successfully';
        } else {
            $class = VehicleClass::findOrFail($id);
            $notification = 'Vehicle class updated successfully';
        }

        $class->name = $request->name;
        $class->class_base_fare = $request->class_base_fare;
        $class->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return VehicleClass::changeStatus($id);
    }
}
