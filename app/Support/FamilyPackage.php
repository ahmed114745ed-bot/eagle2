<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class FamilyPackage
{
    private const ENTITY_MAP = [
        'family' => 'Utd\\Family\\Entities\\Family',
        'family_user' => 'Utd\\Family\\Entities\\FamilyUser',
        'family_level' => 'Utd\\Family\\Entities\\FamilyLevel',
        'family_rank' => 'Utd\\Family\\Entities\\FamilyRank',
        'family_view' => 'Utd\\Family\\Entities\\FamilyView',
    ];

    public static function isAvailable(): bool
    {
        return class_exists('Utd\\Family\\FamilyServiceProvider');
    }

    public static function entity(string $key): ?string
    {
        $class = self::ENTITY_MAP[$key] ?? null;
        return $class && class_exists($class) ? $class : null;
    }

    public static function newQuery(string $key): ?Builder
    {
        $class = self::entity($key);
        return $class ? $class::query() : null;
    }

    public static function relation(Model $model, string $method, string $key, array $arguments = []): Relation
    {
        $class = self::entity($key);

        if ($class) {
            array_unshift($arguments, $class);
            return $model->{$method}(...$arguments);
        }

        return self::nullRelation($model, $method, $arguments);
    }

    public static function call(string $key, callable $callback, mixed $default = null): mixed
    {
        $class = self::entity($key);

        if (!$class) {
            return $default;
        }

        return $callback($class);
    }

    private static function nullRelation(Model $model, string $method, array $arguments = []): Relation
    {
        $arguments = array_pad($arguments, 2, $model->getKeyName());
        array_unshift($arguments, $model::class);

        return $model->{$method}(...$arguments)->whereRaw('1 = 0');
    }
}
