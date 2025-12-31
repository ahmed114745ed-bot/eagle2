<?php

namespace App\Admin\Actions;

use Carbon\Carbon;
use Modules\Vip\Entities\OVip;
use App\Models\Pack;
use App\Models\User;
use App\Models\Ware;
use App\Helpers\Common;
use Modules\Vip\Entities\UserVip;
use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;
use App\Facades\CustomNotification;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\RowAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Classes\Enums\SubTypeMessagesType;
use Modules\Public\Http\Services\UserCounterServices;
use Modules\Public\Http\Services\UpgradeLevelServices;
use Modules\Public\Http\Services\UpgradeServices;

class WareDedicateAction extends Action
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
        $countryID = empty((array)session('filter_country_id')) ? Common::areaCountries() : (array)session('filter_country_id');

        $user = User::query()
            ->when($countryID, fn($q) => $q->whereIn('country_id', $countryID))
            ->searchByUuid($request->user_uuid)->first();

        if (!$user) {
            return $this->response()->error(__('dashboard.userNotFound'))->refresh();
        }

        $ware = Ware::find($request->id);
        if (!$ware) {
            return $this->response()->error(__('not found'))->refresh();
        }
        if ($ware && $ware->type == 25) {
            $special_id_check = Pack::query()->where('target_id', $ware->id)->first();
            if ($special_id_check) {
                return $this->response()->error(__('dashboard.taken'))->refresh();
            }
        }

        DB::beginTransaction();
        try {
            $types = [8, 9, 12, 14, 17];
            $arr['user_id'] = $user->id;
            $arr['type'] = $ware->type;
            $arr['get_type'] = $ware->get_type;
            $arr['target_id'] = $ware->id;
            $arr['num'] = 1; //$qty;
            if (in_array($ware->type, $types)) {
                $arr['expire'] = $request->days ? time() + (($request->days ?? $ware->expire) * 86400) : 0;
                $arr['is_used'] = 1;
                $arr['using'] = 1;
            } else {
                $arr['is_used'] = 0;
                $arr['using'] = 0;
            }

            $arr['is_read'] = 1;
            $arr['days'] = $request->days ? $request->days ?? $ware->expire : 0;
            $arr['receive_type'] = 'wares-dash-dedicate';

            $enableVipAuto = Common::getConf('enable_vip_auto') ?? "false";
            // $arr['is_used'] = $enableVipAuto === "true" ? 1 : 0;
            $arr['dash_user_id']  = auth()->id();

            Pack::query()->create($arr);
            if ($ware->type == 25) {
                $user->special_id = $ware->value;
                $user->save();
            }
            DB::commit();
            (new UserCounterServices)->eventUser($user, 'mybag', 1);
            CustomNotification::wareVip($user, $request->days, $ware->name, $ware->show_img);
            return $this->response()->success(__('dashboard.successful'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->response()->error('خطا غير متوقع');
        }
    }

    public function form()
    {
        $this->hidden('id', __('id'))->attribute('id', 'vid');
        $this->integer('days', __('days'));
        $this->text('user_uuid', __('user uuid'));
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
