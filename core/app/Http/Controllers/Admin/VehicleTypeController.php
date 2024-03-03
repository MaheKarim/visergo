<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            'base_fare' => 'required|numeric|min:0',
            'ride_fare_per_km' => 'required|numeric|min:0',
            'intercity_fare_per_km' => 'required|numeric|min:0',
            'rental_fare_per_km' => 'required|numeric|min:0',
            'reserve_fare_per_km' => 'required|numeric|min:0',
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
        $vehicle->ride_fare_per_km = $request->ride_fare_per_km;
        $vehicle->intercity_fare_per_km = $request->intercity_fare_per_km;
        $vehicle->rental_fare_per_km = $request->rental_fare_per_km;
        $vehicle->reserve_fare_per_km = $request->reserve_fare_per_km;
        $vehicle->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return VehicleType::changeStatus($id);
    }
}
