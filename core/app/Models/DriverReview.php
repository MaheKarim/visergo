<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverReview extends Model
{
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
