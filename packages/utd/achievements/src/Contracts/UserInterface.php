<?php

namespace Utd\Achievements\Contracts;

/**
 * User Model Contract
 *
 * The host application's User model must implement this interface
 * for the Achievement package to work with it.
 *
 * This allows COMPLETE DECOUPLING - the package doesn't know
 * anything about App\Models\User
 */
interface UserInterface
{
    /**
     * Get user ID
     */
    public function getId(): int;

    /**
     * Get user UUID (if applicable)
     */
    public function getUuid(): ?string;
}
