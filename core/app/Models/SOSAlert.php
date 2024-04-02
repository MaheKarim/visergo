<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class SOSAlert extends Model
{
    use GlobalStatus;

    protected $table = 'sos_alerts';

    public function ride()
    {
        return $this->belongsTo(Ride::class, 'user_id', 'id');
    }
}

