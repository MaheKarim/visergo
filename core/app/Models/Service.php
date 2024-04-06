<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    public function rideFareId()
    {
        return $this->belongsTo(RideFare::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', Status::ENABLE);
    }
}
