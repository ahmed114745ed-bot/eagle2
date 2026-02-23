<?php

namespace Utd\Vip\Selectables;

use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;
use Utd\Vip\Entities\VipPrivilege;

class Privileges extends Selectable
{
    public $model = VipPrivilege::class;

    public function make()
    {
        $this->column('id');
        $this->column('name')->display(function ($name) {
            return app()->getLocale() === 'ar' ? $name : ($this->en_name ?? $name);
        });
        $this->column('img1', __('Image'))->image();

        $this->filter(function (Filter $filter) {
            $filter->like('name');
        });
    }
}
