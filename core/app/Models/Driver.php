<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\DriverNotify;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Driver extends Authenticatable
{
    use HasApiTokens, Searchable, DriverNotify;

    protected $guarded=['id'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','ver_code','balance'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'address' => 'object',
        'driver_verification' => 'object',
        'vehicle_verification' => 'object',
        'ver_code_send_at' => 'datetime'
    ];

    public function loginLogs()
    {
        return $this->hasMany(DriverLogin::class);
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', Status::USER_ACTIVE)->where('ev',Status::VERIFIED)->where('sv',Status::VERIFIED);
    }

    public function scopeBanned($query)
    {
        return $query->where('status', Status::USER_BAN);
    }

    public function scopeEmailUnverified($query)
    {
        return $query->where('ev', Status::UNVERIFIED);
    }

    public function scopeMobileUnverified($query)
    {
        return $query->where('sv', Status::UNVERIFIED);
    }

    public function scopeVehicleUnverified($query)
    {
        return $query->where('vv', Status::KYC_UNVERIFIED);
    }
    public function scopeVehiclePending($query)
    {
        return $query->where('vv', Status::KYC_PENDING);
    }

    public function scopeKycPending($query)
    {
        return $query->where('dv', Status::KYC_PENDING);
    }

    public function scopeEmailVerified($query)
    {
        return $query->where('ev', Status::VERIFIED);
    }

    public function scopeMobileVerified($query)
    {
        return $query->where('sv', Status::VERIFIED);
    }

    public function scopeWithBalance($query)
    {
        return $query->where('balance','>', 0);
    }
}
