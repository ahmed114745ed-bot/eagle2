<?php

namespace Modules\DailyPrize\Http\Controllers\web;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Controllers\MainController;
use Modules\DailyPrize\Entities\DailyGiftType;
use Encore\Admin\Layout\Content;

class DailyPrizeTypeController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'DailyGiftType';
    public $permission_name = 'daily-prize';


    public function index(Content $content)
    {
        return $content
            ->title(trans('daily prize'))
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->title(trans('daily prize'))
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title(trans('daily prize'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('daily prize'))
            ->body($this->form());
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DailyGiftType());

        $grid->column('type', __('Type'));
        $grid->column( __('procedures'))->display(function () {
            $url1 = url('admin/daily-gifts/'.$this->type);
            $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>".__('create')."</a>";
            return $button1;
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(DailyGiftType::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'));


        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DailyGiftType());

        $form->select('type', __('type'))->options([1 => 1, 2 => 2, 3 => 3, 4 => 4]);

        return $form;
    }
}
