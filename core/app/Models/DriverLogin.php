<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLogin extends Model
{
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
