<?php

namespace Utd\Agency\Services;

use App\Contracts\Agency\AgencyEntityResolverContract;

class AgencyEntityResolver implements AgencyEntityResolverContract
{
    protected array $models;

    public function __construct(?array $models = null)
    {
        $this->models = $models ?? (array) config('agency-package.models', []);
    }

    public function get(string $key): ?string
    {
        $class = $this->models[$key] ?? null;

        if ($class && class_exists($class)) {
            return $class;
        }

        return null;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function all(): array
    {
        $resolved = [];

        foreach ($this->models as $key => $class) {
            if ($class && class_exists($class)) {
                $resolved[$key] = $class;
            }
        }

        return $resolved;
    }
}
