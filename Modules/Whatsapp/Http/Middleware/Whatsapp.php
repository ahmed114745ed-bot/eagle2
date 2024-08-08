<?php

namespace Modules\Whatsapp\Http\Middleware;

use App\Helpers\Common;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Whatsapp\Entities\WhatsappApp;

class Whatsapp
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


        if (!Auth::user() instanceof WhatsappApp) {
            return Common::apiResponse(false, __('un_auth'),401 );
        }
        return $next($request);
    }
}
