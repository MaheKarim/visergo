<?php

namespace App\Lib;

use App\Models\Zone;

class ZoneHelper {

    public static function underZone(float $lat, float $long, Zone $zone): bool
    {
        $inside = false;
        // Prepare the test point
        $x = $lat;
        $y = $long;
        $coordinates = json_decode($zone->coordinates->toJson())->coordinates[0];

        $verticesCount = count($coordinates);
        for ($i = 0, $j = $verticesCount - 1; $i < $verticesCount; $j = $i++) {
            $xi = $coordinates[$i][1]; //lat
            $yi = $coordinates[$i][0]; // lng
            $xj = $coordinates[$j][1]; //lat
            $yj = $coordinates[$j][0]; // lng
            // Check if the test point is between the vertex's y-coordinates
            $intersect = (($yi > $y) != ($yj > $y)) &&
                ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
            if ($intersect) {
                $inside = !$inside; // Toggle the inside status
            }
        }
        return $inside;
    }

    public static function getZoneId($lat, $long)
    {
        $zones = Zone::active()->get();
        foreach ($zones as $zone) {
            if (self::underZone($lat, $long, $zone)) {
                return $zone->id;
            }
        }
        return null;
    }

    public static function getPickupZone(float $pickupLat, float $pickupLong): ?Zone
    {
        $zones = Zone::active()->get();

        foreach ($zones as $zone) {
            if (self::underZone($pickupLat, $pickupLong, $zone)) {
                return $zone;
            }
        }

        return null;
    }

    public static function getDestinationZones(array $destinations): array
    {
        $zones = Zone::active()->get();
        $destinationZones = [];

        foreach ($destinations as $index => $destination) {
            foreach ($zones as $zone) {
                $isThisZone = self::underZone($destination['lat'], $destination['long'], $zone);
                $destinationZones[$index]['zone_id'] = $isThisZone ? $zone->id : null;
            }
        }

        return $destinationZones;
    }

    public static function zonesMatch(?Zone $pickupZone, array $destinationZones): bool
    {
        if (!$pickupZone) {
            return false;
        }

        foreach ($destinationZones as $destination) {
            if (!isset($destination['zone_id']) || $destination['zone_id'] !== $pickupZone->id) {
                return false;
            }
        }

        return true;
    }
}
