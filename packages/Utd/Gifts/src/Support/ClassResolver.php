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
        return config("gifts.models.{$key}");
    }

    /**
     * Get controller class from config
     */
    public static function controller(string $key): ?string
    {
        return config("gifts.controllers.{$key}");
    }

    /**
     * Get helper class from config
     */
    public static function helper(string $key): ?string
    {
        return config("gifts.helpers.{$key}");
    }

    /**
     * Get service class from config
     */
    public static function service(string $key): ?string
    {
        return config("gifts.services.{$key}");
    }

    /**
     * Get resource class from config
     */
    public static function resource(string $key): ?string
    {
        return config("gifts.resources.{$key}");
    }

    /**
     * Get facade class from config
     */
    public static function facade(string $key): ?string
    {
        return config("gifts.facades.{$key}");
    }

    /**
     * Get job class from config
     */
    public static function job(string $key): ?string
    {
        return config("gifts.jobs.{$key}");
    }

    /**
     * Get event class from config
     */
    public static function event(string $key): ?string
    {
        return config("gifts.events_classes.{$key}");
    }

    /**
     * Get trait from config
     */
    public static function trait(string $key): ?string
    {
        return config("gifts.traits.{$key}");
    }

    /**
     * Get action class from config
     */
    public static function action(string $key): ?string
    {
        return config("gifts.actions.{$key}");
    }

    /**
     * Get form class from config
     */
    public static function form(string $key): ?string
    {
        return config("gifts.forms.{$key}");
    }

    /**
     * Get exception class from config
     */
    public static function exception(string $key): ?string
    {
        return config("gifts.exceptions.{$key}");
    }

    /**
     * Get contract from config
     */
    public static function contract(string $key): ?string
    {
        return config("gifts.contracts.{$key}");
    }

    /**
     * Get observer class from config
     */
    public static function observer(string $key): ?string
    {
        return config("gifts.observers.{$key}");
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
