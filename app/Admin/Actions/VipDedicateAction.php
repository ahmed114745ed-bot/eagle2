<?php

namespace App\Admin\Actions;

use Carbon\Carbon;
use App\Models\OVip;
use App\Models\User;
use App\Helpers\Common;
use App\Models\UserVip;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;
use App\Facades\CustomNotification;


class VipDedicateAction extends Action
{
    protected $selector = '.salary_action';
    public $id;


    public function __construct($id = 0)
    {
        $this->id = $id;
        parent::__construct();
    }

    public function handle(Request $request)
    {

        $user = User::query()->searchByUuid($request->user_uuid)->first();
        if (!$user) {
            return $this->response()->error(__('dashboard.userNotFound'))->refresh();
        }
        // dd(request('id'));
        $vip = OVip::find(request('id'));
        // dd(123, $vip);
        // admin only put to user vip greater than 30 days
        if (!Admin::user()->can('*') && $request->days > 30) {
            return $this->response()->error(__('dashboard.addAchivement'))->refresh();
        }
        DB::beginTransaction();


        $enableVipAuto = Common::getConf('enable_vip_auto') ?? "false";
        $is_used = $enableVipAuto === "true" ? 1 : 0;

        try {
            $uniqueAttributes = [
                'sender_id' => 0,
                'user_id'   => $user->id,
                'vip_id'    => $vip->id,
                'level'     => $vip->level,
            ];
            $userVip = UserVip::query()->where($uniqueAttributes)->first();
            if (!$userVip) {
                UserVip::query()->create(
                    [
                        ...$uniqueAttributes,
                        'type'   => 1,
                        'expire' => Carbon::now()->addDays($request->days ?: 1)->timestamp,
                        'qty'    => 1,
                        'price'  => 0,
                        'total'  => 0,
                        'is_used'  => $is_used,
                        'dash_user_id'  => \auth()->user()->id,
                    ]
                );
            } else {
                $userVip->qty++;
                if ($userVip->expire > now()->timestamp) {
                    $userVip->expire += ($request->days * 86400);
                    $userVip->is_used += $is_used;
                } else {
                    $userVip->expire = now()->timestamp + ($request->days * 86400);
                    $userVip->is_used += $is_used;
                }
                $userVip->save();
            }
            Common::handelVip($vip, $user, expire: $request->days ?? 1);

            DB::commit();
            CustomNotification::vips($user, $request->days, $vip->img);
            return $this->response()->success(__('dashboard.successful'));
        } catch (\Exception $exception) {

            echo ($exception->getMessage());
            DB::rollBack();
            return $this->response()->error('خطا.')->refresh();
        }
    }

    public function form()
    {
        $this->hidden('id', __('id'))->attribute('id', 'vid');
        $this->integer('days', 'days');
        $this->text('user_uuid', 'user uuid');
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
}
