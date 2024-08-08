<?php

namespace Modules\SalaryTransaction\Entities;

use App\Models\Agency;
use App\Models\Country;
use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\SalaryTransaction\Database\factories\AgentSalaryRequestFactory;

class AgentSalaryRequest extends Model
{
    use HasFactory;

    protected $guarded = ["id"];


    public function agent()
    {
        return $this->belongsTo(User::class,'agency_owner_id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class,'agency_id');
    }

    public function payment_gateway()
    {
        return $this->belongsTo(PaymentGateway::class,"payment_gateway_id");
    }

    public function country()
    {
        return $this->belongsTo(Country::class,"country_id");
    }
}
