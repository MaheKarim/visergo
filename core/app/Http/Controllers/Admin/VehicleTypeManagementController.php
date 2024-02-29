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

    public function store(Request $request, $id = 0)
    {
        $request->validate([
            'name' => 'required',
            'base_fare' => 'required|numeric|min:0',
            'ride_per_km_cost' => 'required|numeric|min:0',
            'intercity_per_km_cost' => 'required|numeric|min:0',
            'rental_per_km_cost' => 'required|numeric|min:0',
            'reserve_per_km_cost' => 'required|numeric|min:0',
        ]);

        if (!$id) {
            $notification = 'Vehicle type added successfully';
            $vehicle = new VehicleType();
        } else{
            $notification = 'Vehicle type updated successfully';
            $vehicle = VehicleType::findOrFail($id);
        }

        $vehicle->name = $request->name;
        $vehicle->base_fare = $request->base_fare;
        $vehicle->ride_per_km_cost = $request->ride_per_km_cost;
        $vehicle->intercity_per_km_cost = $request->intercity_per_km_cost;
        $vehicle->rental_per_km_cost = $request->rental_per_km_cost;
        $vehicle->reserve_per_km_cost = $request->reserve_per_km_cost;
        $vehicle->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return VehicleType::changeStatus($id);
    }
}
