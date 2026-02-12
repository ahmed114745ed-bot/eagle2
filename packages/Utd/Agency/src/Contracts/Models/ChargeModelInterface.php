<?php

namespace Utd\Agency\Contracts\Models;

interface ChargeModelInterface
{
    /**
     * Query charges
     */
    public function where($column, $operator = null, $value = null);

    /**
     * Get charges by user
     */
    public function byUser($userId);

    /**
     * Get charges by date range
     */
    public function byDateRange($startDate, $endDate);

    /**
     * Sum charges
     */
    public function sumAmount();
}
