<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory, Searchable, GlobalStatus;

    protected $guarded = ['id'];

    public function scopeActive()
    {
        return $this->where('status', Status::ENABLE);
    }
}
