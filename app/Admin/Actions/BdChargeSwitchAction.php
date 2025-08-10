<?php

namespace App\Admin\Actions;

use App\Events\UserStatus;
use App\Facades\CustomNotification;
use App\Models\Ban;
use App\Models\Bd;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Auth\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BdChargeSwitchAction extends RowAction
{

    public function name()
    {
        return $this->row->transfer_salary
            ? __('Enable Transfer Salary')
            : __('Disable Transfer Salary');
    }

    public function handle(Model $model)
    {
        Bd::where("id", $model->id)->update([
            'transfer_salary' => DB::raw('NOT transfer_salary')
        ]);

        $model->refresh(); 

        $message = $model->transfer_salary
            ? __('Disable Transfer Salary!')
            : __('Enable Transfer Salary!');

        $response = $model->transfer_salary ? 'success' : 'error';

        return $this->response()->$response($message)->refresh();
    }


    public function icon()
    {
        return $this->row->transfer_salary ? 'fa-toggle-off' : 'fa-toggle-on';
    }

    public function dialog()
    {
        $msg = $this->row->transfer_salary
            ? __('dashboard.confirm_enable_transfer_salary')
            : __('dashboard.confirm_disable_transfer_salary');

        $this->confirm($msg, '', []);
    }
}
