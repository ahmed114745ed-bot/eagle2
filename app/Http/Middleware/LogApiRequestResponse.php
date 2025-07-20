<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequestResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $userId = Auth::id();

        if (! $userId) {
            $userId = Auth::guard('sanctum')->id();
        }
        $matchedIds = settings()->get('debug_ids');

        if (gettype($matchedIds) === 'array' && in_array($userId, $matchedIds)) {
            $log = [
                'user_id' => $userId,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'request_body' => $request->all(),
                'response_status' => $response->getStatusCode(),
                'response_body' => method_exists($response, 'getContent') ? json_decode($response->getContent(), true) : null,
            ];

            if (settings()->get('header_log')) {
                $headers = $request->headers->all();

                // Remove sensitive headers (case-insensitive)
                unset($headers['authorization']);
                unset($headers['cookie']);
                unset($headers['x-api-key']); // Add any others you want excluded

                $log['headers'] = $headers;
            }

            // Write as a pure JSON line
            Log::channel('custom_log')->info($request->fullUrl()." $userId ".PHP_EOL.json_encode($log, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        return $response;
    }
}
