<?php

namespace Utd\Agency\Services;

use Illuminate\Support\Facades\Log;

class ExternalHelperResolver
{
    /**
     * Configuration cache
     */
    protected $config;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = config('agency-dependencies.dependencies', []);
    }
    
    /**
     * Resolve a helper class from configuration
     * 
     * @param string $key The helper key from config
     * @return string|null The fully qualified class name or null if not found
     */
    public function resolveHelper(string $key): ?string
    {
        $helperClass = $this->config['helpers'][$key] ?? null;
        
        if (!$helperClass || !class_exists($helperClass)) {
            Log::warning("Agency Package: Helper '$key' not found or not available", [
                'key' => $key,
                'class' => $helperClass
            ]);
            return null;
        }
        
        return $helperClass;
    }
    
    /**
     * Get helper instance or class
     * 
     * @param string $key The helper key from config
     * @return mixed|null
     */
    public function getHelper(string $key)
    {
        $helperClass = $this->resolveHelper($key);
        
        if (!$helperClass) {
            return null;
        }
        
        try {
            // Try to instantiate or return as a service
            return app($helperClass);
        } catch (\Exception $e) {
            Log::error("Agency Package: Failed to get helper '$key'", [
                'key' => $key,
                'class' => $helperClass,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Call a helper method safely
     * 
     * @param string $key The helper key
     * @param string $method The method name
     * @param array $params The method parameters
     * @return mixed
     */
    public function callHelper(string $key, string $method, array $params = [])
    {
        $helper = $this->getHelper($key);
        
        if (!$helper) {
            Log::warning("Agency Package: Helper '$key' not available, skipping method call '$method'");
            return null;
        }
        
        if (!method_exists($helper, $method)) {
            Log::warning("Agency Package: Method '$method' not found in helper '$key'");
            return null;
        }
        
        try {
            return call_user_func_array([$helper, $method], $params);
        } catch (\Exception $e) {
            Log::error("Agency Package: Failed to call helper method", [
                'helper' => $key,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Check if a helper is available
     * 
     * @param string $key The helper key from config
     * @return bool
     */
    public function isAvailable(string $key): bool
    {
        $helperClass = $this->resolveHelper($key);
        return $helperClass !== null;
    }
    
    /**
     * Get Common helper
     */
    public function getCommon()
    {
        return $this->getHelper('common');
    }
    
    /**
     * Get UserCommon helper
     */
    public function getUserCommon()
    {
        return $this->getHelper('user_common');
    }
    
    /**
     * Get CustomNotification helper
     */
    public function getCustomNotification()
    {
        return $this->getHelper('custom_notification');
    }
    
    /**
     * Get UserHandling helper
     */
    public function getUserHandling()
    {
        return $this->getHelper('user_handling');
    }
}
