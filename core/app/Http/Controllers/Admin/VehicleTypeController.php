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
            'classes' => 'nullable|array', // Ensure classes is an array
//            'fare' => 'required_if:manage_class,1|array',
//            'fare.*' => 'required|numeric|min:0'
        ]);


//        return $request;

        if($id){
            $vehicleType = VehicleType::findOrFail($id);
            $notification = 'Vehicle type updated successfully';
        }else{
            $vehicleType = new VehicleType();
            $notification = 'Vehicle type added successfully';
        }

        $vehicleType->name = $request->name;
        $vehicleType->base_fare = $request->base_fare ?? 0;
        $vehicleType->save();

        $serviceIds = array_keys($request->fare);
        $classIds = array_keys($request->fare[$serviceIds[0]]);

        $vehicleType->vehicleServices()->sync($serviceIds);
        $vehicleType->classes()->sync($classIds);

//        foreach ($serviceIds as $serviceId) {
//            $vehicleService = new VehicleService();
//            $vehicleService->vehicle_type_id = $vehicleType->id;
//            $vehicleService->service_id = $serviceId;
//            $vehicleService->save();
//        }
//
//        foreach ($classIds as $classId) {
//            $typeClass = new TypeClass();
//            $typeClass->vehicle_type_id = $vehicleType->id;
//            $typeClass->vehicle_class_id = $classId;
//            $typeClass->save();
//        }


        foreach ($request->fare as $service => $classes) {
            foreach ($classes as $class => $fare) {
                $rideFare = new RideFare();
                $rideFare->vehicle_type_id = $vehicleType->id;
                $rideFare->service_id = $service;
                $rideFare->vehicle_class_id = $class;
                // $rideFare->fare = $request->fare[$service][$class];
                $rideFare->fare = $fare;
                $rideFare->save();
            }
        }


//        if (!$id) {
//            $vehicle = new VehicleType();
//            $notification = 'Vehicle type added successfully';
//        } else {
//            $vehicle = VehicleType::findOrFail($id);
//            $notification = 'Vehicle type updated successfully';
//        }
//
//        $vehicle->name = $request->name;
//        $vehicle->base_fare = $request->base_fare;
//        $vehicle->is_ride = $request->is_ride;
//        $vehicle->is_intercity = $request->is_intercity;
//        $vehicle->is_rental = $request->is_rental;
//        $vehicle->is_reserve = $request->is_reserve;
//        $vehicle->manage_class = $request->manage_class;
//        $vehicle->manage_brand = $request->manage_brand;
//        $vehicle->save();
//
//        if ($request->manage_class && is_array($request->classes)) {
//            $vehicle->classes()->sync($request->classes);
//        }


        $notify[] = ['success', 'Vehicle type added successfully'];
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
}
