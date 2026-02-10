<?php

namespace Utd\Family\Support;

/**
 * ClassResolver
 * 
 * Helper class to resolve external classes from config for Family package
 */
class ClassResolver
{
    /**
     * Get model class from config
     */
    public static function model(string $key): ?string
    {
        $class = config("family.models.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get service instance from config
     */
    public static function getService(string $key)
    {
        $class = config("family.services.{$key}");
        return $class && class_exists($class) ? app($class) : null;
    }

    /**
     * Get service class from config
     */
    public static function service(string $key): ?string
    {
        $class = config("family.services.{$key}");
        return $class && class_exists($class) ? $class : null;
    }
}
