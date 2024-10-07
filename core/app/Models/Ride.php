<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\Searchable;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    Use Uuid, Searchable;

    protected $guarded = [];

    public function sosAlerts()
    {
        return $this->hasMany(SosAlert::class, 'ride_id');
    }

    public function destinations()
    {
        return $this->hasMany(RideDestination::class, 'ride_id', 'id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
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

    public function payment()
    {
        return $this->hasOne(Deposit::class, 'ride_id')->where('status', Status::PAYMENT_SUCCESS);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function vehicleClass()
    {
        return $this->belongsTo(VehicleClass::class);
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
        return $query->whereIn('status', [Status::RIDE_INITIATED, Status::RIDE_ACTIVE, Status::RIDE_ONGOING]);
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

    public function statusBadge(): Attribute
    {
        return new Attribute(function () {
            $html = '';
            if ($this->status == Status::RIDE_INITIATED && $this->driver_id == null) {
                $html = '<span class="badge badge--primary">' . trans('Pending') . '</span>';
            } elseif ($this->status == Status::RIDE_ACTIVE && $this->driver_id != null) {
                $html = '<span class="badge badge--info">' . trans('Accepted') . '</span>';
            } elseif ($this->status == Status::RIDE_ONGOING && $this->driver_id != null && $this->otp == null) {
                $html = '<span class="badge badge--warning">' . trans('Running') . '</span>';
            } elseif ($this->status == Status::RIDE_COMPLETED) {
                            $html = '<span class="badge badge--success">' . trans('Completed') . '</span>';
            } elseif ($this->status == Status::RIDE_CANCELED) {
                $html = '<span class="badge badge--danger">' . trans('Canceled') . '</span>';
            }
            return $html;
        });
    }

    public function paymentTypes(): Attribute
    {
        return new Attribute(function () {
            $html = '';
            if ($this->payment_type == Status::ONLINE_PAYMENT) {
                $html = '<span class="badge badge--warning">' . '<i class="far fa-credit-card me-2"></i>' . trans('Gateway') . '</span>';
            } elseif ($this->payment_type == Status::CASH_PAYMENT) {
                $html = '<span class="badge badge--success">' . '<i class="fas fa-money-bill me-2"></i>' . trans('Cash') . '</span>';
            } else {
                $html = '<span class="badge badge--primary">' . '<i class="fas fa-wallet me-2"></i>' . trans('Wallet') . '</span>';
            }
            return $html;
        });
    }

    public function paymentStatusType(): Attribute
    {
        return new Attribute(function () {
            $html = '';
            if ($this->payment_status == Status::PAYMENT_SUCCESS) {
                $html = '<span class="badge badge--success">' . '<i class="las la-check me-2"></i>' . trans('Paid') . '</span>';
            } elseif ($this->payment_status == Status::PAYMENT_PENDING) {
                $html = '<span class="badge badge--warning">' . '<i class="las la-redo-alt me-2"></i>' . trans('Pending') . '</span>';
            } elseif ($this->payment_status == Status::PAYMENT_REJECT) {
                $html = '<span class="badge badge--danger">' . '<i class="las la-times me-2"></i>' . trans('Rejected') . '</span>';
            } else {
                $html = '<span class="badge badge--primary">' . '<i class="fas fa-wallet me-2"></i>' . trans('Wallet') . '</span>';
            }
            return $html;
        });
    }
}
