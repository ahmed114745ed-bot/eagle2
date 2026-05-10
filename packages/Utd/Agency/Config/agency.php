<?php

return [

    'name' => 'Agency Package',

    'routes' => [

        'api_enabled' => true,

        'utd_enabled' => true,

        'web_enabled' => true,

        'shipping_api_enabled' => true,

        'shipping_utd_enabled' => true,

        'shipping_web_enabled' => true,
    ],

    'route' => [
        'prefix' => 'agency',
        'middleware' => ['web', 'admin'],
        'namespace' => 'Utd\\Agency\\Http\\Controllers',
    ],

    'tables' => [
        'agencies' => 'agencies',
        'agency_join_requests' => 'agency_join_requests',
        'agency_salaries' => 'agency_sallaries',
        'agency_user_jobs' => 'agency_user_jobs',
        'users_joined_agency' => 'users_joined_agency',
        'agency_packages' => 'agency_packages',
    ],

    'types' => [
        'host' => 1,
        'shipping' => 2,
    ],

    'status' => [
        'pending' => 0,
        'active' => 1,
        'rejected' => 2,
        'frozen' => 3,
    ],

    'job_types' => [
        'owner' => 'owner',
        'admin' => 'admin',
        'request_manager' => 'requestManger',
        'operator' => 'operator',
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'agency_',
    ],

    'pagination' => [
        'per_page' => 20,
    ],

    'uploads' => [
        'path' => 'agency',
        'national_id_path' => 'nationalId',
    ],

    'notifications' => [
        'enabled' => true,
        'channels' => ['database', 'fcm'],
    ],

    'feature_key' => 'agencies',

    'models' => [
        'user' => App\Models\User::class,
        'admin' => App\Models\Admin::class,
        'admin_user' => App\Models\AdminUser::class,
        'agent' => App\Models\Agent::class,
        'country' => App\Models\Country::class,
        'charge' => App\Models\Charge::class,
        'coin_log' => App\Models\CoinLog::class,
        'gift_log' => Utd\Gifts\Entities\GiftLog::class,
        'user_salary' => App\Models\UserSallary::class,
        'user_target' => App\Models\UserTarget::class,
        'bd' => Utd\Bd\Entities\Bd::class,
        'payment_gateway' => App\Models\PaymentGateway::class,
        'config' => App\Models\Config::class,
        'language' => App\Models\Language::class,
        'setting' => App\Models\Setting::class,
        'country_rate' => App\Models\CountryRate::class,
        'gift' => Utd\Gifts\Entities\Gift::class,
        'room' => Utd\Room\Entities\Room::class,
        'chat' => App\Models\Chat::class,
        'notification' => App\Models\Notification::class,
        'user_gift' => Utd\Gifts\Entities\UserGift::class,

        // Agency Package Models
        'agency' => Utd\Agency\Entities\Agency::class,
        'agency_salary' => Utd\Agency\Entities\AgencySalary::class,
        'agency_join_request' => Utd\Agency\Entities\AgencyJoinRequest::class,
        'agency_user_job' => Utd\Agency\Entities\AgencyUserJob::class,
    ],

    'helpers' => [
        'common' => App\Helpers\Common::class,
    ],

    'controllers' => [
        'main_controller' => App\Admin\Controllers\MainController::class,
    ],

    'required_traits' => [
        App\Traits\CreatedByTrait::class,
        App\Traits\PaymentGetWayTrait::class,
        App\Traits\TimestampsWithTimezone::class,
    ],

    'modules' => [
        'salary_transaction' => [
            'enabled' => true,
            'charge_agency' => Modules\SalaryTransaction\Entities\ChargeAgency::class,
            'salary_request' => Modules\SalaryTransaction\Entities\SalaryRequest::class,
        ],
        'milestones' => [
            'enabled' => true,
            'helper' => Utd\Milestones\Helpers\MilestoneHelper::class,
        ],
        'agency_app' => [
            'enabled' => true,
        ],
    ],
];
