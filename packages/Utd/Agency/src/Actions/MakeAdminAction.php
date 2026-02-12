<?php

namespace Utd\Agency\Actions;

use Encore\Admin\Actions\RowAction;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Utd\Agency\Entities\AgencyUserJob;

class MakeAdminAction extends RowAction
{
    public $name = 'Make Admin';

    public function handle(Model $model, Request $request)
    {
        try {
            // Check if already admin
            $exists = AgencyUserJob::where('user_id', $model->id)
                ->where('agency_id', $model->agency_id)
                ->where('type', 'requestManger')
                ->exists();

            if ($exists) {
                return $this->response()->error('User is already an admin.');
            }

            // Create admin job
            AgencyUserJob::create([
                'user_id' => $model->id,
                'agency_id' => $model->agency_id,
                'type' => 'requestManger',
            ]);

            return $this->response()->success('User is now an admin.')->refresh();
        } catch (Exception $e) {
            return $this->response()->error($e->getMessage());
        }
    }

    public function dialog()
    {
        $this->confirm('Are you sure you want to make this user an admin?');
    }
}
