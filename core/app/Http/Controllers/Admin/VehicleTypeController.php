<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RideFare;
use App\Models\Service;
use App\Models\TypeClass;
use App\Models\VehicleClass;
use App\Models\VehicleService;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

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
            'manage_class' => 'required|boolean',
            'manage_brand' => 'nullable|boolean',
            'classes' => 'nullable|array',
            'fare' => 'required|array',
            'fare.*' => 'required|numeric|min:0'
        ]);

        if($id){
            $vehicleType = VehicleType::findOrFail($id);
            $notification = ['success', 'Vehicle type updated successfully'];
        }else{
            $vehicleType = new VehicleType();
            $notification = ['success', 'Vehicle type added successfully'];
        }

        if($id && !$request->old_value && $vehicleType->manage_class == 1)
        {
            RideFare::where('vehicle_type_id', $vehicleType->id)->delete();
        }

        $vehicleType->name = $request->name;
        $vehicleType->base_fare = $request->base_fare ?? 0;
        $vehicleType->manage_class = $request->manage_class;
        $vehicleType->save();

        if($vehicleType->manage_class == 1)
        {
            $serviceIds = array_keys($request->fare);
            $classIds = array_keys($request->fare[$serviceIds[0]]);

            $vehicleType->vehicleServices()->sync($serviceIds);
            $vehicleType->classes()->sync($classIds);

            foreach ($request->fare as $service => $classes) {
                foreach ($classes as $class => $fare) {
                    if($request->old_value){
                        $rideFare = RideFare::find($request->old_value[$service][$class]);
                    }else{
                        $rideFare = new RideFare();
                    }

                    $rideFare->vehicle_type_id = $vehicleType->id;
                    $rideFare->service_id = $service;
                    $rideFare->vehicle_class_id = $class;
                    $rideFare->fare = $fare;
                    $rideFare->save();
                }
            }

        }else{
            $serviceIds = array_keys($request->fare);
            $vehicleType->vehicleServices()->sync($serviceIds);
            foreach ($request->fare as $service => $fare) {
                if($request->old_value){
                    $rideFare = RideFare::find($request->old_value[$service]);
                }else{
                    $rideFare = new RideFare();
                }
                $rideFare->vehicle_type_id = $vehicleType->id;
                $rideFare->service_id = $service;
                $rideFare->fare = $fare;
                $rideFare->save();
            }
        }

        return to_route('admin.vehicle.type.index')->withNotify($notification);
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
