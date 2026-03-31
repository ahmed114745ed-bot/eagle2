<?php

namespace App\Admin\Actions;

use App\Models\Agency;
use App\Models\Bd;
use App\Models\User;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Milestones\Entities\Milestone;
use Modules\Milestones\Entities\MilestoneReward;

class DeleteBdAction extends RowAction
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
        $this->agencyCount = Agency::where('bd_id', $model->app_id)->count();
        return parent::setModel($model);
    }


    public function handle(Model $model, Request $request)
    {

        if ($model->default == 1 && !$model->country_id) {
            return $this->response()->error(__('You cannot delete the default BD.'))->refresh();
        }
        if ($model->default == 1) {
            return $this->response()->error(__('You cannot delete the default BD for this country.'))->refresh();
        }

        if ($model->created_by == 'owner') {
            return $this->response()->error(__('You cannot delete a BD created by the owner.'))->refresh();
        }

        if ($this->agencyCount > 0) {
            $defaultBd = Bd::where('default', 1)->where('country_id', $model->country_id)->first();
            if (!$defaultBd)   return $this->response()->error(__('No default BD  for this country found to transfer agencies to.'))->refresh();
            Agency::where('bd_id', $model->id)->update(['bd_id' => $defaultBd->id]);
        }
        if($model->app_id) {
            $userApp = User::find($model->app_id);
            if ($userApp) {
                $userApp->is_bd = 0;
                $userApp->save();
            }
        }
        $this->deleteMilestoneRewards($model);

        $model->delete();

        return $this->response()->success('BD deleted successfully.')->refresh();
    }

    public function dialog()
    {
        $this->confirm(__('dashboard.chickDelete'), '', []);
    }
    
    protected function deleteMilestoneRewards(Bd $bd)
    {
        $milestone = Milestone::where('slug', 'bd')->first();
        if ($milestone) {
            MilestoneReward::where('milestone_id', $milestone->id)
                ->where('rewardable_id', $bd->app_id)
                ->delete();
        }
    }
}