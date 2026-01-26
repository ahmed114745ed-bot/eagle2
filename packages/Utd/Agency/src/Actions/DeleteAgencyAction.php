<?php

namespace Utd\Agency\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DeleteAgencyAction extends RowAction
{
    public $name = 'Delete Agency';

    public function handle(Model $model, Request $request)
    {
        try {
            // Remove all members from agency
            \App\Models\User::where('agency_id', $model->id)->update([
                'agency_id' => null,
                'type_user' => 0,
            ]);

            // Delete agency
            $model->delete();

            return $this->response()->success('Agency deleted successfully.')->refresh();
        } catch (\Exception $e) {
            return $this->response()->error($e->getMessage());
        }
    }

    public function dialog()
    {
        $this->confirm('Are you sure you want to delete this agency? All members will be removed.');
    }
}
