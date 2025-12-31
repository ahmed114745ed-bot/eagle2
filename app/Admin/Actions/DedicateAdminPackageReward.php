<?php

namespace App\Admin\Actions;


use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Actions\Action;
use App\Models\SuperPackageReward;
use Modules\SuperAdmin\Entities\SuperAdmin;
use Modules\AreaManager\Entities\AreaManager;
use Modules\SuperAdmin\Entities\SuperAdminReward;

class DedicateAdminPackageReward extends Action
{
    public $name;
    protected $selector = '.salary_action';
    public $id;

    public function __construct($id = 0,)
    {
        $this->name = __('dedicate');
        $this->id = $id;

        parent::__construct();
    }

    public function handle(Request $request)
    {
        try {
            $superPackage = SuperPackageReward::with('packageRewards')->find($request->uid);

            if (!$superPackage) {
                return $this->response()->error(__('Super Package not found.'));
            }

            $superAdmins = $request->input('super_admin_id', []);
            $areaAdmins = $request->input('area_admin_id', []);
            $userType = $request->input('user_type');
            if ($request->input('user_type') == 'area_manager') {
                $superAdmins = $areaAdmins;
            }

            foreach ($superAdmins as $superAdmin) {
                foreach ($superPackage->packageRewards as $reward) {
                    SuperAdminReward::create([
                        'super_admin_id' => $superAdmin,
                        'type' => $reward->type,
                        'target' => $reward->target,
                        'expire' => $reward->expire,
                        'no_reward' => $reward->quantity,
                        'user_type' => $userType,
                        'created_by' => Admin::user()->id,
                    ]);
                }
            }


            return $this->response()->success(__('Dedicated successfully'))->refresh();
        } catch (\Exception $exception) {
            return $this->response()->error(__('Something went wrong') . $exception->getMessage());
        }
    }

    public function form()
    {
        $this->hidden('uid', __('id'))->attribute('id', 'uid');
        $this->select('user_type', __('user Type'))->options(['area_manager' => __('Region Manager'), 'super_admin' => __('Country Manager')])->default('area_manager')->required()->attribute(['id' => 'user-type-select']);

        $this->multipleSelect('area_admin_id', __('Select Region Manager'))
            ->options(self::getSuperAdmins())->attribute(['id' => 'area-admin-select']);

        $this->multipleSelect('super_admin_id', __('Select Country Manager'))
            ->options(self::getAreaAdmins())->attribute(['id' => 'super-admin-select']);

        Admin::script(<<<'SCRIPT'
            function toggleUserTypeFields() {
                var selected = $('#user-type-select').val();

                if (selected === 'area_manager') {
                    $('#area-admin-select').closest('.form-group').show();
                    $('#super-admin-select').closest('.form-group').hide();
                } else if (selected === 'super_admin') {
                    $('#super-admin-select').closest('.form-group').show();
                    $('#area-admin-select').closest('.form-group').hide();
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
        return '<a href="#" onclick="dedicateSet(\'' . $this->id . '\')" class="btn btn-sm btn-success salary_action">'
            . __('dedicate') . '</a>
        <script>
            function dedicateSet(uid) {
                $("#uid").val(uid);
            }
        </script>';
    }


    protected static function getSuperAdmins()
    {
        static $admins = null;

        if ($admins === null) {
            $admins = SuperAdmin::query()
                ->where('type', 'superadmin')
                ->whereNull('deleted_at')
                ->pluck('name', 'id')
                ->toArray();
        }

        return $admins;
    }


    protected static function getAreaAdmins()
    {
        static $admins = null;

        if ($admins === null) {
            $admins = AreaManager::query()
                ->where('type', 'area-manager')
                ->whereNull('deleted_at')
                ->pluck('name', 'id')
                ->toArray();
        }

        return $admins;
    }
}
