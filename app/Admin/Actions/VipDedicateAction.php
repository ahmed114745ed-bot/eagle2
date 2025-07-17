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

    public function __construct($id = null)
    {
        $this->id = $id;
        parent::__construct();
    }

    public function handle(Request $request)
    {
        try {
            // Validate user
            $user = User::query()->searchByUuid($request->user_uuid)->first();
            if (!$user) {
                return $this->response()->error(__('dashboard.userNotFound'))->refresh();
            }

            // Get VIP
            $vip = OVip::find($request->get('id'));
            if (!$vip) {
                return $this->response()->error('VIP not found')->refresh();
            }

            // Check admin permissions
            if (!Admin::user()->can('*') && $request->days > 30) {
                return $this->response()->error(__('dashboard.addAchivement'))->refresh();
            }

            DB::beginTransaction();

            $enableVipAuto = config('admin.isUsed_vip');


            $is_used = $enableVipAuto === true ? 1 : 0;
            $uniqueAttributes = [
                'sender_id' => 0,
                'user_id'   => $user->id,
                'vip_id'    => $vip->id,
                'level'     => $vip->level,
            ];

            //  $userVip = UserVip::query()->where($uniqueAttributes)->first();

            // if (!$userVip) {
            $userVip = UserVip::create([
                ...$uniqueAttributes,
                'type'   => 1,
                'expire' => $is_used ? Carbon::now()->addDays($request->days ?: $vip->expire)->timestamp : null,
                'qty'    => 1,
                'price'  => 0,
                'total'  => 0,
                'is_used'  => $is_used,
                'dash_user_id'  => auth()->id(),
                'using' => $is_used,
                'days' => $request->days,
            ]);
            // } else {
            //     $userVip->qty++;
            //     if ($userVip->expire > now()->timestamp) {
            //         $userVip->expire += ($request->days * 86400);
            //     } else {
            //         $userVip->expire = now()->timestamp + ($request->days * 86400);
            //     }
            //     $userVip->is_used += $is_used;
            //     $userVip->save();
            // }

            if ($is_used)  Common::handelVip($vip, $user, expire: $request->days ?? $vip->expire, userVip: $userVip);

            DB::commit();

            CustomNotification::vips($user, $request->days, $vip->img);

            $title = 'VIP Assigned';
            $body = 'You have received VIP access for :days days from admin.';

            CustomNotification::charges($user, $title, $body, ['days' => $request->days]);

            return $this->response()->success(__('dashboard.successful'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->response()->error(__('dashboard.error'))->refresh();
        }
    }

    public function form()
    {
        $this->hidden('id')->default($this->id);
        $this->integer('days', __('days'))->required();
        $this->text('user_uuid', __('user uuid'))->required();
    }

    public function html()
    {
        return '<a href="javascript:void(0);" onclick="pu(' . $this->id . ')" class="btn btn-sm btn-info salary_action">' . __('dedicate') . '</a>
    <script>
    function pu(val) {
        $("input[name=\'id\']").val(val);
    }
    </script>';
    }
}
