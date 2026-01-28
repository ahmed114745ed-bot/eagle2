<?php

namespace Utd\Agency\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Utd\Agency\Entities\AgencyUserJob;

class RemoveAdminAction extends RowAction
{
    public $name = 'Remove Admin';

    public function handle(Model $model, Request $request)
    {
        try {
            // Remove admin job
            AgencyUserJob::where('user_id', $model->id)
                ->where('agency_id', $model->agency_id)
                ->where('type', 'requestManger')
                ->delete();

            return $this->response()->success('Admin role removed.')->refresh();
        } catch (\Exception $e) {
            return $this->response()->error($e->getMessage());
        }
    }

    public function dialog()
    {
        $this->confirm('Are you sure you want to remove admin role from this user?');
    }
}
