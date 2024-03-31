<?php

namespace App\Lib;

use Illuminate\Support\Collection;

class DistanceMatrix {

    public static function getDistanceMatrix($origins, $destinations)
    {
        $apiKey = gs('location_api');
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origins}&destinations={$destinations}&key={$apiKey}";

        $response = json_decode(file_get_contents($url), true);

        if ($response['status'] != 'OK') {
            return response()->json([
                'remark' => 'api_error',
                'status' => 'error',
                'message' => $response['status'],
            ]);
        }

        return $response;
    }

    public static function getTotalDistanceAndDuration(array $origins, array $destinations)
    {
        $originString = implode("|", array_map(function ($a) {
            return implode(",", $a);
        }, $origins));

        $destinationString = implode("|", array_map(function ($a) {
            return implode(",", $a);
        }, $destinations));

        $response = self::getDistanceMatrix($originString, $destinationString);

        $pairs = [];
        for ($i = 0; $i < count($origins) - 1; $i++) {
            $pairs[] = [$i, $i + 1];
        }

        $distances = [];
        $addresses = array_values(array_unique(array_merge($response['origin_addresses'], $response['destination_addresses'])));

        foreach ($pairs as $pair) {
            $distances[] = $response['rows'][$pair[0]]['elements'][$pair[1 - 1]];
        }

        $totalDistance = (Collection::make($distances)->sum('distance.value') / 1000);
        $totalDuration = (Collection::make($distances)->sum('duration.value') / 60);

        return (object) [
            'total_distance' => $totalDistance,
            'total_duration' => $totalDuration,
            'addresses' => $addresses,
            'pickup_address' => $response['origin_addresses'][0],
            'destination_address' => $response['destination_addresses'],
        ];

    }

}
