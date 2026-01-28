<?php

namespace Utd\Agency\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AcceptJoinRequestAction extends RowAction
{
    public $name = 'Accept';

    public function handle(Model $model, Request $request)
    {
        try {
            $model->status = 1;
            $model->save();

            // Update user agency
            $user = $model->user;
            if ($user) {
                $user->agency_id = $model->agency_id;
                $user->type_user = 1;
                $user->save();
            }

            return $this->response()->success('Request accepted successfully.')->refresh();
        } catch (\Exception $e) {
            return $this->response()->error($e->getMessage());
        }
    }

    public function dialog()
    {
        $this->confirm('Are you sure you want to accept this request?');
    }
}
