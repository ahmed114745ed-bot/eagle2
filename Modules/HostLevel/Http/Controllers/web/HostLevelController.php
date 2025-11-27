<?php

namespace Modules\HostLevel\Http\Controllers\web;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use Modules\HostLevel\Entities\HostLevel;

class HostLevelController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'HostLevel';
    public $permission_name = 'host_level';


    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans($this->title))
            ->body($this->grid()));
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
        return parent::show($id, $content
            ->title(trans($this->title))
            ->body($this->detail($id)));
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
        return parent::edit($id, $content
            ->title(trans($this->title))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans($this->title))
            ->body($this->form()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new HostLevel());

        $grid->column('id', __('Id'));
        $grid->column('img', __('Img'));
        $grid->column('level', __('Level'));
        if (Admin::user()->can('browse-' . 'host_level_reward') || Admin::user()->can('*')) {
                $grid->column(__('procedures'))->display(function () {

                    if (request()->filled('_export_')) {
                        return '';
                    }
                    $url1 = url('admin/host-level-reward/' . $this->id);
                    $gifts = __('gifts');
                    $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>" .   $gifts . "</a>";

                    return $button1;
                });
            }
            $this->extendGrid($grid);

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
        $show = new Show(HostLevel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('img', __('Img'));
        $show->field('level', __('Level'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new HostLevel());

        $form->image('img', __('Img'));
        $form->number('level', __('Level'));

        return $form;
    }
}
