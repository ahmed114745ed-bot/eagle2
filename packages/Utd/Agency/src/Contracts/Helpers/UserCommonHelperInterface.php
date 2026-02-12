<?php

namespace Utd\Agency\Contracts\Helpers;

interface UserCommonHelperInterface
{
    /**
     * Any user helper method
     */
    public function __call($method, $parameters);

    /**
     * Get user data
     */
    public function getUserData($userId);

    /**
     * Check user permissions
     */
    public function hasPermission($userId, $permission): bool;

    /**
     * Update user stats
     */
    public function updateUserStats($userId, array $stats);
}
