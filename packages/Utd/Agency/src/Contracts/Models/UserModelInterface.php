<?php

namespace Utd\Agency\Contracts\Models;

interface UserModelInterface
{
    /**
     * Get the user by ID
     */
    public function find($id);

    /**
     * Get user by column
     */
    public function where($column, $operator = null, $value = null);

    /**
     * Create new user
     */
    public function create(array $attributes);

    /**
     * Update user
     */
    public function update(array $attributes);

    /**
     * Delete user
     */
    public function delete();

    /**
     * Get user's agency relation
     */
    public function agency();

    /**
     * Get user's agency ID
     */
    public function getAgencyId();

    /**
     * Check if user has agency
     */
    public function hasAgency(): bool;
}
