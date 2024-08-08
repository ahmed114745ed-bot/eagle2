<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Modules\Moment\Entities\Moment;
use Encore\Admin\Grid\Displayers\Table;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form\Field\Table as FieldTable;
use Encore\Admin\Widgets\Table as WidgetsTable;

class MomentController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Moment';

    public $permission_name = 'moment';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    public function index(Content $content)
    {
        return $content
            ->title(__($this->title))
            ->row(function (Row $row) {
                $row->column(12, $this->grid2());
            })
            ->row(function ($row) {
                $row->column(12, $this->grid());
            });
    }
    protected function grid2()
    {
        $form = new Box();
        $form->view('admin.grid.common.moments');

        return $form;
    }
    protected function grid()
    {
        $grid = new Grid(new Moment());
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column('1/2', function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;

                    $query->whereHas('user', function ($query) use ($input) {
                        $query->where('name', 'like', "%$input%")
                        ->orWhere('uuid', 'like', "%$input%");
                    });
                }, __('User'))->placeholder(__('Search by name or UUID'));
            });
        });
        $grid->model()->orderByDesc('created_at');
        $grid->column('description', __('Description'));
        $grid->column('user.name', __('user_id'))->display(function ($name) {
            $uid = @$this->user->uuid;

            return "$name <br>
            <span style=\"color: #aaa; font-size: smaller;\">UID: $uid</span>";
        });
        $grid->column('comment_num', __('Stats'))->display(function ($commentNum) {
            $like = count(@$this->likes);
            $commentNum = count(@$this->comments);
            return "<span class=\"fa fa-comment\"> $commentNum</span>  <span class=\"fa fa-thumbs-up\"> $like</span> ";
        });
        $grid->column('created_at', __('Created at'))->sortable()->diffForHumans();
        $grid->column('img',__('Img'))->modal('show image' , function ($model, ) {
            $img = $model->img;
            if($img == null || $img == ''){
                return 'No image founded';
            }

            $img = getDriverUrl().'/'.$img;
            $img = "<img src='" . $img ."' style='width:500px;height:500px' class='img img-thumbnail'$ />";

            return (new WidgetsTable([''], [[$img]]));
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
        $show = new Show(Moment::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('description', __('Description'));
        $show->field('comment_num', __('Comment num'))->as(function () {
            return count($this->comments);
        });
        $show->field('like_num', __('Like num'))->as(function () {
            return count($this->likes);
        });
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('img', __('Img'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Moment());

        $form->number('user_id', __('User id'));
        $form->text('description', __('Description'));
        // $form->number('comment_num', __('Comment num'));
        // $form->number('like_num', __('Like num'));
        $form->image('img', __('Img'));

        return $form;
    }
}
