<?php

namespace Utd\AreaManager\Actions;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Ware;
use App\Helpers\Common;
use App\Models\Country;
use App\Helpers\UserCommon;
use Illuminate\Http\Request;
use App\Enums\UserCoinLogType;
use Utd\Vip\Entities\OVip;
use App\Support\PackageHelper;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Actions\Action;
use App\Helpers\UserCoinLogHelper;
use Illuminate\Support\Facades\DB;
use Utd\AreaManager\Entities\Region;
use Modules\SuperAdmin\Entities\SuperAdmin;
use Modules\SuperAdmin\Entities\SuperAdminReward;
use Utd\Achievements\Entities\UserAchievementLevel;

class SuperAdminDedicateRewardAction extends Action
{
    public $name;
    protected $selector = '.salary_action';
    public $id;


    public function __construct($id = 0)
    {
        $this->name = __('dedicate');
        $this->id = $id;
        parent::__construct();
    }


    public function handle(Request $request)
    {


        $reward = SuperAdminReward::find($request->id);
        $rewardNom =  $reward->no_reward -  $reward->gave_reward_no;
        try {
            if ($rewardNom == 0) {
                return $this->response()->error(__('your reward finished'));
            }
            if ($request->user_type == 'user') {
                $user = User::query()->searchByUuid($request->user_uuid)->first();
                if (!$user)   return $this->response()->error(__('dashboard.userNotFound'))->refresh();
                $this->assignRewards($reward, $user);
            } else {
                $user = SuperAdmin::find($request->super_admin_id);
                if (!$user) return $this->response()->error(__('dashboard.userNotFound'))->refresh();
                DB::table('admin_rewards')->insert([
                    'super_admin_id' => $user->id,
                    'type' => $reward->type,
                    'target' => $reward->target,
                    'expire' => $reward->expire,
                    'no_reward' => 1,
                    'user_type' => 'super_admin',
                    'created_by' => Admin::user()->id,
                    'created_at' => now()

                ]);
            }

            SuperAdminReward::where('id', $request->id)
                ->increment('gave_reward_no', 1);
            return $this->response()->success(__('dashboard.successful'));
        } catch (\Exception $exception) {
            dd($exception->getMessage());
            return $this->response()->error('you dedicate all reward');
        }
    }

    public function form()
    {
        $this->hidden('id', __('id'))->attribute('id', 'vid');
        $this->select('user_type', __('user Type'))->options(['super_admin' => __('Country Manager'), 'user' => __('user')])->default('super_admin')->required()->attribute(['id' => 'user-type-select']);

        $this->text('user_uuid', __('user uuid'))->attribute(['id' => 'user-select']);
        $this->select('super_admin_id', __('Select Country Manager'))
            ->options(self::getSuperAdmins())
            // ->ajax('/areaManager/search/super-admin', 'id', 'name')
            ->attribute(['id' => 'super-admin-select']);

        Admin::script(<<<'SCRIPT'
            function toggleUserTypeFields() {
                var selected = $('#user-type-select').val();

                if (selected === 'user') {
                    $('#user-select').closest('.form-group').show();
                    $('#super-admin-select').closest('.form-group').hide();
                } else if (selected === 'super_admin') {
                    $('#super-admin-select').closest('.form-group').show();
                    $('#user-select').closest('.form-group').hide();
                }
            }

            // listen to correct select
            $(document).on('change', '#user-type-select', toggleUserTypeFields);

            // run on page load
            toggleUserTypeFields();
            SCRIPT);
    }

    public function html()
    {
        return '<a href="javascript:void(0);" onclick="pu(' . $this->id . ')" class="btn btn-sm btn-info salary_action ">' . __('dedicate') . '</a>
<script>
function pu(val) {

  $("#vid").val(val)
}
</script>
';
    }


    // protected static function getSuperAdmins()
    // {
    //     static $admins = null;
    //     $authId = auth()->user()->type == 'area-manager' ? auth()->user()->id : auth()->user()->parent_id;

    //     $region = Region::where('manager_id', $authId)->with('countries')->first();
    //     $countries = $region->countries->pluck('id')->toArray();
    //     if ($admins === null) {
    //         $admins = SuperAdmin::query()
    //             ->whereIn('country_id', $countries)
    //             ->where('type', 'superadmin')
    //             ->whereNull('deleted_at')
    //             ->pluck('name', 'id')
    //             ->toArray();
    //     }

    //     return $admins;
    // }

    protected static function getSuperAdmins()
    {
        $authId = auth()->user()->type === 'area-manager'
            ? auth()->user()->id
            : auth()->user()->parent_id;

        return cache()->remember(
            "super_admins_by_manager_{$authId}",
            600,
            function () use ($authId){
                $region = Region::where('manager_id', $authId)->with('countries')->first();
        $countries = $region->countries->pluck('id')->toArray();

                return SuperAdmin::query()
                    ->select('id', 'name')
                    ->whereIn('country_id', $countries)
                    ->where('type', 'superadmin')
                    ->whereNull('deleted_at')
                    ->pluck('name', 'id')
                    ->toArray();
            }
        );
    }

   




    protected function assignRewards($reward, $user)
    {

        DB::table('dedicate_admin_rewards')->insert([
            'user_id' => $user->id,
            'reward_id' => $reward->id,
            'admin_id' => auth()->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        switch ($reward->type) {
            case "coin":

                $amountBefore = $user->di;
                UserCoinLogHelper::logByType(
                    $user->id,
                    $reward->target,
                    $amountBefore,
                    UserCoinLogType::REGION_MANAGER_REWARD,
                );

                $user->di += $reward->target;
                $user->save();


                break;
            case "vip":
                $vip = PackageHelper::isInstalled('vip') ? OVip::find($reward->target) : null;
                if ($vip) UserCommon::addVipToUser($user, $vip, $reward->expire, null, 'region_manager_dedicate');
                break;
            case "ware":
                $ware = Ware::find($reward->target);
                UserCommon::addWareToUser($user, $ware, $reward->expire, null, 'region_manager_dedicate');
                break;
            case "badge":
                if (PackageHelper::isInstalled('badge')) {
                    Common::userBadge($user->id, $reward->target, $reward->expire, 'region_manager_dedicate');
                }
                break;
            case "achievement":
                if (class_exists(UserAchievementLevel::class)) {
                    $dateTimestamp = Carbon::parse($reward->expire)->format("Y-m-d H:i:s");
                    $attributes = [
                        'user_id'       => $user->id,
                        'custom_image' => $reward->target,
                        'end_at' => $dateTimestamp,
                    ];
                    UserAchievementLevel::create($attributes);
                }
                break;
        }
    }
}
