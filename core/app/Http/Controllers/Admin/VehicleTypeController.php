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
        $vehicleType->manage_class = $request->manage_class ?? 0;
        $vehicleType->manage_brand = $request->manage_brand ?? 0;
        $vehicleType->save();

        $vehicleType->vehicleServices()->sync($request->service);
        $vehicleType->classes()->sync($request->classes);

        RideFare::where('vehicle_type_id', $vehicleType->id)->delete();
        $vehicleTypeArr = [];
        if ($vehicleType->manage_class == Status::YES) {

            foreach ($request->service as $serviceId) {
                foreach ($request->classes as $key => $classId) {
                    $fare = @$request?->fare[$serviceId][$classId] ?? 0;
                    $perKmFare = @$request?->per_km_fare[$serviceId][$classId] ?? 0;
                    $hourlyFare = @$request?->hourly_fare[$serviceId][$classId] ?? 0;
                    $dailyFare = @$request?->daily_fare[$serviceId][$classId] ?? 0;
                    $monthlyFare = @$request?->monthly_fare[$serviceId][$classId] ?? 0;
                    $vehicleTypeArr[] = [
                        'vehicle_type_id' => $vehicleType->id,
                        'service_id' => $serviceId,
                        'vehicle_class_id' => $classId,
                        'fare' => $fare,
                        'per_km_fare' => $perKmFare,
                        'hourly_fare' => $hourlyFare,
                        'daily_fare' => $dailyFare,
                        'monthly_fare' => $monthlyFare,
                        'created_at' => now()

                    ];
                }
            }
            // if ($request->fare) {
            //     $serviceIds = array_keys($request->fare);
            //     $classIds = array_keys($request->fare[$serviceIds[0]]);

            //     $vehicleType->vehicleServices()->sync($serviceIds);
            //     $vehicleType->classes()->sync($classIds);

            //     foreach ($request->fare as $service => $classes) {
            //         foreach ($classes as $class => $fare) {
            //             $rideFare = new RideFare();
            //             $rideFare->vehicle_type_id = $vehicleType->id;
            //             $rideFare->service_id = $service;
            //             $rideFare->vehicle_class_id = $class;
            //             $rideFare->fare = $fare;
            //             $rideFare->per_km_fare = $request->per_km_fare[$service][$class];
            //             $rideFare->save();
            //         }
            //     }
            // }
            // foreach ($request->hourly_fare as $service => $classes) {
            //     foreach ($classes as $class => $fare) {
            //         $rideFare = new RideFare();
            //         $rideFare->vehicle_type_id = $vehicleType->id;
            //         $rideFare->service_id = $service;
            //         $rideFare->vehicle_class_id = $class;
            //         $rideFare->hourly_fare = $fare;
            //         $rideFare->save();
            //     }
            // }

            // foreach ($request->daily_fare as $service => $classes) {
            //     foreach ($classes as $class => $fare) {
            //         $rideFare = new RideFare();
            //         $rideFare->vehicle_type_id = $vehicleType->id;
            //         $rideFare->service_id = $service;
            //         $rideFare->vehicle_class_id = $class;
            //         $rideFare->daily_fare = $fare;
            //         $rideFare->save();
            //     }
            // }

            // foreach ($request->monthly_fare as $service => $classes) {
            //     foreach ($classes as $class => $fare) {
            //         $rideFare = new RideFare();
            //         $rideFare->vehicle_type_id = $vehicleType->id;
            //         $rideFare->service_id = $service;
            //         $rideFare->vehicle_class_id = $class;
            //         $rideFare->monthly_fare = $fare;
            //         $rideFare->save();
            //     }
            // }
        } else {
            foreach ($request->service as $serviceId) {
                $fare = @$request?->fare[$serviceId] ?? 0;
                $perKmFare = @$request?->per_km_fare[$serviceId] ?? 0;
                $hourlyFare = @$request?->hourly_fare[$serviceId] ?? 0;
                $dailyFare = @$request?->daily_fare[$serviceId] ?? 0;
                $monthlyFare = @$request?->monthly_fare[$serviceId] ?? 0;
                $vehicleTypeArr[] = [
                    'vehicle_type_id' => $vehicleType->id,
                    'service_id' => $serviceId,
                    'fare' => $fare,
                    'per_km_fare' => $perKmFare,
                    'hourly_fare' => $hourlyFare,
                    'daily_fare' => $dailyFare,
                    'monthly_fare' => $monthlyFare,
                    'created_at' => now()

                ];
            }

            // $serviceIds = array_keys($request->fare);
            // $vehicleType->vehicleServices()->sync($serviceIds);

            // foreach ($request->fare as $service => $fare) {
            //     if ($request->old_value) {
            //         $rideFare = RideFare::find($request->old_value[$service]);
            //     } else {
            //         $rideFare = new RideFare();
            //     }

            //     $rideFare->vehicle_type_id = $vehicleType->id;
            //     $rideFare->service_id = $service;
            //     $rideFare->fare = $fare;
            //     $rideFare->per_km_fare = $request->per_km_fare[$service];
            //     $rideFare->save();
            // }
            // foreach ($request->hourly_fare as $service => $fare) {
            //     $rideFare = new RideFare();
            //     $rideFare->vehicle_type_id = $vehicleType->id;
            //     $rideFare->service_id = $service;
            //     $rideFare->hourly_fare = $fare;
            //     $rideFare->save();
            // }

            // foreach ($request->daily_fare as $service => $fare) {
            //     $rideFare = new RideFare();
            //     $rideFare->vehicle_type_id = $vehicleType->id;
            //     $rideFare->service_id = $service;
            //     $rideFare->daily_fare = $fare;
            //     $rideFare->save();
            // }

            // foreach ($request->monthly_fare as $service => $fare) {
            //     $rideFare = new RideFare();
            //     $rideFare->vehicle_type_id = $vehicleType->id;
            //     $rideFare->service_id = $service;
            //     $rideFare->monthly_fare = $fare;
            //     $rideFare->save();
            // }
        }

        RideFare::insert($vehicleTypeArr);

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
