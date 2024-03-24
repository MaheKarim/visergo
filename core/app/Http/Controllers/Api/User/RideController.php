<?php

namespace App\Http\Controllers\Api\User;

use App\Lib\DistanceMatrix;
use App\Lib\RideFareSearch;
use App\Lib\ZoneHelper;
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

    public function rideSearch(Request $request)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $pickupLat = $request->pickup_lat;
        $pickupLong = $request->pickup_long;
        $allDestinations = $request->destinations;

        $pickupZone = ZoneHelper::getPickupZone($pickupLat, $pickupLong);
        if (!$pickupZone) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => 'Pickup point not matched with any zone',
            ]);
        }

        $destinationZones = ZoneHelper::getDestinationZones($allDestinations);

        if (!ZoneHelper::zonesMatch($pickupZone, $destinationZones)) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => 'Some destination coordinates not matched with any zone',
            ]);
        }

        $originArray = $request->destinations;

        array_unshift($originArray, [
            "lat" => $request->pickup_lat,
            "long" => $request->pickup_long,
        ]);
        $origins = $originArray;
        array_pop($originArray);
        $destinations = $request->destinations;

        $distanceMatrix = DistanceMatrix::getTotalDistanceAndDuration($origins, $destinations);

        $totalDistance = $distanceMatrix['total_distance'];
        $totalDuration = $distanceMatrix['total_duration'];
        $pickupAddress = $distanceMatrix['pickup_address'];
        $destinationAddress = $distanceMatrix['destination_address'];

       $fareDetails = RideFareSearch::getFareDetails(
           $totalDistance,
           $totalDuration,
           $pickupAddress,
           $destinationAddress,
           $request->service_id
       );

        if (isset($fareDetails['remark'])) {
            return response()->json($fareDetails);
        }

        return response()->json($fareDetails);
    }

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
                'message' => 'You have already requested a ride',
                'data' => $request->all(),
            ]);
        } else {

            $pickupLat = $request->pickup_lat;
            $pickupLong = $request->pickup_long;
            $destinationLat = $request->destination_lat;
            $destinationLong = $request->destination_long;

            $zones = Zone::active()->get();

            foreach ($zones as $zone) {
                $pickup_in_zone = underZone($pickupLat, $pickupLong, $zone);

                // Check if any destination is under the zone
                $destination_in_zone = false;
                foreach ($destinationLat as $index => $dest_lat) {
                    $dest_long = $destinationLong[$index];
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
            $distances_durations = calculateDistancesDurations($request);
            $totalDistance = $distances_durations['totalDistance'];
            $totalDuration = $distances_durations['totalDuration'];
            $pickupAddress = $distances_durations['pickupAddress'];
            $destinationAddress = $distances_durations['destinationAddress'];

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
            $fare = $totalDistance * $rideFare->per_km_cost;

            if ($fare < $base_fare) {
                $fare = $base_fare;
            }
            $amount = $fare;
            $vatAmount = gs('vat_amount') * $amount / 100;

            if ($request->tips != 0) {
                $tips = $request->tips;
            } else {
                $tips = 0;
            }

            $totalAmount = $amount + $vatAmount + $tips;

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
            $ride->pickup_lat = $pickupLat;
            $ride->pickup_long = $pickupLong;
            $ride->pickup_address = $pickupAddress;

            $ride->distance = $totalDistance;
            $ride->duration = $totalDuration;
            $ride->otp = generateOTP();
            $ride->base_fare = $base_fare;
            $ride->vat_amount = $vatAmount;
            //            $ride->tips = $request->tips;

            $ride->total = $totalAmount;
            $ride->status = Status::RIDE_INITIATED;
            $ride->payment_status = Status::PAYMENT_INITIATE;
            $ride->payment_type = Status::NO;
            $ride->save();

            foreach ($destinationLat as $index => $lat) {
                $destination = new RideDestination();
                $destination->ride_id = $ride->id;
                $destination->destination_lat = $lat;
                $destination->destination_long = $destinationLong[$index];
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
        $ride = Ride::where('user_id', $user->id)->completed()->paginate(10);
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
            'destinations' => 'array|min:1',
            'destinations.*.lat' => 'required',
            'destinations.*.long' => 'required',
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

    private function validationErrorResponse($validator)
    {
        return response()->json([
            'remark' => 'validation_error',
            'status' => 'error',
            'message' => $validator->errors()->first(),
        ], 422);
    }

    public function rideTips(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tips' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $ride = Ride::where('user_id', auth()->user()->id)->rideEnd()->find($id);
        if ($request->tips != 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Ride Tips Added Already',
                'data' => $ride,
            ]);
        } else {
            $ride->tips = $request->tips;
            $ride->total = $ride->total + $request->tips;
            $ride->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Ride Tips Added Successfully',
                'data' => $ride,
            ]);
        }
    }

    public function rideOngoing()
    {
        $ride = Ride::where('user_id', auth()->user()->id)->ongoingRide()->first();
        if ($ride == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'No Ongoing Ride Found',
                'data' => $ride,
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Ongoing Ride',
            'data' => $ride,
        ]);
    }
}
