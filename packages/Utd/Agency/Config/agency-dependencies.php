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
            'user' => App\Models\User::class,
            'agency' => Utd\Agency\Entities\Agency::class,
            'agency_join_request' => Utd\Agency\Entities\AgencyJoinRequest::class,
            'users_joined_agency' => Utd\Agency\Entities\UsersJoinedAgency::class,
            'agency_user_job' => Utd\Agency\Entities\AgencyUserJob::class,
            'agency_salary' => Utd\Agency\Entities\AgencySalary::class,
            'gift_log' => Utd\Gifts\Entities\GiftLog::class,
            'user_salary' => App\Models\UserSallary::class,
            'target' => Utd\Agency\Entities\Target::class,
            'admin' => App\Models\Admin::class,
            'admin_user' => App\Models\AdminUser::class,
            'country' => App\Models\Country::class,
            'charge' => App\Models\Charge::class,
            'coin_log' => App\Models\CoinLog::class,
            'config' => App\Models\Config::class,
            'setting' => App\Models\Setting::class,
            'language' => App\Models\Language::class,
            'payment_gateway' => App\Models\PaymentGateway::class,
            'bd' => Utd\Bd\Entities\Bd::class,
            'user_target' => App\Models\UserTarget::class,
            'gift' => Utd\Gifts\Entities\Gift::class,
            'room' => Utd\Room\Entities\Room::class,
            'ware' => App\Models\Ware::class,
            'salary_trx' => App\Models\SalaryTrx::class,
        ],

        // Services mapping
        'services' => [
            'agency_service' => Utd\Agency\Services\AgencyService::class,
            'charge_service' => App\Tik\Services\ChargeRepoService::class,
            'agency_host_invite_service' => Utd\Agency\Services\AgencyHostInviteService::class,
            'app_feature_service' => App\Services\AppFeatureService::class,
            'user_service' => App\Admin\Services\UserService::class,
            'admin_agency_service' => App\Admin\Services\AgencyService::class,
        ],

        // Helpers mapping
        'helpers' => [
            'common' => App\Helpers\Common::class,
            'user_common' => App\Helpers\UserCommon::class,
            'custom_notification' => App\Helpers\CustomNotification::class,
            'user_handling' => App\Helpers\UserHandling::class,
            'agency_package_helper' => App\Helpers\AgencyPackageHelper::class,
        ],

        // Controllers mapping (for Laravel Admin)
        'controllers' => [
            'main_controller' => App\Admin\Controllers\MainController::class,
        ],

        // Facades mapping
        'facades' => [
            'custom_notification' => App\Facades\CustomNotification::class,
            'user_handling' => App\Facades\UserHandling::class,
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
            'enabled' => class_exists(Modules\Reals\Http\Services\RealsService::class),
            'service' => Modules\Reals\Http\Services\RealsService::class,
        ],
        'fixed_target' => [
            'enabled' => class_exists(Modules\FixedTarget\Services\FixedTargetService::class),
            'service' => Modules\FixedTarget\Services\FixedTargetService::class,
        ],
        'salary_transaction' => [
            'enabled' => class_exists(Modules\SalaryTransaction\Entities\ChargeAgency::class),
            'entity' => Modules\SalaryTransaction\Entities\ChargeAgency::class,
            'charge_agency' => Modules\SalaryTransaction\Entities\ChargeAgency::class,
            'salary_request' => Modules\SalaryTransaction\Entities\SalaryRequest::class,
            'resources' => [
                'filter_agency' => Modules\SalaryTransaction\Transformers\FilterAgancyResource::class,
                'filter_agency_manager' => Modules\SalaryTransaction\Transformers\FilterAgencyMangerResource::class,
            ],
        ],
        'milestones' => [
            'enabled' => class_exists(Utd\Milestones\Helpers\MilestoneHelper::class),
            'helper' => Utd\Milestones\Helpers\MilestoneHelper::class,
        ],
        'switch_account' => [
            'enabled' => class_exists(Utd\SwitchAccount\Entities\UserAccount::class),
            'entity' => Utd\SwitchAccount\Entities\UserAccount::class,
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
        'milestones_integration' => env('AGENCY_MILESTONES_ENABLED', true),
    ],

];
