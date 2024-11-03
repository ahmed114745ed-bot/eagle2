<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Admin\Controllers{
/**
 * 
 *
 * @property-read string $test
 * @method static \Illuminate\Database\Eloquent\Builder|TemporaryModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TemporaryModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TemporaryModel query()
 */
	class TemporaryModel extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $name
 * @property string $avatar
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $di
 * @property int $Agency_manger
 * @property int $app_id
 * @property string $time_zone
 * @property int $is_preview
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Agency> $agencies
 * @property-read int|null $agencies_count
 * @property-read mixed $agency_id
 * @property-read mixed $img
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Agency> $per
 * @property-read int|null $per_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Encore\Admin\Auth\Database\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Encore\Admin\Auth\Database\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereAgencyManger($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereDi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereIsPreview($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereTimeZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereUsername($value)
 */
	class Admin extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $parent_id
 * @property int $order
 * @property string $title
 * @property string $icon
 * @property string|null $uri
 * @property string|null $permission
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AdminMenu> $children
 * @property-read int|null $children_count
 * @property-read AdminMenu|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Encore\Admin\Auth\Database\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|AdminMenu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminMenu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminMenu query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminMenu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminMenu whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminMenu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminMenu whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminMenu whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminMenu wherePermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminMenu whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminMenu whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminMenu whereUri($value)
 */
	class AdminMenu extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $name
 * @property string $avatar
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $di
 * @property int $Agency_manger
 * @property int $app_id
 * @property string $time_zone
 * @property int $is_preview
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Agency> $managerAgencies
 * @property-read int|null $manager_agencies_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Encore\Admin\Auth\Database\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Encore\Admin\Auth\Database\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser whereAgencyManger($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser whereDi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser whereIsPreview($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser whereTimeZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser whereUsername($value)
 */
	class AdminUser extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Administrator.
 *
 * @property Role[] $roles
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $name
 * @property string $avatar
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $di
 * @property int $Agency_manger
 * @property int $app_id
 * @property string $time_zone
 * @property int $is_preview
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Encore\Admin\Auth\Database\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator query()
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator whereAgencyManger($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator whereDi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator whereIsPreview($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator whereTimeZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Administrator whereUsername($value)
 */
	class Administrator extends \Eloquent implements \Illuminate\Contracts\Auth\Authenticatable {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $owner_id صاحب الوكالة
 * @property string|null $name اسم الوكالة
 * @property string|null $notice جملة الترحيب
 * @property int $status
 * @property string|null $phone
 * @property string|null $url
 * @property string|null $img
 * @property string|null $contents
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $old_usd
 * @property float|null $target_usd
 * @property float|null $target_token_usd
 * @property int|null $app_owner_id
 * @property float $salary
 * @property int $Shipping_agency
 * @property int $Host_agency
 * @property int|null $agency_manger_id
 * @property int|null $agency_dash_manger_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property float $monthly_target
 * @property string|null $password
 * @property int $coins
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserTarget> $AgencyUsersTargets
 * @property-read int|null $agency_users_targets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentGateway> $AgencypaymentGateways
 * @property-read int|null $agencypayment_gateways_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Country> $Countries
 * @property-read int|null $countries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserTarget> $UserTarget
 * @property-read int|null $user_target_count
 * @property-read \Modules\AgencyApp\Entities\AdditionalInfo|null $additionalInfo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AgencySallary> $agencySalaries
 * @property-read int|null $agency_salaries_count
 * @property-read \App\Models\AgencySallary|null $agencySalary
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\SalaryTransaction\Entities\ChargeAgency> $chargeAgency
 * @property-read int|null $charge_agency_count
 * @property-read mixed $last_month_salary
 * @property-read mixed $pending_salary
 * @property-read mixed $target
 * @property-read mixed $transfer_salary
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mempers
 * @property-read int|null $mempers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentGateway> $paymentGateways
 * @property-read int|null $payment_gateways_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserSallary> $salaries
 * @property-read int|null $salaries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\SalaryTransaction\Entities\SalaryRequest> $salaryRequests
 * @property-read int|null $salary_requests_count
 * @method static \Illuminate\Database\Eloquent\Builder|Agency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Agency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Agency ofOwner($owner_id)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Agency query()
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereAgencyDashMangerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereAgencyMangerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereAppOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereContents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereHostAgency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereMonthlyTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereNotice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereOldUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereShippingAgency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereTargetTokenUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereTargetUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agency withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Agency withoutTrashed()
 */
	class Agency extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $agency_id
 * @property int|null $status 0=pending 1=accepted 2=denid
 * @property int|null $change_status_admin_id admin that accept or denid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $whatsapp
 * @property-read \App\Models\Agent|null $admin
 * @property-read \App\Models\User|null $requsers
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyJoinRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyJoinRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyJoinRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyJoinRequest whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyJoinRequest whereChangeStatusAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyJoinRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyJoinRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyJoinRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyJoinRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyJoinRequest whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyJoinRequest whereWhatsapp($value)
 */
	class AgencyJoinRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $dash_id
 * @property int $app_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangLink whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangLink whereDashId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangLink whereUpdatedAt($value)
 */
	class AgencyMangLink extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $admin_id
 * @property int $agency_manger_id
 * @property string $agencies_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerDeleted newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerDeleted newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerDeleted query()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerDeleted whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerDeleted whereAgenciesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerDeleted whereAgencyMangerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerDeleted whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerDeleted whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerDeleted whereUpdatedAt($value)
 */
	class AgencyMangerDeleted extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $agency_manger_id
 * @property int $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerPullingOut newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerPullingOut newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerPullingOut query()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerPullingOut whereAgencyMangerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerPullingOut whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerPullingOut whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerPullingOut whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyMangerPullingOut whereUpdatedAt($value)
 */
	class AgencyMangerPullingOut extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $agency_id
 * @property float $sallary
 * @property float $cut_amount
 * @property int $month
 * @property int $year
 * @property int $is_paid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $total_salary
 * @method static \Illuminate\Database\Eloquent\Builder|AgencySallary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencySallary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencySallary query()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencySallary whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencySallary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencySallary whereCutAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencySallary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencySallary whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencySallary whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencySallary whereSallary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencySallary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencySallary whereYear($value)
 */
	class AgencySallary extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $agency_id
 * @property int $user_id
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Agency|null $agency
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob query()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob whereUserId($value)
 */
	class AgencyUserJob extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $dash_id
 * @property int $app_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AgencymAngerLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencymAngerLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencymAngerLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencymAngerLink whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencymAngerLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencymAngerLink whereDashId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencymAngerLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencymAngerLink whereUpdatedAt($value)
 */
	class AgencymAngerLink extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $name
 * @property string|null $avatar
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $di
 * @property int $Agency_manger
 * @property int $app_id
 * @property string $time_zone
 * @property int $is_preview
 * @method static \Illuminate\Database\Eloquent\Builder|Agent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Agent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Agent query()
 * @method static \Illuminate\Database\Eloquent\Builder|Agent whereAgencyManger($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agent whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agent whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agent whereDi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agent whereIsPreview($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agent whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agent wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agent whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agent whereTimeZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agent whereUsername($value)
 */
	class Agent extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $name_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $custom_id
 * @property string|null $url
 * @property string|null $image
 * @property string|null $mini_url
 * @property string $is_enable
 * @property int $type
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame query()
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame whereCustomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame whereIsEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame whereMiniUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllGame whereUrl($value)
 */
	class AllGame extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $name_ar
 * @property string $slug
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AppFeature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppFeature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppFeature query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppFeature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppFeature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppFeature whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppFeature whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppFeature whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppFeature whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppFeature whereUpdatedAt($value)
 */
	class AppFeature extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $img
 * @property int|null $enable Whether to enable 1 enable 2 disable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $use_count
 * @property int $sort
 * @method static \Illuminate\Database\Eloquent\Builder|Background newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Background newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Background query()
 * @method static \Illuminate\Database\Eloquent\Builder|Background whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Background whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Background whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Background whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Background whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Background whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Background whereUseCount($value)
 */
	class Background extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $uid
 * @property int $user_type 0=app 1=dash
 * @property int $duration
 * @property string|null $type
 * @property string|null $ip
 * @property string|null $device_number
 * @property int|null $staff_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $description_ar
 * @property string $description_en
 * @property string|null $img
 * @property int|null $ban_type_id
 * @property int|null $user_id
 * @property-read \App\Models\BanType|null $banType
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Ban newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ban newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ban query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereBanTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereDescriptionAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereDescriptionEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereDeviceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ban whereUserType($value)
 */
	class Ban extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $name_ar
 * @property string|null $name_en
 * @property string $route
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $method
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ban> $bans
 * @property-read int|null $bans_count
 * @method static \Illuminate\Database\Eloquent\Builder|BanType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BanType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BanType query()
 * @method static \Illuminate\Database\Eloquent\Builder|BanType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BanType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BanType whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BanType whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BanType whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BanType whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BanType whereUpdatedAt($value)
 */
	class BanType extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $sort
 * @property string|null $title
 * @property string|null $button_text
 * @property string|null $image_url
 * @property string|null $redirect_url
 * @property string|null $publish_at
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $expire
 * @method static \Illuminate\Database\Eloquent\Builder|Banner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Banner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Banner query()
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereButtonText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereIsNotSeen($utcTimestamp)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner wherePublishAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereUpdatedAt($value)
 */
	class Banner extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id البادئ
 * @property int $from_uid مدرج في القائمة السوداء
 * @property int|null $status 1 سحب الأسود 2 فتح الأسود
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $blockedPerson
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|BlackList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlackList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlackList query()
 * @method static \Illuminate\Database\Eloquent\Builder|BlackList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlackList whereFromUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlackList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlackList whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlackList whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlackList whereUserId($value)
 */
	class BlackList extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $type 0=local 1=global
 * @property int|null $coins
 * @property int|null $users allowed users
 * @property string|null $image
 * @property int|null $has_label
 * @property string|null $default_label
 * @property int|null $duration in minutes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Box newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Box newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Box query()
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereDefaultLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereHasLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereUsers($value)
 */
	class Box extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $box_id
 * @property int|null $user_id
 * @property int|null $coins
 * @property int|null $end_at
 * @property int|null $room_uid
 * @property int|null $room_id
 * @property int|null $users_num
 * @property int|null $type 0=local 1=global
 * @property string|null $label
 * @property int|null $used_num
 * @property int|null $not_used_num
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $used_coins
 * @property int|null $unused_coins
 * @property int|null $start_at
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse query()
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereBoxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereNotUsedNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereRoomUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereUnusedCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereUsedCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereUsedNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereUsersNum($value)
 */
	class BoxUse extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $agencies_ids
 * @property int|null $old_agency_manger_id
 * @property int|null $new_agency_manger_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ChangeAgencyManger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChangeAgencyManger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChangeAgencyManger query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChangeAgencyManger whereAgenciesIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChangeAgencyManger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChangeAgencyManger whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChangeAgencyManger whereNewAgencyMangerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChangeAgencyManger whereOldAgencyMangerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChangeAgencyManger whereUpdatedAt($value)
 */
	class ChangeAgencyManger extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $charger_id
 * @property string $charger_type
 * @property int|null $user_id
 * @property string $user_type
 * @property string|null $amount
 * @property int|null $amount_type 1=diamonds 2=coins 3=gold 4=flowers
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $balance_before
 * @property int $is_used_transferred
 * @property float $usd
 * @property int|null $agency_id
 * @property-read \App\Models\User|null $receiver
 * @property-read \App\Models\User|null $sender
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Charge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Charge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Charge query()
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereAmountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereBalanceBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereChargerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereChargerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereIsUsedTransferred($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereUserType($value)
 */
	class Charge extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property float|null $usd
 * @property float|null $value
 * @property int|null $type 0=coins 1=selver coins
 * @property string|null $usd_img
 * @property string|null $type_img
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeValue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeValue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeValue query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeValue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeValue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeValue whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeValue whereTypeImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeValue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeValue whereUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeValue whereUsdImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeValue whereValue($value)
 */
	class ChargeValue extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int $chat_with_friends
 * @property int $chat_with_followers
 * @property int $chat_with_all
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $chat_with_following
 * @method static \Illuminate\Database\Eloquent\Builder|ChatSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatSetting whereChatWithAll($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatSetting whereChatWithFollowers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatSetting whereChatWithFollowing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatSetting whereChatWithFriends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatSetting whereUserId($value)
 */
	class ChatSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $phone
 * @property string $code
 * @property string|null $aid admin_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Code newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Code newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Code query()
 * @method static \Illuminate\Database\Eloquent\Builder|Code whereAid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Code whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Code whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Code whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Code wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Code whereUpdatedAt($value)
 */
	class Code extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property float|null $usd
 * @property int|null $coin
 * @property int|null $first_charge_coin
 * @property int|null $status
 * @property string|null $discount_code
 * @property int|null $discount_code_expire_in days
 * @property int|null $extra_value
 * @property int|null $extra_value_end_in days
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $sort
 * @method static \Illuminate\Database\Eloquent\Builder|Coin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Coin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Coin onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Coin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Coin whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coin whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coin whereDiscountCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coin whereDiscountCodeExpireIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coin whereExtraValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coin whereExtraValueEndIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coin whereFirstChargeCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coin whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coin whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coin whereUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Coin withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Coin withoutTrashed()
 */
	class Coin extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int $coins
 * @property int $type
 * @property string $game_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Game|null $game
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|CoinGameUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CoinGameUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CoinGameUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|CoinGameUser whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinGameUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinGameUser whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinGameUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinGameUser whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinGameUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinGameUser whereUserId($value)
 */
	class CoinGameUser extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property float|null $paid_usd
 * @property int|null $obtained_coins
 * @property int|null $user_id
 * @property string|null $method
 * @property int|null $donor_id
 * @property string|null $donor_type
 * @property int|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $trx
 * @property string|null $pid
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog whereDonorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog whereDonorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog whereObtainedCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog wherePaidUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog wherePid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog whereTrx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoinLog whereUserId($value)
 */
	class CoinLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $color
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Color newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Color newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Color query()
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereUpdatedAt($value)
 */
	class Color extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $author_id
 * @property int $commentable_id
 * @property string|null $commentable_type
 * @property string|null $body
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereCommentableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereCommentableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereUpdatedAt($value)
 */
	class Comment extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property float $amount
 * @property int|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Commission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Commission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Commission query()
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereUpdatedAt($value)
 */
	class Commission extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $value
 * @property string|null $desc
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_hidden
 * @method static \Illuminate\Database\Eloquent\Builder|Config newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Config newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Config query()
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Config whereValue($value)
 */
	class Config extends \Eloquent {}
}

namespace App\Models\Conversation{
/**
 * 
 *
 * @property int $id
 * @property int $first_user_id
 * @property int $second_user_id
 * @property int $is_accepted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File\File> $files
 * @property-read int|null $files_count
 * @property-read \App\Models\User|null $firstUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message\Message> $messages
 * @property-read int|null $messages_count
 * @property-read \App\Models\User|null $secondUser
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereFirstUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereIsAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereSecondUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereUpdatedAt($value)
 */
	class Conversation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property int $coins
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallet query()
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallet whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallet whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallet whereUpdatedAt($value)
 */
	class CoreWallet extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property int $coins
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $update_for_human
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallets newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallets newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallets query()
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallets whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallets whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallets whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallets whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CoreWallets whereUpdatedAt($value)
 */
	class CoreWallets extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $e_name
 * @property string|null $flag
 * @property string|null $status
 * @property string|null $phone_code
 * @property string|null $language
 * @property string|null $iso
 * @property string|null $iso3
 * @property string|null $continent_name
 * @property string|null $e_continent_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\SalaryTransaction\Entities\ChargeCountry> $chargeCountry
 * @property-read int|null $charge_country_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Country query()
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereContinentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereEContinentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereEName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereIso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereIso3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country wherePhoneCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Country whereUpdatedAt($value)
 */
	class Country extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $cp_relation_id
 * @property int $user_one_id
 * @property int $user_two_id
 * @property int $status pending=>0,accepted=>1,refused=>2,stop=>3,restore=>4
 * @property int $di
 * @property int $level_id
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $fromUser
 * @property-read \Modules\CP\Entities\CpRelation|null $relation
 * @property-read \App\Models\User $toUser
 * @method static \Illuminate\Database\Eloquent\Builder|Cp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cp query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cp relation()
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereCpRelationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereDi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereUserOneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereUserTwoId($value)
 */
	class Cp extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $room_id
 * @property int|null $percentage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $gift_id
 * @property-read \App\Models\Gift|null $gift
 * @property-read \App\Models\Room|null $room
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|CustomZegoMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomZegoMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomZegoMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomZegoMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomZegoMessage whereGiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomZegoMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomZegoMessage wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomZegoMessage whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomZegoMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomZegoMessage whereUserId($value)
 */
	class CustomZegoMessage extends \Eloquent {}
}

namespace App\Models\DashboardModels{
/**
 * 
 *
 * @property int $id
 * @property int|null $ware_id
 * @property int $count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet query()
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet whereWareId($value)
 */
	class Sheet extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $action_mode
 * @property int $diamonds
 * @property string|null $type
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EarnedDiamond newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EarnedDiamond newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EarnedDiamond query()
 * @method static \Illuminate\Database\Eloquent\Builder|EarnedDiamond whereActionMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EarnedDiamond whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EarnedDiamond whereDiamonds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EarnedDiamond whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EarnedDiamond whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EarnedDiamond whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EarnedDiamond whereUserId($value)
 */
	class EarnedDiamond extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $pid parent class id
 * @property string|null $name
 * @property string|null $emoji static image
 * @property int|null $t_length Duration (seconds)
 * @property int|null $enable 1 enable 2 disable
 * @property int|null $sort to sort
 * @property int|null $addtime
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $name_en
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji query()
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji whereAddtime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji whereEmoji($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji wherePid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji whereTLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Emoji whereUpdatedAt($value)
 */
	class Emoji extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $uid
 * @property int $ruid
 * @property int $rid
 * @property string|null $entered_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Room|null $room
 * @method static \Illuminate\Database\Eloquent\Builder|EnteredRoom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EnteredRoom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EnteredRoom query()
 * @method static \Illuminate\Database\Eloquent\Builder|EnteredRoom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EnteredRoom whereEnteredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EnteredRoom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EnteredRoom whereRid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EnteredRoom whereRuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EnteredRoom whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EnteredRoom whereUpdatedAt($value)
 */
	class EnteredRoom extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $diamonds
 * @property float|null $value
 * @property int|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $sort
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange query()
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereDiamonds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exchange whereValue($value)
 */
	class Exchange extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $diamonds
 * @property float|null $value
 * @property int|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $operation_no
 * @property int|null $status
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeLog whereDiamonds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeLog whereOperationNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeLog whereValue($value)
 */
	class ExchangeLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $is_success
 * @property string $image avatar
 * @property string $name
 * @property string|null $introduce
 * @property string|null $notice announcement
 * @property int|null $num number of people
 * @property int $user_id owner
 * @property int|null $speakswitch Whether members are banned
 * @property int|null $status
 * @property int|null $update_user_id editor
 * @property int|null $suctime success time
 * @property int|null $start_time Start time for less than 20 people
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $hot
 * @property int $current_level_id
 * @property int|null $today_rank
 * @property int|null $week_rank
 * @property int|null $month_rank
 * @property int $total_diamond
 * @property-read mixed $admins_num
 * @property-read mixed $level
 * @property-read mixed $level_max_admins_num
 * @property-read mixed $level_max_members_num
 * @property-read mixed $members_count
 * @property-read mixed $members_num
 * @property-read mixed $num_admins
 * @property-read mixed $rank
 * @property-read mixed $rank_string
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Family newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Family newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Family query()
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereCurrentLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereHot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereIntroduce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereIsSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereMonthRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereNotice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereSpeakswitch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereSuctime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereTodayRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereTotalDiamond($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereUpdateUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Family whereWeekRank($value)
 */
	class Family extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $sort
 * @property string|null $name
 * @property string|null $img
 * @property int|null $exp
 * @property int|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $members
 * @property int|null $admins
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyLevel query()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyLevel whereAdmins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyLevel whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyLevel whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyLevel whereMembers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyLevel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyLevel whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyLevel whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyLevel whereUpdatedAt($value)
 */
	class FamilyLevel extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $family_id
 * @property int $day
 * @property int $month
 * @property int $year
 * @property int $coins
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Family $family
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyRank newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyRank newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyRank query()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyRank whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyRank whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyRank whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyRank whereFamilyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyRank whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyRank whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyRank whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyRank whereYear($value)
 */
	class FamilyRank extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $family_id
 * @property int $user_type Status 0=ordinary member 1=administrator 2=patriarch
 * @property int|null $status Status 0=pending 1=passed 2=rejected
 * @property int|null $type Type 0=Invite 1=Apply
 * @property int|null $ope_user_id operator id
 * @property int|null $ope_time operating time
 * @property int|null $closeswitch Whether to block family news 0=not block 1=block
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\FamilyUserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser whereCloseswitch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser whereFamilyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser whereOpeTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser whereOpeUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyUser whereUserType($value)
 */
	class FamilyUser extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FamilyView query()
 */
	class FamilyView extends \Eloquent {}
}

namespace App\Models\File{
/**
 * 
 *
 * @property int $id
 * @property int $conversation_id
 * @property string $conversation_type
 * @property int $message_id
 * @property int $user_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $conversation
 * @property-read mixed $file_details
 * @property-read \App\Models\Message\Message|null $message
 * @property-read \App\Models\User|null $sender
 * @method static \Illuminate\Database\Eloquent\Builder|File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|File query()
 * @method static \Illuminate\Database\Eloquent\Builder|File whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereConversationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUserId($value)
 */
	class File extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $role_id
 * @property int $menu_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FixMenuRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FixMenuRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FixMenuRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|FixMenuRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixMenuRole whereMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixMenuRole whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixMenuRole whereUpdatedAt($value)
 */
	class FixMenuRole extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $followed_user_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Moment\Entities\Moment> $moments
 * @property-read int|null $moments_count
 * @property-read \App\Models\Room|null $room
 * @method static \Illuminate\Database\Eloquent\Builder|Follow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Follow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Follow query()
 * @method static \Illuminate\Database\Eloquent\Builder|Follow whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Follow whereFollowedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Follow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Follow whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Follow whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Follow whereUserId($value)
 */
	class Follow extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $game_id
 * @property string|null $lang
 * @property string|null $sign
 * @property string|null $uid
 * @property int|null $amount
 * @property int|null $round
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Game newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Game newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Game query()
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereRound($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereSign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereUpdatedAt($value)
 */
	class Game extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $value
 * @property int $admin_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GameChargeHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameChargeHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameChargeHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|GameChargeHistory whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GameChargeHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GameChargeHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GameChargeHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GameChargeHistory whereValue($value)
 */
	class GameChargeHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $balance
 * @property int $used
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GameWallet filterByMonth()
 * @method static \Illuminate\Database\Eloquent\Builder|GameWallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameWallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameWallet query()
 * @method static \Illuminate\Database\Eloquent\Builder|GameWallet whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GameWallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GameWallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GameWallet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GameWallet whereUsed($value)
 */
	class GameWallet extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $name اسم الهدية
 * @property string|null $e_name الاسم الانجليزي
 * @property int|null $type 1 هدية عادية 2 هدية ساخنة
 * @property int|null $vip_level المستوى المطلوب لكبار الشخصيات
 * @property int|null $hot
 * @property int|null $is_play 0 لا يوجد بث 1 خدمة كاملة البث
 * @property int|null $price السعر
 * @property string|null $img
 * @property string|null $show_img
 * @property string|null $show_img2
 * @property int|null $sort
 * @property int|null $enable 1 ممكنة 2 غير ممكنة
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $music_gift
 * @property int $international_gift
 * @property int $use_count
 * @property string|null $image_type
 * @property-read \Modules\Achievement\Entities\GiftAchievement|null $achievement
 * @property-read \App\Models\LuckyGift|null $luckyGift
 * @property-read \App\Models\LuckyGift|null $lucky_gift
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Moment\Entities\Moment> $moments
 * @property-read int|null $moments_count
 * @method static \Illuminate\Database\Eloquent\Builder|Gift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Gift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Gift query()
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereEName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereHot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereImageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereInternationalGift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereIsPlay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereMusicGift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereShowImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereShowImg2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereUseCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gift whereVipLevel($value)
 */
	class Gift extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $type 1 جوهرة 2 هدية
 * @property int $giftId معرف الهدية
 * @property int $roomowner_id معرف صاحب الغرفة
 * @property string $giftName اسم الهدية
 * @property int $giftNum كمية الهدية
 * @property string $giftPrice سعر الهدية
 * @property int $sender_id هوية مرسل الهدية
 * @property int $receiver_id معرف المستلم
 * @property int|null $cp_id
 * @property int|null $is_play 1 بث 2 لا يبث
 * @property string|null $platform_obtain المبلغ الذي حصلت عليه المنصة
 * @property string|null $receiver_obtain المبلغ الذي حصل عليه المستلم
 * @property string|null $roomowner_obtain دخل صاحب الغرفة
 * @property int|null $union_id معرف النقابة
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $sender_family_id
 * @property int|null $receiver_family_id
 * @property int|null $agency_id
 * @property string|null $agency_obtain
 * @property int|null $moent_id
 * @property int $pk
 * @property int|null $room_id
 * @property int $room_gift_status
 * @property-read \App\Models\Cp|null $cp
 * @property-read \App\Models\Cp|null $cps
 * @property-read \App\Models\Gift|null $gift
 * @property-read \App\Models\User|null $receiver
 * @property-read \App\Models\User|null $roomOwner
 * @property-read \App\Models\User|null $sender
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereAgencyObtain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereCpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereGiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereGiftName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereGiftNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereGiftPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereIsPlay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereMoentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog wherePk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog wherePlatformObtain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereReceiverFamilyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereReceiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereReceiverObtain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereRoomGiftStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereRoomownerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereRoomownerObtain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereSenderFamilyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereUnionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftLog whereUpdatedAt($value)
 */
	class GiftLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $skill_apply_id user skill id
 * @property string|null $order_no
 * @property int|null $user_id consumer
 * @property int|null $master_id
 * @property int|null $status
 * @property int|null $skill_id
 * @property int|null $start_time service hours
 * @property int|null $num quantity
 * @property string|null $remarks Remark
 * @property int|null $price unit price
 * @property string|null $unit
 * @property int|null $total_price
 * @property string|null $fee handling fee
 * @property string|null $real_price Actual credited amount
 * @property string|null $refund refund amount
 * @property int|null $pay_type 1 google pay 2 apple pay 3 Balance 4 Pending payment
 * @property int|null $is_first Immediate service: 0 not applied 1 applied
 * @property int|null $is_discuss Rating: 0 not rated 1 rated
 * @property int|null $is_notify Whether the opening notification has been sent
 * @property string|null $cancel Reason for Cancellation
 * @property int|null $coupon_id
 * @property string|null $coupon_price Discounted price
 * @property string|null $reason سبب الاستئناف
 * @property string|null $images لقطة من الاستئناف
 * @property int|null $f_user_id
 * @property string|null $out_refund_no رقم طلب رد الأموال
 * @property int|null $addtime وقت الطلب
 * @property int|null $paytime وقت الدفع
 * @property int|null $refusetime وقت رفض الاسترداد
 * @property int|null $finishtime وقت الاكتمال
 * @property int|null $union_id معرف النقابة
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereAddtime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereCancel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereCouponPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereFUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereFinishtime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereIsDiscuss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereIsFirst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereIsNotify($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereMasterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereOutRefundNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder wherePayType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder wherePaytime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereRealPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereRefund($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereRefusetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereSkillApplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereSkillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereUnionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GmOrder whereUserId($value)
 */
	class GmOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $text
 * @property string $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $image
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|GroupChat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupChat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupChat query()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupChat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupChat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupChat whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupChat whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupChat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupChat whereUserId($value)
 */
	class GroupChat extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $agency_id
 * @property int $diamond
 * @property int $month
 * @property int $year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $pid
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|History newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|History newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|History query()
 * @method static \Illuminate\Database\Eloquent\Builder|History whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereDiamond($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History wherePid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereYear($value)
 */
	class History extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $img
 * @property string|null $contents
 * @property string|null $url
 * @property int|null $enable 1 enable 2 disable
 * @property int|null $sort
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $type
 * @property int|null $owner_id
 * @property int|null $duration
 * @property int|null $form
 * @property int|null $input
 * @property string|null $event_type
 * @property-read \App\Models\Room|null $room
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel query()
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereContents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereForm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereInput($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomeCarousel whereUrl($value)
 */
	class HomeCarousel extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $url
 * @property int|null $type
 * @property int|null $status
 * @property string|null $title
 * @property string|null $description
 * @property int|null $admin_user_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Image newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Image query()
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereAdminUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereUserId($value)
 */
	class Image extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $name
 * @property string $image
 * @property string $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ImageColor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ImageColor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ImageColor query()
 * @method static \Illuminate\Database\Eloquent\Builder|ImageColor whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImageColor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImageColor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImageColor whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImageColor whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImageColor whereUpdatedAt($value)
 */
	class ImageColor extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $sort
 * @property string|null $name
 * @property string|null $img
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Interest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Interest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Interest query()
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereUpdatedAt($value)
 */
	class Interest extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $interests
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Interest|null $interests2
 * @method static \Illuminate\Database\Eloquent\Builder|InterestUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InterestUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InterestUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|InterestUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InterestUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InterestUser whereInterests($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InterestUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InterestUser whereUserId($value)
 */
	class InterestUser extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $ip
 * @property int|null $uid
 * @property int|null $user_type 0=app 1=dash
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Ip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ip query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ip whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ip whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ip whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ip whereUserType($value)
 */
	class Ip extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $kicked_user_id
 * @property int|null $user_id
 * @property int|null $room_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|KickRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KickRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KickRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|KickRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KickRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KickRecord whereKickedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KickRecord whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KickRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KickRecord whereUserId($value)
 */
	class KickRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $uid
 * @property int $user_id
 * @property int $scale
 * @property int|null $status 1. The host sends out an application. 2. The host accepts the invitation. 3. The host terminates the relationship. 4. The host terminates the relationship.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Leader newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leader newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leader query()
 * @method static \Illuminate\Database\Eloquent\Builder|Leader whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leader whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leader whereScale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leader whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leader whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leader whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leader whereUserId($value)
 */
	class Leader extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $author_id
 * @property int $likeable_id
 * @property string $likeable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Like newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Like newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Like query()
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereLikeableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereLikeableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereUpdatedAt($value)
 */
	class Like extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $uid room uid
 * @property int $start_time
 * @property string|null $end_time
 * @property string|null $hours
 * @property string|null $days
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id
 * @method static \Illuminate\Database\Eloquent\Builder|LiveTime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LiveTime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LiveTime query()
 * @method static \Illuminate\Database\Eloquent\Builder|LiveTime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiveTime whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiveTime whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiveTime whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiveTime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiveTime whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiveTime whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiveTime whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LiveTime whereUserId($value)
 */
	class LiveTime extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $gift_id
 * @property int $win_probability
 * @property string|null $min_percentage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LuckyGift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LuckyGift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LuckyGift query()
 * @method static \Illuminate\Database\Eloquent\Builder|LuckyGift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LuckyGift whereGiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LuckyGift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LuckyGift whereMinPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LuckyGift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LuckyGift whereWinProbability($value)
 */
	class LuckyGift extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $name_ar
 * @property string|null $name_en
 * @property string|null $img
 * @property string|null $description_ar
 * @property string|null $description_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MangerType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MangerType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MangerType query()
 * @method static \Illuminate\Database\Eloquent\Builder|MangerType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MangerType whereDescriptionAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MangerType whereDescriptionEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MangerType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MangerType whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MangerType whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MangerType whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MangerType whereUpdatedAt($value)
 */
	class MangerType extends \Eloquent {}
}

namespace App\Models\Message{
/**
 * 
 *
 * @property int $id
 * @property int $conversation_id
 * @property string $conversation_type
 * @property int $user_id
 * @property string $text
 * @property int $is_reading
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $conversation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File\File> $files
 * @property-read int|null $files_count
 * @property-read \App\Models\User|null $sender
 * @method static \Illuminate\Database\Eloquent\Builder|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Message query()
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereConversationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereIsReading($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereUserId($value)
 */
	class Message extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $roomowner_id room ouner
 * @property int|null $user_id on mic user
 * @property int|null $type 1 ordinary row of mic 2 point single row of mic
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Mic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mic query()
 * @method static \Illuminate\Database\Eloquent\Builder|Mic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mic whereRoomownerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mic whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mic whereUserId($value)
 */
	class Mic extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $uid
 * @property int $skill_id
 * @property int $service_time service hours
 * @property string|null $remark
 * @property int $addtime
 * @property int|null $endtime deadline
 * @property int|null $status 1=valid 0=invalid
 * @property string|null $adduser Operator
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Monad newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Monad newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Monad query()
 * @method static \Illuminate\Database\Eloquent\Builder|Monad whereAddtime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Monad whereAdduser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Monad whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Monad whereEndtime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Monad whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Monad whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Monad whereServiceTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Monad whereSkillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Monad whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Monad whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Monad whereUpdatedAt($value)
 */
	class Monad extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $sort
 * @property int|null $level
 * @property string|null $name
 * @property string|null $img
 * @property float|null $price
 * @property string|null $privileges
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $expire
 * @property int|null $exp
 * @method static \Illuminate\Database\Eloquent\Builder|OVip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OVip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OVip query()
 * @method static \Illuminate\Database\Eloquent\Builder|OVip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OVip whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OVip whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OVip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OVip whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OVip whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OVip whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OVip wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OVip wherePrivileges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OVip whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OVip whereUpdatedAt($value)
 */
	class OVip extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $off_id official message id
 * @property int $user_id
 * @property int|null $is_read Whether it has been read 1 has been read 2 has been deleted
 * @property int|null $addtime
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OffRead newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OffRead newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OffRead query()
 * @method static \Illuminate\Database\Eloquent\Builder|OffRead whereAddtime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffRead whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffRead whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffRead whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffRead whereOffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffRead whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OffRead whereUserId($value)
 */
	class OffRead extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $title
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $title_en
 * @property string|null $body_en
 * @method static \Illuminate\Database\Eloquent\Builder|Offer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Offer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Offer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereBodyEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereTitleEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offer whereUpdatedAt($value)
 */
	class Offer extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $img
 * @property int|null $user_id 0 for all users, others for users
 * @property string|null $content
 * @property int|null $type Message type, 1 system message 2 system announcement released in the background
 * @property int|null $sub_type
 * @property string|null $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $title_ar
 * @property int|null $from_user_id
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage whereFromUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage whereSubType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage whereTitleAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessage whereUserId($value)
 */
	class OfficialMessage extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $img
 * @property int|null $user_id 0 for all users, others for users
 * @property string|null $content
 * @property int|null $type Message type, 1 system message 2 system announcement released in the background
 * @property int|null $sub_type
 * @property string|null $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $title_ar
 * @property int|null $from_user_id
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin query()
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin whereFromUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin whereSubType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin whereTitleAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OfficialMessageAdmin whereUserId($value)
 */
	class OfficialMessageAdmin extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $agency_id
 * @property string $usd_pid
 * @property int $month
 * @property int $year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $total
 * @property int $OwnerBid
 * @method static \Illuminate\Database\Eloquent\Builder|Owner_pid_target newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Owner_pid_target newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Owner_pid_target query()
 * @method static \Illuminate\Database\Eloquent\Builder|Owner_pid_target whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Owner_pid_target whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Owner_pid_target whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Owner_pid_target whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Owner_pid_target whereOwnerBid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Owner_pid_target whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Owner_pid_target whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Owner_pid_target whereUsdPid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Owner_pid_target whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Owner_pid_target whereYear($value)
 */
	class Owner_pid_target extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $get_type Obtaining method 1 vip level automatic acquisition 2 activities 3 treasure box 4 purchase 5 = background addition
 * @property int|null $type 1 gem 2 = gift 3 card roll 4 avatar frame 5 bubble frame 6 entry special effects 7 microphone aperture 8 badge
 * @property int|null $target_id
 * @property int|null $num quantity
 * @property int|null $expire 0 forever else is expiry time
 * @property int|null $is_read Whether read 0=read 1=unread
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $sender_id
 * @property int|null $is_used
 * @property int|null $use_num
 * @property int $price
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\Ware|null $ware
 * @method static \Illuminate\Database\Eloquent\Builder|Pack newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pack newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pack query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pack whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pack whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pack whereGetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pack whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pack whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pack whereIsUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pack whereNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pack wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pack whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pack whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pack whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pack whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pack whereUseNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pack whereUserId($value)
 */
	class Pack extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $use_type 1 normal use, 2 background deduction
 * @property int|null $type
 * @property int|null $target_id item id
 * @property int|null $get_nums
 * @property int|null $now_nums
 * @property int|null $addtime
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PackLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PackLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PackLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|PackLog whereAddtime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackLog whereGetNums($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackLog whereNowNums($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackLog whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackLog whereUseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackLog whereUserId($value)
 */
	class PackLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $type
 * @property string|null $name
 * @property string|null $url
 * @property string|null $content تفاصيل
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $content_en
 * @method static \Illuminate\Database\Eloquent\Builder|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereContentEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereUrl($value)
 */
	class Page extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $photo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentGateway whereUpdatedAt($value)
 */
	class PaymentGateway extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethodHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethodHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethodHistory query()
 */
	class PaymentMethodHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $payment_withdraw_type_id
 * @property string $name
 * @property string $type
 * @property string|null $validate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $name_en
 * @property-read \App\Models\PaymentWithdrawType $withdrawType
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawField query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawField whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawField wherePaymentWithdrawTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawField whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawField whereValidate($value)
 */
	class PaymentWithdrawField extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $name_en
 * @property float $min_value
 * @property int $exchange_rate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserPaymentWithdrawField> $userWithdrawFields
 * @property-read int|null $user_withdraw_fields_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentWithdrawField> $withdrawFields
 * @property-read int|null $withdraw_fields_count
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawType query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawType whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawType whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawType whereMinValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawType whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentWithdrawType whereUpdatedAt($value)
 */
	class PaymentWithdrawType extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $team_1_boss
 * @property string|null $team_1
 * @property int|null $team_2_boss
 * @property string|null $team_2
 * @property int|null $judge
 * @property int|null $status
 * @property float|null $prize_value
 * @property int|null $room_id
 * @property string|null $start_at
 * @property string|null $end_at
 * @property string|null $winner
 * @property string|null $title
 * @property string|null $team_1_title
 * @property string|null $team_2_title
 * @property string|null $conditions
 * @property string|null $team_1_votes
 * @property string|null $team_2_votes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $mics
 * @property float|null $t1_score
 * @property float|null $t2_score
 * @property int $show_status
 * @property-read mixed $t1_per
 * @property-read mixed $t2_per
 * @method static \Illuminate\Database\Eloquent\Builder|Pk newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pk newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pk query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereJudge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereMics($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk wherePrizeValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereShowStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereT1Score($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereT2Score($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereTeam1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereTeam1Boss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereTeam1Title($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereTeam1Votes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereTeam2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereTeam2Boss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereTeam2Title($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereTeam2Votes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pk whereWinner($value)
 */
	class Pk extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $uid room owner id
 * @property int $user_id
 * @property int $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PlayNumLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlayNumLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlayNumLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlayNumLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlayNumLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlayNumLog wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlayNumLog whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlayNumLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlayNumLog whereUserId($value)
 */
	class PlayNumLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $title
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $title_en
 * @property string|null $body_en
 * @method static \Illuminate\Database\Eloquent\Builder|Police newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Police newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Police query()
 * @method static \Illuminate\Database\Eloquent\Builder|Police whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Police whereBodyEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Police whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Police whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Police whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Police whereTitleEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Police whereUpdatedAt($value)
 */
	class Police extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $name
 * @property string $avatar
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $di
 * @property int $Agency_manger
 * @property int $app_id
 * @property string $time_zone
 * @property int $is_preview
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Agency> $agencies
 * @property-read int|null $agencies_count
 * @property-read mixed $agency_id
 * @property-read mixed $img
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Agency> $per
 * @property-read int|null $per_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Encore\Admin\Auth\Database\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Encore\Admin\Auth\Database\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin query()
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin whereAgencyManger($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin whereDi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin whereIsPreview($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin whereTimeZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PreviewAdmin whereUsername($value)
 */
	class PreviewAdmin extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $avatar
 * @property int|null $gender
 * @property string|null $birthday
 * @property string|null $province
 * @property string|null $city
 * @property string|null $country
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $image_id
 * @method static \Database\Factories\ProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Profile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Profile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Profile query()
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereUserId($value)
 */
	class Profile extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $img
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGallary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGallary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGallary query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGallary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGallary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGallary whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGallary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileGallary whereUserId($value)
 */
	class ProfileGallary extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $liked_user_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileUserLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileUserLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileUserLike query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileUserLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileUserLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileUserLike whereLikedUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileUserLike whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileUserLike whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileUserLike whereUserId($value)
 */
	class ProfileUserLike extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileUserignore newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileUserignore newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileUserignore query()
 */
	class ProfileUserignore extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $visitor_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileVisitor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileVisitor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileVisitor query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileVisitor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileVisitor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileVisitor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileVisitor whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProfileVisitor whereVisitorId($value)
 */
	class ProfileVisitor extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $question
 * @property string|null $answer
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Question newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Question newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Question query()
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereUpdatedAt($value)
 */
	class Question extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $charger_id
 * @property float|null $value_usd
 * @property int|null $status 0=pending  1=accepted  2=denied
 * @property float|null $type_value coins value or selver coins value
 * @property int|null $type 0=coins 1=selver_coins
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $whatsapp
 * @method static \Illuminate\Database\Eloquent\Builder|RechargeRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RechargeRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RechargeRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|RechargeRequest whereChargerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RechargeRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RechargeRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RechargeRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RechargeRequest whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RechargeRequest whereTypeValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RechargeRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RechargeRequest whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RechargeRequest whereValueUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RechargeRequest whereWhatsapp($value)
 */
	class RechargeRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $room_id
 * @property int $room_game_id
 * @property int $user_id
 * @property string $type
 * @property float $coins
 * @property int $player_win_id
 * @property int $round_num
 * @property int $current_round
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $is_finished
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecordRoomGameUser> $players
 * @property-read int|null $players_count
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame whereCurrentRound($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame whereIsFinished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame wherePlayerWinId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame whereRoomGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame whereRoundNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGame whereUserId($value)
 */
	class RecordRoomGame extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $record_room_game_round_id
 * @property int $user_id
 * @property string|null $answer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameAnswerRound newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameAnswerRound newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameAnswerRound query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameAnswerRound whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameAnswerRound whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameAnswerRound whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameAnswerRound whereRecordRoomGameRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameAnswerRound whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameAnswerRound whereUserId($value)
 */
	class RecordRoomGameAnswerRound extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $record_room_game_id
 * @property int $round_number
 * @property int $player_win_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameRound newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameRound newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameRound query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameRound whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameRound whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameRound wherePlayerWinId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameRound whereRecordRoomGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameRound whereRoundNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameRound whereUpdatedAt($value)
 */
	class RecordRoomGameRound extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $record_room_game_id
 * @property int $user_id
 * @property string $status
 * @property string|null $team_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameUser whereRecordRoomGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameUser whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameUser whereTeamType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecordRoomGameUser whereUserId($value)
 */
	class RecordRoomGameUser extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $type
 * @property string $report_details
 * @property int $user_id
 * @property int $Reporter_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $image
 * @property-read \App\Models\User $Reporter
 * @property-read \App\Models\User $report
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Report_user filter($uid)
 * @method static \Illuminate\Database\Eloquent\Builder|Report_user newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Report_user newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Report_user query()
 * @method static \Illuminate\Database\Eloquent\Builder|Report_user whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report_user whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report_user whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report_user whereReportDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report_user whereReporterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report_user whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report_user whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report_user whereUserId($value)
 */
	class Report_user extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $owner_room_id
 * @property string $img
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float $price
 * @property int $expair
 * @property string $type
 * @method static \Illuminate\Database\Eloquent\Builder|RequestBackgroundImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestBackgroundImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestBackgroundImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestBackgroundImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestBackgroundImage whereExpair($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestBackgroundImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestBackgroundImage whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestBackgroundImage whereOwnerRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestBackgroundImage wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestBackgroundImage whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestBackgroundImage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestBackgroundImage whereUpdatedAt($value)
 */
	class RequestBackgroundImage extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property string|null $phone
 * @property string|null $gmail
 * @property string|null $country
 * @property int $status 0=>pending,1=>acceptable,2=>refused
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $payment_withdraw_type_id
 * @property string|null $reason_rejected
 * @property-read \App\Models\PaymentWithdrawType|null $paymentWithDraw
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary query()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary whereGmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary wherePaymentWithdrawTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary whereReasonRejected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestTakeSalary whereUserId($value)
 */
	class RequestTakeSalary extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @method static withoutAppends()
 * @property int $id
 * @property string $numid غرفة
 * @property int $uid صاحب الغرفة
 * @property string|null $room_status حالة الغرفة 1 عادية 2 مقفلة 3 محظورة 4 مغلقة
 * @property string $room_name اسم الغرفة
 * @property string|null $room_cover غطاء الغرفة صورة
 * @property string|null $room_intro إعلان الغرفة
 * @property string|null $room_pass كلمة المرور الغرفة
 * @property string|null $room_class القسم الرئيسي
 * @property string|null $room_type فئة الغرفة الفرعية
 * @property string|null $room_welcome تحية الغرفة
 * @property string|null $room_admin مدير الغرفة
 * @property string $room_visitor شاغلو الغرفة الحاليون ، قم بإزالة المالك
 * @property string|null $room_speak قائمة حظر الغرف
 * @property string|null $room_sound قائمة كتم الغرفة
 * @property string|null $room_black قائمة الأشخاص الذين تم طردهم من الغرفة
 * @property int|null $week_star 1 لا 2 نعم
 * @property int|null $ranking ترتيب عكسي
 * @property int|null $is_popular هل هي شعبية 1 2 ليست كذلك
 * @property int|null $secret_chat ما إذا كانت الدردشة السرية موصى بها 1 نعم 2 لا
 * @property int|null $is_top ما إذا كان التمسك بالقمة 1 هو 2 ليس كذلك
 * @property int|null $sort
 * @property int|null $room_background معرف صورة خلفية الغرفة
 * @property int|null $super_uid ما إذا كان يمكن لصاحب الغرفة تعيين نسبة المشاركة
 * @property int|null $is_afk 0 اترك 1 يلعب
 * @property int|null $hot 0 لا 1 نعم
 * @property string|null $room_judge قضاة الغرفة
 * @property string|null $microphone معلومات الميكروفون فارغة 0 ، -1 يقفل الميكروفون ، والآخرون مستخدمون
 * @property string|null $is_prohibit_sound سواء كان تعطيل الصوت امكانية تعطيل صوت الميكروفون 0 ليس ممنوع 1
 * @property string|null $openid غير مفعل
 * @property string|null $commission_proportion غير مفعل
 * @property string|null $fresh_time وقت تحديث الغرفة ، غير ممكّن
 * @property int|null $start_hour
 * @property int|null $end_hour
 * @property int|null $is_recommended سواء كان موصى به 1 نعم 2 لا
 * @property int|null $play_num مفتاح اللعبة الرقمي 1 على 0 إيقاف
 * @property int|null $free_mic بت مجاني للميكروفون 1 عند 0 إيقاف
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $mode
 * @property int|null $session
 * @property int|null $hour_hot
 * @property int|null $visitor_count
 * @property int|null $top_room
 * @property int|null $max_admin
 * @property int|null $count_room_socket
 * @property int $no_of_members
 * @property int $is_show_pk
 * @property int|null $top_user_id
 * @property int $pin
 * @property int $charizma_status
 * @property int|null $charizma_timestamp
 * @property int $sort_num
 * @property int|null $game_id
 * @property int|null $total_diamond
 * @property \App\Models\Vip|null $level
 * @property int|null $exp
 * @property int|null $level_id
 * @property int $total_game_coins
 * @property int $writing_disabled
 * @property int $is_pk_custom
 * @property-read \App\Models\Background|null $background
 * @property-read \App\Models\RequestBackgroundImage|null $backgroundImage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BoxUse> $boxUse
 * @property-read int|null $box_use_count
 * @property-read \App\Models\Family|null $family
 * @property-read \App\Models\AllGame|null $game
 * @property-read mixed $country
 * @property-read mixed $final_room_image
 * @property-read mixed $lang
 * @property-read mixed $main_microphone
 * @property-read mixed $salary
 * @property-read mixed $session_string
 * @property-read \App\Models\Pk|null $lastPk
 * @property-read \App\Models\User|null $owner
 * @property-read \App\Models\RoomCategory|null $roomCategory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $roomVisitorUsers
 * @property-read int|null $room_visitor_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoomVisitor> $roomVisitors
 * @property-read int|null $room_visitors_count
 * @property-read \App\Models\User|null $topUser
 * @method static \Illuminate\Database\Eloquent\Builder|Room newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Room newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Room query()
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereCharizmaStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereCharizmaTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereCommissionProportion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereCountRoomSocket($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereEndHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereFreeMic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereFreshTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereHot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereHourHot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereIsAfk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereIsPkCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereIsPopular($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereIsProhibitSound($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereIsRecommended($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereIsShowPk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereIsTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereMaxAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereMicrophone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereNoOfMembers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereNumid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereOpenid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room wherePin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room wherePlayNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRanking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomBlack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomCover($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomJudge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomSound($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomSpeak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomVisitor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereRoomWelcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereSecretChat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereSession($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereSortNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereStartHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereSuperUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereTopRoom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereTopUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereTotalDiamond($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereTotalGameCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereVisitorCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereWeekStar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Room whereWritingDisabled($value)
 */
	class Room extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $img
 * @property int $enable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $sort
 * @property string|null $name_ar
 * @property string|null $name_en
 * @method static \Illuminate\Database\Eloquent\Builder|RoomCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomCategory whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomCategory whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomCategory whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomCategory whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomCategory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomCategory whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomCategory whereUpdatedAt($value)
 */
	class RoomCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGame newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGame newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGame query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGame whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGame whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGame whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGame whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGame whereUpdatedAt($value)
 */
	class RoomGame extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $coins
 * @property string|null $image
 * @property int $target
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGiftTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGiftTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGiftTarget query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGiftTarget whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGiftTarget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGiftTarget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGiftTarget whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGiftTarget whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomGiftTarget whereUpdatedAt($value)
 */
	class RoomGiftTarget extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $room_id
 * @property int $target_id
 * @property int $coins
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoomGiftTarget> $roomTarget
 * @property-read int|null $room_target_count
 * @method static \Illuminate\Database\Eloquent\Builder|RoomOwnerAchievement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomOwnerAchievement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomOwnerAchievement query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomOwnerAchievement whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomOwnerAchievement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomOwnerAchievement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomOwnerAchievement whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomOwnerAchievement whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomOwnerAchievement whereUpdatedAt($value)
 */
	class RoomOwnerAchievement extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $from_user_id
 * @property int $to_user_id
 * @property string $message
 * @property int|null $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|RoomPrivateMessages newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomPrivateMessages newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomPrivateMessages query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomPrivateMessages whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomPrivateMessages whereFromUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomPrivateMessages whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomPrivateMessages whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomPrivateMessages wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomPrivateMessages whereToUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomPrivateMessages whereUpdatedAt($value)
 */
	class RoomPrivateMessages extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $room_id
 * @property int $salary
 * @property int $cut_amount
 * @property int $month
 * @property int $year
 * @property int $is_paid
 * @property string $diamond
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Room $room
 * @method static \Illuminate\Database\Eloquent\Builder|RoomSalary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomSalary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomSalary query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomSalary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomSalary whereCutAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomSalary whereDiamond($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomSalary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomSalary whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomSalary whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomSalary whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomSalary whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomSalary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomSalary whereYear($value)
 */
	class RoomSalary extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $coins
 * @property int $usd
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTarget query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTarget whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTarget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTarget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTarget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTarget whereUsd($value)
 */
	class RoomTarget extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $room_id
 * @property int $user_id
 * @property int $coins
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTopUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTopUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTopUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTopUser whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTopUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTopUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTopUser whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTopUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomTopUser whereUserId($value)
 */
	class RoomTopUser extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int|null $id
 * @property string|null $numid
 * @property int|null $uid
 * @property string|null $room_status
 * @property string|null $room_name
 * @property string|null $room_cover
 * @property string|null $room_intro
 * @property string|null $room_pass
 * @property string|null $room_class
 * @property string|null $room_type
 * @property string|null $room_welcome
 * @property string|null $room_admin
 * @property string|null $room_visitor
 * @property string|null $room_speak
 * @property string|null $room_sound
 * @property string|null $room_black
 * @property int|null $week_star
 * @property int|null $ranking
 * @property int|null $is_popular
 * @property int|null $secret_chat
 * @property int|null $is_top
 * @property int|null $sort
 * @property int|null $room_background
 * @property int|null $super_uid
 * @property int|null $is_afk
 * @property int|null $hot
 * @property string|null $room_judge
 * @property string|null $microphone
 * @property string|null $is_prohibit_sound
 * @property string|null $openid
 * @property string|null $commission_proportion
 * @property string|null $fresh_time
 * @property int|null $start_hour
 * @property int|null $end_hour
 * @property int|null $is_recommended
 * @property int|null $play_num
 * @property int|null $free_mic
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string|null $today_rank
 * @property-read mixed $country
 * @property-read mixed $lang
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereCommissionProportion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereEndHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereFreeMic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereFreshTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereHot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereIsAfk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereIsPopular($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereIsProhibitSound($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereIsRecommended($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereIsTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereMicrophone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereNumid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereOpenid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView wherePlayNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRanking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomBlack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomCover($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomJudge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomSound($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomSpeak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomVisitor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereRoomWelcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereSecretChat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereStartHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereSuperUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereTodayRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomView whereWeekStar($value)
 */
	class RoomView extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $room_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|RoomVisitor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomVisitor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomVisitor query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomVisitor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomVisitor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomVisitor whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomVisitor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomVisitor whereUserId($value)
 */
	class RoomVisitor extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $type 0=user 1=agency
 * @property int $oid object id
 * @property float|null $amount
 * @property string|null $t_no
 * @property string|null $note
 * @property float|null $before_pay
 * @property float|null $after_pay
 * @property int|null $payer_id
 * @property int|null $payer_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx whereAfterPay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx whereBeforePay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx whereOid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx wherePayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx wherePayerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx whereTNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx whereUpdatedAt($value)
 */
	class SalaryTrx extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $type 1Official popular search 2User search history
 * @property int|null $user_id
 * @property string|null $search
 * @property int|null $sort to sort
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereSearch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereUserId($value)
 */
	class SearchHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $server_name
 * @property string|null $domain
 * @property string|null $short_name
 * @property string|null $img
 * @property string|null $description_ar
 * @property string|null $description_en
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $default
 * @property string|null $bucket_name
 * @property string|null $login_background
 * @property string|null $splash_background
 * @property int $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServerCountry> $serverCountries
 * @property-read int|null $server_countries_count
 * @method static \Illuminate\Database\Eloquent\Builder|Server newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Server newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Server query()
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereBucketName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereDescriptionAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereDescriptionEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereLoginBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereServerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereSplashBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereUpdatedAt($value)
 */
	class Server extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property-read \App\Models\Server|null $server
 * @method static \Illuminate\Database\Eloquent\Builder|ServerCountry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServerCountry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServerCountry query()
 */
	class ServerCountry extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $shareable_id
 * @property string $shareable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Share newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Share newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Share query()
 * @method static \Illuminate\Database\Eloquent\Builder|Share whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Share whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Share whereShareableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Share whereShareableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Share whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Share whereUserId($value)
 */
	class Share extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property float|null $coin
 * @property float|null $silver
 * @property int|null $sort
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Silver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Silver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Silver query()
 * @method static \Illuminate\Database\Eloquent\Builder|Silver whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Silver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Silver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Silver whereSilver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Silver whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Silver whereUpdatedAt($value)
 */
	class Silver extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property float|null $coins
 * @property float|null $silvers
 * @property int|null $user_id
 * @property int|null $selver_id
 * @property int|null $charger_id
 * @property int|null $charger_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SilverHestory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SilverHestory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SilverHestory query()
 * @method static \Illuminate\Database\Eloquent\Builder|SilverHestory whereChargerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SilverHestory whereChargerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SilverHestory whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SilverHestory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SilverHestory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SilverHestory whereSelverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SilverHestory whereSilvers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SilverHestory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SilverHestory whereUserId($value)
 */
	class SilverHestory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id 0 is background
 * @property string|null $get_nums إجمالي مبلغ التحويل
 * @property int|null $get_type
 * @property string|null $now_nums الرصيد الحالي
 * @property string|null $adduser مسؤول الخلفية
 * @property string|null $symbol
 * @property int|null $types Currency Type 1=Diamond 2=Coin 3=Room Flow
 * @property int|null $union_id معرف النقابة
 * @property int|null $family_id معرف الاسرة
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog whereAdduser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog whereFamilyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog whereGetNums($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog whereGetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog whereNowNums($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog whereTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog whereUnionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreLog whereUserId($value)
 */
	class StoreLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Video> $videos
 * @property-read int|null $videos_count
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereUpdatedAt($value)
 */
	class Tag extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $tag_id
 * @property int $taggable_id
 * @property string $taggable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable query()
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable whereTaggableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable whereTaggableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable whereUpdatedAt($value)
 */
	class Taggable extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $level
 * @property int|null $diamonds
 * @property int|null $minuts
 * @property int|null $hours
 * @property int|null $days
 * @property string|null $img
 * @property string|null $usd
 * @property string|null $coin
 * @property string|null $gold
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $agency_share
 * @property string $moment
 * @property string $reel
 * @method static \Illuminate\Database\Eloquent\Builder|Target newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Target newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Target query()
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereAgencyShare($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereDiamonds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereGold($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereMinuts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereMoment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereReel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereUsd($value)
 */
	class Target extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $type 1 novice task 2 daily tasks
 * @property string|null $img
 * @property string|null $title
 * @property int|null $num Completions
 * @property int|null $jinbi Number of reward coins
 * @property int|null $enable 1 enable 2 disable
 * @property int|null $addtime
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereAddtime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereJinbi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUpdatedAt($value)
 */
	class Task extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $user_id
 * @property string|null $contact_num
 * @property string|null $problem
 * @property string|null $img
 * @property int|null $status
 * @property int|null $admin_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $description
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereContactNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereProblem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUserId($value)
 */
	class Ticket extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $uid Homeo wner ID
 * @property int $user_id
 * @property int $time Duration  in seconds
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TimeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TimeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TimeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|TimeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeLog whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeLog whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeLog whereUserId($value)
 */
	class TimeLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $img صورة التأهيل
 * @property string|null $nickname كنية
 * @property string|null $notice
 * @property string|null $contents
 * @property string|null $phone رقم الاتصال
 * @property string $url الصورة الرمزية
 * @property int|null $status 1 عادي 2 معطل 3 حذف
 * @property int|null $users_id إضافة مستخدم
 * @property string|null $check_time وقت المراجعة
 * @property int|null $check_uid مستخدم التدقيق
 * @property int|null $check_status 0 لم تتم مراجعته 1 تمت مراجعته 2 مرفوض
 * @property int|null $admin_id معرف حساب المسؤول
 * @property float|null $share قسّم إلى نسبة
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Union newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Union newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Union query()
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereCheckStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereCheckTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereCheckUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereContents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereNotice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereShare($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Union whereUsersId($value)
 */
	class Union extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $admin_id معرف حساب المسؤول
 * @property int|null $user_id
 * @property int|null $agency_id
 * @property int $user_type 0=>users,1=>agencies
 * @property int $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UsdTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsdTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsdTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder|UsdTransfer whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsdTransfer whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsdTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsdTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsdTransfer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsdTransfer whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsdTransfer whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsdTransfer whereValue($value)
 */
	class UsdTransfer extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @method static withoutAppends()
 * @property int $id
 * @property int $online
 * @property string|null $name
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $phone
 * @property string|null $google_id
 * @property string|null $huawei_id
 * @property string|null $facebook_id
 * @property int|null $di رصيد الماس
 * @property float|null $coins رصيد كوين
 * @property float|null $room_coins ايرادات الغرفة كوين
 * @property float|null $flowers زهور
 * @property float|null $flowers_value قيمة الزهور
 * @property float|null $gold ذهب
 * @property int|null $is_leader
 * @property int|null $is_sign
 * @property int|null $isOnline
 * @property int|null $status 1 عادي 0 ممنوع تسجيل الدخول
 * @property int|null $is_points_first هل النقطة 500 لأول مرة 1 = نعم
 * @property string|null $locktime وقت التوقيف
 * @property int|null $online_time
 * @property int|null $dress_1 الصورة الرمزية الإطار واللباس
 * @property int|null $dress_2 صندوق الدردشه
 * @property int|null $dress_3 تأثيرات الدخول
 * @property int|null $dress_4 هالة على المايك
 * @property int|null $cp_card عدد حقول CP
 * @property int|null $keys_num عدد المفاتيح
 * @property string|null $nickname
 * @property string|null $idno رقم الهوية
 * @property string|null $mykeep غرفتي المفضلة
 * @property string|null $system
 * @property string|null $channel
 * @property string|null $img_1
 * @property string|null $img_2
 * @property string|null $img_3
 * @property int|null $points غرفتي المفضلة
 * @property string|null $login_ip
 * @property string|null $device_token
 * @property int|null $scale معدل الدوران مقسم إلى نسبة وحدة (٪)
 * @property int|null $is_idcard التاكد من الهوية 0 لم تؤكد 1 تم تاكيدها
 * @property int|null $country_id
 * @property int|null $now_room_uid
 * @property string|null $bio
 * @property int|null $agency_id
 * @property int|null $family_id
 * @property int|null $is_host
 * @property string|null $whatsapp
 * @property float|null $old_usd
 * @property float|null $target_usd
 * @property float|null $target_token_usd
 * @property string|null $uuid
 * @property int|null $is_gold_id
 * @property string|null $chat_id
 * @property string|null $notification_id
 * @property int|null $vip
 * @property int|null $sub_sender_level
 * @property int|null $sub_receiver_level
 * @property int|null $sub_sender_num
 * @property int|null $sub_receiver_num
 * @property float $salary
 * @property int $monthly_diamond_send
 * @property int $total_diamond_send
 * @property int $monthly_diamond_received
 * @property int $total_diamond_received
 * @property int $sender_level
 * @property int $received_level
 * @property int $type_user
 * @property int $is_manger
 * @property int $dashboard_manager_id
 * @property string|null $apple_id
 * @property int $today_days
 * @property int $monthly_days
 * @property int $total_days
 * @property int $unread_count_message
 * @property string $lan
 * @property int|null $image_color_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $current_app_version
 * @property int $can_play
 * @property int|null $stopshow_gift
 * @property int|null $manger_type_id
 * @property string|null $reel_following_type
 * @property bool $charge_status
 * @property int $android_version
 * @property int $ios_version
 * @property int $huawei_version
 * @property int $appear_charger_agency
 * @property string|null $special_id
 * @property int|null $current_room_chat
 * @property string $type
 * @property int|null $game_id
 * @property string|null $join_agency_date
 * @property bool $transfer_salary
 * @property int $salary_is_updated
 * @property int $is_logout
 * @property string|null $auth_token
 * @property string|null $lat
 * @property string|null $long
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentGateway> $AgencypaymentGateways
 * @property-read int|null $agencypayment_gateways_count
 * @property-read \App\Models\UserVip|null $UserVip
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\AgencyApp\Entities\AdditionalInfo> $additionalInfo
 * @property-read int|null $additional_info_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Agency> $agencies
 * @property-read int|null $agencies_count
 * @property-read \App\Models\Agency|null $agency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AgencyJoinRequest> $agencyJoinRequest
 * @property-read int|null $agency_join_request_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ban> $bans
 * @property-read int|null $bans_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HomeCarousel> $carousels
 * @property-read int|null $carousels_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Charge> $charges
 * @property-read int|null $charges_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChatSetting> $chat_settings
 * @property-read int|null $chat_settings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Chat\Entities\ChatRoom> $chats
 * @property-read int|null $chats_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $codeInvitations
 * @property-read int|null $code_invitations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserEarnInvitation> $codeInvitationsEarn
 * @property-read int|null $code_invitations_earn_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CoinGameUser> $coinGameUser
 * @property-read int|null $coin_game_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CoinLog> $coinLogs
 * @property-read int|null $coin_logs_count
 * @property-read \App\Models\ImageColor|null $color_image
 * @property-read \App\Models\Country|null $country
 * @property-read \App\Models\Ware|null $dress1
 * @property-read \App\Models\Ware|null $dress2
 * @property-read \App\Models\Ware|null $dress3
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExchangeLog> $exchangeLogs
 * @property-read int|null $exchange_logs_count
 * @property-read \App\Models\Family|null $family
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $followPacks
 * @property-read int|null $follow_packs_count
 * @property-read \App\Models\Follow|null $followedByAuthUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Follow> $followeds
 * @property-read int|null $followeds_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Follow> $followers
 * @property-read int|null $followers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $followersMoment
 * @property-read int|null $followers_moment_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $followerss
 * @property-read int|null $followerss_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $following
 * @property-read int|null $following_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Follow> $follows
 * @property-read int|null $follows_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $friends
 * @property-read int|null $friends_count
 * @property-read mixed $april_salary
 * @property-read mixed $avatar
 * @property-read mixed $bubble
 * @property-read mixed $coins_string
 * @property-read mixed $flag
 * @property-read mixed $follow_date
 * @property-read mixed $followed_date
 * @property mixed $following_unique_value
 * @property-read mixed $frame
 * @property-read mixed $gender
 * @property-read mixed $img
 * @property-read mixed $intro
 * @property-read mixed $is_agent
 * @property-read mixed $is_family_admin
 * @property-read mixed $is_family_owner
 * @property-read mixed $is_follow
 * @property-read mixed $lang
 * @property mixed $last_all_reel_id
 * @property mixed $last_following_reel_id
 * @property-read int $month_diamond
 * @property-read mixed $my_store
 * @property-read mixed $old
 * @property-read mixed $original_uuid
 * @property-read mixed $real_online_time
 * @property mixed $real_type
 * @property-read mixed $total_received_diamonds
 * @property mixed $total_received_level
 * @property-read mixed $total_sender_diamonds
 * @property mixed $total_sender_level
 * @property-read mixed $usd
 * @property mixed $user_diamond
 * @property-read mixed $user_type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GiftLog> $giftLogsSender
 * @property-read int|null $gift_logs_sender_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserVip> $haveVip
 * @property-read int|null $have_vip_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\History> $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $ignoredBy
 * @property-read int|null $ignored_by_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $ignores
 * @property-read int|null $ignores_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProfileGallary> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Interest> $interests
 * @property-read int|null $interests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ip> $ips
 * @property-read int|null $ips_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $likedBy
 * @property-read int|null $liked_by_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $likes
 * @property-read int|null $likes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LiveTime> $liveTime
 * @property-read int|null $live_time_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserLuckyGift> $luckyGifts
 * @property-read int|null $lucky_gifts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Agency> $managedAgencies
 * @property-read int|null $managed_agencies_count
 * @property-read \App\Models\MangerType|null $mangerType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Achievement\Entities\UserAchievementLevel> $medals
 * @property-read int|null $medals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Moment\Entities\MomentCommint> $moment_comments
 * @property-read int|null $moment_comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Moment\Entities\MomentLikes> $moment_likes
 * @property-read int|null $moment_likes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Moment\Entities\Moment> $moments
 * @property-read int|null $moments_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Agency|null $ownAgency
 * @property-read \App\Models\Room|null $ownerRoom
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $packs
 * @property-read int|null $packs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentGateway> $paymentGateways
 * @property-read int|null $payment_gateways_count
 * @property-read \App\Models\Profile|null $profile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $profileVisits
 * @property-read int|null $profile_visits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Reals\Entities\RealUserComment> $real_comments
 * @property-read int|null $real_comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Reals\Entities\RealUserLike> $real_likes
 * @property-read int|null $real_likes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Reals\Entities\Real> $reals
 * @property-read int|null $reals_count
 * @property-read \Modules\Reals\Entities\ReelsUserSetting|null $reelSetting
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RequestBackgroundImage> $requestBackgroundImages
 * @property-read int|null $request_background_images_count
 * @property-read \App\Models\Room|null $room
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Room> $rooms
 * @property-read int|null $rooms_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $sendPacks
 * @property-read int|null $send_packs_count
 * @property-read \App\Models\Pack|null $soundEffect
 * @property-read \App\Models\Pack|null $specialId
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserTarget> $targets
 * @property-read int|null $targets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TimeLog> $timeLog
 * @property-read int|null $time_log_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $userPacks
 * @property-read int|null $user_packs_count
 * @property-read \App\Models\UserSallary|null $userSalary
 * @property-read \App\Models\UserSallary|null $userSallary
 * @property-read \App\Models\UserSetting|null $userSetting
 * @property-read \App\Models\Family|null $user_family
 * @property-read \App\Models\Vip|null $vipImage
 * @property-read \App\Models\Ware|null $ware
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User getFollowers($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|User isFollow($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User ofAgency()
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User searchByUuid($toId)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAndroidVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAppearChargerAgency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAppleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAuthToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCanPlay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereChargeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCpCard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCurrentAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCurrentRoomChat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDashboardManagerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDress3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDress4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFacebookId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFamilyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFlowers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFlowersValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGold($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGoogleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHuaweiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHuaweiVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIdno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereImageColorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereImg1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereImg2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereImg3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIosVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsGoldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsIdcard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsLeader($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsLogout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsManger($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsPointsFirst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsSign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJoinAgencyDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereKeysNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLocktime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLong($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMangerTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMonthlyDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMonthlyDiamondReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMonthlyDiamondSend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMykeep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNowRoomUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOldUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOnlineTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereReceivedLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereReelFollowingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRoomCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSalaryIsUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereScale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSenderLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSpecialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStopshowGift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSubReceiverLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSubReceiverNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSubSenderLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSubSenderNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTargetTokenUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTargetUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTodayDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTotalDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTotalDiamondReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTotalDiamondSend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTransferSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTypeUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUnreadCountMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereVip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereWhatsapp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $banner_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Banner|null $banner
 * @method static \Illuminate\Database\Eloquent\Builder|UserBannerShow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBannerShow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBannerShow query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBannerShow whereBannerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBannerShow whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBannerShow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBannerShow whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBannerShow whereUserId($value)
 */
	class UserBannerShow extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $box_uses_id
 * @property int|null $user_id
 * @property int|null $coins
 * @property int|null $room_uid
 * @property int|null $room_id
 * @property int|null $type 0=local 1=global
 * @property int|null $box_uses_owner_id
 * @property string|null $image
 * @property string|null $label
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift whereBoxUsesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift whereBoxUsesOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift whereRoomUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBoxGift whereUserId($value)
 */
	class UserBoxGift extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $invited_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float $invited_charge
 * @property float $user_percentage
 * @property-read \App\Models\User|null $invited
 * @property-read \App\Models\User $parent
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation whereInvitedCharge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation whereInvitedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation whereUserPercentage($value)
 */
	class UserCodeInvitation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id معرف المستخدم
 * @property int $ware_id معرف السلعة
 * @property int|null $status 1 unused 2 used 3 expired
 * @property int $expire انتهاء الصلاحية
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoupon query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoupon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoupon whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoupon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoupon whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoupon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoupon whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoupon whereWareId($value)
 */
	class UserCoupon extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $parent_id
 * @property int|null $user_id
 * @property float $user_charge
 * @property float $parent_percentage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserEarnInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserEarnInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserEarnInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserEarnInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserEarnInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserEarnInvitation whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserEarnInvitation whereParentPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserEarnInvitation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserEarnInvitation whereUserCharge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserEarnInvitation whereUserId($value)
 */
	class UserEarnInvitation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $game_id
 * @property int $room_id
 * @property int $player_one_id
 * @property int $player_two_id
 * @property string $status
 * @property string $type
 * @property float $coins
 * @property string|null $answer_player_one
 * @property string|null $answer_player_two
 * @property int|null $player_win_id
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $player_one
 * @property-read \App\Models\User $player_two
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange whereAnswerPlayerOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange whereAnswerPlayerTwo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange wherePlayerOneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange wherePlayerTwoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange wherePlayerWinId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGameChallange whereUpdatedAt($value)
 */
	class UserGameChallange extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $type
 * @property int|null $level
 * @property int|null $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserLevelLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLevelLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLevelLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLevelLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLevelLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLevelLog whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLevelLog whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLevelLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLevelLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLevelLog whereUserId($value)
 */
	class UserLevelLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $gift_id
 * @property int $type
 * @property float $value
 * @property int $number
 * @property float $gift_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $total_win
 * @property int $total_num_win
 * @property-read \App\Models\Gift|null $gift
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift whereGiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift whereGiftPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift whereTotalNumWin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift whereTotalWin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift whereValue($value)
 */
	class UserLuckyGift extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $low_price
 * @property int|null $mid_price
 * @property int|null $high_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftCount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftCount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftCount query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftCount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftCount whereHighPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftCount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftCount whereLowPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftCount whereMidPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftCount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftCount whereUserId($value)
 */
	class UserLuckyGiftCount extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property float|null $win
 * @property float|null $lose
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftReport query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftReport whereLose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftReport whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGiftReport whereWin($value)
 */
	class UserLuckyGiftReport extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int $payment_gateway_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $agency_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentGateway newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentGateway newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentGateway query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentGateway whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentGateway whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentGateway whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentGateway wherePaymentGatewayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentGateway whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentGateway whereUserId($value)
 */
	class UserPaymentGateway extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $payment_withdraw_type_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PaymentWithdrawType $payment_withdraw_type
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdraw newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdraw newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdraw query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdraw whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdraw whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdraw wherePaymentWithdrawTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdraw whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdraw whereUserId($value)
 */
	class UserPaymentWithdraw extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $payment_withdraw_field_id
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $payment_withdraw_type_id
 * @property-read \App\Models\PaymentWithdrawField $payment_withdraw_field
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdrawField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdrawField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdrawField query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdrawField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdrawField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdrawField wherePaymentWithdrawFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdrawField wherePaymentWithdrawTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdrawField whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdrawField whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPaymentWithdrawField whereValue($value)
 */
	class UserPaymentWithdrawField extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $hours
 * @property string $days
 * @property float $sallary
 * @property float $agency_sallary
 * @property float $cut_amount
 * @property float $system_earning_usd
 * @property int $month
 * @property int $year
 * @property int $is_paid
 * @property int $user_agency_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $owner_pide
 * @property float $system_salary
 * @property \Modules\FixedTarget\Enums\TargetType $type to show all types go to Modules\FixedTarget\Enums\TargetType
 * @property int $is_saved
 * @property array|null $extras
 * @property string|null $diamond
 * @property float $pending_dollar
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereAgencySallary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereCutAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereDiamond($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereIsSaved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereOwnerPide($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary wherePendingDollar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereSallary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereSystemEarningUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereSystemSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereUserAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereYear($value)
 */
	class UserSallary extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $show_git
 * @property int|null $show_intro
 * @property int|null $show_banner
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $show_invite_code
 * @property int $hide_chat
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting whereHideChat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting whereShowBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting whereShowGit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting whereShowIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting whereShowInviteCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSetting whereUserId($value)
 */
	class UserSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $agency_id
 * @property int|null $union_id
 * @property int|null $family_id
 * @property int|null $target_id
 * @property float|null $target_diamonds
 * @property int $add_month
 * @property int $add_year
 * @property float|null $target_usd
 * @property int|null $target_hours
 * @property int|null $target_days
 * @property float|null $target_agency_share
 * @property float|null $user_diamonds
 * @property int|null $user_hours
 * @property int|null $user_days
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $agency_obtain
 * @property float|null $user_obtain
 * @property int $is_saved
 * @property string $type to show all types go to Modules\FixedTarget\Enums\TargetType
 * @property array|null $extras
 * @property-read \App\Models\Agency|null $agency
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget ofAgency()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereAddMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereAddYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereAgencyObtain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereFamilyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereIsSaved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereTargetAgencyShare($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereTargetDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereTargetDiamonds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereTargetHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereTargetUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereUnionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereUserDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereUserDiamonds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereUserHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereUserObtain($value)
 */
	class UserTarget extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $not_fin_1 Unfinished tasks
 * @property string|null $fin_1 Number of tasks completed
 * @property string|null $receive_1 received
 * @property string|null $fin_2 Number of tasks completed
 * @property string|null $receive_2 received
 * @property int|null $is_open Whether to request this interface today 0 Not requested 1 Requested
 * @property int|null $addtime
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask whereAddtime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask whereFin1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask whereFin2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask whereIsOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask whereNotFin1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask whereReceive1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask whereReceive2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTask whereUserId($value)
 */
	class UserTask extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $hours
 * @property string $days
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserTime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTime query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTime whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTime whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTime whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTime whereUserId($value)
 */
	class UserTime extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $room اجمالي المبلغ الجاري في الغرفة
 * @property int $send اجمالي المبلغ المرسل (الماس)
 * @property int $gain المبلغ الإجمالي المستلم (الماس)
 * @property int $vip_level المستوى vip
 * @property int $cp_level المستوى cp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserTotal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTotal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTotal query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTotal whereCpLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTotal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTotal whereGain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTotal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTotal whereRoom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTotal whereSend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTotal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTotal whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTotal whereVipLevel($value)
 */
	class UserTotal extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $union_id guild id
 * @property int $user_id
 * @property string|null $total_price lump sum
 * @property string|null $settlement_price Settled amount
 * @property string $check_time Review time
 * @property string|null $check_content grounds for refusal
 * @property int|null $check_uid Audit user
 * @property string $check_status 0 not reviewed 1 reviewed 2 rejected
 * @property string|null $di
 * @property string|null $coins
 * @property string|null $room_coins
 * @property string|null $flowers
 * @property string|null $flowers_value
 * @property string|null $gold
 * @property string|null $unsettled_price outstanding amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereCheckContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereCheckStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereCheckTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereCheckUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereDi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereFlowers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereFlowersValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereGold($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereRoomCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereSettlementPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereUnionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereUnsettledPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnion whereUserId($value)
 */
	class UserUnion extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $union_id guild id
 * @property int $users_id user id
 * @property string|null $real_price total amount
 * @property int $add_time time
 * @property int $add_time_month month
 * @property string|null $lw_price total amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnionTj newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnionTj newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnionTj query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnionTj whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnionTj whereAddTimeMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnionTj whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnionTj whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnionTj whereLwPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnionTj whereRealPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnionTj whereUnionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnionTj whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserUnionTj whereUsersId($value)
 */
	class UserUnionTj extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $type 0=buy ,1= send
 * @property int|null $sender_id
 * @property int $user_id
 * @property int $vip_id
 * @property int|null $expire
 * @property int|null $qty
 * @property int|null $price
 * @property int|null $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $level
 * @property string|null $type_send
 * @property int $dash_user_id
 * @property int $is_used
 * @property int $num_used
 * @property-read \App\Models\OVip|null $OVip
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereDashUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereIsUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereNumUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereTypeSend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereVipId($value)
 */
	class UserVip extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $description
 * @property float|null $duration
 * @property int|null $author_id
 * @property string|null $url
 * @property string|null $tages
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $shares_num
 * @property int|null $comments_num
 * @property int|null $likes_num
 * @property int|null $views_num
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|Video newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Video newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Video query()
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereCommentsNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereLikesNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereSharesNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereTages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Video whereViewsNum($value)
 */
	class Video extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $type 1 نجمة شارب 2 ذهبي حاد 3 كبار الشخصيات
 * @property int $level المستوى
 * @property string|null $img
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $exp خبرة
 * @property int|null $di ماسات
 * @property int|null $co عملات
 * @property string|null $name_en
 * @property string|null $name_ar
 * @method static \Illuminate\Database\Eloquent\Builder|Vip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vip query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereCo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereDi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereUpdatedAt($value)
 */
	class Vip extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $type 3vip5 guardian cp
 * @property int|null $level المستوى المطلوب
 * @property int|null $enable 1 enable 2 disable
 * @property string|null $name
 * @property string|null $title
 * @property string|null $img_0
 * @property string|null $img_1
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|VipAuth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VipAuth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VipAuth query()
 * @method static \Illuminate\Database\Eloquent\Builder|VipAuth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipAuth whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipAuth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipAuth whereImg0($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipAuth whereImg1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipAuth whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipAuth whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipAuth whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipAuth whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipAuth whereUpdatedAt($value)
 */
	class VipAuth extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $title
 * @property string|null $img1
 * @property string|null $img2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $type
 * @property string|null $en_name
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege query()
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege whereEnName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege whereImg1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege whereImg2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege whereUpdatedAt($value)
 */
	class VipPrivilege extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $get_type Obtaining method 1 vip level automatic acquisition 2 activity 3 treasure box 4 purchase 5 background modification 6 limited time purchase 7 treasure box point exchange 8 cp level unlock 104 not for sale
 * @property int $type 1 الأحجار الكريمة 3 بطاقة التمرير 4 إطار الصورة الرمزية 5 إطار الفقاعة 6 دخول المؤثرات الخاصة 7 فتحة الميكروفون 8 شارة
 * @property string|null $name
 * @property string|null $title
 * @property int|null $price سعر
 * @property int|null $score النقاط المطلوبة
 * @property int|null $level المستوى المطلوب لكبار الشخصيات
 * @property string|null $show_img
 * @property string|null $img1
 * @property string|null $img2
 * @property string|null $img3
 * @property string|null $color
 * @property int|null $expire الوقت الصالح 0 دائم يتم حساب الآخرين بالأيام
 * @property int|null $enable 1 تمكين 2 تعطيل
 * @property int|null $sort
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $num
 * @property int $is_active_for_vip
 * @property string|null $name_en
 * @property string|null $title_en
 * @property string|null $value
 * @property int|null $exp
 * @property string|null $image_type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $packs
 * @property-read int|null $packs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $ware_users
 * @property-read int|null $ware_users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Ware isNotUsedInPacks()
 * @method static \Illuminate\Database\Eloquent\Builder|Ware newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ware newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ware query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ware showUserCustom(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereGetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereImageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereImg1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereImg2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereImg3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereIsActiveForVip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereShowImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereTitleEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereValue($value)
 */
	class Ware extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $logo
 * @property string|null $footer_description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|WebSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|WebSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebSetting whereFooterDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebSetting whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebSetting whereUpdatedAt($value)
 */
	class WebSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $uuid
 * @property string $verification_code
 * @property string|null $phone_number
 * @property string|null $profile_name
 * @property string $status
 * @property string $app_id
 * @property string $requested_at
 * @property string $expires_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate query()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate validated()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereProfileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereVerificationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate withoutTrashed()
 */
	class WhatsappWebhookValidate extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $value
 * @property string|null $desc
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_hidden
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel query()
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereValue($value)
 */
	class configesModel extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $o_vip_id
 * @property int $o_vip_privilege_id
 * @method static \Illuminate\Database\Eloquent\Builder|vip_prev newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|vip_prev newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|vip_prev query()
 * @method static \Illuminate\Database\Eloquent\Builder|vip_prev whereOVipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|vip_prev whereOVipPrivilegeId($value)
 */
	class vip_prev extends \Eloquent {}
}

namespace Modules\Achievement\Entities{
/**
 * 
 *
 * @property int $id
 * @property \Modules\Achievement\Enums\AchievementType $type
 * @property string|null $name
 * @property string|null $valid_image
 * @property string|null $invalid_image
 * @property int|null $target
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property \Modules\Achievement\Enums\TargetType $target_type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Achievement\Entities\AchievementLevel> $levels
 * @property-read int|null $levels_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $userAchievement
 * @property-read int|null $user_achievement_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Achievement\Entities\UserAchievementLevel> $userAchievementLevel
 * @property-read int|null $user_achievement_level_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Achievement\Entities\UserAchievement> $userAchievments
 * @property-read int|null $user_achievments_count
 * @method static \Illuminate\Database\Eloquent\Builder|Achievement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Achievement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Achievement query()
 * @method static \Illuminate\Database\Eloquent\Builder|Achievement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achievement whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achievement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achievement whereInvalidImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achievement whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achievement whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achievement whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achievement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achievement whereValidImage($value)
 */
	class Achievement extends \Eloquent {}
}

namespace Modules\Achievement\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $achievement_id
 * @property int|null $target
 * @property \Modules\Achievement\Enums\TargetType|null $target_type
 * @property string|null $valid_image
 * @property string|null $invalid_image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $ar_description
 * @property string|null $en_description
 * @property-read \Modules\Achievement\Entities\Achievement|null $achievement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Achievement\Entities\UserAchievementLevel> $achievementUsers
 * @property-read int|null $achievement_users_count
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel query()
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel whereAchievementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel whereArDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel whereEnDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel whereInvalidImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AchievementLevel whereValidImage($value)
 */
	class AchievementLevel extends \Eloquent {}
}

namespace Modules\Achievement\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $achievement_id
 * @property int $gift_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Achievement\Entities\Achievement $Achievement
 * @property-read \App\Models\Gift $gift
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|GiftAchievement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GiftAchievement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GiftAchievement query()
 * @method static \Illuminate\Database\Eloquent\Builder|GiftAchievement whereAchievementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftAchievement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftAchievement whereGiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftAchievement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftAchievement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftAchievement whereUserId($value)
 */
	class GiftAchievement extends \Eloquent {}
}

namespace Modules\Achievement\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $achievement_id
 * @property int $user_id
 * @property int|null $gift_achievement_id
 * @property float $target
 * @property float $total_target
 * @property string|null $unique_value
 * @property int|null $day
 * @property int $month
 * @property int $year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Achievement\Entities\Achievement $achievement
 * @property-read \Modules\Achievement\Entities\GiftAchievement|null $giftAchievement
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement whereAchievementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement whereGiftAchievementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement whereTotalTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement whereUniqueValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievement whereYear($value)
 */
	class UserAchievement extends \Eloquent {}
}

namespace Modules\Achievement\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $achievement_level_id
 * @property int $user_id
 * @property int|null $gift_achievement_id
 * @property string|null $unique_value
 * @property string|null $end_at
 * @property int $is_enable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $achievement_id
 * @property string|null $custom_image
 * @property int|null $picked
 * @property-read \Modules\Achievement\Entities\Achievement|null $achievement
 * @property-read \Modules\Achievement\Entities\AchievementLevel|null $achievementLevel
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel userPickProfile()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel whereAchievementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel whereAchievementLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel whereCustomImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel whereEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel whereGiftAchievementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel whereIsEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel wherePicked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel whereUniqueValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAchievementLevel withAllData()
 */
	class UserAchievementLevel extends \Eloquent {}
}

namespace Modules\AgencyApp\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $agency_id
 * @property string|null $gmail
 * @property string|null $face_image_nationalId
 * @property string|null $back_image_nationalId
 * @property \App\Models\Country|null $country
 * @property string|null $history_app_info
 * @property int|null $salary
 * @property int|null $host
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $status
 * @property int|null $user_id
 * @property string|null $video
 * @property int|null $owner_id
 * @property-read \App\Models\Agency|null $agency
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereBackImageNationalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereFaceImageNationalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereGmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereHistoryAppInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalInfo whereVideo($value)
 */
	class AdditionalInfo extends \Eloquent {}
}

namespace Modules\AgencyApp\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $agency_id
 * @property int|null $user_invite_id
 * @property int|null $user_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $agency
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\User|null $userInvite
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyHostInvite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyHostInvite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyHostInvite query()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyHostInvite whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyHostInvite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyHostInvite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyHostInvite whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyHostInvite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyHostInvite whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyHostInvite whereUserInviteId($value)
 */
	class AgencyHostInvite extends \Eloquent {}
}

namespace Modules\AgencyApp\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $agency_id
 * @property int $user_id
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Agency|null $agency
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob query()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyUserJob whereUserId($value)
 */
	class AgencyUserJob extends \Eloquent {}
}

namespace Modules\AgencyApp\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $agency_id
 * @property int $user_id
 * @property int $admin_id
 * @property int|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Agent|null $admin
 * @property-read \App\Models\Agency|null $agency
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveAgencyRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveAgencyRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveAgencyRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveAgencyRequest whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveAgencyRequest whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveAgencyRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveAgencyRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveAgencyRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveAgencyRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveAgencyRequest whereUserId($value)
 */
	class LeaveAgencyRequest extends \Eloquent {}
}

namespace Modules\CP\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $cp_relation_id
 * @property int $user_one_id
 * @property int $user_two_id
 * @property int $status pending=>0,accepted=>1,refused=>2,stop=>3,restore=>4
 * @property int $di
 * @property int $level_id
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\CP\Entities\CpRelation|null $cpRelation
 * @method static \Illuminate\Database\Eloquent\Builder|Cp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cp query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereCpRelationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereDi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereUserOneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cp whereUserTwoId($value)
 */
	class Cp extends \Eloquent {}
}

namespace Modules\CP\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $cp_relation_id
 * @property int $level
 * @property int $exp
 * @property string|null $name_en
 * @property string|null $name_ar
 * @property string|null $img
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevel query()
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevel whereCpRelationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevel whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevel whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevel whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevel whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevel whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevel whereUpdatedAt($value)
 */
	class CpLevel extends \Eloquent {}
}

namespace Modules\CP\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $vip_id
 * @property int $item_id
 * @property string $type
 * @property string $sub_type
 * @property string $gender
 * @property string $expire
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OVip|null $vip
 * @property-read \App\Models\Ware|null $ware
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelGift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelGift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelGift query()
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelGift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelGift whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelGift whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelGift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelGift whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelGift whereSubType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelGift whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelGift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelGift whereVipId($value)
 */
	class CpLevelGift extends \Eloquent {}
}

namespace Modules\CP\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $cp_id
 * @property int $level
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelTakeGift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelTakeGift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelTakeGift query()
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelTakeGift whereCpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelTakeGift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelTakeGift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelTakeGift whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpLevelTakeGift whereUpdatedAt($value)
 */
	class CpLevelTakeGift extends \Eloquent {}
}

namespace Modules\CP\Entities{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string|null $image
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $cp_one
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereCpOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereUpdatedAt($value)
 */
	class CpRelation extends \Eloquent {}
}

namespace Modules\CP\Entities{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CpWinnerReward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpWinnerReward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpWinnerReward query()
 */
	class CpWinnerReward extends \Eloquent {}
}

namespace Modules\CP\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $cp_relation_id
 * @property int $count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserRelationAvilable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRelationAvilable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRelationAvilable query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRelationAvilable whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRelationAvilable whereCpRelationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRelationAvilable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRelationAvilable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRelationAvilable whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRelationAvilable whereUserId($value)
 */
	class UserRelationAvilable extends \Eloquent {}
}

namespace Modules\CP\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $weekly_cp_id
 * @property int $level
 * @property string $target
 * @property string $type
 * @property string|null $sub_type
 * @property string $gender
 * @property string $expire
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $target1
 * @property-read mixed $target2
 * @property-read mixed $target3
 * @property-read mixed $target4
 * @property-read \App\Models\OVip|null $vip
 * @property-read \App\Models\Ware|null $ware
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpGift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpGift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpGift query()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpGift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpGift whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpGift whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpGift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpGift whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpGift whereSubType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpGift whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpGift whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpGift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpGift whereWeeklyCpId($value)
 */
	class WeeklyCpGift extends \Eloquent {}
}

namespace Modules\CP\Entities{
/**
 * 
 *
 * @property-read \App\Models\User|null $userOne
 * @property-read \App\Models\User|null $userTwo
 * @property-read \Modules\Events\Entities\WeeklyStar|null $weeklyCp
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner query()
 */
	class WeeklyCpWinner extends \Eloquent {}
}

namespace Modules\Charizma\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $total
 * @property int $room_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExtraDataInRoom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExtraDataInRoom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExtraDataInRoom query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExtraDataInRoom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExtraDataInRoom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExtraDataInRoom whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExtraDataInRoom whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExtraDataInRoom whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExtraDataInRoom whereUserId($value)
 */
	class ExtraDataInRoom extends \Eloquent {}
}

namespace Modules\Chat\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $blocker_id
 * @property int|null $blocked_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BlockUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlockUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlockUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|BlockUser whereBlockedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockUser whereBlockerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BlockUser whereUpdatedAt($value)
 */
	class BlockUser extends \Eloquent {}
}

namespace Modules\Chat\Entities{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ChatLetter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatLetter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatLetter query()
 */
	class ChatLetter extends \Eloquent {}
}

namespace Modules\Chat\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $chat_room_id
 * @property int|null $user_id
 * @property string|null $message
 * @property string $type
 * @property string|null $file
 * @property string $status
 * @property string|null $user_1_deleted
 * @property string|null $user_2_deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Chat\Entities\MessageAlbum> $albums
 * @property-read int|null $albums_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Chat\Entities\React> $reacts
 * @property-read int|null $reacts_count
 * @property-read \Modules\Chat\Entities\ChatRoom|null $room
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereChatRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereUser1Deleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereUser2Deleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereUserId($value)
 */
	class ChatMessage extends \Eloquent {}
}

namespace Modules\Chat\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $user_id2
 * @property string $type
 * @property string|null $user_1_deleted
 * @property string|null $user_2_deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $last_message_created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Chat\Entities\ChatMessage> $messages
 * @property-read int|null $messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Chat\Entities\ChatMessage> $unReadMessages
 * @property-read int|null $un_read_messages_count
 * @property-read \App\Models\User|null $userOne
 * @property-read \App\Models\User|null $userTwo
 * @method static \Illuminate\Database\Eloquent\Builder|ChatRoom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatRoom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatRoom query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatRoom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatRoom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatRoom whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatRoom whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatRoom whereUser1Deleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatRoom whereUser2Deleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatRoom whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatRoom whereUserId2($value)
 */
	class ChatRoom extends \Eloquent {}
}

namespace Modules\Chat\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $chat_room_id
 * @property int|null $chat_message_id
 * @property int|null $user_id
 * @property string|null $file
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $type
 * @property string|null $frame
 * @property-read \Modules\Chat\Entities\ChatMessage|null $message
 * @property-read \Modules\Chat\Entities\ChatRoom|null $room
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|MessageAlbum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageAlbum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageAlbum query()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageAlbum whereChatMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageAlbum whereChatRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageAlbum whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageAlbum whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageAlbum whereFrame($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageAlbum whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageAlbum whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageAlbum whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageAlbum whereUserId($value)
 */
	class MessageAlbum extends \Eloquent {}
}

namespace Modules\Chat\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $message_id
 * @property int|null $from_message_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Chat\Entities\ChatMessage|null $from_message
 * @property-read \Modules\Chat\Entities\ChatMessage|null $message
 * @method static \Illuminate\Database\Eloquent\Builder|MessageReplay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageReplay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageReplay query()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageReplay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageReplay whereFromMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageReplay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageReplay whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageReplay whereUpdatedAt($value)
 */
	class MessageReplay extends \Eloquent {}
}

namespace Modules\Chat\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $chat_room_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PinToTop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PinToTop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PinToTop query()
 * @method static \Illuminate\Database\Eloquent\Builder|PinToTop whereChatRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PinToTop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PinToTop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PinToTop whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PinToTop whereUserId($value)
 */
	class PinToTop extends \Eloquent {}
}

namespace Modules\Chat\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $chat_room_id
 * @property int|null $chat_message_id
 * @property int|null $user_id
 * @property string|null $react
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Chat\Entities\ChatMessage|null $message
 * @property-read \Modules\Chat\Entities\ChatRoom|null $room
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|React newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|React newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|React query()
 * @method static \Illuminate\Database\Eloquent\Builder|React whereChatMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|React whereChatRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|React whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|React whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|React whereReact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|React whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|React whereUserId($value)
 */
	class React extends \Eloquent {}
}

namespace Modules\DailyPrize\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $type
 * @property int|null $order
 * @property string|null $gift_type
 * @property string|null $target
 * @property int|null $expir
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $target1
 * @property-read mixed $target2
 * @property-read mixed $target3
 * @property-read mixed $target4
 * @property-read \App\Models\OVip|null $vip
 * @property-read \App\Models\Ware|null $ware
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift query()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift whereExpir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift whereGiftType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift whereUpdatedAt($value)
 */
	class DailyGift extends \Eloquent {}
}

namespace Modules\DailyPrize\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $day_count
 * @property string|null $last_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftCount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftCount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftCount query()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftCount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftCount whereDayCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftCount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftCount whereLastActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftCount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftCount whereUserId($value)
 */
	class DailyGiftCount extends \Eloquent {}
}

namespace Modules\DailyPrize\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftType query()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftType whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGiftType whereUpdatedAt($value)
 */
	class DailyGiftType extends \Eloquent {}
}

namespace Modules\DailyPrize\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $gift_type
 * @property int|null $target
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DailyUserGift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyUserGift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyUserGift query()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyUserGift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyUserGift whereGiftType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyUserGift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyUserGift whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyUserGift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyUserGift whereUserId($value)
 */
	class DailyUserGift extends \Eloquent {}
}

namespace Modules\Events\Entities{
/**
 * 
 *
 * @property int $id
 * @property string|null $tile
 * @property int|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $ware
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Events\Entities\RewardTarget> $rewards
 * @property-read int|null $rewards_count
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeTargetEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeTargetEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeTargetEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeTargetEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeTargetEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeTargetEvent whereTile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeTargetEvent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeTargetEvent whereValue($value)
 */
	class ChargeTargetEvent extends \Eloquent {}
}

namespace Modules\Events\Entities{
/**
 * 
 *
 * @property int $id
 * @property string|null $type
 * @property string|null $sub_type
 * @property string|null $desc_en
 * @property string|null $desc_ar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $url
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole whereDescAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole whereDescEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole whereSubType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole whereUrl($value)
 */
	class GeneralRole extends \Eloquent {}
}

namespace Modules\Events\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $admin_id
 * @property string $start_date
 * @property string $end_date
 * @property int|null $editor_id
 * @property string|null $description_en
 * @property string|null $description_ar
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Events\Entities\PkWinner> $WinnersPK
 * @property-read int|null $winners_p_k_count
 * @property-read \App\Models\User|null $admin
 * @property-read \App\Models\User|null $editor
 * @property-read mixed $end_date_local
 * @property-read mixed $start_date_local
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Events\Entities\PkReward> $rewards
 * @property-read int|null $rewards_count
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent currentEvent()
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent endToday()
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent previousEvent()
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent whereDescriptionAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent whereDescriptionEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent whereEditorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent whereUpdatedAt($value)
 */
	class PkEvent extends \Eloquent {}
}

namespace Modules\Events\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $pk_event_id
 * @property string $type
 * @property int $level
 * @property string $target
 * @property int $expire
 * @property string $pk_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $target1
 * @property-read mixed $target2
 * @property-read mixed $target3
 * @property-read mixed $target4
 * @property-read \Modules\Events\Entities\PkEvent|null $pkEvent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Events\Entities\PkWinner> $rewardsPk
 * @property-read int|null $rewards_pk_count
 * @property-read \App\Models\OVip|null $vip
 * @property-read \App\Models\Ware|null $ware
 * @method static \Illuminate\Database\Eloquent\Builder|PkReward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PkReward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PkReward query()
 * @method static \Illuminate\Database\Eloquent\Builder|PkReward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkReward whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkReward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkReward whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkReward wherePkEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkReward wherePkType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkReward whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkReward whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkReward whereUpdatedAt($value)
 */
	class PkReward extends \Eloquent {}
}

namespace Modules\Events\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $pk_event_id
 * @property int $user_id
 * @property int $level
 * @property string $pk_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Events\Entities\PkEvent|null $pkEvent
 * @property-read \Modules\Events\Entities\PkReward|null $reward
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Events\Entities\PkReward> $rewardsPk
 * @property-read int|null $rewards_pk_count
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\User|null $winner
 * @method static \Illuminate\Database\Eloquent\Builder|PkWinner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PkWinner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PkWinner query()
 * @method static \Illuminate\Database\Eloquent\Builder|PkWinner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkWinner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkWinner whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkWinner wherePkEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkWinner wherePkType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkWinner whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PkWinner whereUserId($value)
 */
	class PkWinner extends \Eloquent {}
}

namespace Modules\Events\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $weekly_star_id
 * @property string $type
 * @property int $level
 * @property string $target
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $expire
 * @property-read mixed $target1
 * @property-read mixed $target2
 * @property-read mixed $target3
 * @property-read mixed $target4
 * @property-read \App\Models\OVip|null $vip
 * @property-read \App\Models\Ware|null $ware
 * @property-read \Modules\Events\Entities\WeeklyStar|null $weeklyEvent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Events\Entities\Winner> $winners
 * @property-read int|null $winners_count
 * @method static \Illuminate\Database\Eloquent\Builder|Reward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reward query()
 * @method static \Illuminate\Database\Eloquent\Builder|Reward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reward whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reward whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reward whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reward whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reward whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reward whereWeeklyStarId($value)
 */
	class Reward extends \Eloquent {}
}

namespace Modules\Events\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $charge_event_id
 * @property string $type
 * @property \Modules\Events\Entities\ChargeTargetEvent|null $target
 * @property int $expire
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $target1
 * @property-read mixed $target2
 * @property-read mixed $target3
 * @property-read mixed $target4
 * @property-read \App\Models\OVip|null $vip
 * @property-read \App\Models\Ware|null $ware
 * @method static \Illuminate\Database\Eloquent\Builder|RewardTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RewardTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RewardTarget query()
 * @method static \Illuminate\Database\Eloquent\Builder|RewardTarget whereChargeEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardTarget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardTarget whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardTarget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardTarget whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardTarget whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardTarget whereUpdatedAt($value)
 */
	class RewardTarget extends \Eloquent {}
}

namespace Modules\Events\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $pk_winner_id
 * @property int $pk_reward_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Events\Entities\PkReward|null $reward
 * @property-read \App\Models\User|null $winner
 * @method static \Illuminate\Database\Eloquent\Builder|RewardWinnerPk newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RewardWinnerPk newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RewardWinnerPk query()
 * @method static \Illuminate\Database\Eloquent\Builder|RewardWinnerPk whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardWinnerPk whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardWinnerPk wherePkRewardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardWinnerPk wherePkWinnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardWinnerPk whereUpdatedAt($value)
 */
	class RewardWinnerPk extends \Eloquent {}
}

namespace Modules\Events\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $charge_event_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Events\Entities\ChargeTargetEvent|null $event
 * @property-read \App\Models\User|null $winner
 * @method static \Illuminate\Database\Eloquent\Builder|UserChargeEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserChargeEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserChargeEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserChargeEvent whereChargeEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserChargeEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserChargeEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserChargeEvent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserChargeEvent whereUserId($value)
 */
	class UserChargeEvent extends \Eloquent {}
}

namespace Modules\Events\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $admin_id
 * @property string $start_date
 * @property string $end_date
 * @property int|null $editor_id
 * @property string|null $description_en
 * @property string|null $description_ar
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\CP\Entities\WeeklyCpWinner> $WeeklyCpWinners
 * @property-read int|null $weekly_cp_winners_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Events\Entities\WeeklyStarGift> $WeeklyStarGifts
 * @property-read int|null $weekly_star_gifts_count
 * @property-read \App\Models\User|null $admin
 * @property-read \App\Models\User|null $editor
 * @property-read mixed $end_date_local
 * @property-read mixed $start_date_local
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gift> $gifts
 * @property-read int|null $gifts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Events\Entities\Reward> $rewards
 * @property-read int|null $rewards_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\CP\Entities\WeeklyCpGift> $weeklyCpGifts
 * @property-read int|null $weekly_cp_gifts_count
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar currentEvent()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar endToday()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar period()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar previousEvent()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar query()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar weeklyCP()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar weeklyStar()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar whereDescriptionAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar whereDescriptionEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar whereEditorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar withoutTrashed()
 */
	class WeeklyStar extends \Eloquent {}
}

namespace Modules\Events\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $weekly_star_id
 * @property int $gift_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gift> $gifts
 * @property-read int|null $gifts_count
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStarGift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStarGift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStarGift query()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStarGift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStarGift whereGiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStarGift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStarGift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStarGift whereWeeklyStarId($value)
 */
	class WeeklyStarGift extends \Eloquent {}
}

namespace Modules\Events\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $weekly_star_id
 * @property int $user_id
 * @property int $level
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Events\Entities\Reward> $rewards
 * @property-read int|null $rewards_count
 * @property-read \App\Models\User|null $user
 * @property-read \Modules\Events\Entities\WeeklyStar|null $weeklyEvent
 * @method static \Illuminate\Database\Eloquent\Builder|Winner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Winner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Winner query()
 * @method static \Illuminate\Database\Eloquent\Builder|Winner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Winner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Winner whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Winner whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Winner whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Winner whereWeeklyStarId($value)
 */
	class Winner extends \Eloquent {}
}

namespace Modules\Events\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $winner_id
 * @property int $reward_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $expaired_at
 * @property string|null $type
 * @property-read \Modules\Events\Entities\Reward|null $reward
 * @property-read \App\Models\User|null $winner
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerReward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerReward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerReward query()
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerReward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerReward whereExpairedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerReward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerReward whereRewardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerReward whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerReward whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerReward whereWinnerId($value)
 */
	class WinnerReward extends \Eloquent {}
}

namespace Modules\FixedTarget\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $diamonds
 * @property int|null $hours
 * @property int|null $days
 * @property int $count_moment
 * @property int $count_real
 * @property string|null $usd
 * @property float|null $agency_share
 * @property string|null $img
 * @property string|null $coin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget query()
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget whereAgencyShare($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget whereCountMoment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget whereCountReal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget whereDiamonds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedTarget whereUsd($value)
 */
	class FixedTarget extends \Eloquent {}
}

namespace Modules\FixedTarget\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialUser whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialUser whereUserId($value)
 */
	class SpecialUser extends \Eloquent {}
}

namespace Modules\Moment\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $description
 * @property int $comment_num
 * @property int $like_num
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $img
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Moment\Entities\MomentCommint> $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gift> $gifts
 * @property-read int|null $gifts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Moment\Entities\MomentLikes> $likes
 * @property-read int|null $likes_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Moment likeExists($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|Moment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Moment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Moment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Moment whereCommentNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Moment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Moment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Moment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Moment whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Moment whereLikeNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Moment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Moment whereUserId($value)
 */
	class Moment extends \Eloquent {}
}

namespace Modules\Moment\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $moment_id
 * @property int $user_id
 * @property string $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|MomentCommint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MomentCommint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MomentCommint query()
 * @method static \Illuminate\Database\Eloquent\Builder|MomentCommint whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MomentCommint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MomentCommint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MomentCommint whereMomentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MomentCommint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MomentCommint whereUserId($value)
 */
	class MomentCommint extends \Eloquent {}
}

namespace Modules\Moment\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $moment_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Moment\Entities\MomentCommint> $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MomentLikes> $gifts
 * @property-read int|null $gifts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MomentLikes> $likes
 * @property-read int|null $likes_count
 * @property-read \Modules\Moment\Entities\Moment|null $moment
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|MomentLikes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MomentLikes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MomentLikes query()
 * @method static \Illuminate\Database\Eloquent\Builder|MomentLikes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MomentLikes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MomentLikes whereMomentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MomentLikes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MomentLikes whereUserId($value)
 */
	class MomentLikes extends \Eloquent {}
}

namespace Modules\Moment\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $moment_id
 * @property int $Reporter_id
 * @property int $Reported_id
 * @property string $description
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Moment\Entities\Moment|null $moment
 * @method static \Illuminate\Database\Eloquent\Builder|ReportMoment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportMoment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportMoment query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportMoment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportMoment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportMoment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportMoment whereMomentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportMoment whereReportedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportMoment whereReporterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportMoment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportMoment whereUpdatedAt($value)
 */
	class ReportMoment extends \Eloquent {}
}

namespace Modules\Payment\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $coin_id
 * @property string|null $reference_id
 * @property string|null $order_no
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Coin|null $coin
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinPayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinPayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinPayment query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinPayment whereCoinId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinPayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinPayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinPayment whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinPayment whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinPayment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinPayment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinPayment whereUserId($value)
 */
	class UserCoinPayment extends \Eloquent {}
}

namespace Modules\Public\Entities{
/**
 * 
 *
 * @property int $id
 * @property string|null $name
 * @property int $min
 * @property int $max
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Public\Entities\RewardLevelInterval> $rewards
 * @property-read int|null $rewards_count
 * @method static \Illuminate\Database\Eloquent\Builder|LevelInterval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LevelInterval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LevelInterval query()
 * @method static \Illuminate\Database\Eloquent\Builder|LevelInterval whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LevelInterval whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LevelInterval whereMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LevelInterval whereMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LevelInterval whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LevelInterval whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LevelInterval whereUpdatedAt($value)
 */
	class LevelInterval extends \Eloquent {}
}

namespace Modules\Public\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $level_interval_id
 * @property string $type
 * @property string $target
 * @property int $expire
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $target1
 * @property-read mixed $target2
 * @property-read mixed $target3
 * @property-read mixed $target4
 * @property-read \Modules\Public\Entities\LevelInterval|null $levelInterval
 * @property-read \App\Models\OVip|null $vip
 * @property-read \App\Models\Ware|null $ware
 * @method static \Illuminate\Database\Eloquent\Builder|RewardLevelInterval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RewardLevelInterval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RewardLevelInterval query()
 * @method static \Illuminate\Database\Eloquent\Builder|RewardLevelInterval whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardLevelInterval whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardLevelInterval whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardLevelInterval whereLevelIntervalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardLevelInterval whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardLevelInterval whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RewardLevelInterval whereUpdatedAt($value)
 */
	class RewardLevelInterval extends \Eloquent {}
}

namespace Modules\Public\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserCounter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCounter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCounter query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCounter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCounter whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCounter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCounter whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCounter whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCounter whereUserId($value)
 */
	class UserCounter extends \Eloquent {}
}

namespace Modules\Public\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $reward_level_interval_id
 * @property int $user_level
 * @property int $min
 * @property int $max
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $level_interval_id
 * @property-read \Modules\Public\Entities\LevelInterval|null $levelInterval
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerLevelInterval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerLevelInterval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerLevelInterval query()
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerLevelInterval whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerLevelInterval whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerLevelInterval whereLevelIntervalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerLevelInterval whereMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerLevelInterval whereMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerLevelInterval whereRewardLevelIntervalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerLevelInterval whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerLevelInterval whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerLevelInterval whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WinnerLevelInterval whereUserLevel($value)
 */
	class WinnerLevelInterval extends \Eloquent {}
}

namespace Modules\Reals\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $description
 * @property string $url
 * @property int $share_num
 * @property int $comment_num
 * @property int $like_num
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $sub_video
 * @property string|null $intro_image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Reals\Entities\RealUserView> $Views
 * @property-read int|null $views_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Interest> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Reals\Entities\RealUserComment> $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Reals\Entities\RealUserLike> $likes
 * @property-read int|null $likes_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Real newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Real newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Real query()
 * @method static \Illuminate\Database\Eloquent\Builder|Real whereCommentNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Real whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Real whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Real whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Real whereIntroImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Real whereLikeNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Real whereShareNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Real whereSubVideo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Real whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Real whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Real whereUserId($value)
 */
	class Real extends \Eloquent {}
}

namespace Modules\Reals\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $real_id
 * @property int $category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RealCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|RealCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealCategory whereRealId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealCategory whereUpdatedAt($value)
 */
	class RealCategory extends \Eloquent {}
}

namespace Modules\Reals\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $real_id
 * @property int $user_id
 * @property string $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserComment query()
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserComment whereRealId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserComment whereUserId($value)
 */
	class RealUserComment extends \Eloquent {}
}

namespace Modules\Reals\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $real_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserLike query()
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserLike whereRealId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserLike whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserLike whereUserId($value)
 */
	class RealUserLike extends \Eloquent {}
}

namespace Modules\Reals\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $real_id
 * @property int $user_id
 * @property int $duration_in_minute
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserView query()
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserView whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserView whereDurationInMinute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserView whereRealId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserView whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealUserView whereUserId($value)
 */
	class RealUserView extends \Eloquent {}
}

namespace Modules\Reals\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $all_unique_value
 * @property string|null $following_unique_value
 * @property int|null $last_all_reel_id
 * @property int|null $last_following_reel_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ReelsUserSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReelsUserSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReelsUserSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReelsUserSetting whereAllUniqueValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReelsUserSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReelsUserSetting whereFollowingUniqueValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReelsUserSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReelsUserSetting whereLastAllReelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReelsUserSetting whereLastFollowingReelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReelsUserSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReelsUserSetting whereUserId($value)
 */
	class ReelsUserSetting extends \Eloquent {}
}

namespace Modules\Reals\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $real_id
 * @property int $Reporter_id
 * @property int $Reported_id
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Reals\Entities\Real|null $reel
 * @method static \Illuminate\Database\Eloquent\Builder|ReportReals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportReals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportReals query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportReals whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportReals whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportReals whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportReals whereRealId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportReals whereReportedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportReals whereReporterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportReals whereUpdatedAt($value)
 */
	class ReportReals extends \Eloquent {}
}

namespace Modules\Reals\Entities{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory whereName($value)
 */
	class VideoCategory extends \Eloquent {}
}

namespace Modules\SalaryTransaction\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $request_id
 * @property int $admin_check 1=>checked
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\SalaryTransaction\Entities\SalaryRequest $request
 * @method static \Illuminate\Database\Eloquent\Builder|AdminCheck newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminCheck newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminCheck query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminCheck whereAdminCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminCheck whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminCheck whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminCheck whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminCheck whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminCheck whereUpdatedAt($value)
 */
	class AdminCheck extends \Eloquent {}
}

namespace Modules\SalaryTransaction\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $agency_id
 * @property float $salary
 * @property float $cut_amount
 * @property int $month
 * @property int $year
 * @property int $pending_usd
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyTransferSalary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyTransferSalary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyTransferSalary query()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyTransferSalary whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyTransferSalary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyTransferSalary whereCutAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyTransferSalary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyTransferSalary whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyTransferSalary wherePendingUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyTransferSalary whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyTransferSalary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyTransferSalary whereYear($value)
 */
	class AgencyTransferSalary extends \Eloquent {}
}

namespace Modules\SalaryTransaction\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $agency_id
 * @property int|null $agency_owner_id
 * @property int $status 0=>waiting,1=>accepting,3=>rejected
 * @property int $type 1=>coins,2=>reel mony
 * @property int $payment_gateway_id
 * @property int|null $country_id
 * @property int $usd
 * @property int $coins
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $salary_request_id
 * @property-read \App\Models\Agency|null $agency
 * @property-read \App\Models\User|null $agent
 * @property-read \App\Models\Country|null $country
 * @property-read \App\Models\PaymentGateway|null $payment_gateway
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest whereAgencyOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest wherePaymentGatewayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest whereSalaryRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgentSalaryRequest whereUsd($value)
 */
	class AgentSalaryRequest extends \Eloquent {}
}

namespace Modules\SalaryTransaction\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $agency_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Agency|null $agency
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeAgency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeAgency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeAgency query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeAgency whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeAgency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeAgency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeAgency whereUpdatedAt($value)
 */
	class ChargeAgency extends \Eloquent {}
}

namespace Modules\SalaryTransaction\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $country_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Country|null $country
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeCountry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeCountry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeCountry query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeCountry whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeCountry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeCountry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeCountry whereUpdatedAt($value)
 */
	class ChargeCountry extends \Eloquent {}
}

namespace Modules\SalaryTransaction\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $salary
 * @property string|null $type
 * @property int|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PendingSalaryRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PendingSalaryRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PendingSalaryRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|PendingSalaryRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingSalaryRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingSalaryRequest whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingSalaryRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingSalaryRequest whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingSalaryRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PendingSalaryRequest whereUserId($value)
 */
	class PendingSalaryRequest extends \Eloquent {}
}

namespace Modules\SalaryTransaction\Entities{
/**
 * 
 *
 * @property int $id
 * @property int|null $agency_id
 * @property int $host_id
 * @property int $status 0=>waiting,1=>accepting,2=>transferred,3=>completed,4=>rejected
 * @property int $payment_gateway_id
 * @property int|null $country_id
 * @property int|null $agency_owner_id
 * @property int $usd
 * @property int $coins
 * @property string|null $bill_image
 * @property int $host_check 0=>لم يتم اكشن من قبل الhost ,1=>confirm,2=>rejected
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $note
 * @property int $request_admin_status
 * @property-read \App\Models\Agency|null $agency
 * @property-read \App\Models\User|null $agencyOwner
 * @property-read \App\Models\Country|null $country
 * @property-read \App\Models\User $host
 * @property-read \App\Models\PaymentGateway $payment_gateway
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereAgencyOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereBillImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereHostCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereHostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest wherePaymentGatewayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereRequestAdminStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryRequest whereUsd($value)
 */
	class SalaryRequest extends \Eloquent {}
}

namespace Modules\SpecialId\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $status 1=>used 0=>unused
 * @property int $user_id
 * @property int $ware_id معرف السلعة
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Ware|null $ware
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialHistory whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialHistory whereWareId($value)
 */
	class SpecialHistory extends \Eloquent {}
}

namespace Modules\SpecialId\Entities{
/**
 * 
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $image
 * @property string|null $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialIdFram newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialIdFram newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialIdFram query()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialIdFram whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialIdFram whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialIdFram whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialIdFram whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialIdFram whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialIdFram whereUpdatedAt($value)
 */
	class SpecialIdFram extends \Eloquent {}
}

namespace Modules\SpecialId\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $ware_id
 * @property int $disable
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Ware|null $ware
 * @method static \Illuminate\Database\Eloquent\Builder|UserWare newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserWare newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserWare query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserWare whereDisable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWare whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWare whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWare whereWareId($value)
 */
	class UserWare extends \Eloquent {}
}

namespace Modules\SwitchAccount\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $parent_user_id
 * @property int $child_user_id
 * @property string|null $device_token
 * @property string|null $key
 * @property string|null $expire
 * @property int $is_change
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $child_user
 * @property-read \App\Models\User|null $parent_user
 * @method static \Illuminate\Database\Eloquent\Builder|UserAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAccount whereChildUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAccount whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAccount whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAccount whereIsChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAccount whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAccount whereParentUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAccount whereUpdatedAt($value)
 */
	class UserAccount extends \Eloquent {}
}

namespace Modules\SwitchAccount\Entities{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $device_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $device_name
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevicesHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevicesHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevicesHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevicesHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevicesHistory whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevicesHistory whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevicesHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevicesHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevicesHistory whereUserId($value)
 */
	class UserDevicesHistory extends \Eloquent {}
}

namespace Modules\WhatsappAuth\Entities{
/**
 * 
 *
 * @property int $id
 * @property string $text_ar
 * @property string $text_en
 * @property int $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappMessage whereTextAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappMessage whereTextEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappMessage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappMessage whereUpdatedAt($value)
 */
	class WhatsappMessage extends \Eloquent {}
}

namespace Modules\WhatsappAuth\Entities{
/**
 * 
 *
 * @property int $id
 * @property string $uuid
 * @property string $verification_code
 * @property string|null $phone_number
 * @property string|null $profile_name
 * @property string $status
 * @property string $app_id
 * @property string $requested_at
 * @property string $expires_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate query()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate validated()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereProfileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate whereVerificationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WhatsappWebhookValidate withoutTrashed()
 */
	class WhatsappWebhookValidate extends \Eloquent {}
}

