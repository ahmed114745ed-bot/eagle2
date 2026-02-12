<?php

namespace App\Support\Agency;

use App\Contracts\Agency\AgencyEntityResolverContract;

class NullAgencyEntityResolver implements AgencyEntityResolverContract
{
    public function get(string $key): ?string
    {
        return null;
    }

    public function has(string $key): bool
    {
        return false;
    }

    public function all(): array
    {
        return [];
    }
}
