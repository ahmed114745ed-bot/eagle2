<?php

namespace Utd\Agency\Helpers;

use Illuminate\Support\Facades\Cache;

class AgencyHelper
{
    /**
     * Clear agency cache
     */
    public static function clearAgencyCache($agencyId): void
    {
        Cache::forget("agency_{$agencyId}");
        Cache::forget("agency_members_{$agencyId}");
        Cache::forget("agency_salary_{$agencyId}");
    }

    /**
     * Get agency from cache or database
     */
    public static function getAgency($agencyId)
    {
        return Cache::remember("agency_{$agencyId}", 3600, function () use ($agencyId) {
            return \Utd\Agency\Entities\Agency::find($agencyId);
        });
    }

    /**
     * Format salary amount
     */
    public static function formatSalary($amount): string
    {
        return number_format($amount, 2);
    }

    /**
     * Get status label
     */
    public static function getStatusLabel($status): string
    {
        return match ($status) {
            0 => 'Pending',
            1 => 'Active',
            2 => 'Rejected',
            3 => 'Frozen',
            default => 'Unknown',
        };
    }

    /**
     * Get status color
     */
    public static function getStatusColor($status): string
    {
        return match ($status) {
            0 => 'warning',
            1 => 'success',
            2 => 'danger',
            3 => 'info',
            default => 'secondary',
        };
    }

    /**
     * Check if agency feature is enabled
     */
    public static function isAgencyFeatureEnabled(): bool
    {
        if (function_exists('appFeatureEnabled')) {
            return appFeatureEnabled('agencies');
        }
        
        return true;
    }

    /**
     * Get agency type label
     */
    public static function getTypeLabel($type): string
    {
        return match ($type) {
            1 => 'Host Agency',
            2 => 'Shipping Agency',
            default => 'Unknown',
        };
    }

    /**
     * Calculate percentage
     */
    public static function calculatePercentage($value, $total): float
    {
        if ($total == 0) {
            return 0;
        }
        
        return round(($value / $total) * 100, 2);
    }

    /**
     * Get month name
     */
    public static function getMonthName($month): string
    {
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
        
        return $months[$month] ?? '';
    }
}
