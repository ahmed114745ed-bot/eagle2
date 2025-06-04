<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequestResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = \Auth::user();

        if ($user && $user->id === 2) {
            \Log::channel('custom_log')->info('API Request by User ID 2', [
                'user_id' => $user->id,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'request_body' => $request->all(),
                'response_status' => $response->getStatusCode(),
                'response_body' => method_exists($response, 'getContent') ? $response->getContent() : null,
            ]);
        }

        return $response;
    }
}
