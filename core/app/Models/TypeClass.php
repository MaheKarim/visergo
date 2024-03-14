<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeClass extends Model
{

    protected $guarded = [];

    public function vehicleType()
    {
        return $this->belongsToMany(VehicleType::class);
    }
}
