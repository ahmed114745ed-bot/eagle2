<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Agency Package Configuration
    |--------------------------------------------------------------------------
    */

    'name' => 'Agency Package',

    /*
    |--------------------------------------------------------------------------
    | Routes Enable/Disable
    |--------------------------------------------------------------------------
    | Control which route groups are loaded by the package
    */
    'routes' => [
        // Enable/disable Host Agency API routes
        'api_enabled' => true,

        // Enable/disable UTD API routes
        'utd_enabled' => true,

        // Enable/disable Web/Admin routes (requires Laravel Admin)
        'web_enabled' => true,

        // Enable/disable Shipping Agency API routes
        'shipping_api_enabled' => true,

        // Enable/disable Shipping Agency UTD routes
        'shipping_utd_enabled' => true,

        // Enable/disable Shipping Agency Web routes
        'shipping_web_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'route' => [
        'prefix' => 'agency',
        'middleware' => ['web', 'admin'],
        'namespace' => 'Utd\\Agency\\Http\\Controllers',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Table Names
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'agencies' => 'agencies',
        'agency_join_requests' => 'agency_join_requests',
        'agency_salaries' => 'agency_sallaries',
        'agency_user_jobs' => 'agency_user_jobs',
        'users_joined_agency' => 'users_joined_agency',
        'agency_packages' => 'agency_packages',
    ],

    /*
    |--------------------------------------------------------------------------
    | Agency Types
    |--------------------------------------------------------------------------
    */
    'types' => [
        'host' => 1,
        'shipping' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Agency Status
    |--------------------------------------------------------------------------
    */
    'status' => [
        'pending' => 0,
        'active' => 1,
        'rejected' => 2,
        'frozen' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Agency User Job Types
    |--------------------------------------------------------------------------
    */
    'job_types' => [
        'owner' => 'owner',
        'admin' => 'admin',
        'request_manager' => 'requestManger',
        'operator' => 'operator',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'agency_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'per_page' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'path' => 'agency',
        'national_id_path' => 'nationalId',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'enabled' => true,
        'channels' => ['database', 'fcm'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Agency Feature Flag
    |--------------------------------------------------------------------------
    */
    'feature_key' => 'agencies',

    /*
    |--------------------------------------------------------------------------
    | External Models Configuration
    |--------------------------------------------------------------------------
    | Configure external model classes used by this package.
    | This allows the package to be independent of the main application.
    | Override these in your published config to use custom models.
    */
    'models' => [
        // External Models
        'user' => \App\Models\User::class,
        'admin' => \App\Models\Admin::class,
        'admin_user' => \App\Models\AdminUser::class,
        'agent' => \App\Models\Agent::class,
        'country' => \App\Models\Country::class,
        'charge' => \App\Models\Charge::class,
        'coin_log' => \App\Models\CoinLog::class,
        'gift_log' => \Utd\Gifts\Entities\GiftLog::class,
        'user_salary' => \App\Models\UserSallary::class,
        'user_target' => \App\Models\UserTarget::class,
        'target' => \App\Models\Target::class,
        'bd' => \Utd\Bd\Entities\Bd::class,
        'payment_gateway' => \App\Models\PaymentGateway::class,
        'config' => \App\Models\Config::class,
        'language' => \App\Models\Language::class,
        
        // Agency Package Models
        'agency' => \Utd\Agency\Entities\Agency::class,
        'agency_salary' => \Utd\Agency\Entities\AgencySalary::class,
        'agency_join_request' => \Utd\Agency\Entities\AgencyJoinRequest::class,
        'agency_user_job' => \Utd\Agency\Entities\AgencyUserJob::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | External Helpers Configuration
    |--------------------------------------------------------------------------
    */
    'helpers' => [
        'common' => \App\Helpers\Common::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | External Controllers Configuration
    |--------------------------------------------------------------------------
    */
    'controllers' => [
        'main_controller' => \App\Admin\Controllers\MainController::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | External Traits Configuration
    |--------------------------------------------------------------------------
    | Note: Traits cannot be configured at runtime, but this documents
    | which traits are required from the main application.
    */
    'required_traits' => [
        \App\Traits\CreatedByTrait::class,
        \App\Traits\PaymentGetWayTrait::class,
        \App\Traits\TimestampsWithTimezone::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | External Modules Configuration
    |--------------------------------------------------------------------------
    | Optional modules that enhance functionality when available.
    */
    'modules' => [
        'salary_transaction' => [
            'enabled' => true,
            'charge_agency' => \Modules\SalaryTransaction\Entities\ChargeAgency::class,
            'salary_request' => \Modules\SalaryTransaction\Entities\SalaryRequest::class,
            'agency_transfer_salary' => \Modules\SalaryTransaction\Entities\AgencyTransferSalary::class,
        ],
        'milestones' => [
            'enabled' => true,
            'helper' => \Utd\Milestones\Helpers\MilestoneHelper::class,
        ],
        'agency_app' => [
            'enabled' => true,
        ],
    ],
];
