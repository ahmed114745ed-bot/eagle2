<?php

return [
    'enabled' => env('FAMILY_ENABLED', true),
    /*
    |--------------------------------------------------------------------------
    | Family Package Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the mappings for external classes used within the package.
    | To ensure package isolation, avoid manual "use App\..." statements and
    | use Utd\Family\Support\ClassResolver instead.
    |
    */

    'models' => [
        'user' => App\Models\User::class,
        'family' => Utd\Family\Entities\Family::class,
        'family_member' => Utd\Family\Entities\FamilyMember::class,
        'profile' => App\Models\Profile::class,
        'pack' => App\Models\Pack::class,
        'ware' => App\Models\Ware::class,
        'agency' => App\Models\Agency::class,
        'country' => App\Models\Country::class,
        'manger_type' => App\Models\MangerType::class,
        'agency_join_request' => App\Models\AgencyJoinRequest::class,
        'config' => App\Models\Config::class,
        'configes_model' => App\Models\configesModel::class,
    ],
    'helpers' => [
        'common' => App\Helpers\Common::class,
        'custom_notification' => App\Classes\CustomNotification::class,
        'user_coin_log' => App\Helpers\UserCoinLogHelper::class,
        'agency_package' => App\Helpers\AgencyPackageHelper::class,
    ],

    'services' => [
        'app_feature' => App\Services\AppFeatureService::class,
    ],
    'facades' => [
        'user_handling' => App\Facades\UserHandling::class,
        'custom_notification' => App\Facades\CustomNotification::class,
    ],
    'contracts' => [
        'room_repository' => App\Contracts\RoomRepositoryContract::class,
        'user_achievement' => App\Contracts\UserAchievementContract::class,
        'family_service' => App\Contracts\FamilyContract::class,
    ],
    'resources' => [
        'room' => App\Http\Resources\Api\V1\RoomResource::class,
        'user' => App\Http\Resources\Api\V1\UserResource::class,
        'country' => App\Http\Resources\CountryResource::class,
        'manger_type' => App\Http\Resources\Api\V1\MangerTypeResource::class,
        'short_family_user' => App\Http\Resources\Api\V1\ShortFamilyUserResource::class,
    ],
    'controllers' => [
        'base' => App\Http\Controllers\Controller::class,
        'admin_main' => App\Admin\Controllers\MainController::class,
    ],
    'traits' => [
        'dashboard' => App\Traits\Dashboard\DashBoardTrait::class,
        'timestamps' => App\Traits\TimestampsWithTimezone::class,
    ],

    'enums' => [
        'user_coin_log_type' => App\Enums\UserCoinLogType::class,
    ],
    'repositories' => [
        'abstract' => App\Tik\Repositories\AbstractRepository::class,
    ],
    'admin' => [
        'info_box' => App\Admin\Widgets\InfoBox::class,
        'profile_form' => App\Admin\Forms\ProfileForm::class,
        'image_colors' => App\Admin\Selectable\ImageColors::class,
        'delete_pack' => App\Admin\Actions\DeletePackAction::class,
        'delete_user_vip' => App\Admin\Actions\DeleteUserVipAction::class,
        'edit_pack_expire' => App\Admin\Actions\EditPackExpireAction::class,
        'user_action' => App\Admin\Actions\UserAction::class,
        'table' => App\Admin\Widgets\Table::class,
    ],

    'modules' => [
        'milestones' => [
            'enabled' => class_exists(Modules\Milestones\Helpers\MilestoneHelper::class),
            'helper' => Modules\Milestones\Helpers\MilestoneHelper::class,
            'entity' => Modules\Milestones\Entities\Milestone::class,
        ],
        'vip' => [
            'enabled' => class_exists(Modules\Vip\Entities\UserVip::class),
            'user_vip' => Modules\Vip\Entities\UserVip::class,
            'vip' => Modules\Vip\Entities\Vip::class,
            'o_vip' => Modules\Vip\Entities\OVip::class,
        ],
        'switch_account' => [
            'enabled' => class_exists(Modules\SwitchAccount\Entities\UserAccount::class),
            'user_account' => Modules\SwitchAccount\Entities\UserAccount::class,
        ],
    ],
    'route_middlewares' => [
        'families_api' => [
            'api',
            'auth:sanctum',
            'checkLatestToken',
            'generalBan',
            'userBan',
            'update.last.seen',
            'localization',
        ],
        'utd_api' => [
            'api',
            'auth:sanctum',
            'checkLatestToken',
            'generalBan',
            'userBan',
            'update.last.seen',
            'localization',
        ],
        'family_levels_api' => [
            'api',
            'auth:sanctum',
            'checkLatestToken',
            'generalBan',
            'userBan',
            'update.last.seen',
            'localization',
        ],
    ],
];
