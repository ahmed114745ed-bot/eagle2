<?php

namespace Utd\Agency\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Trait for resolving external services safely
 */
trait ResolvesServices
{
    /**
     * Resolve a service from configuration
     *
     * @return mixed|null
     */
    protected function resolveService(string $serviceKey, ?string $default = null)
    {
        $serviceClass = config("agency-dependencies.dependencies.services.{$serviceKey}") ?? $default;

        if (! $serviceClass || ! class_exists($serviceClass)) {
            Log::debug("Agency Package: Service '{$serviceKey}' not available");

            return null;
        }

        try {
            return app($serviceClass);
        } catch (Exception $e) {
            Log::error("Agency Package: Failed to resolve service '{$serviceKey}'", [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Call a service method safely
     *
     * @return mixed|null
     */
    protected function callService(string $serviceKey, string $method, array $params = [])
    {
        $service = $this->resolveService($serviceKey);

        if (! $service) {
            Log::debug("Agency Package: Service '{$serviceKey}' not available, skipping '{$method}'");

            return null;
        }

        if (! method_exists($service, $method)) {
            Log::warning("Agency Package: Method '{$method}' not found in service '{$serviceKey}'");

            return null;
        }

        try {
            return call_user_func_array([$service, $method], $params);
        } catch (Exception $e) {
            Log::error('Agency Package: Failed to call service method', [
                'service' => $serviceKey,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get Agency Service
     */
    protected function getAgencyService()
    {
        return $this->resolveService('agency_service');
    }

    /**
     * Get Charge Service
     */
    protected function getChargeService()
    {
        return $this->resolveService('charge_service');
    }

    /**
     * Get Agency Host Invite Service
     */
    protected function getAgencyHostInviteService()
    {
        return $this->resolveService('agency_host_invite_service');
    }

    /**
     * Get App Feature Service
     */
    protected function getAppFeatureService()
    {
        return $this->resolveService('app_feature_service');
    }

    /**
     * Get User Service
     */
    protected function getUserService()
    {
        return $this->resolveService('user_service');
    }

    /**
     * Get Admin Agency Service
     */
    protected function getAdminAgencyService()
    {
        return $this->resolveService('admin_agency_service');
    }
}
