<?php

namespace Modules\TribeReward\Http\Controllers\web;

use App\Admin\Controllers\MainController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Modules\TribeReward\Entities\TribePeriod;

class TribePeriodController extends MainController
{
    public $permission_name = 'tribe-periods';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(__('Tribe Periods'))
            ->body($this->grid()));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(__('Tribe Periods'))
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(__('Tribe Periods'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(__('Tribe Periods'))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new TribePeriod());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('start_date', __('Start Date'));
        $grid->column('end_date', __('End Date'));

        if (Admin::user()->can('browse-tribe-tops') || Admin::user()->can('*')) {
            $grid->column(__('Procedures'))->display(function () {
                $url = url('admin/tribe_tops/'.$this->id);
                $gifts = __('Tribe Tops');
                return "<a href='{$url}' class='btn btn-sm btn-info'>{$gifts}</a>";
            });
        }

        if (method_exists($this, 'extendGrid')) {
            $this->extendGrid($grid);
        }

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(TribePeriod::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('start_date', __('Start Date'));
        $show->field('end_date', __('End Date'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new TribePeriod());

        $form->datetime('start_date', __('Start Date'))->required();
        $form->datetime('end_date', __('End Date'))->required();

        return $form;
    }
}
