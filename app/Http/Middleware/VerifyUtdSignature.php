<?php

namespace App\Http\Middleware;

use App\Helpers\Common;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VerifyUtdSignature
{
    public function handle(Request $request, Closure $next)
    {
        try {

            $gameSetting = Common::getByCode('utd_games');

            if (!$gameSetting || !$gameSetting->is_active) {
                return $this->error(4005, 'UTD game integration is not active');
            }

            $key = $gameSetting->app_key ?? '';
            if (!$key) {
                return $this->error(4005, 'Missing API key');
            }

            if (!$request->has('sign') || !$request->has('timestamp')) {
                return $this->error(4005, 'Missing signature parameters');
            }

            // Timestamp validation — reject requests older than 60 seconds
            $timestamp = (int) $request->input('timestamp');
            if (abs(time() - $timestamp) > 60) {
                return $this->error(10004, 'Request expired');
            }

            // Duplicate order check
            if ($request->has('orderId')) {
                if (Cache::has("utd_order_{$request->orderId}")) {
                    return $this->error(10003, 'Order already exists');
                }
            }

            // Build signature: sort all params (except sign), concatenate key=value, append app_key
            $params = $request->except('sign');
            ksort($params);
            $rawString = '';
            foreach ($params as $k => $v) {
                $rawString .= $k . '=' . $v . '&';
            }
            $rawString .= 'key=' . $key;

            $expectedSign = md5($rawString);

            if (!hash_equals(strtolower($expectedSign), strtolower($request->input('sign')))) {
                Log::warning('UTD Signature mismatch', [
                    'expected' => $expectedSign,
                    'received' => $request->input('sign'),
                ]);
                return $this->error(10004, 'Verify signature fail');
            }

            return $next($request);
        } catch (\Throwable $e) {
            Log::error('UTD Middleware Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return $this->error(5000, 'Internal server error');
        }
    }

    private function error(int $code, string $message)
    {
        return response()->json([
            'errorCode' => $code,
            'errorMsg'  => $message,
            'data'      => [],
        ]);
    }
}
