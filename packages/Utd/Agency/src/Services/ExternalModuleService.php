<?php

namespace Utd\Agency\Services;

use Utd\Agency\Contracts\ExternalModuleInterface;

class ExternalModuleService
{
    /**
     * Get module wrapper
     */
    public function module(string $moduleName): ExternalModuleInterface
    {
        $modules = [
            'reals' => RealsModuleWrapper::class,
            'fixed_target' => FixedTargetModuleWrapper::class,
            'salary_transaction' => SalaryTransactionModuleWrapper::class,
            'milestones' => MilestonesModuleWrapper::class,
        ];

        $wrapperClass = $modules[$moduleName] ?? null;

        if (! $wrapperClass) {
            return new NullModuleWrapper();
        }

        return app($wrapperClass);
    }

    /**
     * Check if module is enabled in config
     */
    public function isEnabled(string $moduleName): bool
    {
        return config("agency.modules.{$moduleName}.enabled", false);
    }
}

class NullModuleWrapper implements ExternalModuleInterface
{
    public function isAvailable(): bool
    {
        return false;
    }

    public function get($identifier = null)
    {
        return null;
    }
}

class RealsModuleWrapper implements ExternalModuleInterface
{
    public function isAvailable(): bool
    {
        return class_exists(\Modules\Reals\Http\Services\RealsService::class);
    }

    public function get($identifier = null)
    {
        if ($this->isAvailable()) {
            return app(\Modules\Reals\Http\Services\RealsService::class);
        }

        return null;
    }
}

class FixedTargetModuleWrapper implements ExternalModuleInterface
{
    public function isAvailable(): bool
    {
        return class_exists(\Modules\FixedTarget\Services\FixedTargetService::class);
    }

    public function get($identifier = null)
    {
        if ($this->isAvailable()) {
            return app(\Modules\FixedTarget\Services\FixedTargetService::class);
        }

        return null;
    }
}

class SalaryTransactionModuleWrapper implements ExternalModuleInterface
{
    public function isAvailable(): bool
    {
        return class_exists(\Modules\SalaryTransaction\Entities\ChargeAgency::class);
    }

    public function get($identifier = null)
    {
        if ($this->isAvailable()) {
            return \Modules\SalaryTransaction\Entities\ChargeAgency::class;
        }

        return null;
    }
}

class MilestonesModuleWrapper implements ExternalModuleInterface
{
    public function isAvailable(): bool
    {
        return class_exists(\Utd\Milestones\Helpers\MilestoneHelper::class);
    }

    public function get($identifier = null)
    {
        if ($this->isAvailable()) {
            return \Utd\Milestones\Helpers\MilestoneHelper::class;
        }

        return null;
    }
}
