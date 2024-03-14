<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideFare extends Model
{
    use HasFactory;


    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function vehicleClass()
    {
        return $this->belongsTo(VehicleClass::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }
}
