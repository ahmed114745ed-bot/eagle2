<?php

namespace Utd\Agency\Contracts\Modules;

interface FixedTargetModuleInterface
{
    /**
     * Check if Fixed Target module is available
     */
    public function isAvailable(): bool;
    
    /**
     * Get Fixed Target service
     */
    public function getService();
    
    /**
     * Get user target data
     */
    public function getUserTarget($userId);
}
