<?php

namespace App\Lib;

use App\Models\Driver;
use App\Models\Ride;
use App\Models\User;

class RewardPoints
{
    public static function distribute($rideId)
    {
        $ride = Ride::find($rideId);
        $driver = $ride->driver_id;
        $user = $ride->user_id;
        $amount = $ride->total;

        $totalPoints =intval($amount / gs('spend_amount_for_reward') * gs('reward_point'));
        $pointsPerUser = intval($totalPoints / 2);

        $driver = Driver::where('id', $driver)->first();
        $driver->reward_points += $pointsPerUser;
        $driver->save();

        $user = User::where('id', $user)->first();
        $user->reward_points += $pointsPerUser;
        $user->save();

        return $totalPoints;
    }
}
