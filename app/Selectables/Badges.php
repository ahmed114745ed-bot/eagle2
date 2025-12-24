<?php

namespace App\Selectables;

use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;
use Modules\Badge\Entities\Badge;

class Badges extends Selectable
{

    public $model = Badge::class;

    public function make()
    {
        $this->column('id', __('ID'));
        $this->column('name', __('name'));
        $this->column('image', __('show image'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

         $this->column('show_image', __('image'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $this->column('priority', __('Priority'))->sortable();

        $this->filter(function (Filter $filter) {
            $filter->expand();
            $filter->like('name', __('name'));
            $filter->equal('priority', __('Priority'));
        });
    }
}
