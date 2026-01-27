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
 * @property string|null $transaction_type
 * @property-read \App\Models\Agency|null $agency
 * @property-read \App\Models\User|null $user
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
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryTrx whereTransactionType($value)
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
 * @property-read mixed $created_at
 * @property-read mixed $updated_at
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
 * @property string $key
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $type
 * @property string|null $input_type
 * @property int|null $item_id
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereInputType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereValue($value)
 */
	class Setting extends \Eloquent {}
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
 * @property int $type 1 = Host Agency, 2 = Shipping
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
 * @property int|null $agency_manger_id
 * @property int|null $agency_dash_manger_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property float $monthly_target
 * @property string|null $password
 * @property int $coins
 * @property int $is_frozen
 * @property string|null $phone_code
 * @property int|null $bd_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserTarget> $AgencyUsersTargets
 * @property-read int|null $agency_users_targets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentGateway> $AgencypaymentGateways
 * @property-read int|null $agencypayment_gateways_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Country> $Countries
 * @property-read int|null $countries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserTarget> $UserTarget
 * @property-read int|null $user_target_count
 * @property-read \Modules\AgencyApp\Entities\AdditionalInfo|null $additionalInfo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AgencyUserJob> $admins
 * @property-read int|null $admins_count
 * @property-read \App\Models\User|null $agencyManger
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AgencySallary> $agencySalaries
 * @property-read int|null $agency_salaries_count
 * @property-read \App\Models\AgencySallary|null $agencySalary
 * @property-read \Modules\SalaryTransaction\Entities\ChargeAgency|null $chargeAgency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Charge> $charges
 * @property-read int|null $charges_count
 * @property-read \App\Models\Admin|null $dashOwner
 * @property-read mixed $last_month_salary
 * @property-read mixed $pending_salary
 * @property-read mixed $target
 * @property-read mixed $targets
 * @property-read mixed $transfer_salary
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AgencyJoinRequest> $joinRequests
 * @property-read int|null $join_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $mempers
 * @property-read int|null $mempers_count
 * @property-read \App\Models\User|null $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentGateway> $paymentGateways
 * @property-read int|null $payment_gateways_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Charge> $receiveShippingAgencyCharges
 * @property-read int|null $receive_shipping_agency_charges_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserSallary> $salaries
 * @property-read int|null $salaries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\SalaryTransaction\Entities\SalaryRequest> $salaryRequests
 * @property-read int|null $salary_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Charge> $senderCharges
 * @property-read int|null $sender_charges_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency ofOwner($owner_id)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereAgencyDashMangerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereAgencyMangerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereAppOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereBdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereContents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereIsFrozen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereMonthlyTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereNotice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereOldUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency wherePhoneCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereTargetTokenUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereTargetUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingAgency withoutTrashed()
 */
	class ShippingAgency extends \Eloquent {}
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
 * @method static \Illuminate\Database\Eloquent\Builder|SuperBoomRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuperBoomRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuperBoomRule query()
 */
	class SuperBoomRule extends \Eloquent {}
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
 * @property string $app_profit_percentage
 * @property string $db_percentage
 * @property int|null $created_by
 * @property int|null $updated_by
 * @method static \Illuminate\Database\Eloquent\Builder|Target newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Target newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Target query()
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereAgencyShare($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereAppProfitPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereDbPercentage($value)
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
 * @method static \Illuminate\Database\Eloquent\Builder|Target whereUpdatedBy($value)
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
 * @property-read \App\Models\User|null $user
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
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $offset
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone query()
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereOffset($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone whereUpdatedAt($value)
 */
	class Timezone extends \Eloquent {}
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
 * @property float $exchange_diamonds
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
 * @property int $total_points
 * @property int|null $total_charge_coins
 * @property int|null $charge_level
 * @property string|null $color_id
 * @property int $exchange_coins
 * @property \Illuminate\Database\Eloquent\Collection<int, User> $following
 * @property int $follower
 * @property int $friend
 * @property int $new_gift
 * @property int|null $sub_charger_level
 * @property int|null $sub_charger_coins
 * @property int $profile_count
 * @property int $is_bd
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentGateway> $AgencypaymentGateways
 * @property-read int|null $agencypayment_gateways_count
 * @property-read \Modules\Vip\Entities\UserVip|null $UserVip
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LiveTime> $UserliveTime
 * @property-read int|null $userlive_time_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\AgencyApp\Entities\AdditionalInfo> $additionalInfo
 * @property-read int|null $additional_info_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Agency> $agencies
 * @property-read int|null $agencies_count
 * @property-read \App\Models\Agency|null $agency
 * @property-read \App\Models\AgencyUserJob|null $agencyAdmins
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AgencyJoinRequest> $agencyJoinRequest
 * @property-read int|null $agency_join_request_count
 * @property-read \App\Models\AgencyUserJob|null $agencyUserJob
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ban> $bans
 * @property-read int|null $bans_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BDSallary> $bdSalaries
 * @property-read int|null $bd_salaries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BlackList> $blockedMe
 * @property-read int|null $blocked_me_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BlackList> $blockedUsers
 * @property-read int|null $blocked_users_count
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $eligiblePacks
 * @property-read int|null $eligible_packs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Achievement\Entities\UserAchievementLevel> $enabledMedals
 * @property-read int|null $enabled_medals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExchangeLog> $exchangeLogs
 * @property-read int|null $exchange_logs_count
 * @property-read \App\Models\Family|null $family
 * @property-read \App\Models\FamilyUser|null $familyType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $followPacks
 * @property-read int|null $follow_packs_count
 * @property-read \App\Models\Follow|null $followedByAuthUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Follow> $followeds
 * @property-read int|null $followeds_count
 * @property-read \App\Models\Follow|null $followerByAuthUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Follow> $followers
 * @property-read int|null $followers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $followersMoment
 * @property-read int|null $followers_moment_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $followerss
 * @property-read int|null $followerss_count
 * @property-read int|null $following_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Follow> $follows
 * @property-read int|null $follows_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $friends
 * @property-read int|null $friends_count
 * @property-read mixed $april_salary
 * @property-read mixed $avatar
 * @property-read mixed $bd_salary
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
 * @property-read mixed $is_followed
 * @property-read mixed $is_frozen
 * @property-read mixed $lang
 * @property mixed $last_all_reel_id
 * @property mixed $last_following_reel_id
 * @property-read int $month_diamond
 * @property-read mixed $my_store
 * @property-read mixed $old
 * @property-read mixed $original_uuid
 * @property-read mixed $photo
 * @property-read mixed $real_online_time
 * @property mixed $real_type
 * @property-read mixed $salary_by_agency
 * @property-read mixed $salary_without_cut_amount
 * @property mixed $total_charge_level
 * @property-read mixed $total_received_diamonds
 * @property mixed $total_received_level
 * @property-read mixed $total_sender_diamonds
 * @property mixed $total_sender_level
 * @property-read mixed $usd
 * @property mixed $user_diamond
 * @property-read mixed $user_type
 * @property-read array $user_types
 * @property-read mixed $uuid_v2
 * @property-read mixed $uuid_v3
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GiftLog> $giftLogsSender
 * @property-read int|null $gift_logs_sender_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Vip\Entities\UserVip> $haveVip
 * @property-read int|null $have_vip_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\History> $history
 * @property-read int|null $history_count
 * @property-read \App\Models\Agency|null $hostAgency
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
 * @property-read \App\Models\UserSallary|null $lastSallary
 * @property-read \App\Models\UsersJoinedAgency|null $latestJoin
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $likedBy
 * @property-read int|null $liked_by_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $likes
 * @property-read int|null $likes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LiveTime> $liveTime
 * @property-read int|null $live_time_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Agency> $managedAgencies
 * @property-read int|null $managed_agencies_count
 * @property-read \App\Models\Admin|null $manager
 * @property-read \App\Models\MangerType|null $mangerType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Achievement\Entities\UserAchievementLevel> $medals
 * @property-read int|null $medals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Moment\Entities\MomentUserGift> $momentUserGift
 * @property-read int|null $moment_user_gift_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Moment\Entities\MomentCommint> $moment_comments
 * @property-read int|null $moment_comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Moment\Entities\MomentLikes> $moment_likes
 * @property-read int|null $moment_likes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Moment\Entities\Moment> $Moments
 * @property-read int|null $moments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $mutualFollows
 * @property-read int|null $mutual_follows_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gift> $myGifts
 * @property-read int|null $my_gifts_count
 * @property-read \App\Models\Room|null $myroom
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\AllGame|null $nowGame
 * @property-read \App\Models\Agency|null $ownAgency
 * @property-read \App\Models\Room|null $ownerRoom
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $packs
 * @property-read int|null $packs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $packsUser
 * @property-read int|null $packs_user_count
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
 * @property-read \Modules\Vip\Entities\Vip|null $receiverLevel
 * @property-read \Modules\Reals\Entities\ReelsUserSetting|null $reelSetting
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RequestBackgroundImage> $requestBackgroundImages
 * @property-read int|null $request_background_images_count
 * @property-read \App\Models\Room|null $room
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Room> $rooms
 * @property-read int|null $rooms_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserSallary> $sallariesByMonth
 * @property-read int|null $sallaries_by_month_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $sameDeviceUsers
 * @property-read int|null $same_device_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $sendPacks
 * @property-read int|null $send_packs_count
 * @property-read \Modules\Vip\Entities\Vip|null $senderLevel
 * @property-read \App\Models\ShippingAgency|null $shippingAgency
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserSallary> $totalUserSalary
 * @property-read int|null $total_user_salary_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $type16Packs
 * @property-read int|null $type16_packs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UsersJoinedAgency> $userAgencyJoined
 * @property-read int|null $user_agency_joined_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Vip\Entities\UserVip> $userHaveVip
 * @property-read int|null $user_have_vip_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $userPacks
 * @property-read int|null $user_packs_count
 * @property-read \App\Models\UserSallary|null $userSalary
 * @property-read \App\Models\UserSallary|null $userSallary
 * @property-read \App\Models\UserSetting|null $userSetting
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Vip\Entities\UserVip> $userVips
 * @property-read int|null $user_vips_count
 * @property-read \App\Models\Family|null $user_family
 * @property-read \Modules\Vip\Entities\Vip|null $vipImage
 * @property-read \App\Models\UserWallet|null $wallet
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WalletTransactionBackup> $walletTransactionBackups
 * @property-read int|null $wallet_transaction_backups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WalletTransaction> $walletTransactions
 * @property-read int|null $wallet_transactions_count
 * @property-read \App\Models\Ware|null $ware
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User fitterByUuid($toId)
 * @method static \Illuminate\Database\Eloquent\Builder|User fitterByUuidUser($toId)
 * @method static \Illuminate\Database\Eloquent\Builder|User getFollowers($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|User isFollow($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|User likeSearchByUuid($toId)
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
 * @method static \Illuminate\Database\Eloquent\Builder|User whereChargeLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereChargeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereColorId($value)
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
 * @method static \Illuminate\Database\Eloquent\Builder|User whereExchangeCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereExchangeDiamonds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFacebookId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFamilyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFlowers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFlowersValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFollower($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFollowing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFriend($value)
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
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsBd($value)
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
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNewGift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNowRoomUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOldUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOnlineTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProfileCount($value)
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
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSubChargerCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSubChargerLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSubReceiverLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSubReceiverNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSubSenderLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSubSenderNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTargetTokenUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTargetUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTodayDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTotalChargeCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTotalDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTotalDiamondReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTotalDiamondSend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTotalPoints($value)
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
 * @property int $user_id
 * @property int|null $invited_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float $invited_charge
 * @property float $user_percentage
 * @property string|null $code
 * @property-read \App\Models\User|null $invited
 * @property-read \App\Models\User $parent
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCodeInvitation whereCode($value)
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
 * @property int $user_id
 * @property string|null $type
 * @property string $sub_type
 * @property int $amount
 * @property string|null $from_date
 * @property string|null $to_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $amount_before
 * @property string|null $item_name
 * @property string $helper_amount
 * @property string|null $user_type
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog whereAmountBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog whereFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog whereHelperAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog whereSubType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog whereToDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinLog whereUserType($value)
 */
	class UserCoinLog extends \Eloquent {}
}

namespace App\Models{
/**
 *
 *
 * @property-read mixed $created_at
 * @property-read mixed $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoinTarget query()
 */
	class UserCoinTarget extends \Eloquent {}
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
 * @property int $gift_id
 * @property int $user_id
 * @property int $quantity
 * @property int $expire
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserGift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserGift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserGift query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserGift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGift whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGift whereGiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGift whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGift whereUserId($value)
 */
	class UserGift extends \Eloquent {}
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
 * @property string $cut_amount
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
 * @property int $achieved_diamond
 * @property int $achieved_days
 * @property int $achieved_hours
 * @property float $pending_dollar
 * @property int|null $period_id
 * @property float $remaining_diamond
 * @property int|null $target_id
 * @property string|null $dB
 * @property string|null $app_profit
 * @property float $target_diamonds
 * @property int $is_finished
 * @property-read \App\Models\Agency|null $agency
 * @property-read \App\Models\Target|null $target
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereAchievedDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereAchievedDiamond($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereAchievedHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereAgencySallary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereAppProfit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereCutAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereDB($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereDiamond($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereIsFinished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereIsSaved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereOwnerPide($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary wherePendingDollar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary wherePeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereRemainingDiamond($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereSallary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereSystemEarningUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereSystemSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereTargetDiamonds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSallary whereTargetId($value)
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
 * @property int|null $period_target_id
 * @property float|null $next_diamond
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
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget whereNextDiamond($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTarget wherePeriodTargetId($value)
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
 * @property int $user_id
 * @property int $total_coins
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserTargetCoin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTargetCoin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTargetCoin query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTargetCoin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTargetCoin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTargetCoin whereTotalCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTargetCoin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTargetCoin whereUserId($value)
 */
	class UserTargetCoin extends \Eloquent {}
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
 * @property int $user_id
 * @property float $value
 * @property float $cut_amount
 * @property float $pending_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $current_balance
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WalletTransaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallet query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallet whereCutAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallet wherePendingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallet whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserWallet whereValue($value)
 */
	class UserWallet extends \Eloquent {}
}

namespace App\Models{
/**
 *
 *
 * @property int $id
 * @property int $agency_id
 * @property int $user_id
 * @property int $type 1:owner , 2:host
 * @property string $join_date
 * @property string|null $leave_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $status
 * @property int|null $kicked_by_app
 * @property int|null $kicked_by_admin
 * @property-read \App\Models\Agency|null $agency
 * @property-read \App\Models\Admin|null $kickedByAdmin
 * @property-read \App\Models\User|null $kickedByApp
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency query()
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency whereJoinDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency whereKickedByAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency whereKickedByApp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency whereLeaveDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsersJoinedAgency whereUserId($value)
 */
	class UsersJoinedAgency extends \Eloquent {}
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
 * @property-read \App\Models\User|null $author
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
 * @property int $user_id
 * @property string $type
 * @property string|null $message
 * @property float $value
 * @property string|null $description
 * @property string|null $description_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $transactions_type
 * @property-read \App\Models\User $user
 * @property-read \App\Models\UserWallet $wallet
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereDescriptionData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereTransactionsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereValue($value)
 */
	class WalletTransaction extends \Eloquent {}
}

namespace App\Models{
/**
 *
 *
 * @property-read mixed $created_at
 * @property-read mixed $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransactionBackup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransactionBackup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransactionBackup query()
 */
	class WalletTransactionBackup extends \Eloquent {}
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
 * @property int $half_image_profile
 * @property string|null $key
 * @property array|null $key_json
 * @property float $top
 * @property float $left
 * @property float $right
 * @property float $bottom
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property-read mixed $image_type1
 * @property-read mixed $profile_frame_type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $packs
 * @property-read int|null $packs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $ware_users
 * @property-read int|null $ware_users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Ware isNotUsedInPacks()
 * @method static \Illuminate\Database\Eloquent\Builder|Ware newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ware newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ware query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ware showUserCustom(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereBottom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereGetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereHalfImageProfile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereImageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereImg1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereImg2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereImg3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereIsActiveForVip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereKeyJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereLeft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereRight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereShowImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereTitleEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ware whereUpdatedBy($value)
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
 * @property-read mixed $created_at
 * @property-read mixed $updated_at
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
 * @property string|null $category
 * @property string $type types => string,integer,select
 * @property string|null $sub_type 1 => yes or no,2=>true or false
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel query()
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereSubType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|configesModel whereType($value)
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
 * @property-read mixed $created_at
 * @property-read mixed $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|vip_prev newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|vip_prev newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|vip_prev query()
 * @method static \Illuminate\Database\Eloquent\Builder|vip_prev whereOVipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|vip_prev whereOVipPrivilegeId($value)
 */
	class vip_prev extends \Eloquent {}
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
 * @property-read \App\Models\User $fromUser
 * @property-read \Modules\CP\Entities\CpLevel|null $level
 * @property-read \Modules\CP\Entities\CpRelation|null $relation
 * @property-read \App\Models\User $toUser
 * @method static \Illuminate\Database\Eloquent\Builder|Cp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cp query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cp relation()
 * @method static \Illuminate\Database\Eloquent\Builder|Cp relationType($type)
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\CP\Entities\CpLevelGift> $gifts
 * @property-read int|null $gifts_count
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
 * @property string|null $item_id
 * @property string $type
 * @property string|null $sub_type
 * @property string $gender
 * @property string $expire
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\CP\Entities\CpLevel|null $cp_level
 * @property-read mixed $type_ware
 * @property-read \Modules\Vip\Entities\OVip|null $vip
 * @property-read \Modules\Vip\Entities\OVip|null $vip_item
 * @property-read \App\Models\Ware|null $ware
 * @property-read \App\Models\Ware|null $ware_item
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
 * @property string $type الاخوة و صديق حميم و حبايب و حلال العلاقة
 * @property float $relations_number
 * @property string|null $description
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\CP\Entities\CpLevel> $levels
 * @property-read int|null $levels_count
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereCpOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereRelationsNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRelation whereUpdatedAt($value)
 */
	class CpRelation extends \Eloquent {}
}

namespace Modules\CP\Entities{
/**
 *
 *
 * @property int $id
 * @property int $room_id
 * @property int $user_one_id
 * @property int $user_two_id
 * @property int $index1
 * @property int $index2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CpRoomHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpRoomHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpRoomHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|CpRoomHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRoomHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRoomHistory whereIndex1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRoomHistory whereIndex2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRoomHistory whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRoomHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRoomHistory whereUserOneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpRoomHistory whereUserTwoId($value)
 */
	class CpRoomHistory extends \Eloquent {}
}

namespace Modules\CP\Entities{
/**
 *
 *
 * @property int $id
 * @property int $winner_id
 * @property int $reward_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CpWinnerReward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpWinnerReward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CpWinnerReward query()
 * @method static \Illuminate\Database\Eloquent\Builder|CpWinnerReward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpWinnerReward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpWinnerReward whereRewardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpWinnerReward whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CpWinnerReward whereWinnerId($value)
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
 * @property-read \Modules\Vip\Entities\OVip|null $vip
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
 * @property int $id
 * @property int $weekly_cp_id
 * @property int $user_one_id
 * @property int $user_two_id
 * @property int $level
 * @property string $type_relation
 * @property float $total_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $userOne
 * @property-read \App\Models\User|null $userTwo
 * @property-read \Modules\Events\Entities\WeeklyStar|null $weeklyCp
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner query()
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner whereTypeRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner whereUserOneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner whereUserTwoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyCpWinner whereWeeklyCpId($value)
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
 * @property-read mixed $created_at
 * @property-read mixed $updated_at
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
 * @property string|null $duration
 * @property int|null $room_owner_id
 * @property int|null $room_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Chat\Entities\MessageAlbum> $albums
 * @property-read int|null $albums_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Chat\Entities\React> $reacts
 * @property-read int|null $reacts_count
 * @property-read \Modules\Chat\Entities\ChatRoom|null $room
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage byUserInRoom($chatRoomId, $userId)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage eligibleForDeletion()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage inRoom($roomId)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage ownedBy($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereChatRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereRoomOwnerId($value)
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
 * @method static \Illuminate\Database\Eloquent\Builder|ChatRoom betweenUsers($userId, $otherUserId)
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
 * @method static \Illuminate\Database\Eloquent\Builder|React findReact($chatRoomId, $messageId, $userId)
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
 * @property int|null $expire
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $target1
 * @property-read mixed $target2
 * @property-read mixed $target3
 * @property-read mixed $target4
 * @property-read \Modules\Vip\Entities\OVip|null $vip
 * @property-read \App\Models\Ware|null $ware
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift query()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyGift whereExpire($value)
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
 * @property string|null $target
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Events\Entities\UserChargeEvent> $users
 * @property-read int|null $users_count
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
 * @property string|null $desc_tr
 * @property string|null $desc_hi
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole whereDescAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole whereDescEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole whereDescHi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralRole whereDescTr($value)
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
 * @method static \Illuminate\Database\Eloquent\Builder|PkEvent previousNewEvent()
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
 * @property-read \Modules\Vip\Entities\OVip|null $vip
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
 * @property-read \Modules\Vip\Entities\OVip|null $vip
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
 * @property-read \Modules\Vip\Entities\OVip|null $vip
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
 * @property-read \Modules\Events\Entities\ChargeTargetEvent|null $ChargeEvents
 * @property-read \Modules\Events\Entities\ChargeTargetEvent|null $event
 * @property-read \Modules\Events\Entities\RewardTarget|null $rewardCharge
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Events\Entities\RewardTarget> $rewardCharges
 * @property-read int|null $reward_charges_count
 * @property-read \App\Models\User|null $user
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
 * @method static \Illuminate\Database\Eloquent\Builder|WeeklyStar previousNewEvent()
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

namespace Modules\LuckyBox\Entities{
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
 * @property string|null $dynamic_users_values
 * @method static \Illuminate\Database\Eloquent\Builder|Box newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Box newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Box query()
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereDefaultLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereDynamicUsersValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereHasLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Box whereUsers($value)
 */
	class Box extends \Eloquent {}
}

namespace Modules\LuckyBox\Entities{
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
 * @property int $is_closed
 * @property-read \Modules\LuckyBox\Entities\Box|null $box
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\LuckyBox\Entities\UserBoxGift> $picks
 * @property-read int|null $picks_count
 * @property-read \App\Models\Room|null $room
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\LuckyBox\Entities\UserBoxGift> $userBoxGifts
 * @property-read int|null $user_box_gifts_count
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse query()
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereBoxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BoxUse whereIsClosed($value)
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

namespace Modules\LuckyBox\Entities{
/**
 *
 *
 * @property int $id
 * @property int $user_id
 * @property int $box_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PickBoxList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PickBoxList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PickBoxList query()
 * @method static \Illuminate\Database\Eloquent\Builder|PickBoxList whereBoxUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickBoxList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickBoxList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickBoxList whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickBoxList whereUserId($value)
 */
	class PickBoxList extends \Eloquent {}
}

namespace Modules\LuckyBox\Entities{
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

namespace Modules\LuckyBox\Entities{
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
 * @property int $app_profit_coins
 * @property-read \App\Models\Gift|null $gift
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLuckyGift whereAppProfitCoins($value)
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
 * @property-read \Modules\Vip\Entities\OVip|null $vip
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
 * @property-read \App\Models\User|null $reportedUser
 * @property-read \App\Models\User|null $reporter
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
 * @property-read mixed $created_at
 * @property-read mixed $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory whereName($value)
 */
	class VideoCategory extends \Eloquent {}
}

namespace Modules\RoomBoom\Entities{
/**
 *
 *
 * @property int $id
 * @property int $total_room_gift_id
 * @property int $room_boom_level_id
 * @property string $started_at
 * @property int|null $trigger_gift_id
 * @property string|null $ended_at
 * @property int|null $final_gift_id
 * @property string $total_gifts_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\RoomBoom\Entities\RoomBoomLevel $roomBoomLevel
 * @property-read \Modules\RoomBoom\Entities\TotalRoomGift $totalRoomGift
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoom query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoom whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoom whereFinalGiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoom whereRoomBoomLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoom whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoom whereTotalGiftsValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoom whereTotalRoomGiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoom whereTriggerGiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoom whereUpdatedAt($value)
 */
	class RoomBoom extends \Eloquent {}
}

namespace Modules\RoomBoom\Entities{
/**
 *
 *
 * @property-read \Modules\RoomBoom\Entities\RoomBoomLevel|null $level
 * @property-read \App\Models\Room|null $room
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomDailyLevels newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomDailyLevels newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomDailyLevels query()
 */
	class RoomBoomDailyLevels extends \Eloquent {}
}

namespace Modules\RoomBoom\Entities{
/**
 *
 *
 * @property int $id
 * @property int $level
 * @property int $min_target
 * @property int $target
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\RoomBoom\Entities\RoomBoomReward> $roomBoomRewards
 * @property-read int|null $room_boom_rewards_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\RoomBoom\Entities\RoomBoom> $roomBooms
 * @property-read int|null $room_booms_count
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomLevel query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomLevel whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomLevel whereMinTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomLevel whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomLevel whereUpdatedAt($value)
 */
	class RoomBoomLevel extends \Eloquent {}
}

namespace Modules\RoomBoom\Entities{
/**
 *
 *
 * @property int $id
 * @property int $room_boom_level_id
 * @property string $target
 * @property string $target_type
 * @property int $priority
 * @property int $quantity
 * @property int|null $expire_days
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Gift|null $gift
 * @property-read \App\Models\Gift|null $gift_target
 * @property-read \App\Models\Ware|null $ware
 * @property-read \App\Models\Ware|null $ware_target
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomReward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomReward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomReward query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomReward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomReward whereExpireDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomReward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomReward wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomReward whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomReward whereRoomBoomLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomReward whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomReward whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoomBoomReward whereUpdatedAt($value)
 */
	class RoomBoomReward extends \Eloquent {}
}

namespace Modules\RoomBoom\Entities{
/**
 *
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SuperBoomRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuperBoomRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuperBoomRule query()
 */
	class SuperBoomRule extends \Eloquent {}
}

namespace Modules\RoomBoom\Entities{
/**
 *
 *
 * @property int $id
 * @property int $room_id
 * @property string $current_total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\RoomBoom\Entities\RoomBoom> $roomBooms
 * @property-read int|null $room_booms_count
 * @method static \Illuminate\Database\Eloquent\Builder|TotalRoomGift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TotalRoomGift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TotalRoomGift query()
 * @method static \Illuminate\Database\Eloquent\Builder|TotalRoomGift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TotalRoomGift whereCurrentTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TotalRoomGift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TotalRoomGift whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TotalRoomGift whereUpdatedAt($value)
 */
	class TotalRoomGift extends \Eloquent {}
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
 * @property-read \App\Models\ShippingAgency|null $agency
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
 * @property-read \App\Models\User $childUser
 * @property-read \App\Models\User $parentUser
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

namespace Modules\Tasks\Entities{
/**
 *
 *
 * @property int $id
 * @property int $day_id
 * @property string|null $title_ar
 * @property string $type
 * @property string|null $sub_type
 * @property int $count
 * @property int $total_points
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $title_en
 * @property-read mixed $title
 * @method static \Illuminate\Database\Eloquent\Builder|DailyTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyTask whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyTask whereDayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyTask whereSubType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyTask whereTitleAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyTask whereTitleEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyTask whereTotalPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyTask whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DailyTask whereUpdatedAt($value)
 */
	class DailyTask extends \Eloquent {}
}

namespace Modules\Tasks\Entities{
/**
 *
 *
 * @property int $id
 * @property int $day_number
 * @property string $title
 * @property int $is_unlocked
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Tasks\Entities\UserDayProgress> $userDaysProgress
 * @property-read int|null $user_days_progress_count
 * @method static \Illuminate\Database\Eloquent\Builder|Day newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Day newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Day query()
 * @method static \Illuminate\Database\Eloquent\Builder|Day whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Day whereDayNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Day whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Day whereIsUnlocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Day whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Day whereUpdatedAt($value)
 */
	class Day extends \Eloquent {}
}

namespace Modules\Tasks\Entities{
/**
 *
 *
 * @property int $id
 * @property int $day_id
 * @property string $type
 * @property string $target
 * @property int $expire
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Vip\Entities\OVip|null $vip
 * @property-read \App\Models\Ware|null $ware
 * @method static \Illuminate\Database\Eloquent\Builder|TaskReward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskReward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskReward query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskReward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskReward whereDayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskReward whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskReward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskReward whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskReward whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskReward whereUpdatedAt($value)
 */
	class TaskReward extends \Eloquent {}
}

namespace Modules\Tasks\Entities{
/**
 *
 *
 * @property int $id
 * @property int $user_id
 * @property int $day_id
 * @property int $points
 * @property int $is_completed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $get_rewards
 * @property-read \Modules\Tasks\Entities\Day $day
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayProgress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayProgress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayProgress query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayProgress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayProgress whereDayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayProgress whereGetRewards($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayProgress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayProgress whereIsCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayProgress wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayProgress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayProgress whereUserId($value)
 */
	class UserDayProgress extends \Eloquent {}
}

namespace Modules\Tasks\Entities{
/**
 *
 *
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property int $count
 * @property int $is_completed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_collect
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayTaskProgress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayTaskProgress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayTaskProgress query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayTaskProgress whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayTaskProgress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayTaskProgress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayTaskProgress whereIsCollect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayTaskProgress whereIsCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayTaskProgress whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayTaskProgress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDayTaskProgress whereUserId($value)
 */
	class UserDayTaskProgress extends \Eloquent {}
}

namespace Modules\Tasks\Entities{
/**
 *
 *
 * @property int $id
 * @property int $user_id
 * @property int $task_reward_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskReward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskReward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskReward query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskReward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskReward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskReward whereTaskRewardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskReward whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTaskReward whereUserId($value)
 */
	class UserTaskReward extends \Eloquent {}
}

namespace Modules\TribeReward\Entities{
/**
 *
 *
 * @property int $id
 * @property int $agency_id
 * @property string $type
 * @property string $target_type
 * @property string $target
 * @property int $quantity
 * @property int $available_quantity
 * @property int $expire_days
 * @property string|null $expire_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward query()
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward whereAgencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward whereAvailableQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward whereExpireAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward whereExpireDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AgencyReward whereUpdatedAt($value)
 */
	class AgencyReward extends \Eloquent {}
}

namespace Modules\TribeReward\Entities{
/**
 *
 *
 * @property int $id
 * @property string $start_date
 * @property string $end_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\TribeReward\Entities\TribeTop> $tribeTops
 * @property-read int|null $tribe_tops_count
 * @method static \Illuminate\Database\Eloquent\Builder|TribePeriod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TribePeriod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TribePeriod query()
 * @method static \Illuminate\Database\Eloquent\Builder|TribePeriod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribePeriod whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribePeriod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribePeriod whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribePeriod whereUpdatedAt($value)
 */
	class TribePeriod extends \Eloquent {}
}

namespace Modules\TribeReward\Entities{
/**
 *
 *
 * @property int $id
 * @property int $tribe_top_id
 * @property string $type
 * @property string $target_type
 * @property string $target
 * @property int $quantity
 * @property int $expire_days
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ware|null $ware
 * @method static \Illuminate\Database\Eloquent\Builder|TribeReward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TribeReward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TribeReward query()
 * @method static \Illuminate\Database\Eloquent\Builder|TribeReward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribeReward whereExpireDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribeReward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribeReward whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribeReward whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribeReward whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribeReward whereTribeTopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribeReward whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribeReward whereUpdatedAt($value)
 */
	class TribeReward extends \Eloquent {}
}

namespace Modules\TribeReward\Entities{
/**
 *
 *
 * @property int $id
 * @property int $tribe_period_id
 * @property int $min
 * @property int $max
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\TribeReward\Entities\TribeReward> $tribeRewards
 * @property-read int|null $tribe_rewards_count
 * @method static \Illuminate\Database\Eloquent\Builder|TribeTop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TribeTop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TribeTop query()
 * @method static \Illuminate\Database\Eloquent\Builder|TribeTop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribeTop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribeTop whereMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribeTop whereMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribeTop whereTribePeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TribeTop whereUpdatedAt($value)
 */
	class TribeTop extends \Eloquent {}
}

namespace Modules\Vip\Entities{
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Vip\Entities\VipPrivilege> $privilegs
 * @property-read int|null $privilegs_count
 * @property-read \App\Models\Ware|null $wareIcon
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ware> $wares
 * @property-read int|null $wares_count
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

namespace Modules\Vip\Entities{
/**
 *
 *
 * @property int $id
 * @property int|null $type 0=buy ,1= send
 * @property int|null $sender_id
 * @property string|null $sender_type
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
 * @property int $using
 * @property int|null $days
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Modules\Vip\Entities\OVip|null $OVip
 * @property-read \App\Models\Admin|null $admin
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pack> $packs
 * @property-read int|null $packs_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $senderable
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip active()
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereDashUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereIsUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereNumUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereSenderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereTypeSend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereUsing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip whereVipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|UserVip withoutTrashed()
 */
	class UserVip extends \Eloquent {}
}

namespace Modules\Vip\Entities{
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
 * @property int|null $created_by
 * @property int|null $updated_by
 * @method static \Illuminate\Database\Eloquent\Builder|Vip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vip query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereCo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereDi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereExp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vip whereUpdatedBy($value)
 */
	class Vip extends \Eloquent {}
}

namespace Modules\Vip\Entities{
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
 * @property string|null $en_title
 * @property-read \Illuminate\Database\Eloquent\Collection<int, VipPrivilege> $vip
 * @property-read int|null $vip_count
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege query()
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege whereEnName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VipPrivilege whereEnTitle($value)
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

