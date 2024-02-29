<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleColor extends Model
{
    use HasFactory, Searchable, GlobalStatus;

    protected $guarded = ['id'];
}
