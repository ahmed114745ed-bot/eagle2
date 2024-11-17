<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StopInProduction
{

    public function handle(Request $request, \Closure $next, ...$guards)
    {
        if (config('app.env') == 'production' && $this->isBlock()) abort(403, __('something went wrong'));

        return $next($request);
    }

    /**
     * @return void
     */
    public function isBlock(): bool
    {
        $blockProductionRoutes = [
            'helpers/routes',
            'helpers/terminal/database',
            'helpers/terminal/artisan',
            'helpers/scaffold',
        ];


        foreach ($blockProductionRoutes as $route) {
            $isBlock = str_ends_with( \request()->url() , $route);
            if ($isBlock) break;
        }

        return @$isBlock ?? false;
    }

}
