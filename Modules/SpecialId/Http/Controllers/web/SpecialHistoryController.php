<?php

namespace Modules\SpecialId\Http\Controllers\web;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\SpecialId\Entities\SpecialHistory;
use Encore\Admin\Layout\Content;

class SpecialHistoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    public $permission_name = 'special-history';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('special-histories'))
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
        return $content
            ->title(trans('special-histories'))
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
            ->title(trans('special-histories'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('special-histories'))
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SpecialHistory());

        $grid->column('id', __('Id'));
        $grid->column('user.name', __('User'))->display(function () {
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($this->user?->profile?->avatar) ?? $defaultImage;
            $name = @$this->user?->name ?? '';
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            return '
                <div style="display: flex; align-items: center; gap: 10px;">
                    <img src="'.$url.'" alt="User Image" style="width: 40px; height: 40px;">
                    <div>
                        <a href="/admin/users/'.$this->user_id.'" style="text-decoration: none; color:rgb(253, 253, 253); font-weight: bold;">'.$name.'</a>
                        <div style="font-size: 12px; color: #fff;">' .'Uuid: '.$this->user?->uuid.'</div>
                    </div>
                </div>
            ';
        });
        $grid->column('ware.show_img', __('image'))->image('', 50);
        $grid->column('status', __('status'))->display(function ($status) {
         // استخدم الشهر والسنة كمعاملات إذا لزم الأمر
            return $status == 1 ? "<span class='label-success' " .'style="width: 8px;height: 8px;padding: 0;border-radius: 50%;display: inline-block;"'.
                "></span>" : "<span class='label-warning' " .'style="width: 8px;height: 8px;padding: 0;border-radius: 50%;display: inline-block;"'.
                "></span>";
        });

        $grid->column('created_at', trans('admin.created_at'))->diffForHumans ();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->disableCreateButton();

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
        $show = new Show(SpecialHistory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('status', __('Status'));
        $show->field('user_id', __('User id'));
        $show->field('ware_id', __('Ware id'));
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
        $form = new Form(new SpecialHistory());

        $form->switch('status', __('Status'));
        $form->number('user_id', __('User id'));
        $form->number('ware_id', __('Ware id'));

        return $form;
    }
}
