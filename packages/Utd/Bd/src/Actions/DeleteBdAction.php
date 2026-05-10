<?php

namespace Utd\Bd\Actions;

use App\Support\PackageHelper;

use App\Models\Agency;
use Utd\Bd\Entities\Bd;
use App\Models\User;
use App\Models\UserSallary;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

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
        if ($model->default == 1) {
            return $this->response()->error(__('You cannot delete the default BD for this country.'))->refresh();
        }

        if ($model->created_by == 'owner') {
            return $this->response()->error(__('You cannot delete a BD created by the owner.'))->refresh();
        }

        if ($this->agencyCount > 0) {
            $defaultBd = Bd::where('default', 1)->where('id', '!=', $model->app_id)->first();
            if (!$defaultBd) {
                return $this->response()->error(__('No default BD found to transfer agencies to.'))->refresh();
            }
            if (!$defaultBd->app_id) return $this->response()->error(__('No default BD found to transfer agencies to.'))->refresh();
            Agency::where('bd_id', $model->id)->update(['bd_id' => $defaultBd->app_id]);
        }
        $owner = User::find($model->app_id);

        if ($owner) { app(\App\Contracts\MilestoneHelperContract::class)->removeReward($owner, 'bd'); }

        $model->delete();

        return $this->response()->success('BD deleted successfully.')->refresh();
    }






    public function dialog()
    {
        $this->confirm(__('dashboard.chickDelete'), '', []);
    }
}
