<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideCancel extends Model
{

    public function rides()
    {
        return $this->belongsTo(Ride::class);
    }
}
