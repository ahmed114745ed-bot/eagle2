<?php

namespace Utd\Agency\Traits;

use InvalidArgumentException;

/**
 * Trait to provide configurable model classes
 * This allows the package to be independent of the main application
 */
trait ConfigurableModelsTrait
{
    /**
     * Get model class from config with fallback
     */
    protected function getModelClass(string $key, ?string $fallback = null): string
    {
        $default = $fallback ?? $this->getDefaultModelClass($key);

        return config("agency-package.models.{$key}", $default);
    }

    /**
     * Get default model class mapping
     */
    protected function getDefaultModelClass(string $key): string
    {
        return match ($key) {
            'user' => \App\Models\User::class,
            'admin' => \App\Models\Admin::class,
            'admin_user' => \App\Models\AdminUser::class,
            'country' => \App\Models\Country::class,
            'charge' => \App\Models\Charge::class,
            'coin_log' => \App\Models\CoinLog::class,
            'gift_log' => \Utd\Gifts\Entities\GiftLog::class,
            'user_salary' => \App\Models\UserSallary::class,
            'user_target' => \App\Models\UserTarget::class,
            'bd' => \Utd\Bd\Entities\Bd::class,
            'payment_gateway' => \App\Models\PaymentGateway::class,
            'config' => \App\Models\Config::class,
            'language' => \App\Models\Language::class,
            default => throw new InvalidArgumentException("Unknown model key: {$key}"),
        };
    }

    /**
     * Get helper class from config
     */
    protected function getHelperClass(string $key): ?string
    {
        $class = config("agency-package.helpers.{$key}");

        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get controller class from config
     */
    protected function getControllerClass(string $key): ?string
    {
        $class = config("agency-package.controllers.{$key}");

        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Check if a module is enabled
     */
    protected function isModuleEnabled(string $module): bool
    {
        return config("agency-package.modules.{$module}.enabled", false)
            && $this->isModuleClassesExist($module);
    }

    /**
     * Check if module classes exist
     */
    protected function isModuleClassesExist(string $module): bool
    {
        $classes = config("agency-package.modules.{$module}", []);
        unset($classes['enabled']);

        foreach ($classes as $class) {
            if (is_string($class) && ! class_exists($class)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get module class
     */
    protected function getModuleClass(string $module, string $key): ?string
    {
        if (! $this->isModuleEnabled($module)) {
            return null;
        }

        $class = config("agency-package.modules.{$module}.{$key}");

        return $class && class_exists($class) ? $class : null;
    }
}
