<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Modules\Reals\Entities\Real;

class ReelController extends MainController
{
    use HasResourceActions;

    protected $title = 'Reels';

    public function index(Content $content)
    {
        return $content
            ->header(__('Reels'))
            ->description(__('admin.list'))
            ->body($this->grid());
    }

    public function show($id, Content $content)
    {
        return $content
            ->header(__('Reels'))
            ->description(__('admin.detail'))
            ->body($this->detail($id));
    }

    protected function grid()
    {
        $grid = new Grid(new Real());

        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('ID'))->sortable();
        $grid->column('user_id', __('User ID'));
        $grid->column('user.name', __('User'));
        $grid->column('description', __('Description'))->limit(50);
        $grid->column('url', __('Video'))->display(function ($url) {
            if (empty($url)) return '';
            return "<a href='{$url}' target='_blank'>View</a>";
        });
        $grid->column('like_num', __('Likes'));
        $grid->column('comment_num', __('Comments'));
        $grid->column('share_num', __('Shares'));
        $grid->column('created_at', __('admin.created_at'));

        $grid->filter(function ($filter) {
            $filter->like('user_id', __('User ID'));
            $filter->like('description', __('Description'));
        });

        $grid->disableCreateButton();
        $grid->disableExport();

        $grid->actions(function ($actions) {
            $actions->disableEdit();
        });

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Real::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('user_id', __('User ID'));
        $show->field('description', __('Description'));
        $show->field('url', __('URL'));
        $show->field('like_num', __('Likes'));
        $show->field('comment_num', __('Comments'));
        $show->field('share_num', __('Shares'));
        $show->field('created_at', __('admin.created_at'));
        $show->field('updated_at', __('admin.updated_at'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new Real());

        $form->display('id', __('ID'));
        $form->text('description', __('Description'));
        $form->display('created_at', __('admin.created_at'));
        $form->display('updated_at', __('admin.updated_at'));

        return $form;
    }
}
