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

        $roomCup = \Cache::rememberForever('room_cup', function () {
            $setting =   Setting::where('key', 'room_cup')->first();
            return $setting?->value ?? 0;
        });
        if (!$roomCup) return throw new NotFoundHttpException();

        return $next($request);
    }
}
