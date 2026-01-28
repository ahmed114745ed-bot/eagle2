<?php

namespace Utd\Room\Entities;

use App\Models\User;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class RoomView extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'rooms_view_with_today_rank';

    protected $appends = ['lang', 'country'];

    public function getRoomBackgroundAttribute($val)
    {
        return @Background::query()->where('id', $val)->first()->img;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }

    public function getLangAttribute()
    {
        return @$this->owner->country->language;
    }

    public function getCountryAttribute()
    {
        $country = @$this->owner->country;
        if ($country) {
            return [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
            ];
        }
        return null;
    }
}
