<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClearSessionAfterRequest
{
    public function handle(Request $request, Closure $next)
    {
        // Proceed with the request
        $response = $next($request);

        // Clear the session after the request is handled
        // $request->session()->forget('auth');

        return $response;
    }
}