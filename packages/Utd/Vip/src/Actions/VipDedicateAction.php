<?php

namespace Utd\Vip\Actions;

use App\Facades\CustomNotification;
use App\Helpers\Common;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Utd\Vip\Entities\OVip;
use Utd\Vip\Helpers\VipCommon;

class VipDedicateAction extends Action
{
    public $id;

    protected $selector = '.salary_action';

    public function __construct($id = null)
    {
        $this->id = $id;
        parent::__construct();
    }

    public function handle(Request $request)
    {
        try {
            $countryID = empty((array) session('filter_country_id')) ? Common::areaCountries() : (array) session('filter_country_id');

            // Validate user
            $user = User::query()
                ->when($countryID, fn ($q) => $q->whereIn('country_id', $countryID))
                ->searchByUuid($request->user_uuid)->first();

            if (! $user) {
                return $this->response()->error(__('dashboard.userNotFound'))->refresh();
            }

            // Get VIP
            $vip = OVip::find($request->get('id'));
            if (! $vip) {
                return $this->response()->error('VIP not found')->refresh();
            }

            // Check admin permissions
            if (! Admin::user()->can('*') && $request->days > 30) {
                return $this->response()->error(__('dashboard.addAchivement'))->refresh();
            }

            DB::beginTransaction();

            VipCommon::createUserVip($vip, $user, $request->days, Admin::user()->id, '', 1, 0, 0, 'admin-dedicate');

            DB::commit();

            CustomNotification::vips($user, $request->days, $vip->img);

            $title = 'VIP Assigned';
            $body = 'You have received VIP access for :days days from admin.';

            CustomNotification::charges($user, $title, $body, ['days' => $request->days]);

            return $this->response()->success(__('dashboard.successful'));
        } catch (Exception $exception) {
            DB::rollBack();

            return $this->response()->error(__('dashboard.error'))->refresh();
        }
    }

    public function form()
    {
        $this->hidden('id')->default($this->id);
        $this->integer('days', __('days'))->rules(['required', 'integer', 'min:1']);
        $this->text('user_uuid', __('user uuid'))->required();
    }

    public function html()
    {
        return '<a href="javascript:void(0);" onclick="pu('.$this->id.')" class="btn btn-sm btn-info salary_action">'.__('dedicate').'</a>
    <script>
    function pu(val) {
        $("input[name=\'id\']").val(val);
    }
    </script>';
    }
}
