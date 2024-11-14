<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UtdDecreptHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasHeader('X-Encrypt')) {
            $encrypted = $request->header('X-Encrypt');

            $ZegoEncreyptkey = '7b5d61e6f4a8c2d3e9b7a6f8e1c3d2f4';

            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $ZegoEncreyptkey, 0, substr($ZegoEncreyptkey, 0, 16));
            if ($decrypted === false) {
                return response()->json(['error' => 'Decryption failed'], 500);
            }

            if ($decrypted != config('app.utd_client_id')) {
                return response()->json(['error' => 'something wrong'], 500);
            }
        }

        return $next($request);
    }
}
