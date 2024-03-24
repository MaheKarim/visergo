<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use GlobalStatus, Searchable;

    public function vehicleServices()
    {
        return $this->belongsToMany(Service::class, 'vehicle_services', 'vehicle_type_id', 'service_id');
    }

//    public function vehicleServices()
//    {
//        return $this->belongsToMany(VehicleService::class, 'vehicle_services', 'vehicle_type_id', 'service_id');
//    }

    public function classes()
    {
        return $this->belongsToMany(VehicleClass::class, 'type_classes', 'vehicle_type_id', 'vehicle_class_id');
    }

//    public function vehicleServices()
//    {
//        return $this->hasMany(VehicleService::class);
//    }
//

//    public function classes()
//    {
//        return $this->hasMany(TypeClass::class);
//    }

    public  function  rideFares()
    {
        return $this->hasMany(RideFare::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', Status::ENABLE);
    }
}
