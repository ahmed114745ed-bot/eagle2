<?php

namespace Utd\Agency\Contracts\Modules;

interface SalaryTransactionModuleInterface
{
    /**
     * Check if Salary Transaction module is available
     */
    public function isAvailable(): bool;

    /**
     * Get Charge Agency model
     */
    public function getChargeAgencyModel();

    /**
     * Get Salary Request model
     */
    public function getSalaryRequestModel();

    /**
     * Create salary transaction
     */
    public function createTransaction(array $data);
}
