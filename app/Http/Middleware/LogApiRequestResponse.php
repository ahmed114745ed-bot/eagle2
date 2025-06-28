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


        $userId = \Auth::id();

        $matchedId = settings()->get('debug_id');

        if ($matchedId && $userId === $matchedId) {
            $log = [
                'user_id' => $userId,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'request_body' => $request->all(),
                'response_status' => $response->getStatusCode(),
                'response_body' => method_exists($response, 'getContent') ? json_decode($response->getContent(), true) : null,
            ];

            // Write as a pure JSON line
            \Log::channel('custom_log')->info($request->fullUrl(). ' '. PHP_EOL . json_encode($log, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        return $response;
    }
}
