<?php

namespace App\Selectables;

use App\Models\Gift;
use Modules\Vip\Entities\VipPrivilege;
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
        $this->column('img', __('Image'))->display(function ($path) {
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 10, 10);
        });
        $this->column('price', __('Price'));

        $this->filter(function (Filter $filter) {
            $filter->like('name');
        });
    }
}
