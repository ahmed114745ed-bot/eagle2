<?php

return [
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
        'user' => \App\Models\User::class,
        'family' => \Utd\Family\Entities\Family::class,
        'family_member' => \Utd\Family\Entities\FamilyMember::class,
        'pack' => \App\Models\Pack::class,
        'ware' => \App\Models\Ware::class,
        'agency' => \App\Models\Agency::class,
        'country' => \App\Models\Country::class,
        'manger_type' => \App\Models\MangerType::class,
        'agency_join_request' => \App\Models\AgencyJoinRequest::class,
        'config' => \App\Models\Config::class,
        'configes_model' => \App\Models\configesModel::class,
    ],
    'helpers' => [
        'common' => \App\helper\Common::class,
        'app_feature' => \App\Services\AppFeatureService::class,
        'custom_notification' => \App\Classes\CustomNotification::class,
        'user_coin_log' => \App\Helpers\UserCoinLogHelper::class,
        'agency_package' => \App\Helpers\AgencyPackageHelper::class,
    ],
    'facades' => [
        'user_handling' => \App\Facades\UserHandling::class,
        'custom_notification' => \App\Facades\CustomNotification::class,
    ],
    'contracts' => [
        'room_repository' => \App\Contracts\RoomRepositoryContract::class,
        'user_achievement' => \App\Contracts\UserAchievementContract::class,
    ],
    'resources' => [
        'room' => \App\Http\Resources\Api\V1\RoomResource::class,
        'user' => \App\Http\Resources\Api\V1\UserResource::class,
        'country' => \App\Http\Resources\CountryResource::class,
        'manger_type' => \App\Http\Resources\Api\V1\MangerTypeResource::class,
        'short_family_user' => \App\Http\Resources\Api\V1\ShortFamilyUserResource::class,
    ],
    'controllers' => [
        'base' => \App\Http\Controllers\Controller::class,
    ],
    'traits' => [
        'dashboard' => \App\Traits\Dashboard\DashBoardTrait::class,
        'timestamps' => \App\Traits\TimestampsWithTimezone::class,
    ],
    'repositories' => [
        'abstract' => \App\Tik\Repositories\AbstractRepository::class,
    ],
    'admin' => [
        'info_box' => \App\Admin\Widgets\InfoBox::class,
        'profile_form' => \App\Admin\Forms\ProfileForm::class,
        'image_colors' => \App\Admin\Selectable\ImageColors::class,
        'delete_pack' => \App\Admin\Actions\DeletePackAction::class,
        'delete_user_vip' => \App\Admin\Actions\DeleteUserVipAction::class,
        'edit_pack_expire' => \App\Admin\Actions\EditPackExpireAction::class,
        'table' => \App\Admin\Widgets\Table::class,
    ],
];
