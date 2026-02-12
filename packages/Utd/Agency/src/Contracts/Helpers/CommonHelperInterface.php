<?php

namespace Utd\Agency\Contracts\Helpers;

interface CommonHelperInterface
{
    /**
     * Any common helper method
     */
    public function __call($method, $parameters);

    /**
     * Check if a value exists
     */
    public function exists($value): bool;

    /**
     * Format date
     */
    public function formatDate($date, $format = null): string;

    /**
     * Generate random string
     */
    public function generateRandomString($length = 10): string;

    /**
     * Upload file
     */
    public function uploadFile($file, $path = null);
}
