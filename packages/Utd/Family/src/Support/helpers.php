<?php

use Utd\Family\Support\ClassResolver;

if (!function_exists('family_model')) {
    function family_model(string $key)
    {
        return ClassResolver::model($key);
    }
}

if (!function_exists('family_helper')) {
    function family_helper(string $key)
    {
        return ClassResolver::helper($key);
    }
}

if (!function_exists('family_service')) {
    function family_service(string $key)
    {
        return ClassResolver::service($key);
    }
}

if (!function_exists('family_facade')) {
    function family_facade(string $key)
    {
        return ClassResolver::facade($key);
    }
}

if (!function_exists('family_resource')) {
    function family_resource(string $key)
    {
        return ClassResolver::resource($key);
    }
}

if (!function_exists('family_enum')) {
    function family_enum(string $key)
    {
        return ClassResolver::enum($key);
    }
}

if (!function_exists('family_trait')) {
    function family_trait(string $key)
    {
        return ClassResolver::trait($key);
    }
}

if (!function_exists('family_contract')) {
    function family_contract(string $key)
    {
        return ClassResolver::contract($key);
    }
}

if (!function_exists('family_controller')) {
    function family_controller(string $key)
    {
        return ClassResolver::controller($key);
    }
}

if (!function_exists('family_repository')) {
    function family_repository(string $key)
    {
        return ClassResolver::repository($key);
    }
}

if (!function_exists('family_admin')) {
    function family_admin(string $key)
    {
        return ClassResolver::admin($key);
    }
}
