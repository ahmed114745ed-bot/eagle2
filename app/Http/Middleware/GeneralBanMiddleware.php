<?php

namespace App\Http\Middleware;

use App\Facades\UserHandling;
use App\Helpers\Common;
use App\Models\Ban;
use Closure;
use Illuminate\Http\Request;

class GeneralBanMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user ();

        $message = UserHandling::hasReasonOfBan($user->uuid, $request);

        if ($message){
            return  Common::apiResponse(0, $message, null, 501);
        }

        return $next($request);
    }
}

