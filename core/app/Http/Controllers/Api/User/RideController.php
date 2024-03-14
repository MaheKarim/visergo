<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Ride;
use App\Models\VehicleType;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RideController extends Controller
{
    public function rideRequest(Request $request, $id = 0, $type = '')
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
            ->where('ride_request_type', Status::RIDE_SERVICE)
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
                $type = VehicleType::where('id', $request->ride_request_type)->first();
//                dd($type->base_fare);
//                $base_fare = $type->value('base_fare');
                if ($type->manage_class == 0) {
                    $base_fare = $type->value('base_fare');
//                    dd($base_fare);
                }
                if (($request->ride_request_type = $type) && ($pickup_in_zone && $destination_in_zone)) {
                    $perKMCost = $type->value('base_fare');

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
