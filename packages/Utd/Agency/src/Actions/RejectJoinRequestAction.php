<?php

namespace Utd\Agency\Actions;

use Encore\Admin\Actions\RowAction;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class RejectJoinRequestAction extends RowAction
{
    public $name = 'Reject';

    public function handle(Model $model, Request $request)
    {
        try {
            $model->status = 2;
            $model->save();

            return $this->response()->success('Request rejected successfully.')->refresh();
        } catch (Exception $e) {
            return $this->response()->error($e->getMessage());
        }
    }

    public function dialog()
    {
        $this->confirm('Are you sure you want to reject this request?');
    }
}
