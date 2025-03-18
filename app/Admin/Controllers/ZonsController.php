<?php

namespace App\Admin\Controllers;

use App\Models\Zone;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ZonsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Zone';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Zone());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('coordinates', __('Coordinates'))->display(function ($coordinates) {
            return "{$coordinates['latitude']}, {$coordinates['longitude']}";
        });
        $grid->column('created_at', __('Created At'))->sortable();

        return $grid;
    }

    protected function form()
    {
        $form = new Form(new Zone());

        $form->text('name', __('Name'))->required();
        $form->decimal('latitude', __('Latitude'))->required();
        $form->decimal('longitude', __('Longitude'))->required();

        return $form;
    }
}
