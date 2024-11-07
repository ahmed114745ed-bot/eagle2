<?php

namespace App\Selectables;

use App\Models\Ware;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;
use Encore\Admin\Facades\Admin;

class Wares extends Selectable
{

    public $model = Ware::class;

    public function make()
    {
        $this->grid->model()->whereIn('type', [4, 5, 6]);
        $this->column('id');
        $this->column('name');
        $this->column('show_img', __('show_img'))->image('', 30);
        $this->column('img2', __('show_img'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 20, 20);
        });
        $this->column('type', __('type'))->select(
            [ 
                4 => trans('Avatar Frame'),
                5 => trans('Bubble Frame'),
                6 => trans('Entering Special Effects'),
            ]
        );

        $this->filter(function (Filter $filter) {
            $filter->like('name');
            $filter->column(1 / 2, function ($filter) {

                $filter->equal('type', __('type'))->select([
                   
                    4 => trans('Avatar Frame'),
                    5 => trans('Bubble Frame'),
                    6 => trans('Entering Special Effects'),
                ]);
            });
        });
        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");
    }
}