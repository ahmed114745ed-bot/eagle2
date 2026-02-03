<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/**
 * Cache clearing routes for production servers
 * Access via: /clear-all-cache, /clear-opcache, /clear-config
 */

Route::get('/clear-all-cache', function () {
    try {
        $results = [];
        
        // Clear application cache
        Artisan::call('cache:clear');
        $results[] = 'Application cache cleared';
        
        // Clear config cache
        Artisan::call('config:clear');
        $results[] = 'Config cache cleared';
        
        // Clear route cache
        Artisan::call('route:clear');
        $results[] = 'Route cache cleared';
        
        // Clear view cache
        Artisan::call('view:clear');
        $results[] = 'View cache cleared';
        
        // Clear compiled classes
        Artisan::call('clear-compiled');
        $results[] = 'Compiled classes cleared';
        
        // Clear OPcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $results[] = 'OPcache cleared';
        } else {
            $results[] = 'OPcache not available';
        }
        
        // Clear APCu cache if available
        if (function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
            $results[] = 'APCu cache cleared';
        }
        
        // Remove bootstrap cache files
        $bootstrapCache = [
            base_path('bootstrap/cache/packages.php'),
            base_path('bootstrap/cache/services.php'),
            base_path('bootstrap/cache/config.php'),
            base_path('bootstrap/cache/routes-v7.php'),
        ];
        
        foreach ($bootstrapCache as $file) {
            if (file_exists($file)) {
                unlink($file);
                $results[] = 'Deleted: ' . basename($file);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'All caches cleared successfully',
            'results' => $results,
            'timestamp' => now()->toDateTimeString()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error clearing cache',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/clear-opcache', function () {
    try {
        $results = [];
        
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $results[] = 'OPcache reset successfully';
            
            if (function_exists('opcache_get_status')) {
                $status = opcache_get_status();
                $results[] = 'OPcache enabled: ' . ($status ? 'Yes' : 'No');
            }
        } else {
            $results[] = 'OPcache is not available on this server';
        }
        
        // Also clear bootstrap cache
        $files = [
            base_path('bootstrap/cache/packages.php'),
            base_path('bootstrap/cache/services.php'),
        ];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
                $results[] = 'Deleted: ' . basename($file);
            }
        }
        
        return response()->json([
            'success' => true,
            'results' => $results,
            'timestamp' => now()->toDateTimeString()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/clear-config', function () {
    try {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        
        return response()->json([
            'success' => true,
            'message' => 'Config and cache cleared',
            'timestamp' => now()->toDateTimeString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/clear-bootstrap-cache', function () {
    try {
        $results = [];
        
        $files = [
            base_path('bootstrap/cache/packages.php'),
            base_path('bootstrap/cache/services.php'),
            base_path('bootstrap/cache/config.php'),
            base_path('bootstrap/cache/routes-v7.php'),
        ];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
                $results[] = 'Deleted: ' . basename($file);
            } else {
                $results[] = 'Not found: ' . basename($file);
            }
        }
        
        return response()->json([
            'success' => true,
            'results' => $results,
            'timestamp' => now()->toDateTimeString()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/opcache-status', function () {
    if (!function_exists('opcache_get_status')) {
        return response()->json([
            'success' => false,
            'message' => 'OPcache is not available'
        ]);
    }
    
    $status = opcache_get_status();
    $config = opcache_get_configuration();
    
    return response()->json([
        'success' => true,
        'opcache_enabled' => $status !== false,
        'status' => $status,
        'configuration' => $config
    ]);
});
