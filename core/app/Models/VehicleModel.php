<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    use HasFactory, Searchable, GlobalStatus;

    protected $guarded = ['id'];


    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id', 'id');
    }

    public function vehicleClass()
    {
        return $this->belongsTo(VehicleClass::class, 'vehicle_class_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }

    public function colors()
    {
        return $this->belongsToMany(VehicleColor::class, 'model_color', 'model_id', 'color_id');
    }

}
