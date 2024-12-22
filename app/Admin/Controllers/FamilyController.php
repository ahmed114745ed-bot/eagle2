<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\Family;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\FamilyUser;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use App\Services\AppFeatureService;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;

class FamilyController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'family';
    public $hiddenColumns = [];

    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("families");
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Family);

        $grid->id(__('ID'));
        //        $grid->is_success('is_success');
        $grid->column('image', __('image'))->image('', 30);
        $grid->column('name', __('name'));
        $grid->column('introduce', __('introduce'));
        $grid->column('notice', __('notice'));
        $grid->column('num', __('number of people'));
        $grid->column('user_id', __('user id'));
        $grid->column('speakswitch', __('speak switch'));
        $grid->column('status', __('status'));
        //        $grid->update_user_id('update_user_id');
        //        $grid->suctime('suctime');
        //        $grid->start_time('start_time');
        //        $grid->created_at(trans('admin.created_at'));
        //        $grid->updated_at(trans('admin.updated_at'));
        $this->extendGrid($grid);
        $grid->disableExport();
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
        $show = new Show(Family::findOrFail($id));

        $show->id(__('ID'));
        //        $show->is_success('is_success');
        $show->image(__('image'));
        $show->name(__('name'));
        $show->introduce(__('introduce'));
        $show->notice(__('notice'));
        $show->num(__('number of people'));
        $show->user_id(__('user id'));
        $show->speakswitch(__('speak switch'));
        $show->status(__('status'));
        //        $show->update_user_id('update_user_id');
        //        $show->suctime('suctime');
        //        $show->start_time('start_time');
        //        $show->created_at(trans('admin.created_at'));
        //        $show->updated_at(trans('admin.updated_at'));
        $this->extendShow($show);
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Family);

        $form->display('ID');
        $form->text('name', __('name'))->rules('required');
        $form->text('introduce', __('introduce'))->rules('required');
        $form->text('notice', __('notice'))->rules('required');
        $form->image('image', __('image'));
        $form->text('num', __('number of people'))->rules('required|integer|max:10000')->default('20');
        $form->select('user_id', __('user id'))->options(function ($value) {
            $ops2 = [];
            foreach (User::Where('id', $value)->get() as $user) {
                $ops2[$user->id] = $user->uuid . '_' . $user->name;
            }
            return $ops2;
        })->ajax('/api/search/users4', 'id', 'name')->rules('required');
        $form->hidden('is_success', 'is_success')->default(1)->rules('required');

        $form->saved(function (Form $form) {
            User::where('id', $form->model()->user_id)->update(['family_id' => $form->model()->id,]);
            FamilyUser::create([
                'user_id' => $form->model()->user_id,
                'family_id' => $form->model()->id,
                'user_type' => 2,
                'status' => 1,
            ]);
        });

        return $form;
    }
}
