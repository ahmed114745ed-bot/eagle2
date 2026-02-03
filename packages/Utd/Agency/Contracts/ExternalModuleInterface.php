<?php

namespace Utd\Agency\Contracts;

interface ExternalModuleInterface
{
    /**
     * Check if module is available
     */
    public function isAvailable(): bool;
    
    /**
     * Get module service or helper
     */
    public function get();
}
