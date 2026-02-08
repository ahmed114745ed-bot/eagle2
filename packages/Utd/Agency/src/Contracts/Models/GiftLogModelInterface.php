<?php

namespace Utd\Agency\Contracts\Models;

interface GiftLogModelInterface
{
    /**
     * Query gift logs
     */
    public function where($column, $operator = null, $value = null);
    
    /**
     * Get gift logs by sender
     */
    public function bySender($userId);
    
    /**
     * Get gift logs by receiver
     */
    public function byReceiver($userId);
    
    /**
     * Get gift logs by date range
     */
    public function byDateRange($startDate, $endDate);
    
    /**
     * Sum diamonds
     */
    public function sumDiamonds();
}
