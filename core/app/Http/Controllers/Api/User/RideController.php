<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\Zone;
use Illuminate\Http\Request;

class RideController extends Controller
{
    public function rideRequest(Request $request, $id = 0)
    {
        /**
         * 1. Can't request within (minute or request die)
         */
        $request->validate([
            'pickup_lat' => 'required',
            'pickup_long' => 'required',
            'destination_lat' => 'required',
            'destination_long' => 'required',
        ]);

        $user = auth()->user();

        $existingRide = Ride::where('user_id', $user->id)
            ->where('ride_request_type', Status::RIDE)
            ->where('status', Status::RIDE_INITIATED)
            ->first();

        if ($existingRide) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already requested a ride.',
                'data' => $request->all(),
            ]);
        } else{
            $pickup_lat = $request->pickup_lat;
            $pickup_long = $request->pickup_long;
            $destination_lat = $request->destination_lat;
            $destination_long = $request->destination_long;

            $zone = Zone::where('status', Status::ENABLE)->first(); // WIP

            $pickup_in_zone = $zone && underZone($pickup_lat, $pickup_long, $zone);
            $destination_in_zone = $zone && underZone($destination_lat, $destination_long, $zone);

            if (Status::RIDE &&($pickup_in_zone && $destination_in_zone)) {
                $distance = number_format($this->distanceCalculate($pickup_lat, $pickup_long, $destination_lat, $destination_long), 2, '.', '');
                $ride = new Ride();
                $ride->user_id = $user->id;
                $ride->zone_id = $zone->id;
                $ride->pickup_lat = $pickup_lat;
                $ride->pickup_long = $pickup_long;
                $ride->destination_lat = $destination_lat;
                $ride->destination_long = $destination_long;
                $ride->distance = $distance;

                $ride->total = 100;

                $ride->ride_request_type = Status::RIDE;
                $ride->status = Status::RIDE_INITIATED;
                $ride->save();

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
        }
    }


    private function distanceCalculate($lat1, $lon1, $lat2, $lon2)
    {
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLon / 2) * sin($deltaLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = 6371 * $c; // Earth's radius in km
        return $distance;
    }
}
