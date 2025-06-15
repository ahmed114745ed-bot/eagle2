<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pack extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $dates = ['deleted_at'];

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
    public function ware()
    {
        return $this->belongsTo(Ware::class, 'target_id');
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class, 'target_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sender()
    {
        return $this->belongTo(User::class, 'sender_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp);
        })->where('is_used', 1);
    }

    public function getTypeGet()
    {
        switch ($this->get_type) {
            case 1:
                return __('vip level automatic acquisition');
            case 2:
                return __('activities');
            case 3:
                return __('treasure box');
            case 4:
                return __('purchase');
            case 5:
                return __('background addition');
            default:
                return '-';
        }
    }

    public function getType()
    {
        switch ($this->type) {
        case 1: return trans('Gemstone');
        case 3: return trans('Card Scroll');
        case 4: return trans('Avatar Frame');
        case 5: return trans('Bubble Frame');
        case 6: return trans('Entering Special Effects');
        case 7: return trans('Microphone Aperture');
        case 8: return trans('Badge');
        case 9: return trans('NoKick');
        case 10: return trans('Icon');
        case 11: return trans('intro animation');
        case 12: return trans('wapel');
        case 13: return trans('hide country');
        case 14: return trans('vip gifts');
        case 15: return trans('no pan');
        case 16: return trans('hidden room');
        case 17: return trans('anonymous man');
        case 18: return trans('colored name');
        case 19: return trans('profile visitors hide in');
        case 20: return trans('hide last active');
        case 21: return trans('sound effect');
        case 22: return trans('upload GIF image');
        default: return '-';
    }
    }
}
