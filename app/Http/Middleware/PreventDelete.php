<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDelete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->input('_action') === 'Encore_Admin_Grid_Actions_Delete' ) {
            return response()->json([
                'status'  => false,
                'message' => 'لا يمكنك الحذف لان دي نسخه تجريبيه!',
            ], 403);
        }

        return $next($request);
    }
}
