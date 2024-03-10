<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    protected $guarded = [];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }

}
