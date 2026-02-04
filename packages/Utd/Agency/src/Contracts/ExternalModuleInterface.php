<?php

namespace Utd\Agency\Contracts;

interface ExternalModuleInterface
{
    /**
     * Check if module is available
     */
    public function isAvailable(): bool;
    
    /**
     * Get module service, helper, or model class
     * @param string|null $identifier Optional identifier for getting specific models or services
     * @return mixed
     */
    public function get($identifier = null);
}
