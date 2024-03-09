<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverPasswordReset extends Model
{
    public $table = 'driver_password_resets';

    public $timestamps = false;

    protected $hidden = [
        'token'
    ];
}
