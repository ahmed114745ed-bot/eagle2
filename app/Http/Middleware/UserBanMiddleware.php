<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Ban;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Facades\UserHandling;

class UserBanMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        $message = UserHandling::getUserBanType($user->original_uuid, $request);

        if ($message) {
            return  Common::apiResponse(0, $message, null, 422);
        }
        return $next($request);
    }
}
