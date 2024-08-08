<?php

namespace Modules\SalaryTransaction\Entities;

use App\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\SalaryTransaction\Database\factories\ChargeCountryFactory;

class ChargeCountry extends Model
{
    use HasFactory;

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
