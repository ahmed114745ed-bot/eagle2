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


class CanPlaySwitchAction extends RowAction
{
    public $name;

    public function __construct()
    {
        $this->name = __('Toggle Can Play');
    }

    public function handle(Model $model)
    {
        $model->can_play = !$model->can_play;
        $model->save();

        $msg = $model->can_play
            ? __('Can play has been enabled!')
            : __('Can play has been disabled!');

        $response = $model->can_play ? 'success' : 'error';

        return $this->response()->$response($msg)->refresh();
    }

    public function icon()
    {
        return $this->row->can_play ? 'fa-toggle-on' : 'fa-toggle-off';
    }
}
