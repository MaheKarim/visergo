<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    Use Uuid;

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

    public function review()
    {
        return $this->hasOne(DriverReview::class);
    }
    public function rideCancels()
    {
        return $this->hasMany(RideCancel::class, 'ride_id', 'id');
    }
    // Scope

    public function scopeActive($query)
    {
        return $query->where('status', Status::RIDE_ACTIVE);
    }

    public function scopeInitiated($query)
    {
        return $query->where('status', Status::RIDE_INITIATED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', Status::RIDE_COMPLETED);
    }

    public function scopeRideEnd($query)
    {
        return $query->where('status', Status::RIDE_END);
    }

    public function scopeOngoingRide($query)
    {
        return $query->whereIn('status', [Status::RIDE_INITIATED, Status::RIDE_END]);
    }

    public function scopePaymentPending($query)
    {
        return $query->where('payment_status', Status::PAYMENT_PENDING);
    }


}
