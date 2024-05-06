<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model {
    use GlobalStatus, Searchable;

    protected $guarded = ['id'];

    protected $casts = [
        'starts_from'   => 'datetime',
        'ends_at'       => 'datetime',
        'coupon_amount' => 'double',
        'minimum_spend' => 'double',
        'maximum_spend' => 'double',
    ];

    public function appliedCoupons() {
        return $this->hasMany(AppliedCoupon::class);
    }

    public function discountTypeBadge() {
        if ($this->discount_type == Status::DISCOUNT_FIXED) {
            return '<span class="badge badge--primary">' . trans('Fixed') . '</span>';
        } else {
            return '<span class="badge badge--dark">' . trans('Percentage') . '</span>';
        }
    }

    public function discountAmount($total) {
        return $this->discount_type == Status::DISCOUNT_FIXED ? $this->coupon_amount : $total * $this->coupon_amount / 100;
    }

    public function scopeActiveAndValid($query) {
        return $query->active()->where('starts_from', '<=', today())->where('ends_at', '>=', today());
    }

    public function scopeMatchCode($query, $code) {
        return $query->whereRaw("BINARY coupon_code = ?", $code);
    }
}
