<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gifts System Enabled
    |--------------------------------------------------------------------------
    |
    |
    */
    'enabled' => env('GIFTS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Maximum Gift Quantity
    |--------------------------------------------------------------------------
    |
    |
    */
    'max_gift_quantity' => env('GIFTS_MAX_QUANTITY', 9999),

    /*
    |--------------------------------------------------------------------------
    | Lucky Gifts Feature
    |--------------------------------------------------------------------------
    |
    |
    */
    'lucky_gifts_enabled' => env('LUCKY_GIFTS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | CP Gifts Feature
    |--------------------------------------------------------------------------
    |
    |
    */
    'cp_enabled' => env('CP_GIFTS_ENABLED', false),
    'cp_enable_all_gifts' => env('CP_ENABLE_ALL_GIFTS', false),

    /*
    |--------------------------------------------------------------------------
    | Moments Gifts Feature
    |--------------------------------------------------------------------------
    |
    |
    */
    'moments_enabled' => env('MOMENTS_GIFTS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Gift Images Path
    |--------------------------------------------------------------------------
    |
    |
    */
    'images_path' => env('GIFTS_IMAGES_PATH', 'gifts'),

    /*
    |--------------------------------------------------------------------------
    | Gift Rankings
    |--------------------------------------------------------------------------
    |
    |
    */
    'rankings' => [
        'enabled' => env('GIFTS_RANKINGS_ENABLED', true),
        'cache_duration' => env('GIFTS_RANKINGS_CACHE', 3600), // 1 hour
        'types' => [
            'daily' => 'يومي',
            'weekly' => 'أسبوعي',
            'monthly' => 'شهري',
            'all_time' => 'كل الأوقات'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Gift Log Cleanup
    |--------------------------------------------------------------------------
    |
    |
    */
    'log_retention_days' => env('GIFTS_LOG_RETENTION_DAYS', null),

    /*
    |--------------------------------------------------------------------------
    | Gift Sending Limits
    |--------------------------------------------------------------------------
    |
    |
    */
    'limits' => [
        'rate_limit' => env('GIFTS_RATE_LIMIT', 60), // عدد الهدايا في الدقيقة
        'daily_limit' => env('GIFTS_DAILY_LIMIT', null), // null = غير محدود
        'require_authentication' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Events Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'events' => [
        'gift_sent' => env('GIFTS_EVENT_SENT', true),
        'gift_received' => env('GIFTS_EVENT_RECEIVED', true),
        'lucky_gift_won' => env('GIFTS_EVENT_LUCKY_WON', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'cache' => [
        'enabled' => env('GIFTS_CACHE_ENABLED', true),
        'ttl' => env('GIFTS_CACHE_TTL', 3600), // 1 hour
        'prefix' => 'gifts',
    ],

    /*
    |--------------------------------------------------------------------------
    | External Models Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'models' => [
        'user' => env('GIFTS_USER_MODEL', 'App\Models\User'),
        
        'vip' => env('GIFTS_VIP_MODEL', 'Modules\Vip\Entities\OVip'),
        
        'moment' => env('GIFTS_MOMENT_MODEL', 'Utd\Moments\Entities\Moment'),
        
        'room' => env('GIFTS_ROOM_MODEL', 'Utd\Room\Entities\Room'),
        
        'agency' => env('GIFTS_AGENCY_MODEL', 'App\Models\Agency'), 
        
        'null_agency' => env('GIFTS_NULL_AGENCY_MODEL', 'App\Models\NullAgency'),
        
        'cp' => env('GIFTS_CP_MODEL', 'App\Models\Cp'),
        
        'pk' => env('GIFTS_PK_MODEL', 'Utd\Room\Entities\Pk'),
        
        'app_feature' => env('GIFTS_APP_FEATURE_MODEL', 'App\Models\AppFeature'),
        
        'core_wallet' => env('GIFTS_CORE_WALLET_MODEL', 'App\Models\CoreWallet'),
        
        'user_salary' => env('GIFTS_USER_SALARY_MODEL', 'App\Models\UserSallary'),
        
        'remaining_diamond' => env('GIFTS_REMAINING_DIAMOND_MODEL', 'App\Models\RemainingDiamond'),
        
        'monthly_diamond_receive' => env('GIFTS_MONTHLY_DIAMOND_RECEIVE_MODEL', 'App\Models\MonthlyDiamondReceive'),
        
        'setting' => env('GIFTS_SETTING_MODEL', 'App\Models\Setting'),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Controllers Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'controllers' => [
        'main' => env('GIFTS_MAIN_CONTROLLER', 'App\Admin\Controllers\MainController'),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Helpers Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'helpers' => [
        'common' => env('GIFTS_COMMON_HELPER', 'App\Helpers\Common'),
        'user_common' => env('GIFTS_USER_COMMON_HELPER', 'App\Helpers\UserCommon'),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Services Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'services' => [
        'gift' => env('GIFTS_GIFT_SERVICE', 'Utd\Gifts\Services\GiftService'),
        'gift_log' => env('GIFTS_GIFT_LOG_SERVICE', 'Utd\Gifts\Services\GiftLogService'),
        'lucky_gift' => env('GIFTS_LUCKY_GIFT_SERVICE', 'App\Services\LuckyGiftService'),
        'send_gift' => env('GIFTS_SEND_GIFT_SERVICE', 'App\Classes\Gifts\SendGiftService'),
        'gift_service' => env('GIFTS_SERVICE_CLASS', 'App\Services\Gifts\GiftService'),
        'room_level' => env('GIFTS_ROOM_LEVEL_SERVICE', 'App\Services\RoomLevelServices'),
        'update_user_when_send_gift' => env('GIFTS_UPDATE_USER_SERVICE', 'App\Classes\Gifts\UpdateUserWhenSendGift'),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Resources Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'resources' => [
        'gift' => env('GIFTS_GIFT_RESOURCE', 'App\Http\Resources\GiftResource'),
        'gift_category' => env('GIFTS_GIFT_CATEGORY_RESOURCE', 'App\Http\Resources\GiftCategoryResource'),
        'gift_log' => env('GIFTS_GIFT_LOG_RESOURCE', 'App\Http\Resources\Api\V1\GiftLogResource'),
        'gift_log_utd' => env('GIFTS_GIFT_LOG_UTD_RESOURCE', 'App\Http\Resources\GiftLogUtdResource'),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Facades Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'facades' => [
        'user_handling' => env('GIFTS_USER_HANDLING_FACADE', 'App\Facades\UserHandling'),
        'custom_notification' => env('GIFTS_CUSTOM_NOTIFICATION_FACADE', 'App\Facades\CustomNotification'),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Jobs Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'jobs' => [
        'clean_gift_logs' => env('GIFTS_CLEAN_LOGS_JOB', 'App\Jobs\CleanGiftLogsJob'),
        'all_opening_rooms_zego_request' => env('GIFTS_ZEGO_REQUEST_JOB', 'App\Jobs\AllOpeningRoomsZegoRequest'),
        'update_user_data_when_send_gift' => env('GIFTS_UPDATE_USER_DATA_JOB', 'App\Jobs\UpdateUserDataWhenSendGift'),
        'update_pk_and_send_to_zigo' => env('GIFTS_UPDATE_PK_JOB', 'Utd\Room\Jobs\UpdatePkAndSendToZigoJob'),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Events Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'events_classes' => [
        'gift_banner' => env('GIFTS_BANNER_EVENT', 'App\Events\GiftBannerEvent'),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Traits Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'traits' => [
        'achievement_gift' => 'App\Traits\AchievementGift',
        'host_level' => 'App\Traits\HostLevelTrait',
        'cp_gift_log' => 'Modules\CP\Traits\CpGiftLog',
        'win_lucky_gift' => env('GIFTS_WIN_LUCKY_TRAIT', 'App\Traits\Gifts\WinLuckyGift'),
        'lucky_gift_probability' => env('GIFTS_LUCKY_PROBABILITY_TRAIT', 'App\Traits\Gifts\LuckyGiftProbability'),
        'timestamps_with_timezone' => env('GIFTS_TIMESTAMPS_TRAIT', 'App\Traits\TimestampsWithTimezone'),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Actions Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'actions' => [
        'move_gift_category' => env('GIFTS_MOVE_CATEGORY_ACTION', 'Utd\Gifts\Actions\MoveGiftCategory'),
        'move_groups_gifts' => env('GIFTS_MOVE_GROUPS_ACTION', 'Utd\Gifts\Actions\MoveGroupsGifts'),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Forms Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'forms' => [
        'tabs_from' => env('GIFTS_TABS_FORM', 'App\Admin\Forms\TabsFrom'),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Exceptions Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'exceptions' => [
        'not_inf_money' => env('GIFTS_NOT_INF_MONEY_EXCEPTION', 'App\Exceptions\NotInfMoneyException'),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Contracts Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'contracts' => [
        'gifts' => env('GIFTS_CONTRACT', 'App\Contracts\GiftsContract'),
        'room_top_users_repository' => env('GIFTS_ROOM_TOP_USERS_CONTRACT', 'App\Contracts\RoomTopUsersRepositoryContract'),
    ],

    /*
    |--------------------------------------------------------------------------
    | External Observers Configuration
    |--------------------------------------------------------------------------
    |
    |
    */
    'observers' => [
        'gift_category' => env('GIFTS_CATEGORY_OBSERVER', 'App\Observers\GiftCategoryObserver'),
    ],
];
