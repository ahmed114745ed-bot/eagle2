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
        $this->column('price', __('price'))->display(function ($coin) {
            $path = 'coin.png';
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $this->filter(function (Filter $filter) {
            $filter->like('name');
        });
    }
}
