<?php

namespace App\Selectables;

use App\Models\Ware;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;
use Encore\Admin\Facades\Admin;

class WaresByType extends Selectable
{

    public $model = Ware::class;

    public function make()
    {
        $type = request('type_ware');

        if ($type) {
            $this->grid->model()->where('type', $type);
        } else {
            $this->grid->model()->whereIn('type', []);
        }

        $this->column('id', __('ID'));
        $this->column('name', __('Name'));
        $this->column('show_img', __('Show Image'))->image('', 30);
        $this->column('img2', __('Alternative Image'))->display(function ($path) {
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 10, 10);
        });
        $this->column('type', __('Type'))->select([
            4 => trans('Avatar Frame'),
            5 => trans('Bubble Frame'),
            6 => trans('Entering Special Effects'),
        ]);

        $this->filter(function (Filter $filter) {
            $filter->like('name', __('Name'));
            $filter->column(0.5, function ($filter) {
                $filter->equal('type', __('Type'))->select([
                    4 => trans('Avatar Frame'),
                    5 => trans('Bubble Frame'),
                    6 => trans('Entering Special Effects'),
                ]);
            });
        });
    
    }
}