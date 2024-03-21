<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    protected $guarded = [];

    public function destinations()
    {
        return $this->hasMany(RideDestination::class, 'ride_id', 'id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }

    // Scope

    public function scopeActive($query)
    {
        return $query->where('status', Status::RIDE_ACTIVE);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', Status::RIDE_COMPLETED);
    }

    public function scopeRideEnd($query)
    {
        return $query->where('status', Status::RIDE_END);

    }


}
