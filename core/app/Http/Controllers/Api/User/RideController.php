<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Ride;
use App\Models\Driver;
use App\Lib\ZoneHelper;
use App\Models\RideFare;
use App\Constants\Status;
use App\Lib\DistanceMatrix;
use App\Lib\RideFareSearch;
use App\Models\VehicleType;
use App\Models\DriverReview;
use App\Models\Zone;
use Exception;
use Illuminate\Http\Request;
use App\Models\RideDestination;
use App\Traits\RideCancelTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class RideController extends Controller
{

    use RideCancelTrait;

    public function rideSearch(Request $request)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $pickupLat = $request->pickup_lat;
        $pickupLong = $request->pickup_long;
        $allDestinations = $request->destinations;

        $pickupZone = ZoneHelper::getZone($pickupLat, $pickupLong);
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

        $totalDistance = $distanceMatrix->total_distance;
        $totalDuration = $distanceMatrix->total_duration;
        $pickupAddress = $distanceMatrix->pickup_address;
        $destinationAddress = $distanceMatrix->destination_address;

        $fareDetails = RideFareSearch::getFareDetails(
            $totalDistance,
            $totalDuration,
            $pickupAddress,
            $destinationAddress,
            $request->service_id
        );

        if ($fareDetails['error']) {
            return formatResponse('ride_search_error', 'error', $fareDetails['message'], null);
        }

        return formatResponse('ride_search', 'success', 'Ride Search', $fareDetails['data']);
    }

    /**
     * @throws Exception
     */
    public function rideRequest(Request $request)
    {
        $validator = $this->validateRequest($request);

        if ($validator->fails()) {
            return errorResponse('validation_error', $validator->errors(), 422);
        }

        $vehicle = VehicleType::where('id', $request->vehicle_type_id)->first();

        if (!$vehicle) {
            return formatResponse('vehicle_type_not_found', 'error', 'Vehicle type not found', null);
        }

        // Search Ride Fare based on vehicle type
        $rideFare = RideFare::where('vehicle_type_id', $vehicle->id)
            ->where('service_id', $request->service_id)
            ->where('vehicle_class_id', $request->class_id)
            ->first();

        if (!$rideFare) {
            $notify[] = ['error', 'Ride data not found'];
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => $notify,
            ]);
        }

        $user = auth()->user();

        $existingRide = Ride::where('user_id', $user->id)
            ->where('service_id', $request->service_id)
            ->where('class_id', $request->class_id)
            ->where('vehicle_type_id', $request->vehicle_type_id)
            ->where('ride_for', Status::RIDE_FOR_OWN)
            ->where('status', [Status::RIDE_INITIATED, Status::RIDE_ACTIVE])
            ->first();

        if ($existingRide) {
            return errorResponse('error', 'You have already requested a ride');
        }

        $pickupLat = $request->pickup_lat;
        $pickupLong = $request->pickup_long;
        $pickupZone = ZoneHelper::getZone($pickupLat, $pickupLong);

        if (!$pickupZone) {
            return errorResponse('validation_error', 'Pickup point not matched with any zone');
        }
        $destinationZones = null;

        if ($request->service_id != Status::RENTAL_SERVICE) {
            $destinationZones = ZoneHelper::getDestinationZones($request->destinations);

            if (blank(array_filter($destinationZones))) {
                return errorResponse('validation_error', 'Destination point not matched with any zone');
            }

        if($request->service_id == Status::RIDE_SERVICE || $request->service_id == Status::RESERVE_SERVICE){
            $zoneMatch = ZoneHelper::zonesMatch($pickupZone, $destinationZones);

            if (!$zoneMatch) {
                return errorResponse('validation_error', 'Some destination coordinates not matched with any zone');
            }
        }

        if($request->service_id == Status::INTER_CITY_SERVICE){
            $pickupZoneId = $pickupZone->id;
            $destinationZoneIds = array_column($destinationZones, 'id');

            $activeZoneIds = Zone::active()->pluck('id')->toArray();

            if (!in_array($pickupZoneId, $activeZoneIds) || !array_intersect($destinationZoneIds, $activeZoneIds)) {
                return errorResponse('validation_error', 'Pickup or destination zones are not active');
            }
        }

        $originArray = $request->destinations ?: [];

        array_unshift($originArray, [
            "lat" => $request->pickup_lat,
            "long" => $request->pickup_long,
        ]);

        $origins = $originArray;
        array_pop($originArray);
        $destinations = $request->destinations ?: [];

        $distanceMatrix = DistanceMatrix::getTotalDistanceAndDuration($origins, $destinations);
        $totalDistance = $distanceMatrix->total_distance;
        $totalDuration = $distanceMatrix->total_duration;
        $pickupAddress = $distanceMatrix->pickup_address;
        $destinationAddress = $distanceMatrix->destination_address;

        $baseFare = $rideFare->fare;
        $fare = $totalDistance * $rideFare->per_km_fare;

        if ($fare < $baseFare) {
            $fare = $baseFare;
        }

        $vatAmount = gs('vat_amount') * $fare / 100;

        $adminCommission = gs('admin_fixed_commission') + (gs('admin_percent_commission') * $fare / 100);
        $driverAmount = $fare - $adminCommission;
        $totalAmount = $fare + $vatAmount;

        } else {
            $destinations = [];
            $totalDistance = 0;
            $totalDuration = 0;
            $pickupAddress = $request->pickup_address;
            $destinationAddress = "No Destination Address Set";

            // Base Fare
            if ($request->rental_type == Status::RENTAL_HOURLY) {
                $baseFare = $rideFare->hourly_fare;
                $totalFare = $request->rental_time * $baseFare;

            } elseif ($request->rental_type == Status::RENTAL_DAILY) {
                $baseFare = $rideFare->daily_fare;
                $totalFare = $request->rental_time * $baseFare;

            } else{
                $baseFare = $rideFare->monthly_fare;
                $totalFare = $request->rental_time * $baseFare;
            }

            $vatAmount = gs('vat_amount') * $totalFare / 100;
            $adminCommission = gs('admin_fixed_commission') + (gs('admin_percent_commission') * $totalFare / 100);
            $driverAmount = $totalFare - $adminCommission;
            $totalAmount = $totalFare + $vatAmount;
        }


        $ride = new Ride();
        $ride->service_id = $request->service_id;
        $ride->vehicle_type_id = $request->vehicle_type_id;
        $ride->user_id = $user->id;
        $ride->zone_id = $pickupZone->id;
        $ride->class_id = $request->class_id;
        $ride->ride_for = $request->ride_for;

        $ride->pickup_lat = $pickupLat;
        $ride->pickup_long = $pickupLong;
        $ride->pickup_address = $pickupAddress;

        $ride->distance = $totalDistance;
        $ride->duration = showAmount($totalDuration);
        $ride->otp = generateOTP(4);
        $ride->base_fare = $baseFare;

        // rental_type && rental_amount
        $ride->rental_type = $request->rental_type;
        $ride->rental_time = $request->rental_time;

        $ride->admin_commission = $adminCommission;
        $ride->driver_amount = $driverAmount;
        $ride->vat_amount = $vatAmount;
        $ride->total = $totalAmount;

        $ride->status = Status::RIDE_INITIATED;
        $ride->payment_type = $request->payment_type;
        $ride->payment_status = Status::PAYMENT_INITIATE;
        $ride->save();

        if ($request->service_id != Status::RENTAL_SERVICE) {
            foreach ($destinations as $index => $destination) {
                $rideDestination = new RideDestination();
                $rideDestination->ride_id = $ride->id;
                $rideDestination->destination_lat = $destination['lat'];
                $rideDestination->destination_long = $destination['long'];
                $rideDestination->destination_address = $destinationAddress[$index];
                $rideDestination->save();
            }
        }

        // Admin Portion
        // Driver Notification Sent

        return formatResponse('ride_request_created', 'success', 'Ride request created successfully', $ride->load('destinations'));
    }


    private function validateRequest($request)
    {
        return Validator::make($request->all(), [
            'pickup_lat' => 'required',
            'pickup_long' => 'required',
            'destinations' => 'array|min:1',
            'destinations.*.lat' => 'required_unless:service_id,' . Status::RENTAL_SERVICE,
            'destinations.*.long' => 'required_unless:service_id,' . Status::RENTAL_SERVICE,
            'ride_for' => 'required',
            'service_id' => 'required',
            'vehicle_type_id' => 'nullable',
            'departure_time' => [
                'required_if:service_id,' .  Status::RESERVE_SERVICE .',' . Status::INTER_CITY_SERVICE . ',' . Status::RENTAL_SERVICE .'date_format:Y-m-d H:i',
                function ($attribute, $value, $fail) {
                    $bookingLimit = Carbon::now()->addDays(gs('pre_booking_time'));
                    if (Carbon::parse($value)->isAfter($bookingLimit)) {
                        $fail('The departure time must be within ' . gs('pre_booking_time') . ' days from now');
                    }
                },
            ],
            'rental_type' => 'required_if:service_id,' . Status::RENTAL_SERVICE, 'numeric',
            'rental_time' => 'required_if:service_id,' . Status::RENTAL_SERVICE, 'numeric',
            'payment_type' => 'nullable',
        ]);
    }


    private function validationErrorResponse($validator)
    {
        return formatResponse('validation_error', 'error', $validator->errors()->first());
    }

    public function rideTips(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tips' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $ride = Ride::where('user_id', auth()->id())->with('destinations','driver')->rideEnd()->find($id);

        if ($request->tips != 0) {

            return formatResponse('ride_tips', 'error', 'Ride tips already added', $ride);

        } else {
            $ride->tips = $request->tips;
            $ride->total = $ride->total + $request->tips;
            $ride->save();

            return formatResponse('ride_tips', 'success', 'Ride tips added successfully', $ride);
        }
    }

    public function rideOngoing()
    {
        $ride = Ride::where('user_id', auth()->id())->with('destinations', 'driver')->ongoingRide()->first();

        if ($ride == null) {
            return formatResponse('no_ride_found', 'error', 'No Ongoing Ride Found', $ride);
        }

        return formatResponse('ride_ongoing', 'success', 'Ride Ongoing', $ride);
    }

    public function rideCancel(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'cancel_reason' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $ride = Ride::where('user_id', auth()->id())
            ->whereIn('status', [Status::RIDE_INITIATED, Status::RIDE_ACTIVE])
            ->find($id);

        if ($ride == null) {
            return formatResponse('no_ride_found', 'error', 'No Ongoing Ride Found', $ride);
        }

        if ($ride->status == Status::RIDE_CANCELED) {
            return formatResponse('ride_canceled', 'error', 'Ride Already Cancelled', $ride);
        }

        if ($ride->status == Status::RIDE_COMPLETED) {
            return formatResponse('ride_completed', 'error', 'Ride Already Completed', $ride);
        }

        $this->cancelRide($ride->id, Status::USER_TYPE, auth()->id(), $request->cancel_reason);

        return formatResponse('ride_cancel', 'success', 'Ride Cancelled Successfully', $ride);
    }

    public function rideReview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:0|max:5',
            'review' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $findIfReviewed = DriverReview::where('ride_id', $id)->first();
        if ($findIfReviewed != null) {
            return formatResponse('ride_review', 'error', 'Ride Already Reviewed', $findIfReviewed);
        }

        $ride = Ride::where('user_id', auth()->id())->find($id);
        $driverId = $ride->driver_id;

        if ($ride == null) {
            return formatResponse('ride_not_found', 'error', 'No Ride Found', $ride);
        }

        $review = new DriverReview();
        $review->user_id = auth()->user()->id;
        $review->driver_id = $driverId;
        $review->ride_id = $ride->id;
        $review->rating = $request->rating;
        $review->review = $request->review;
        $review->save();

        $count    = DriverReview::where('driver_id', $driverId)->avg('rating');
        $driverId = Driver::find($driverId);
        $driverId->avg_rating = $count;
        $driverId->save();

        return formatResponse('ride_review_added', 'success', 'Ride Review Added Successfully', $review);
    }

    public function rideHistory(Request $request, $flag = 0)
    {
        $user = auth()->user();

        if ($flag == Status::RIDE_COMPLETED) {
            $rides = Ride::where('user_id', $user->id)->completed()->paginate(10);

            $message = 'Completed Rides';

        } elseif ($flag == Status::RIDE_CANCELED) {
            $rides = Ride::where('user_id', $user->id)->canceled()->paginate(10);

            $message = 'Canceled Rides';
        } else {
            $rides = Ride::where('user_id', $user->id)->paginate(10);

            $message = 'All Rides';
        }


        if ($rides->isEmpty()) {
            return formatResponse('ride_history', 'error', 'No ' . $message . ' Found', []);
        }

        return formatResponse('ride_history', $message, 'Ride history', $rides);
    }

    public function rideDetails($id)
    {
        $ride = Ride::with(['destinations', 'driver:id,firstname,lastname,avg_rating,mobile,reward_points'])->where('user_id', auth()->id())->find($id);

        if ($ride == null) {
            return formatResponse('ride_not_found', 'error', 'No Ongoing Ride Found', $ride);
        }

        return formatResponse('ride_details', 'success', 'Ride Details', $ride->load('destinations'));
    }

    public function acceptedRides()
    {
        $rides = Ride::where('user_id', auth()->id())->accepted()->with('destinations', 'driver:id,firstname,lastname,avg_rating,mobile,reward_points,license_expire')->paginate(10);

        if ($rides->isEmpty()) {
            return formatResponse('no_accepted_rides', 'error', 'No accepted rides found', $rides);
        }

        return formatResponse('accepted_rides', 'success', 'Accepted rides', $rides);
    }

}
