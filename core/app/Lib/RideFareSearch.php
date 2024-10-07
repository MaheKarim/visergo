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

        if ($vehicleTypes->isEmpty()) {
            return [
                'error' => true,
                'message' => 'Vehicle types not found',
            ];
        }

        $allRideFares = RideFare::where('service_id', $serviceId)
            ->with(['vehicleClass:id,name'])
            ->get(['id', 'vehicle_type_id', 'vehicle_class_id', 'per_km_fare']);

        $fareData = $vehicleTypes->map(function ($vehicleType) use ($allRideFares, $totalDistance, $totalDuration, $pickupAddress, $destinationAddress, $serviceId) {
            $rideFares = $allRideFares->where('vehicle_type_id', $vehicleType->id);

            $vehicleTypeData = $rideFares->map(function ($rideFare) use ($vehicleType, $totalDistance, $totalDuration, $pickupAddress, $destinationAddress, $serviceId) {
                $fare = $rideFare->per_km_fare * $totalDistance;

                return [
                    'id' => $rideFare->id,
                    'vehicle_type_id' => $vehicleType->id,
                    'service_id' => $serviceId,
                    'class_id' => $rideFare->vehicle_class_id,
                    'class' => $rideFare->vehicleClass->name ?? null,
                    'fare' => getAmount($fare),
                    'vehicle_type' => $vehicleType->name,
                    'total_duration' => getAmount($totalDuration) . ' minutes',
                    'pickup_address' => $pickupAddress,
                    'destination_address' => $destinationAddress,
                ];
            });

            return [
                'id' => $vehicleType->id,
                'name' => $vehicleType->name,
                'data' => $vehicleTypeData,
            ];
        });

        return [
            'error' => false,
            'data' => $fareData
        ];
    }


}
