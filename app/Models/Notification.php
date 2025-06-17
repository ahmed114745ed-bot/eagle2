<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Notification extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $table = 'notifications';

    protected $fillable = ['key'];

    public static function boot()
    {
        parent::boot();

        self::saving(function ($notification) {
            $existingNotification = self::where('key', $notification->key)->first();
            if ($existingNotification && $existingNotification->id !== $notification->id) {
                throw new Exception('The key must be unique.');
            }
        });

        self::saved(function ($notification) {
            $languages = ['ar', 'en', 'tr', 'hi'];

            // foreach ($languages as $code) {
            //     NotificationTranslation::updateOrCreate(
            //         [
            //             'notification_id' => $notification->id,
            //             'language' => $code
            //         ],
            //         [
            //             'title'   => request()->input("title_{$code}"),
            //             'message' => request()->input("message_{$code}")
            //         ]
            //     );
            // }
            Cache::put($notification->key, $notification->translations->toArray());
        });

        self::deleted(function ($notification) {
            Cache::forget($notification->key);
        });
    }

    public function translations()
    {
        return $this->hasMany(NotificationTranslation::class, 'notification_id');
    }
}
