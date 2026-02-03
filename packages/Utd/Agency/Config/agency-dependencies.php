<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Agency Services Dependencies Configuration
    |--------------------------------------------------------------------------
    | Configure which models and services to use for agency functionality
    */
    
    'dependencies' => [
        
        // Models mapping
        'models' => [
            'user' => \App\Models\User::class,
            'agency' => \App\Models\Agency::class,
            'agency_join_request' => \App\Models\AgencyJoinRequest::class,
            'users_joined_agency' => \App\Models\UsersJoinedAgency::class,
            'agency_user_job' => \App\Models\AgencyUserJob::class,
            'agency_salary' => \App\Models\AgencySallary::class,
            'gift_log' => \App\Models\GiftLog::class,
            'user_salary' => \App\Models\UserSallary::class,
            'target' => \App\Models\Target::class,
        ],
        
        // Services mapping
        'services' => [
            'agency_service' => \App\Tik\Services\AgencyService::class,
            'charge_service' => \App\Tik\Services\ChargeRepoService::class,
            'agency_host_invite_service' => \App\Tik\Services\AgencyHostInviteService::class,
        ],
        
        // Helpers mapping
        'helpers' => [
            'common' => \App\Helpers\Common::class,
            'user_common' => \App\Helpers\UserCommon::class,
            'custom_notification' => \App\Helpers\CustomNotification::class,
            'user_handling' => \App\Helpers\UserHandling::class,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | External Modules Configuration
    |--------------------------------------------------------------------------
    | Configure which external modules are available and their classes
    */
    
    'modules' => [
        'reals' => [
            'enabled' => true,
            'service' => \Modules\Reals\Http\Services\RealsService::class,
        ],
        'fixed_target' => [
            'enabled' => true,
            'service' => \Modules\FixedTarget\Services\FixedTargetService::class,
        ],
        'salary_transaction' => [
            'enabled' => true,
            'entity' => \Modules\SalaryTransaction\Entities\ChargeAgency::class,
        ],
        'milestones' => [
            'enabled' => true,
            'helper' => \Modules\Milestones\Helpers\MilestoneHelper::class,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Features Configuration
    |--------------------------------------------------------------------------
    */
    
    'features' => [
        'host_agency' => env('AGENCY_HOST_ENABLED', true),
        'shipping_agency' => env('AGENCY_SHIPPING_ENABLED', true),
        'charge_enabled' => env('AGENCY_CHARGE_ENABLED', true),
        'reals_integration' => env('AGENCY_REALS_ENABLED', true),
        'fixed_target_integration' => env('AGENCY_FIXED_TARGET_ENABLED', true),
    ],
    
];
