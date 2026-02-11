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
     * Get helper class from config
     */
    public static function helper(string $key): ?string
    {
        $class = config("family.helpers.{$key}");
        return $class && (class_exists($class) || trait_exists($class)) ? $class : null;
    }

    /**
     * Get service class from config
     */
    public static function service(string $key): ?string
    {
        $class = config("family.services.{$key}");
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
     * Get resource class from config
     */
    public static function resource(string $key): ?string
    {
        $class = config("family.resources.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get facade class from config
     */
    public static function facade(string $key): ?string
    {
        $class = config("family.facades.{$key}");
        return $class && (class_exists($class) || interface_exists($class)) ? $class : null;
    }

    /**
     * Get contract from config
     */
    public static function contract(string $key): ?string
    {
        $class = config("family.contracts.{$key}");
        return $class && (interface_exists($class) || class_exists($class)) ? $class : null;
    }

    /**
     * Get trait from config
     */
    public static function trait(string $key): ?string
    {
        $class = config("family.traits.{$key}");
        return $class && trait_exists($class) ? $class : null;
    }

    /**
     * Get enum class from config
     */
    public static function enum(string $key): ?string
    {
        $class = config("family.enums.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get repository class from config
     */
    public static function repository(string $key): ?string
    {
        $class = config("family.repositories.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Resolve instance of a class
     */
    public static function resolve(string $class)
    {
        return app($class);
    }

    /**
     * Make instance of a class
     */
    public static function make(string $class, array $parameters = [])
    {
        return app()->make($class, $parameters);
    }
}
