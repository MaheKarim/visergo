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

    public static function getZone(float $pickupLat, float $pickupLong): ?Zone
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
        $destinationZones = [];

        foreach ($destinations as $index => $destination) {
            $destinationZones [] = self::getZone($destination['lat'], $destination['long']);
        }

        return $destinationZones;
    }

    public static function zonesMatch(Zone $pickupZone, array $destinationZones): bool
    {
        foreach ($destinationZones as $destinationZone) {
            if (@$destinationZone->id != $pickupZone->id) {
                return false;
            }
        }

        return true;
    }

    public static function getZoneId($pickupZone, $destinationZones)
    {
        $zone = self::zonesMatch($pickupZone, $destinationZones);

        return $zone ? $pickupZone->id : null;
    }
}
