<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

/**
 * Handler for missing table exceptions
 */
class MissingTableHandler
{
    /**
     * Handle missing table exceptions
     * 
     * @param \Throwable $exception
     * @return bool Returns true if exception was handled
     */
    public static function handle(\Throwable $exception): bool
    {
        if ($exception instanceof QueryException) {
            $errorCode = $exception->getCode();
            $errorMessage = $exception->getMessage();
            
            // Check if it's a "table not found" error (SQLSTATE 42S02)
            if ($errorCode === '42S02' || str_contains($errorMessage, 'Base table or view not found')) {
                // Extract table name from error message
                preg_match("/Table '([^']+)\.([^']+)' doesn't exist/", $errorMessage, $matches);
                $tableName = $matches[2] ?? 'unknown';
                
                Log::warning("Missing table detected: {$tableName}", [
                    'error_code' => $errorCode,
                    'message' => $errorMessage,
                    'trace' => $exception->getTraceAsString()
                ]);
                
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if the exception is a missing table exception
     * 
     * @param \Throwable $exception
     * @return bool
     */
    public static function isMissingTableException(\Throwable $exception): bool
    {
        if ($exception instanceof QueryException) {
            $errorCode = $exception->getCode();
            $errorMessage = $exception->getMessage();
            
            return $errorCode === '42S02' || str_contains($errorMessage, 'Base table or view not found');
        }
        
        return false;
    }
}
