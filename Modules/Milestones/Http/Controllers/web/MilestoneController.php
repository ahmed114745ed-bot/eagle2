<?php

namespace Modules\Milestones\Http\Controllers\web;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\Milestones\Entities\Milestone;
use Encore\Admin\Layout\Content;

class MilestoneController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    // protected $title = 'Milestone';

    public function index(Content $content)
    {
       
        return $content
            ->header(__('Milestone'))
            ->description(__('Milestone'))
            ->body($this->grid());
    }

    protected function grid()
    {
        $grid = new Grid(new Milestone());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Name'));
        $grid->column('slug', __('type'));
        $grid->column('rewards', __('rewards'))->display(function () {
            $url =  admin_url("milestone-rewards/". $this->id);
            return "<a href='{$url}' class='btn btn-xs btn-info'>
                        <i class='fa fa-eye'></i> ".__('rewards')."
                    </a>";
        });
        $grid->column('created_at', __('Created at'))->display(function ($created_at) {
            return \Carbon\Carbon::parse($created_at)->format('Y-m-d');
        });

        $grid->filter(function ($filter) {
            $filter->like('name', __('Name'));
            $filter->equal('type', __('Type'));
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
        $show = new Show(Milestone::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('name', __('Name'));
        $show->field('type', __('Type'));
        $show->field('reward_achievement', __('Reward Achievement'));
        $show->field('expire', __('Expire'));
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
        $form = new Form(new Milestone());

        $form->text('name', __('Name'))->required();
        $form->select('slug', __('Type'))->options([
            'owner' => 'Owner',
            'host' => 'Host',
            'agency' => 'Agency',
            'family' => 'Family',
        ])->required();
        $form->switch('is_active', __('Active'))->default(1);

        return $form;
    }
}
