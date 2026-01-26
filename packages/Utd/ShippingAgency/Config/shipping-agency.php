<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shipping Agency Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Shipping Agency package.
    |
    */

    'enabled' => env('SHIPPING_AGENCY_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Agency Type
    |--------------------------------------------------------------------------
    |
    | The type value used to identify shipping agencies in the database.
    | This distinguishes shipping agencies from host agencies.
    |
    */
    'type' => 2,

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | The database table name for agencies (shared with host agencies).
    |
    */
    'table' => 'agencies',
];
