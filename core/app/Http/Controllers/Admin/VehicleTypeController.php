<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    public function index()
    {
        $pageTitle = 'All Vehicle Types';
        $vehicles = VehicleType::latest()->searchable(['name'])->paginate(getPaginate());

        return view('admin.vehicle_type.index', compact('pageTitle', 'vehicles'));
    }

    public function store(Request $request, $id = 0)
    {
        $request->validate([
            'name' => 'required',
            'base_fare' => 'nullable|numeric|min:0',
            'is_ride' => 'nullable|boolean',
            'is_intercity' => 'nullable|boolean',
            'is_rental' => 'nullable|boolean',
            'is_reserve' => 'nullable|boolean',
            'manage_class' => 'nullable|boolean',
            'manage_brand' => 'nullable|boolean',
        ]);

        if (!$id) {
            $vehicle = new VehicleType();
            $notification = 'Vehicle type added successfully';
        } else{
            $vehicle = VehicleType::findOrFail($id);
            $notification = 'Vehicle type updated successfully';
        }

        $vehicle->name = $request->name;
        $vehicle->base_fare = $request->base_fare;
        $vehicle->is_ride = $request->is_ride;
        $vehicle->is_intercity = $request->is_intercity;
        $vehicle->is_rental = $request->is_rental;
        $vehicle->is_reserve = $request->is_reserve;
        $vehicle->manage_class = $request->manage_class;
        $vehicle->manage_brand = $request->manage_brand;
        $vehicle->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return VehicleType::changeStatus($id);
    }

    public function create()
    {
        $pageTitle = 'Create Vehicle Type';
        $services = Service::all();

        return view('admin.vehicle_type.create', compact('pageTitle', 'services'));
    }
}
