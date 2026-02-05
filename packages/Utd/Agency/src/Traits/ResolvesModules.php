<?php

namespace Utd\Agency\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Trait for resolving external modules safely
 */
trait ResolvesModules
{
    /**
     * Check if a module is available
     * 
     * @param string $moduleName
     * @return bool
     */
    protected function isModuleAvailable(string $moduleName): bool
    {
        $module = config("agency-dependencies.modules.{$moduleName}");
        
        if (!$module || !($module['enabled'] ?? false)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get a module service/entity/helper
     * 
     * @param string $moduleName
     * @param string $type (service, entity, helper)
     * @return mixed|null
     */
    protected function getModule(string $moduleName, string $type = 'service')
    {
        if (!$this->isModuleAvailable($moduleName)) {
            return null;
        }
        
        $className = config("agency-dependencies.modules.{$moduleName}.{$type}");
        
        if (!$className || !class_exists($className)) {
            Log::debug("Agency Package: Module '{$moduleName}' {$type} not available");
            return null;
        }
        
        try {
            return app($className);
        } catch (\Exception $e) {
            Log::error("Agency Package: Failed to get module", [
                'module' => $moduleName,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Get module class without instantiation
     * 
     * @param string $moduleName
     * @param string $type
     * @return string|null
     */
    protected function getModuleClass(string $moduleName, string $type = 'service'): ?string
    {
        if (!$this->isModuleAvailable($moduleName)) {
            return null;
        }
        
        $className = config("agency-dependencies.modules.{$moduleName}.{$type}");
        
        if (!$className || !class_exists($className)) {
            return null;
        }
        
        return $className;
    }
    
    /**
     * Get Reals module service
     */
    protected function getRealsService()
    {
        return $this->getModule('reals', 'service');
    }
    
    /**
     * Check if Reals module is available
     */
    protected function hasRealsModule(): bool
    {
        return $this->isModuleAvailable('reals');
    }
    
    /**
     * Get Fixed Target module service
     */
    protected function getFixedTargetService()
    {
        return $this->getModule('fixed_target', 'service');
    }
    
    /**
     * Check if Fixed Target module is available
     */
    protected function hasFixedTargetModule(): bool
    {
        return $this->isModuleAvailable('fixed_target');
    }
    
    /**
     * Get Salary Transaction ChargeAgency model class
     */
    protected function getSalaryTransactionChargeAgencyClass(): ?string
    {
        return $this->getModuleClass('salary_transaction', 'charge_agency');
    }
    
    /**
     * Get Salary Transaction entity
     */
    protected function getSalaryTransactionEntity()
    {
        return $this->getModule('salary_transaction', 'entity');
    }
    
    /**
     * Check if Salary Transaction module is available
     */
    protected function hasSalaryTransactionModule(): bool
    {
        return $this->isModuleAvailable('salary_transaction');
    }
    
    /**
     * Get Milestones Helper
     */
    protected function getMilestonesHelper()
    {
        return $this->getModule('milestones', 'helper');
    }
    
    /**
     * Check if Milestones module is available
     */
    protected function hasMilestonesModule(): bool
    {
        return $this->isModuleAvailable('milestones');
    }
    
    /**
     * Call a module method safely
     * 
     * @param string $moduleName
     * @param string $type
     * @param string $method
     * @param array $params
     * @return mixed|null
     */
    protected function callModule(string $moduleName, string $type, string $method, array $params = [])
    {
        $module = $this->getModule($moduleName, $type);
        
        if (!$module) {
            return null;
        }
        
        if (!method_exists($module, $method)) {
            Log::warning("Agency Package: Method '{$method}' not found in module '{$moduleName}'");
            return null;
        }
        
        try {
            return call_user_func_array([$module, $method], $params);
        } catch (\Exception $e) {
            Log::error("Agency Package: Failed to call module method", [
                'module' => $moduleName,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
