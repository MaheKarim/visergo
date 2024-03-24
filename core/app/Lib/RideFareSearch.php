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
    ): array{
        $vehicleTypes = VehicleType::active()->get();

        if (empty($vehicleTypes)) {
            return [
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => 'Vehicle types not found',
            ];
        }

        $responses = [];
        foreach ($vehicleTypes as $vehicleType) {
            $multipleClass = RideFare::where('vehicle_type_id', $vehicleType->id)
                ->where('service_id', $serviceId)
                ->with(['vehicleClass'])->get();

            $vehicleTypeData = [];
            foreach ($multipleClass as $class) {
                $baseFare = $class->fare;
                $fare = $baseFare * $totalDistance;
                $getVehicleClass = data_get($class, 'vehicleClass.name');
                $getVehicleClassId = data_get($class, 'vehicleClass.id');

                $vehicleTypeData[] = [
                    'id' => $class->id,
                    'vehicle_type_id' => $vehicleType->id,
                    'service_id' => $serviceId,
                    'class_id' => $getVehicleClassId,
                    'class' => $getVehicleClass,
                    'fare' => getAmount($fare) . ' ' . config('app.currency'),
                    'vehicle_type' => $vehicleType->name,
                    'total_duration' => getAmount($totalDuration) . ' minutes',
                    'pickup_address' => $pickupAddress,
                    'destination_address' => $destinationAddress,
                ];
            }

            $responses[] = [
                'id' => $vehicleType->id,
                'name' => $vehicleType->name,
                'data' => $vehicleTypeData,
            ];
        }

        return $responses;
    }
}
