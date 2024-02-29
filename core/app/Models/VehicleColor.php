<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleColor extends Model
{
    use HasFactory, Searchable, GlobalStatus;

    protected $guarded = ['id'];

    public function models()
    {
        return $this->belongsToMany(VehicleModel::class, 'model_color', 'color_id', 'model_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', Status::ENABLE);
    }
}
