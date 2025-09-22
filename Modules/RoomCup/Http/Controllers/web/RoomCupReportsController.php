<?php

namespace Modules\RoomCup\Http\Controllers\web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\RoomCup\Entities\RoomCupTarget;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;

class RoomCupReportsController extends AdminController
{
    protected $title = '';


    public function index(Content $content)
    {
        return parent::index($content
            ->title(__('Room Cup Targets'))
        );
    }

    protected function grid()
    {
        $grid = new Grid(new RoomCupTarget());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('total', __('Total'))->sortable();
        $grid->column('number_of_visitors', __('Number of Visitors'))->sortable();
        $grid->column('number_of_admins', __('Number of Admins'))->sortable();
        $grid->column('owner_profit', __('Owner Profit'))->sortable();
        $grid->column('admin_profit', __('Admin Profit'))->sortable();

        $grid->filter(function($filter) {
            $filter->between('total', __('Total'));
            $filter->between('number_of_visitors', __('Visitors Count'));
            $filter->between('number_of_admins', __('admins'));
        });

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(RoomCupTarget::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('total', __('Total'));
        $show->field('number_of_visitors', __('Number of Visitors'));
        $show->field('number_of_admins', __('Number of Admins'));
        $show->field('owner_profit', __('Owner Profit'));
        $show->field('admin_profit', __('Admin Profit'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new RoomCupTarget());

        $form->number('total', __('Total'))->default(0);
        $form->number('number_of_visitors', __('Number of Visitors'))->default(0);
        $form->number('number_of_admins', __('Number of Admins'))->default(0);
        $form->decimal('owner_profit', __('Owner Profit'))->default(0.00);
        $form->decimal('admin_profit', __('Admin Profit'))->default(0.00);

        return $form;
    }
}