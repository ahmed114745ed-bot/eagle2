<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Modules\SalaryTransaction\Entities\ChargeCountry;

class Country extends Model
{
    use TimestampsWithTimezone;

    protected $guarded = ['id'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function chargeCountry()
    {
        return $this->hasMany(ChargeCountry::class, 'country_id');
    }
}
