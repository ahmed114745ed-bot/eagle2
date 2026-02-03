<?php

namespace App\Selectables;

use App\Models\Gift;
use App\Models\User;
use Modules\Vip\Entities\VipPrivilege;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;

class Users extends Selectable
{

    public $model = User::class;

    public function make()
    {

        if (!request('id')) {
            $this->grid->model()->with('profile')->doesntHave('gamePercentage');
        }

        $this->column('id');
        $this->column('uuid');
        $this->column('name');
        $this->column('profile.avatar', __('Image'))->image();

        $this->filter(function (Filter $filter) {
            $filter->like('name');
        });
    }
}
