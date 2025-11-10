<?php

namespace  Modules\RoomCup\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CheckAllowedApp
{

    protected array $allowedApps = [
        'tempo',
        'moon',
        'bigo',
        'eagle',
        'Pop'
    ];

    public function handle(Request $request, Closure $next)
    {
        $appName = strtolower(env('APP_NAME', ''));
        if (!in_array($appName, $this->allowedApps)) {
            throw new NotFoundHttpException();
        }

        return $next($request);
    }
}
