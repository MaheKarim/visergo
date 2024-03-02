<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationReason extends Model
{
    use HasFactory, GlobalStatus;

    protected $guarded = ['id'];

    public function scopeActive($query)
    {
        return $query->where('status', Status::ENABLE);
    }
}
