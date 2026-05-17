<?php

namespace Utd\AreaManager\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\Agent;
use App\Models\Agency;
use App\Models\Charge;
use App\Helpers\Common;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use App\Enums\Charges\UserTypeEnum;
use Modules\SuperAdmin\Entities\SuperAdmin;
use Utd\AreaManager\Entities\SubAreaManager;
use App\Admin\Actions\DeleteSubSuperAdmin;
use Utd\AreaManager\Http\Controllers\EncorUsersController;



class AdminUserController extends EncorUsersController
{
    // \Encore\Admin\Controllers\UserController

    protected $model;

    public $permission_name = 'auth-users';

    public function __construct()
    {
        $userModel = Admin::class;
        $this->model = new $userModel;
    }

    public function edit($id, Content $content)
    {

        return parent::edit($id, $content);
    }

    public function grid()
    {

        $grid =  parent::grid();

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->add(new DeleteSubSuperAdmin());
        });
        $grid->disableExport();
        $grid->tools(function ($tools) {
            $logoutUrl = route('admin.custom.logout');
            $loginText = __('login');
            $areaManagerUrl = url('/areaManager/login');

            $customButtonHTML = <<<HTML
                <div style="display: contents; align-items: center;">
                    <a href="{$logoutUrl}" class="btn btn-sm btn-danger" style="margin-right: 10px;">
                        <i class="fa fa-sign-in"></i> {$loginText}
                    </a>
                    <button type="button" class="btn btn-sm btn-primary" onclick="copyAreaManagerUrl()">
                        <i class="fa fa-copy"></i>   
                    </button>

                </div>
                     <script>
                    function copyAreaManagerUrl() {
                        const url = '{$areaManagerUrl}';
                        navigator.clipboard.writeText(url).then(() => {
                            toastr.success('تم نسخ الرابط بنجاح');
                        }).catch(() => {
                            alert('تعذر نسخ الرابط');
                        });
                    }
                </script>
                HTML;

            $tools->append($customButtonHTML);
        });

        return $grid;
    }

    public function update($id)
    {
        $user = Admin::query()->findOrFail($id);
        if (\request('password') != $user->password || \request('username') != $user->username) {
            Agent::where("id", $user->id)->update([
                "remember_token" => null
            ]);
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
        return parent::update($id);
    }

    public function destroy($id)
    {

        $user = $this->model->find($id);
        if ($user) {
            if ($user->isRole('admin') || $user->isRole('developer')) {
                return response()->json(['error' => '', 'message' => __('admin cant be deleted')]);
            }
        }
        Agency::query()->where('owner_id', $id)->delete();


        return parent::destroy($id);
    }

    public function form()
    {

        $form =  parent::form();
        $form->select('app_id', __('validation.select_user'))->options(function ($value) {
            $ops2 = [];
            foreach (User::Where('id', $value)->get() as $user) {
                $ops2[$user->id] = $user->uuid . '_' . $user->name;
            }
            return $ops2;
        })->ajax('/api/search/users-subsuperadmin', 'id', 'name')->rules('required');
        return $form;
    }

    public function showProfile($id, Content $content)
    {
        return  $content
            ->title(__($this->title))
            ->body($this->profile($id));
    }



    public function profile($id)
    {
        $tab = request()->query('tab', 'agencies');

        $areaManager = SubAreaManager::select(['id', 'name', 'app_id', 'avatar', 'username', 'di', 'default', 'country_id'])->findOrFail($id);

        $defaultImage = asset("images/businessman-icon.jpg");
        $imageUrl = getImagePath($areaManager->avatar);
        if (!isImageExists($imageUrl)) {
            $imageUrl = $defaultImage;
        }
        $areaManager->display_image = $imageUrl;

        $agencies = $transactions = $target_history = null;
        $rewards = null;

        $totals = Charge::selectRaw("
            SUM(CASE WHEN user_type = ? AND user_id = ? THEN amount ELSE 0 END) as total_charges,
            SUM(CASE WHEN charger_type = ? AND charger_id = ? THEN amount ELSE 0 END) as total_spent
        ", [
            UserTypeEnum::AREA_MANAGER,
            $areaManager->id,
            UserTypeEnum::AREA_MANAGER,
            $areaManager->id
        ])
            ->first();

        $totalCharges = $totals->total_charges;
        $totalSpent   = $totals->total_spent;

        $chargeTabType = request()->get('type', 'receiver');


        $charges = Charge::query()
            ->when($chargeTabType == 'receiver', function ($q) use ($id) {
                $q->where('user_id', $id)->where('user_type', UserTypeEnum::AREA_MANAGER);
            })
            ->when($chargeTabType == 'charger', function ($q) use ($id) {
                $q->where('charger_id', $id)->where('charger_type', UserTypeEnum::AREA_MANAGER);
            })
            ->with(Common::chargerRelationsQuery())
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'charges_page');
        $superAdmins = SuperAdmin::where('parent_id', $areaManager->parent_id)->with(['appUser', 'country', 'appUser.country'])->paginate(10, ['*'], 'super_admins_page');
        $prefix = dashboardName();
        switch ($tab) {
            case 'agencies':
                $agencies = $areaManager->agencies()->with('owner.profile')->paginate(10, ['*'], 'agencies_page');
                break;
            case 'charge':

                break;
        }

        return view('areamanager::area_manager_profile', compact('areaManager', 'defaultImage', 'prefix', 'superAdmins', 'agencies', 'totalCharges', 'totalSpent', 'chargeTabType', 'charges'));
    }
}
