<?php

namespace App\Contracts\Agency;

interface AgencyEntityResolverContract
{
    /**
     * Resolve a configured agency entity class by key.
     */
    public function get(string $key): ?string;

    /**
     * Determine if an entity key is registered.
     */
    public function has(string $key): bool;

    /**
     * Return all available entity mappings.
     *
     * @return array<string, string>
     */
    public function all(): array;
}
