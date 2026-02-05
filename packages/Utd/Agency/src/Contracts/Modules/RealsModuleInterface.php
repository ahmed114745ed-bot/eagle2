<?php

namespace Utd\Agency\Contracts\Modules;

interface RealsModuleInterface
{
    /**
     * Check if Reals module is available
     */
    public function isAvailable(): bool;
    
    /**
     * Get Reals service
     */
    public function getService();
    
    /**
     * Get user reals data
     */
    public function getUserReals($userId);
}
