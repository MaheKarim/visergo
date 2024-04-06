<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SOSAlert extends Model
{
    use Searchable, GlobalStatus;

    protected $table = 'sos_alerts';

    public function ride()
    {
        return $this->belongsTo(Ride::class, 'ride_id', 'id');
    }

    public function statusBadge(): Attribute
    {
        return new Attribute(function(){
            $html = '';
            if($this->status == Status::PENDING){
                $html = '<span class="badge badge--warning">'.trans('Pending').'</span>';
            }
            elseif($this->status == Status::RESOLVED ){
                $html = '<span><span class="badge badge--success">'.trans('Resolved').'</span>'.'</span>';
            }
            return $html;
        });
    }

    public function scopePending($query)
    {
        return $query->where('status', Status::PENDING);
    }
}

