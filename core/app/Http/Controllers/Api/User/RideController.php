<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Ride;
use App\Models\RideDestination;
use App\Models\Zone;
use App\Models\Driver;
use App\Models\RideFare;
use App\Constants\Status;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RideController extends Controller
{



    public function rideRequest(Request $request, $id = 0)
    {

        $validator = $this->validateRequest($request);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $user = auth()->user();

        if ($this->isDriver($user)) {
            return $this->driverErrorResponse();
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
            $destination_lat = $request->input('destination_lat');
            $destination_long = $request->input('destination_long');

            $zones = Zone::active()->get();

            foreach ($zones as $zone) {
                $pickup_in_zone = underZone($pickup_lat, $pickup_long, $zone);

                // Check if any destination is under the zone
                $destination_in_zone = false;
                foreach ($destination_lat as $index => $dest_lat) {
                    $dest_long = $destination_long[$index];
                    if (underZone($dest_lat, $dest_long, $zone)) {
                        $destination_in_zone = true;
                        break;
                    }
                }

                if ($destination_in_zone && $pickup_in_zone) {
                    $zoneId = $zone->id;
                    break;
                }
            }
            // Introduce Google MAP Api
            $apiKey = gs()->location_api;

            $distances_durations = [];
            $totalDistance = 0;
            $totalDuration = 0;
            $previousDestination = "$pickup_lat,$pickup_long";

            $destinationAddress = array();

            foreach ($destination_lat as $index => $lat) {
                $destination = "$lat,{$destination_long[$index]}";

                $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$previousDestination}&destinations={$destination}&key={$apiKey}";
                $response = json_decode(file_get_contents($url), true);

                if ($response['status'] == 'OK') {
                    $element = $response['rows'][0]['elements'][0];
                    $distance = $element['distance']['value'] / 1000;
                    $duration = $element['duration']['value'] / 60;

                    $result = [
                        'element' => $element,
                        'distance' => $distance,
                        'duration' => $duration
                    ];

                    $totalDistance += $distance;
                    $totalDuration += $duration;

                    $pickupAddress = $response['origin_addresses'][0];
                    $destinationAddress[$index] = $response['destination_addresses'][0];

                    $distances_durations[$index]['destination_addresses'] = $destinationAddress;
                    $distances_durations[$index]['distance'] = $distance;
                    $distances_durations[$index]['duration'] = $duration;
                } else {
                    return response()->json([
                        'remark' => 'api_error',
                        'status' => 'error',
                        'message' => $response['error_message'],
                    ]);
                }

                $previousDestination = "$lat,{$destination_long[$index]}";
            }

            $vehicle = VehicleType::where('id', $request->vehicle_type_id)->first();
            if ($vehicle == null) {
                $notify[] = ['error', 'Vehicle type not found'];
                return response()->json([
                    'remark' => 'validation_error',
                    'status' => 'error',
                    'message' => $notify,
                ]);
            }

            // Search Ride Fare based on vehicle type
            $rideFare = RideFare::where('vehicle_type_id', $vehicle->id)
                ->where('service_id', $request->service_id)
                ->where('vehicle_class_id', $request->class_id)
                ->first();

            if ($rideFare == null) {
                $notify[] = ['error', 'Ride data not found'];
                return response()->json([
                    'remark' => 'validation_error',
                    'status' => 'error',
                    'message' => $notify,
                ]);
            }

            $base_fare = $rideFare->fare;
            $fare = $distance * $rideFare->per_km_cost;

            if ($fare < $base_fare) {
                $fare = $base_fare;
            }
            if ($request->tips != 0) {
                $fare = $fare + $request->tips;
            }
            $amount = $fare;
            $vatAmount = gs('vat_amount') * $amount / 100;
            $totalAmount = $amount + $vatAmount;

            // TODO:: Need To Update
            $ride = new Ride();
            $ride->service_id = $request->service_id;
            $ride->vehicle_type_id = $request->vehicle_type_id;
            $ride->user_id = $user->id;
            $ride->zone_id = $zoneId;
            $ride->ride_for = $request->ride_for;

            if ($ride->ride_for == Status::RIDE_FOR_PILLION) {
                $ride->pillion_name = $request->pillion_name;
                $ride->pillion_number = $request->pillion_number;
            }
            $ride->pickup_lat = $pickup_lat;
            $ride->pickup_long = $pickup_long;


            $ride->pickup_address = $pickupAddress;
//          $ride->destination_address = $destinationAddress;
            $ride->otp = generateOTP();
            $ride->distance = $totalDistance;
            $ride->duration = $totalDuration;
            $ride->base_fare = $base_fare;

            $ride->total = $totalAmount;
            $ride->vat_amount = $vatAmount;

            $ride->status = Status::RIDE_INITIATED;
            $ride->save();

            foreach ($destination_lat as $index => $lat) {
                $destination = new RideDestination();
                $destination->ride_id = $ride->id;
                $destination->destination_lat = $lat;
                $destination->destination_long = $destination_long[$index];
                $destination->destination_address = $destinationAddress[$index];
                $destination->save();
            }

            // Admin Portion
            // Driver Notification Sent

            // TODO:: Coupon Apply Here
            // Reward Claim After Ride Completed
            if ($ride->status == Status::RIDE_COMPLETED) {
                $ride->point = ($ride->total / gs('spend_amount_for_reward')) * gs('reward_point');
                $user->reward_point += $ride->point;
                $user->save();
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Ride Requested Created Successfully',
                'distance' => $totalDistance,
                'destination_address' => $destinationAddress,
                'data' => $ride,
            ]);
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


    private function validateRequest($request)
    {
        return Validator::make($request->all(), [
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
    }

    private function isDriver($user)
    {
        return $user instanceof Driver;
    }

    private function driverErrorResponse()
    {
        return response()->json([
            'remark' => 'unauthorized_action',
            'status' => 'error',
            'message' => 'Drivers are not allowed to make ride requests.'
        ], 403);
    }

    private function validationErrorResponse(\Illuminate\Validation\Validator $validator)
    {
        return response()->json([
            'remark' => 'validation_error',
            'status' => 'error',
            'message' => $validator->errors()->first(),
        ], 422);
    }


}
