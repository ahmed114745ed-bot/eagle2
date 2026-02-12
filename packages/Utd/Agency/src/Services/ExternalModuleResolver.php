<?php

namespace Utd\Agency\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Utd\Agency\Contracts\ExternalModuleInterface;

class ExternalModuleResolver implements ExternalModuleInterface
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
        $this->config = config('agency-dependencies', []);
    }

    /**
     * Check if module is available
     * Implementation of ExternalModuleInterface
     */
    public function isAvailable(): bool
    {
        // Generic check - you can customize based on specific module needs
        return true;
    }

    /**
     * Get module service, helper, or model class
     * Implementation of ExternalModuleInterface
     *
     * @param  string|null  $identifier  Optional identifier for getting specific models or services
     * @return mixed
     */
    public function get($identifier = null)
    {
        if (! $identifier) {
            return $this;
        }

        // Parse the identifier (e.g., "models.gift_log" or "services.reals")
        $parts = explode('.', $identifier);

        if (count($parts) === 2) {
            $type = $parts[0]; // 'models', 'services', 'helpers'
            $name = $parts[1]; // 'gift_log', 'reals', etc.

            // Map types to config structure
            $typeMap = [
                'models' => 'model',
                'services' => 'service',
                'helpers' => 'helper',
                'entities' => 'entity',
            ];

            $configType = $typeMap[$type] ?? $type;

            // Try to get from dependencies config
            $class = $this->config['dependencies'][$type][$name] ?? null;

            if ($class && class_exists($class)) {
                return $class;
            }

            // Fallback to module-based lookup
            foreach ($this->config['modules'] ?? [] as $moduleName => $moduleConfig) {
                if (isset($moduleConfig[$configType])) {
                    $moduleClass = $moduleConfig[$configType];
                    // Simple name matching
                    if (mb_stripos($moduleClass, $name) !== false && class_exists($moduleClass)) {
                        return $moduleClass;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Check if a module is enabled and available
     *
     * @param  string  $moduleName  The module key from config
     */
    public function isModuleAvailable(string $moduleName): bool
    {
        $module = $this->config['modules'][$moduleName] ?? null;

        if (! $module || ! ($module['enabled'] ?? false)) {
            return false;
        }

        // Check if the module's main class exists
        $mainClass = $module['service'] ?? $module['entity'] ?? $module['helper'] ?? null;

        if (! $mainClass || ! class_exists($mainClass)) {
            Log::debug("Agency Package: Module '$moduleName' class not found", [
                'module' => $moduleName,
                'class' => $mainClass,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Get module service/class instance
     *
     * @param  string  $moduleName  The module key from config
     * @param  string  $type  The type of class to get (service, entity, helper)
     * @return mixed|null
     */
    public function getModule(string $moduleName, string $type = 'service')
    {
        if (! $this->isModuleAvailable($moduleName)) {
            return null;
        }

        $module = $this->config['modules'][$moduleName];
        $className = $module[$type] ?? null;

        if (! $className || ! class_exists($className)) {
            return null;
        }

        try {
            return app($className);
        } catch (Exception $e) {
            Log::error("Agency Package: Failed to instantiate module '$moduleName'", [
                'module' => $moduleName,
                'type' => $type,
                'class' => $className,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get Reals module service
     */
    public function getRealsService()
    {
        return $this->getModule('reals', 'service');
    }

    /**
     * Get Fixed Target module service
     */
    public function getFixedTargetService()
    {
        return $this->getModule('fixed_target', 'service');
    }

    /**
     * Get Salary Transaction module entity
     */
    public function getSalaryTransactionEntity()
    {
        return $this->getModule('salary_transaction', 'entity');
    }

    /**
     * Get Milestones module helper
     */
    public function getMilestonesHelper()
    {
        return $this->getModule('milestones', 'helper');
    }

    /**
     * Check if Reals module is available
     */
    public function isRealsAvailable(): bool
    {
        return $this->isModuleAvailable('reals');
    }

    /**
     * Check if Fixed Target module is available
     */
    public function isFixedTargetAvailable(): bool
    {
        return $this->isModuleAvailable('fixed_target');
    }

    /**
     * Check if Salary Transaction module is available
     */
    public function isSalaryTransactionAvailable(): bool
    {
        return $this->isModuleAvailable('salary_transaction');
    }

    /**
     * Check if Milestones module is available
     */
    public function isMilestonesAvailable(): bool
    {
        return $this->isModuleAvailable('milestones');
    }
}
