<?php

namespace Utd\Agency\Traits;

use Utd\Agency\Repositories\ShippingAgencyRepository;

/**
 * Dynamic Service Trait
 *
 * Safely loads services and returns null if not available
 * Prevents application from crashing if service is not registered
 */
trait DynamicServiceTrait
{
    /**
     * Get Agency Service safely
     *
     * @return \Utd\Agency\Contracts\AgencyServiceInterface|null
     */
    protected function getAgencyService()
    {
        try {
            if (app()->bound(\Utd\Agency\Contracts\AgencyServiceInterface::class)) {
                return app(\Utd\Agency\Contracts\AgencyServiceInterface::class);
            }
        } catch (\Exception $e) {
            \Log::warning('AgencyService not available: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get Agency Repository safely
     *
     * @return \Utd\Agency\Contracts\AgencyRepositoryInterface|null
     */
    protected function getAgencyRepository()
    {
        try {
            if (app()->bound(\Utd\Agency\Contracts\AgencyRepositoryInterface::class)) {
                return app(\Utd\Agency\Contracts\AgencyRepositoryInterface::class);
            }
        } catch (\Exception $e) {
            \Log::warning('AgencyRepository not available: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get Shipping Agency Repository safely
     *
     * @return ShippingAgencyRepository|null
     */
    protected function getShippingAgencyRepository()
    {
        try {
            if (class_exists(ShippingAgencyRepository::class)) {
                return app(ShippingAgencyRepository::class);
            }
        } catch (\Exception $e) {
            \Log::warning('ShippingAgencyRepository not available: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Check if agency package is available
     *
     * @return bool
     */
    protected function isAgencyPackageAvailable(): bool
    {
        return class_exists(\Utd\Agency\AgencyServiceProvider::class);
    }

    /**
     * Execute callback only if agency service is available
     *
     * @param callable $callback
     * @param mixed $default
     * @return mixed
     */
    protected function withAgencyService(callable $callback, $default = null)
    {
        $service = $this->getAgencyService();

        if ($service) {
            return $callback($service);
        }

        return $default;
    }

    /**
     * Execute callback only if agency repository is available
     *
     * @param callable $callback
     * @param mixed $default
     * @return mixed
     */
    protected function withAgencyRepository(callable $callback, $default = null)
    {
        $repository = $this->getAgencyRepository();

        if ($repository) {
            return $callback($repository);
        }

        return $default;
    }
}
