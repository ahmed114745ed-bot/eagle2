<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static withoutAppends()
 */
class Room extends Model
{

    /*
 * To enable and disable observer saving and updating methods
 */
    public $enableSaving = true;
    public static $withoutAppends = false;

    protected $guarded = ['id'];
    protected $appends = ['lang', 'country'];
    protected $casts = [
        'is_pk' => 'boolean',
        'is_comment_closed'=> 'boolean',
        'is_live' => 'boolean',
    ];
    //    protected $attributes = ['room_background'];


    public function getCreatedAtAttribute($value)
    {
        $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = \Cache::rememberForever($cacheKey, function () {
        $setting = \App\Models\Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
    return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
        $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = \Cache::rememberForever($cacheKey, function () {
        $setting = \App\Models\Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
    return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'uid');
    }

    public function level()
    {
        return $this->belongsTo(Vip::class, 'level_id');
    }

    public function roomSalary()
    {
        return $this->hasMany(RoomSalary::class, 'room_id');
    }

    public function getSalaryAttribute()
    {
        $salary = RoomSalary::query()->where('room_id', $this->id)->where('is_paid', 0)->sum(\DB::raw('salary - cut_amount'));
        return $salary;
    }

    public function scopeWithoutAppends(Builder $query): Builder
    {
        self::$withoutAppends = true;

        return $query;
    }

    //    public function getRoomBackgroundAttribute($val){
    //        if (self::$withoutAppends){
    //            return;
    //        }
    //        return @Background::query ()->where ('id',$val)->first ()->img;
    //    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }

    public function game()
    {
        return $this->belongsTo(AllGame::class,  'game_id');
    }

    public function getLangAttribute()
    {
        if (self::$withoutAppends) {
            return;
        }
        return @$this->owner->country->language;
    }

    public function getCountryAttribute()
    {
        if (self::$withoutAppends) {
            return;
        }
        $country = @$this->owner->country;
        return $country;
    }


    public function myClass()
    {
        return $this->belongsTo(RoomCategory::class, 'room_class')->select('name', 'img');
    }

    public function myType()
    {
        return $this->belongsTo(RoomCategory::class, 'room_type')->select('name', 'img');
    }

    public function gifts()
    {
        return $this->hasMany(GiftLog::class, 'roomowner_id', 'uid');
    }

    public function topUserGift()
    {
        return $this->hasOne(GiftLog::class, 'roomowner_id', 'id')
            ->selectRaw("SUM(giftPrice) as exp, sender_id, roomowner_id")
            ->whereHas('sender') // Ensures only valid senders are included
            ->groupBy('sender_id', 'roomowner_id')
            ->orderByDesc('exp');
    }


    public function roomCategory()
    {
        return $this->belongsTo(RoomCategory::class, 'room_type');
    }

    public function family()
    {
        return $this->belongsTo(Family::class, 'uid', 'user_id');
    }


    public function lastPk()
    {
        return $this->hasOne(Pk::class, 'room_id', 'id')->where('status', 1)->where('end_at', ">=", now())->orderByDesc('id');
    }

    public function getSessionStringAttribute()
    {
        return numToString($this->session);
    }


    public function getMicrophoneAttribute()
    {
        $microphoneWithOldSeat = array_key_exists('microphone', $this->attributes) ? $this->attributes['microphone'] : '';
        $microphoneWithOldSeat = explode(',', $microphoneWithOldSeat);
        $array    = array_map(function ($id) {
            return explode('#', $id)[0];
        }, $microphoneWithOldSeat);
        return implode(',', $array);
    }

    public function getMainMicrophoneAttribute()
    {
        $microphoneWithOldSeat = array_key_exists('microphone', $this->attributes) ? $this->attributes['microphone'] : '';
        $microphoneWithOldSeat = explode(',', $microphoneWithOldSeat);
        $array    = array_map(function ($id) {
            $arr    = collect(explode('#', $id));
            $value   = $arr->last();
            return $value > 0 ? 0 : $value;
        }, $microphoneWithOldSeat);
        return implode(',', $array);
    }

    public function getCountRoomSocketAttribute()
    {
        $ids        = explode(',', $this->room_visitor);
        $countPacks = Pack::query()->whereIn('user_id', $ids)
            ->where('is_used', 1)
            ->where("type", 17)
            ->where(function ($q) {
                $q->where('packs.expire', 0)->orWhere('packs.expire', '>=', time());
            })
            ->count();
        foreach ($ids as $indes => $id) {
            if ($id == '' || $id < 0) {
                unset($ids[$indes]);
            }
        }

        return count($ids) - $countPacks;
    }

    public function roomVisitors(): HasMany
    {
        return $this->hasMany(RoomVisitor::class, 'room_id');
    }

    public function roomVisitorUsers(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, RoomVisitor::class, 'room_visitors.room_id', 'id', 'id', 'room_visitors.user_id');
    }

    public function getRoomVisitorAttribute(): string
    {
        $usersIds = $this->roomVisitors->pluck('user_id')->toArray();
        return count($usersIds) > 0 ? implode(',', $usersIds) : '';
    }

    public function topUser()
    {
        return $this->belongsTo(User::class, 'top_user_id');
    }

    public function boxUse()
    {
        return $this->hasMany(BoxUse::class, 'room_id');
    }

    public function backgroundImage()
    {
        return $this->hasOneThrough(
            RequestBackgroundImage::class,
            User::class,
            'id',
            'owner_room_id',
            'uid',
            'id'
        )->where('request_background_images.status', 1);
    }

    public function getVisitorsImages()
    {
        $visitors = $this->roomVisitorUsers;

        return $visitors->pluck('profile.avatar');
    }

    public function background()
    {
        return $this->belongsTo(Background::class, 'room_background');
    }

    public function getFinalRoomImageAttribute()
    {
        if ($this->is_pk_custom && $this->mode == 3) return PK_IMAGE;
        if ( $this->mode == 8) return BaCKGROUND_IMAGE_MODE_8;

        $var = /*$this->mode == '3' ?
            'custom_image/back-black.png' :*/
            ($this->backgroundImage?->img ?: ($this->background?->img ?: (request()->default_background ?? \DB::table('backgrounds')->where('enable', 1)->orderBy('id', 'asc')->limit(1)->first()->img)));
        return $var;
    }

    public function getModeAttribute($value)
    {
        return $value === 0 ? 3 : $value;
    }

    protected function  getAdminsAttribute()
    {
       return explode(',', $this->room_admin);

    }

    public function scopeWithoutVisitorsAndActiveMic($query)
    {
        return $query->whereDoesntHave('roomVisitors')
                    ->where('microphone', '!=', '0,0,0,0,0,0,0,0,0,0');
    }

    public function bans()
    {
        return $this->hasMany(BanRoom::class);
    }

}
