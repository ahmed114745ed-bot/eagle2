<?php

namespace App\Selectables;

use App\Models\Country;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;

class Countries extends Selectable
{
    public $model = Country::class;

    public function make()
    {
        $this->column('id', 'ID');
        $this->column('code', __('Code'));
        $this->column('name', __('Name'));

        $this->filter(function (Filter $filter) {
            $filter->like('name', __('Name'));
            $filter->like('code', __('Code'));
        });
    }
}
