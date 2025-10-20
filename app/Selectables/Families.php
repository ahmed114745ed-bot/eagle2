<?php

namespace App\Selectables;

use App\Models\Family;
use Encore\Admin\Grid\Filter;
use Modules\Vip\Entities\OVip;
use Encore\Admin\Grid\Selectable;

class Families extends Selectable
{

    public $model = Family::class;

    public function make()
    {
        $this->column('id');
        $this->column('name', __('name'));
        $this->column('image', __('img'))->display(function ($path) {
            /** @var OVip $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $this->filter(function (Filter $filter) {
            $filter->like('name');
        });
    }
}
