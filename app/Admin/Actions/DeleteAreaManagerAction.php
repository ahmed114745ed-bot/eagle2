<?php

namespace App\Admin\Actions;

use App\Models\AreaManager;
use App\Models\Country;
use App\Models\SubAreaManager;
use App\Models\User;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Modules\Milestones\Helpers\MilestoneHelper;

class DeleteAreaManagerAction extends RowAction
{
    public $name;

    public function __construct()
    {
        parent::__construct();
        $this->name = __('delete');
    }

    public function setModel(Model $model)
    {

        return parent::setModel($model);
    }


    public function handle(Model $model, Request $request)
    {
        if($model->default ==1)  return $this->response()->error('can not delete default area admin')->refresh();
        $user = User::find($model->app_id);
        if ($user) {
            $user->is_area_manager = 0;
            $user->save();
            MilestoneHelper::removeReward($user, 'area-manager');
        }
        $default = AreaManager::where('default', 1)->first();
        if ($default) SuperAdmin::where('parent_id', $model->id)->update(['parent_id' => $default->id]);
        $subAppId = SubAreaManager::where('parent_id', $model->id)->pluck('app_id')->toArray();
        if (!empty($subAppId)) {
            User::whereIn('id', $subAppId)->update(['sub_area_manger' => 0]);
            SubAreaManager::where('parent_id', $model->id)->delete();
        }

        $model->delete();

        return $this->response()->success(__('dashboard.successful'))->refresh();
    }


    public function dialog()
    {
        $this->confirm(__('dashboard.chickDelete'), '', []);
    }
}
