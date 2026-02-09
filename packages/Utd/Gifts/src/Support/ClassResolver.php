<?php

namespace Utd\Gifts\Support;

/**
 * ClassResolver
 * 
 * Helper class to resolve external classes from config
 */
class ClassResolver
{
    /**
     * Get model class from config
     */
    public static function model(string $key): ?string
    {
        $class = config("gifts.models.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get controller class from config
     */
    public static function controller(string $key): ?string
    {
        $class = config("gifts.controllers.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get helper class from config
     */
    public static function helper(string $key): ?string
    {
        $class = config("gifts.helpers.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get service instance from config
     */
    public static function getService(string $key)
    {
        $class = config("gifts.services.{$key}");
        return $class && class_exists($class) ? app($class) : null;
    }

    /**
     * Get service class from config
     */
    public static function service(string $key): ?string
    {
        $class = config("gifts.services.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get resource class from config
     */
    public static function resource(string $key): ?string
    {
        $class = config("gifts.resources.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get facade class from config
     */
    public static function facade(string $key): ?string
    {
        $class = config("gifts.facades.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get job class from config
     */
    public static function job(string $key): ?string
    {
        $class = config("gifts.jobs.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get event class from config
     */
    public static function event(string $key): ?string
    {
        $class = config("gifts.events_classes.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get trait from config
     */
    public static function trait(string $key): ?string
    {
        $class = config("gifts.traits.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get action class from config
     */
    public static function action(string $key): ?string
    {
        $class = config("gifts.actions.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get form class from config
     */
    public static function form(string $key): ?string
    {
        $class = config("gifts.forms.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get exception class from config
     */
    public static function exception(string $key): ?string
    {
        $class = config("gifts.exceptions.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get contract from config
     */
    public static function contract(string $key): ?string
    {
        $class = config("gifts.contracts.{$key}");
        return $class && class_exists($class) ? $class : null;
    }

    /**
     * Get observer class from config
     */
    public static function observer(string $key): ?string
    {
        $class = config("gifts.observers.{$key}");
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
