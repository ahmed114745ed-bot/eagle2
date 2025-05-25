<?php

namespace App\Admin\Actions;

use Illuminate\Http\Request;
use App\Helpers\Common;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class CloseRoomAction extends RowAction
{
    public $name;

    public function __construct($id = 0)
    {
        $this->name = __("dashboard.roomClose");
        parent::__construct();
    }
    public function handle(Model $model, Request $request)
    {
        try {
            DB::beginTransaction();

            Common::kickOfAllUsersRoom($model);
            DB::commit();
            return $this->response()->success(__('dashboard.successful'))->refresh();
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->response()->error($exception->getMessage())->refresh();
        }
    }

    public function dialog()
    {
        $this->confirm(__('dashboard.closeRoom'), '', []);
    }
}
