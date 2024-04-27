<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;

class GatewayCurrency extends Model
{

    protected $hidden = [
        'gateway_parameter'
    ];

    protected $casts = ['status' => 'boolean'];

    // Relation
    public function method()
    {
        return $this->belongsTo(Gateway::class, 'method_code', 'code');
    }

    public function currencyIdentifier()
    {
        return $this->name ?? $this->method->name . ' ' . $this->currency;
    }

    public function scopeBaseCurrency()
    {
        return $this->method->crypto == Status::ENABLE ? 'USD' : $this->currency;
    }

    public function scopeBaseSymbol()
    {
        return $this->method->crypto == Status::ENABLE ? '$' : $this->symbol;
    }

    function totalCharge($amount){
        return $this->fixed_charge + ($amount * $this->percent_charge / 100);
    }

    public function scopeActive()
    {
        return $this->status == Status::ENABLE;
    }

    public function manualGateway($methodCode){
        $this->fixed_charge = 0;
        $this->percent_charge = 0;
        $this->method_code = $methodCode;
        $this->currency = gs('cur_text');
        $this->rate = 1;
    }

}
