<?php

namespace App\Lib;

use App\Models\RideFare;
use App\Models\VehicleType;

class RideFareSearch
{
    public static function getFareDetails(
        float $totalDistance,
        float $totalDuration,
        string $pickupAddress,
        array $destinationAddress,
        int $serviceId
    ): array {
        $vehicleTypes = VehicleType::active()->get();

        if (blank($vehicleTypes)) {
            return [
                'error' => true,
                'message' => 'Vehicle types not found',
            ];
        }

        $fareData = [];

        $allRideFares = RideFare::where('service_id', $serviceId)->with(['vehicleClass'])->get();

        foreach ($vehicleTypes as $vehicleType) {
            $rideFares = $allRideFares->where('vehicle_type_id', $vehicleType->id);
// need To improve
            $vehicleTypeData = [];

            foreach ($rideFares as $rideFare) {
                $fare = $rideFare->per_km_fare * $totalDistance;

                $vehicleTypeData[] = [
                    'id' => $rideFare->id,
                    'vehicle_type_id' => $vehicleType->id,
                    'service_id' => $serviceId,
                    'class_id' => $rideFare->vehicle_class_id,
                    'class' => @$rideFare->vehicleClass->name,
                    'fare' => getAmount($fare),
                    'vehicle_type' => $vehicleType->name,
                    'total_duration' => getAmount($totalDuration) . ' minutes',
                    'pickup_address' => $pickupAddress,
                    'destination_address' => $destinationAddress,
                ];
            }

            $fareData[] = [
                'id' => $vehicleType->id,
                'name' => $vehicleType->name,
                'data' => $vehicleTypeData,
            ];
        }

        return [
            'error' => false,
            'data' => $fareData
        ];
    }
}
