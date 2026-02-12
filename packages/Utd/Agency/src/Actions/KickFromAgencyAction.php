<?php

namespace Utd\Agency\Actions;

use Encore\Admin\Actions\RowAction;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class KickFromAgencyAction extends RowAction
{
    public $name = 'Kick from Agency';

    public function handle(Model $model, Request $request)
    {
        try {
            $agencyId = $model->agency_id;

            // Remove from agency
            $model->agency_id = null;
            $model->type_user = 0;
            $model->save();

            // Record leave
            if ($agencyId) {
                \Utd\Agency\Entities\UsersJoinedAgency::where('user_id', $model->id)
                    ->where('agency_id', $agencyId)
                    ->whereNull('leave_date')
                    ->update(['leave_date' => now()]);
            }

            return $this->response()->success('User kicked from agency.')->refresh();
        } catch (Exception $e) {
            return $this->response()->error($e->getMessage());
        }
    }

    public function dialog()
    {
        $this->confirm('Are you sure you want to kick this user from the agency?');
    }
}
