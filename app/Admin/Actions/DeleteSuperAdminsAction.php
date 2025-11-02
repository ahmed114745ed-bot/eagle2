<?php

namespace App\Admin\Actions;

use App\Models\Bd;
use App\Models\User;
use App\Models\SubAdmin;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Modules\Milestones\Helpers\MilestoneHelper;

class DeleteSuperAdminsAction extends RowAction
{
    public $name;

    protected $agencyCount = 0;

    public function __construct()
    {
        parent::__construct();
        $this->name = __('delete');
    }

    public function setModel(Model $model)
    {
        //            $this->agencyCount = Agency::where('bd_id', $model->app_id)->count();
        return parent::setModel($model);
    }


    public function handle(Model $model, Request $request)
    {

        $user = User::find($model->app_id);
        if ($user) {
            $user->is_super_admin = 0;
            $user->save();
            MilestoneHelper::removeReward($user, 'super-admin');
        }
        $defaultSuperAdmin = SuperAdmin::whereNull('country_id')->first();
        if ($defaultSuperAdmin) Bd::where('parent_id', $model->id)->update(['parent_id' => $defaultSuperAdmin->id]);
        SubAdmin::where('parent_id', $model->id)->delete();

        $model->delete();

        return $this->response()->success(__('super admin deleted successfully.'))->refresh();
    }


    public function dialog()
    {
        $this->confirm(__('dashboard.chickDelete'), '', []);
    }
}
