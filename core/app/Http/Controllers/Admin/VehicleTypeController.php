<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\RideFare;
use App\Models\Service;
use App\Models\VehicleClass;
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

        if ($id) {
            $vehicleType = VehicleType::findOrFail($id);
            $message = 'Vehicle type updated successfully';
        } else {
            $vehicleType = new VehicleType();
            $message = 'Vehicle type added successfully';
        }

        $vehicleType->name = $request->name;
        $vehicleType->manage_class = $request->manage_class;
        $vehicleType->manage_brand = $request->manage_brand;
        $vehicleType->save();

        RideFare::where('vehicle_type_id', $vehicleType->id)->delete();

        if ($vehicleType->manage_class == Status::YES) {
            $serviceIds = array_keys($request->fare);
            $classIds = array_keys($request->fare[$serviceIds[0]]);

            $vehicleType->vehicleServices()->sync($serviceIds);
            $vehicleType->classes()->sync($classIds);

            foreach ($request->fare as $service => $classes) {
                foreach ($classes as $class => $fare) {
                    $rideFare = new RideFare();
                    $rideFare->vehicle_type_id = $vehicleType->id;
                    $rideFare->service_id = $service;
                    $rideFare->vehicle_class_id = $class;
                    $rideFare->fare = $fare;
                    $rideFare->per_km_cost = $request->per_km_cost[$service][$class];
                    $rideFare->save();
                }
            }
        } else {
            $serviceIds = array_keys($request->fare);
            $vehicleType->vehicleServices()->sync($serviceIds);

            foreach ($request->fare as $service => $fare) {
                if ($request->old_value) {
                    $rideFare = RideFare::find($request->old_value[$service]);
                } else {
                    $rideFare = new RideFare();
                }

                $rideFare->vehicle_type_id = $vehicleType->id;
                $rideFare->service_id = $service;
                $rideFare->fare = $fare;
                $rideFare->per_km_cost = $request->per_km_cost[$service];
                $rideFare->save();
            }
        }

        $notify[] = ['success', $message];
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
        $classes = VehicleClass::all();

        return view('admin.vehicle_type.create', compact('pageTitle', 'services', 'classes'));
    }

    public function edit($id)
    {
        $pageTitle = 'Update Vehicle Type';
        $vehicleType = VehicleType::with('vehicleServices', 'classes',  'rideFares.service',  'rideFares.vehicleClass')->findOrFail($id);
        $services = Service::all();
        $classes = VehicleClass::all();

        return view('admin.vehicle_type.create', compact('pageTitle', 'services', 'classes', 'vehicleType'));
    }


}
