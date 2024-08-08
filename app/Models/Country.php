<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\SalaryTransaction\Entities\ChargeCountry;

class Country extends Model
{
    protected $guarded = ['id'];

    public function users()
    {
        return $this->hasMany(User::class );
    }

    Public function chargeCountry()
    {
        return $this->hasMany(ChargeCountry::class ,'country_id');
    }
}
