<?php

namespace Utd\Vip\Http\Middleware;

use App\Services\AppFeatureService;
use Closure;

class CheckVipFeatureEnabled
{
    public function handle($request, Closure $next)
    {
        (new AppFeatureService)->validateStatusEnable('vips');

        return $next($request);
    }
}
