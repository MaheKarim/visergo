<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use GlobalStatus, Searchable;

//    public function vehicle()
//    {
//        return $this->hasMany(Vehicle::class, 'vehicle_type_id', 'id');
//    }

}
