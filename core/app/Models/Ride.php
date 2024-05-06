<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    Use Uuid;

    protected $guarded = [];

    public function sosAlerts()
    {
        return $this->hasMany(SosAlert::class, 'ride_id');
    }

    public function destinations()
    {
        return $this->hasMany(RideDestination::class, 'ride_id', 'id');
    }

    public function userContact()
    {
        return $this->belongsTo(ContactList::class, 'ride_for', 'id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
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

    public function appliedCoupon()
    {
        return $this->belongsTo(AppliedCoupon::class);
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

    public function scopeOngoingRide($query)
    {
        return $query->whereIn('status', [Status::RIDE_INITIATED, Status::RIDE_END]);
    }

    public function scopeAccepted($query)
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

    public function scopeCanceled($query)
    {
        return $query->where('status', Status::RIDE_CANCELED);
    }

    public function scopePaymentPending($query)
    {
        return $query->where('payment_status', Status::PAYMENT_PENDING);
    }

    public function scopeOrderId()
    {
        return getOrderId($this->attributes['uuid']);
    }
}
