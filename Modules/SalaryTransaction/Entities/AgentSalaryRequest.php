<?php

namespace Modules\SalaryTransaction\Entities;

use App\Models\Agency;
use App\Models\Country;
use App\Models\PaymentGateway;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\SalaryTransaction\Database\factories\AgentSalaryRequestFactory;

class AgentSalaryRequest extends Model
{
    use HasFactory;

    protected $guarded = ["id"];


    public function getCreatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }
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
