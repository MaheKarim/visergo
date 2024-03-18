<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Ride;
use App\Models\RideFare;
use App\Models\VehicleType;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RideController extends Controller
{

    public function ride(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'pickup_lat' => 'required',
            'pickup_long' => 'required',
            'destination_lat' => 'required',
            'destination_long' => 'required',
            'ride_for' => 'required',
            'pillion_name' => [
                'required_if:ride_for,' . Status::RIDE_FOR_PILLION,
            ],
            'pillion_number' => [
                'required_if:ride_for,' . Status::RIDE_FOR_PILLION,
            ],
            'service_id' => 'required',
        ]);

        if ($request->has('vehicle_type')) {
            $validator->after(function ($validator) use ($request) {
                if (!VehicleType::where('id', $request->vehicle_type)->exists()) {
                    $validator->errors()->add('vehicle_type', 'Vehicle type not found');
                }
            });
        }

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();

        if ($user instanceof Driver) {
            $notify[] = 'Drivers are not allowed to make ride requests';
            return response()->json([
                'remark' => 'unauthorized_action',
                'status' => 'error',
                'message' => $notify
            ], 403);
        }

        $pickup_lat = $request->pickup_lat;
        $pickup_long = $request->pickup_long;
        $destination_lat = $request->destination_lat;
        $destination_long = $request->destination_long;

        $zones = Zone::active()->first();

        foreach ($zones as $zone) {
            $pickup_in_zone = $zone && underZone($pickup_lat, $pickup_long, $zone);
            $destination_in_zone = $zone && underZone($destination_lat, $destination_long, $zone);
            if ($pickup_in_zone && $destination_in_zone) {
                break;
            }
        }

        if (!$pickup_in_zone) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => 'Pickup point not in zone',
            ]);
        }
        if (!$destination_in_zone) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => 'Destination point not in zone',
            ]);
        }
        // Introduce Google MAP Api
        $apiKey = gs()->location_api;
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$pickup_lat},{$pickup_long}&destinations={$destination_lat},{$destination_long}&key={$apiKey}";
        $response = json_decode(file_get_contents($url), true);

        if ($response['status'] == 'OK') {
            $distance = $response['rows'][0]['elements'][0]['distance']['value'] / 1000;
            $pickupAddress = $response['origin_addresses'][0];
            $destinationAddress = $response['destination_addresses'][0];
        }

        $vehicleTypes = VehicleType::all();
        $responses = [
            'remark' => 'fare_calculated',
            'status' => 'success',
            'data' => []
        ];
        foreach ($vehicleTypes as $vehicleType) {
            $multipleClass = RideFare::where('vehicle_type_id', $vehicleType->id)
                ->where('service_id', $request->service_id)
                ->with(['vehicleClass'])->get();

            foreach ($multipleClass as $class) {

                $baseFare = $class->fare;
                $fare = $baseFare * $distance;
                $getVehicleClass = data_get($class, 'vehicleClass.name');
                $getVehicleClassId = data_get($class, 'vehicleClass.id');

                $responses['data'][] = [
                    'id' => $class->id,
                    'vehicle_type_id' => $vehicleType->id,
                    'service_id' => $request->service_id,
                    'class_id' => $getVehicleClassId,
                    'fare' => getAmount($fare),
                    'class' => $getVehicleClass,
                    'vehicle_type' => $vehicleType->name,
                    'pickup_address' => $pickupAddress,
                    'destination_address' => $destinationAddress,
                ];
            }
        }

        if (empty($responses)) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => 'Vehicle types not found',
            ]);
        }

        return response()->json($responses);

    }
    public function rideRequest(Request $request, $id = 0)
    {

        $validator = Validator::make($request->all(), [
            'pickup_lat' => 'required',
            'pickup_long' => 'required',
            'destination_lat' => 'required',
            'destination_long' => 'required',
            'ride_for' => 'required',
            'pillion_name' => [
                'required_if:ride_for,' . Status::RIDE_FOR_PILLION,
            ],
            'pillion_number' => [
                'required_if:ride_for,' . Status::RIDE_FOR_PILLION,
            ],
            'service_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();

        if ($user instanceof Driver) {
            return response()->json([
                'remark' => 'unauthorized_action',
                'status' => 'error',
                'message' => 'Drivers are not allowed to make ride requests.'
            ], 403);
        }

        $existingRide = Ride::where('user_id', $user->id)
            ->where('service_id', Status::RIDE_SERVICE)
            ->where('ride_for', Status::RIDE_FOR_OWN)
            ->whereNotIn('status', [Status::RIDE_COMPLETED, Status::RIDE_CANCELED])
            ->first();

        if ($existingRide) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already requested a ride.',
                'data' => $request->all(),
            ]);
        } else {

            $pickup_lat = $request->pickup_lat;
            $pickup_long = $request->pickup_long;
            $destination_lat = $request->destination_lat;
            $destination_long = $request->destination_long;

            $zone = Zone::where('status', Status::ENABLE)->first(); // WIP

            $pickup_in_zone = $zone && underZone($pickup_lat, $pickup_long, $zone);
            $destination_in_zone = $zone && underZone($destination_lat, $destination_long, $zone);

            // Introduce Google MAP Api
            $apiKey = gs()->location_api;
            $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$pickup_lat},{$pickup_long}&destinations={$destination_lat},{$destination_long}&key={$apiKey}";
            $response = json_decode(file_get_contents($url), true);

            if ($response['status'] == 'OK') {
                $distance = $response['rows'][0]['elements'][0]['distance']['value'] / 1000;
                $duration = $response['rows'][0]['elements'][0]['duration']['value'] / 60;
                $pickupAddress = $response['origin_addresses'][0];
                $destinationAddress = $response['destination_addresses'][0];

                // TODO:: Need To Update // ride_service_type
                $vehicle = VehicleType::where('id', $request->type_id)->first();
                if ($vehicle == null) {
                    $notify[] = ['error', 'Vehicle type not found'];
                    return response()->json([
                        'remark' => 'validation_error',
                        'status' => 'error',
                        'message' => $notify,
                    ]);
                }

                if ($vehicle->manage_class == null && $vehicle->manage_class == 0) {
                    $base_fare = $vehicle->value('base_fare');
                } else {
//                    $base_fare = $vehicle->base_fare;
                }
                if (($request->ride_request_type = $vehicle) && ($pickup_in_zone && $destination_in_zone)) {
                    $perKMCost = $vehicle->value('base_fare');

                    $rideCost = $distance * $perKMCost;

                    if ($rideCost < $base_fare) {
                        $rideCost = $base_fare;
                    }

                    $vatAmount = $rideCost * (gs('vat_value') / 100);
                    $totalAmount = $rideCost + $vatAmount;

                    // TODO:: Need To Update
                    $ride = new Ride();
                    $ride->user_id = $user->id;
                    $ride->zone_id = $zone->id;
                    $ride->ride_for = $request->ride_for;
                    if ($ride->ride_for == Status::RIDE_FOR_PILLION) {
                        $ride->pillion_name = $request->pillion_name;
                        $ride->pillion_number = $request->pillion_number;
                    }
                    $ride->pickup_lat = $pickup_lat;
                    $ride->pickup_long = $pickup_long;
                    $ride->destination_lat = $destination_lat;
                    $ride->destination_long = $destination_long;
                    $ride->pickup_address = $pickupAddress;
                    $ride->destination_address = $destinationAddress;
                    $ride->otp = generateOTP();
                    $ride->distance = $distance;
                    $ride->duration = $duration;
                    $ride->base_fare = $base_fare;

                    $ride->total = $totalAmount;
                    $ride->vat_amount = $vatAmount;

                    $ride->ride_request_type = $request->ride_request_type;
                    $ride->status = Status::RIDE_INITIATED;
                    $ride->save();

                    // TODO:: Coupon Apply Here
                    // Reward Claim After Ride Completed
                    if ($ride->status == Status::RIDE_COMPLETED) {
                        $ride->point = ($ride->total / gs()->spend_amount_for_reward) * gs()->reward_point;
                        $user->reward_point += $ride->point;
                        $user->save();
                    }
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Ride Requested Created Successfully',
                        'distance' => $distance,
                        'data' => $ride,
                    ]);
                } elseif (!$pickup_in_zone) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Pickup location is not within the zone.',
                        'data' => $request->all(),
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Destination location is not within the zone.',
                        'data' => $request->all(),
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Something went wrong in API',
                    'data' => $request->all(),
                ]);
            }
        }
    }

    public function rideCompleted()
    {
        $user = auth()->user();
        $ride = Ride::where('user_id', $user->id)->where('status', Status::RIDE_COMPLETED)->paginate(10);
        if ($ride == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'No Completed Ride Found',
                'data' => $ride,
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Completed Ride',
            'data' => $ride,
        ]);
    }

}
