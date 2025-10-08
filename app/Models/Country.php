<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Modules\SalaryTransaction\Entities\ChargeCountry;

class Country extends Model
{
    use TimestampsWithTimezone;

    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function chargeCountry()
    {
        return $this->hasMany(ChargeCountry::class, 'country_id');
    }

    public function superAdmin()
    {
        return $this->hasMany( SuperAdmin::class,'country_id');

    }
    public function transName()
    {
        return app()->getLocale() == 'ar' ? $this->name : $this->e_name;
    }

    public function supporters()
    {
        return $this->hasManyThrough(
            GiftLog::class,
            User::class,
            'country_id',
            'sender_id',
            'id',
            'id'
        )
        ->selectRaw('sender_id, users.country_id, SUM(giftPrice) as total_sent')
        ->groupBy('sender_id', 'users.country_id')
        ->with('sender')
        ->orderByDesc('total_sent');
    }
}
