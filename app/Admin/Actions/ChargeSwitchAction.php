<?php

namespace App\Admin\Actions;

use App\Events\UserStatus;
use App\Facades\CustomNotification;
use App\Models\Ban;
use App\Models\User;
use Encore\Admin\Actions\Action;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Auth\Permission;


class ChargeSwitchAction extends RowAction
{
    public $name;

    public function __construct()
    {
        $this->name = __('Toggle Transfer Salary');
    }

    public function handle(Model $model)
    {
        $model->transfer_salary = !$model->transfer_salary;
        $model->save();

        $message = $model->transfer_salary
            ? __('Transfer salary has been enabled!')
            : __('Transfer salary has been disabled!');

        $response = $model->transfer_salary ? 'success' : 'error';

        return $this->response()->$response($message)->refresh();
    }

    public function icon()
    {
        return $this->row->transfer_salary ? 'fa-toggle-on' : 'fa-toggle-off';
    }
}
