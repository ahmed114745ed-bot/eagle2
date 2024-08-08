<?php

namespace App\Selectables;

use App\Models\Gift;
use App\Models\VipPrivilege;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;

class Gifts extends Selectable
{

    public $model = Gift::class;

    public function make()
    {
        $this->grid->model()->orderBy('sort')->orderBy('type')->where('enable', 1);

        $this->column('id');
        $this->column('name');
        $this->column('img',__('Image'))->image();

        $this->filter(function (Filter $filter) {
            $filter->like('name');
        });
    }
}
