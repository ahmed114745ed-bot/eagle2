<?php

namespace App\Admin\Actions;

use Illuminate\Http\Request;
use Encore\Admin\Actions\Action;
use App\Models\SuperAdmin;
use App\Models\SuperAdminReward;

class DedicateSuperAdminRewardAction extends Action
{
    public $name;
    protected $selector = '.salary_action';
    public $id;
    public $type;

    public function __construct($id = 0, $type = '')
    {
         $this->name = __('dedicate');
        $this->id = $id;
        $this->type = $type;
        parent::__construct();
    }

    public function handle(Request $request)
    {
        try {

            $superAdmins = $request->input('super_admin_id', []);
            $expire = $request->input('expire');
            $noReward = $request->input('no_reward');

            foreach ($superAdmins as $superAdmin) {
                SuperAdminReward::create([
                    'super_admin_id' => $superAdmin,
                    'type' => $request->type,
                    'target' => $request->uid,
                    'expire' => $expire,
                    'no_reward' => $noReward,

                ]);
            }

            return $this->response()->success(__('Dedicated successfully'))->refresh();
        } catch (\Exception $exception) {
            return $this->response()->error(__('Something went wrong') . $exception->getMessage());
        }
    }

    public function form()
    {
        $this->hidden('uid', __('id'))->attribute('id', 'uid');
        $this->hidden('type', __('id'))->attribute('id', 'type');

        $this->multipleSelect('super_admin_id', __('Select Super Admins'))
            ->options(function () {
                return SuperAdmin::pluck('name', 'id');
            });

        $this->integer('expire', __('Days'))->default(1);
        $this->integer('no_reward', __('No reward'))->default(1);
    }

    public function html()
    {
        return '<a href="#" onclick="dedicateSet(\'' . $this->id . '\', \'' . $this->type . '\')" class="btn btn-sm btn-success salary_action">'
            . __('dedicate') . '</a>
        <script>
            function dedicateSet(uid, type) {
                $("#uid").val(uid);
                $("#type").val(type);
            }
        </script>';
    }
}
