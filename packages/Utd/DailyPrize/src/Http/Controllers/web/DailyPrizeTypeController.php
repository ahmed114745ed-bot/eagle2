<?php

namespace Utd\DailyPrize\Http\Controllers\web;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Controllers\MainController;
use Utd\DailyPrize\Entities\DailyGiftType;
use Encore\Admin\Layout\Content;

class DailyPrizeTypeController extends MainController
{
    protected $title = 'DailyGiftType';
    public $permission_name = 'daily-prize';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('daily prize'))
            ->body($this->grid()));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('daily prize'))
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('daily prize'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('daily prize'))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new DailyGiftType());

        $grid->column('type', __('Type'))
            ->display(function ($type) {
                $weeks = [
                    1 => __('first_week'),
                    2 => __('second_week'),
                    3 => __('third_week'),
                    4 => __('fourth_week'),
                ];
                return $weeks[$type] ?? $type;
            });

        if (Admin::user()->can('browse-' . 'daily-gift') || Admin::user()->can('*')) {
            $grid->column(__('procedures'))->display(function () {
                $url1 = url('admin/daily-gifts/' . $this->type);
                $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>" . __('add a daily login gift') . "</a>";
                return $button1;
            });
        }
        $this->extendGrid($grid);
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(DailyGiftType::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new DailyGiftType());
        $this->disableFormTools($form);

        $form->select('type', __('type'))->options([
            1 => __('first_week'),
            2 => __('second_week'),
            3 => __('third_week'),
            4 => __('fourth_week'),
        ]);

        return $form;
    }
}
