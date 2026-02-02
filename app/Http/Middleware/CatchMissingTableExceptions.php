<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Exceptions\MissingTableHandler;
use Illuminate\Support\Facades\Log;

/**
 * Middleware to catch and handle missing table exceptions
 */
class CatchMissingTableExceptions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\JsonResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (\Illuminate\Database\QueryException $e) {
            if (MissingTableHandler::isMissingTableException($e)) {
                MissingTableHandler::handle($e);
                
                // For API requests, return JSON response
                if ($request->is('api/*') || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Service temporarily unavailable. Some features are not available.',
                        'data' => null
                    ], 503);
                }
                
                // For web requests, redirect or show error
                return response()->view('errors.503', [], 503);
            }
            
            // Re-throw if it's not a missing table exception
            throw $e;
        }
    }
}
