<?php

namespace  Modules\RoomCup\Http\Middleware;

use Closure;
use App\Models\Setting;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CheckAllowedApp
{

    protected array $allowedApps = [
        'tempo',
        'moon light',
        'bigo room',
        'eagle',
        'pop live'
    ];

    public function handle(Request $request, Closure $next)
    {
        // $appName = strtolower(env('APP_NAME', ''));
        // if (!in_array($appName, $this->allowedApps)) {
        //     throw new NotFoundHttpException();
        // }

        $getSetting = function ($key, $default = 0) {
            return \Cache::rememberForever($key, function () use ($key, $default) {
                return Setting::where('key', $key)->value('value') ?? $default;
            });
        };

        $roomCup = $getSetting('room_cup');
        $roomCupSetting = $getSetting('room_cup_setting');
        if (!$roomCup && !$roomCupSetting) return throw new NotFoundHttpException();

        return $next($request);
    }
}
