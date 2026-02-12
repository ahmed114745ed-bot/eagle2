<?php

namespace Utd\Agency\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Trait for resolving external helpers safely
 */
trait ResolvesHelpers
{
    /**
     * Resolve a helper from configuration
     *
     * @return mixed|null
     */
    protected function resolveHelper(string $helperKey, ?string $default = null)
    {
        $helperClass = config("agency-dependencies.dependencies.helpers.{$helperKey}") ?? $default;

        if (! $helperClass || ! class_exists($helperClass)) {
            Log::debug("Agency Package: Helper '{$helperKey}' not available");

            return null;
        }

        try {
            return app($helperClass);
        } catch (Exception $e) {
            Log::error("Agency Package: Failed to resolve helper '{$helperKey}'", [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Call a helper method safely
     *
     * @return mixed|null
     */
    protected function callHelper(string $helperKey, string $method, array $params = [])
    {
        $helper = $this->resolveHelper($helperKey);

        if (! $helper) {
            Log::debug("Agency Package: Helper '{$helperKey}' not available, skipping '{$method}'");

            return null;
        }

        if (! method_exists($helper, $method)) {
            Log::warning("Agency Package: Method '{$method}' not found in helper '{$helperKey}'");

            return null;
        }

        try {
            return call_user_func_array([$helper, $method], $params);
        } catch (Exception $e) {
            Log::error('Agency Package: Failed to call helper method', [
                'helper' => $helperKey,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get Common Helper
     */
    protected function getCommonHelper()
    {
        return $this->resolveHelper('common');
    }

    /**
     * Get UserCommon Helper
     */
    protected function getUserCommonHelper()
    {
        return $this->resolveHelper('user_common');
    }

    /**
     * Get CustomNotification Helper
     */
    protected function getCustomNotificationHelper()
    {
        return $this->resolveHelper('custom_notification');
    }

    /**
     * Get UserHandling Helper
     */
    protected function getUserHandlingHelper()
    {
        return $this->resolveHelper('user_handling');
    }

    /**
     * Get AgencyPackageHelper
     */
    protected function getAgencyPackageHelper()
    {
        return $this->resolveHelper('agency_package_helper');
    }
}
